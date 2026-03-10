<?php

namespace App\Http\Controllers;

use App\Models\Membership;
use App\Models\MembershipPayment;
use App\Models\GymPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Services\NotificationService;

class MembershipPaymentController extends Controller
{

    /**
     * Display the membership payment page with statistics and transaction history
     */
    public function index(Request $request)
    {
        // Set timezone to Philippine Time
        date_default_timezone_set('Asia/Manila');

        // Calculate statistics
        $monthlyRevenue = MembershipPayment::whereNull('refunded_at')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        $todayRevenue = MembershipPayment::whereNull('refunded_at')
            ->whereDate('created_at', today())
            ->sum('amount');

        $transactionCount = MembershipPayment::whereDate('created_at', today())->count();

        // Dynamic plans from configuration
        $membershipPlans = GymPlan::active()->membership()->ordered()->get();

        return view('PaymentAndBillings.membership-payment', compact(
            'monthlyRevenue',
            'todayRevenue',
            'transactionCount',
            'membershipPlans'
        ));
    }

    /**
     * Plan type configuration - dynamically from database.
     * Returns an associative array keyed by plan_key for backward compatibility.
     */
    public static function planConfig()
    {
        $plans = GymPlan::active()->membership()->ordered()->get();

        $config = [];
        foreach ($plans as $plan) {
            $config[$plan->plan_key] = [
                'price'            => (float) $plan->price,
                'duration'         => $plan->duration_days,
                'label'            => $plan->plan_name,
                'requires_student' => $plan->requires_student,
                'requires_buddy'   => $plan->requires_buddy,
                'buddy_count'      => $plan->buddy_count,
            ];
        }

        return $config;
    }

    /**
     * Store a new membership payment
     */
    public function store(Request $request)
    {
        // Set timezone to Philippine Time
        date_default_timezone_set('Asia/Manila');

        $validPlanTypes = implode(',', array_keys(self::planConfig()));

        // Validation rules based on payment type
        $rules = [
            'payment_type' => 'required|in:new,renewal,extension',
            'plan_type' => "required|in:{$validPlanTypes}",
            'payment_method' => 'required|in:Cash,Credit Card,Debit Card,GCash,PayMaya,Bank Transfer',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ];

        // Add conditional validation based on payment type
        if ($request->payment_type === 'new') {
            $rules['new_member_name'] = 'required|string|max:255';
            $rules['new_member_contact'] = ['required', 'regex:/^(09\d{9}|\+639\d{9})$/'];
            $rules['new_member_avatar'] = 'nullable|image|max:2048';

            // Student plan requires student_id
            if ($request->plan_type === 'Student') {
                $rules['student_id'] = 'required|string|max:100';
            }

            // Gym Buddy plan requires buddy info
            if ($request->plan_type === 'GymBuddy') {
                $rules['buddy_name'] = 'required|string|max:255';
                $rules['buddy_contact'] = ['required', 'regex:/^(09\d{9}|\+639\d{9})$/'];
            }
        } else {
            $rules['member_id'] = 'required|exists:memberships,id';

            // Gym Buddy renewal/extension also requires buddy
            if ($request->plan_type === 'GymBuddy') {
                $rules['buddy_member_id'] = 'required|exists:memberships,id';
            }
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $planType = $request->plan_type;
            $planConfig = self::planConfig()[$planType];
            $previousDueDate = null;
            $buddyPreviousDueDate = null;
            $newDueDate = null;
            $duration = $planConfig['duration'];

            // Validate student plan for renewals/extensions
            if ($planType === 'Student' && $request->payment_type !== 'new') {
                $existingMember = Membership::findOrFail($request->member_id);
                if (!$existingMember->is_student) {
                    throw new \Exception('Student rate is only available for members registered as students.');
                }
            }

            $member = null;
            $buddyMember = null;

            // Handle different payment types
            if ($request->payment_type === 'new') {
                // Handle avatar upload
                $avatarPath = null;
                if ($request->hasFile('new_member_avatar')) {
                    $avatarPath = $request->file('new_member_avatar')->store('avatars', 'public');
                }

                // Determine if student (from toggle checkbox)
                $isStudent = $request->has('member1_is_student') && $request->member1_is_student;

                // Create new member
                $member = Membership::create([
                    'name' => $request->new_member_name,
                    'contact' => $request->new_member_contact,
                    'age' => $request->new_member_age,
                    'sex' => $request->new_member_sex,
                    'avatar' => $avatarPath,
                    'plan_type' => $planType,
                    'start_date' => now(),
                    'due_date' => now()->addDays($duration),
                    'status' => 'Active',
                    'is_student' => $isStudent,
                    'student_id' => $isStudent ? $request->student_id : null,
                ]);

                $newDueDate = $member->due_date;

                // Handle Gym Buddy: create second member with full details
                if ($planType === 'GymBuddy') {
                    $buddyAvatarPath = null;
                    if ($request->hasFile('buddy_avatar')) {
                        $buddyAvatarPath = $request->file('buddy_avatar')->store('avatars', 'public');
                    }

                    $buddyIsStudent = $request->has('buddy_is_student') && $request->buddy_is_student;

                    $buddyMember = Membership::create([
                        'name' => $request->buddy_name,
                        'contact' => $request->buddy_contact,
                        'age' => $request->buddy_age,
                        'sex' => $request->buddy_sex,
                        'avatar' => $buddyAvatarPath,
                        'plan_type' => $planType,
                        'start_date' => now(),
                        'due_date' => now()->addDays($duration),
                        'status' => 'Active',
                        'is_student' => $buddyIsStudent,
                        'student_id' => $buddyIsStudent ? $request->buddy_student_id : null,
                    ]);
                }

            } else {
                // Get existing member
                $member = Membership::findOrFail($request->member_id);
                $previousDueDate = $member->due_date;
                $previousPlanType = $member->plan_type;

                // Handle Gym Buddy: get buddy member
                if ($planType === 'GymBuddy') {
                    $buddyMember = Membership::findOrFail($request->buddy_member_id);
                    $buddyPreviousDueDate = $buddyMember->due_date;
                }

                // Prevent renewal if member is active
                if ($request->payment_type === 'renewal') {
                    if ($member->status === 'Active' && $member->due_date && Carbon::parse($member->due_date)->isFuture()) {
                        throw new \Exception('Member is active. Please use Extension instead of Renewal.');
                    }

                    $planChanged = $previousPlanType !== $planType;
                    
                    if ($member->due_date && Carbon::parse($member->due_date)->isFuture() && !$planChanged) {
                        $newDueDate = Carbon::parse($member->due_date)->addDays($duration);
                    } else {
                        $newDueDate = now()->addDays($duration);
                    }

                    $member->update([
                        'status' => 'Active',
                        'plan_type' => $planType,
                        'start_date' => ($member->due_date && Carbon::parse($member->due_date)->isFuture() && !$planChanged)
                            ? $member->start_date 
                            : now(),
                        'due_date' => $newDueDate,
                    ]);

                    // Update buddy member too
                    if ($buddyMember) {
                        $buddyMember->update([
                            'status' => 'Active',
                            'plan_type' => $planType,
                            'start_date' => now(),
                            'due_date' => $newDueDate,
                        ]);
                    }

                } elseif ($request->payment_type === 'extension') {
                    if (!$member->due_date) {
                        throw new \Exception('Cannot extend: Member has no due date.');
                    }

                    $planChanged = $previousPlanType !== $planType;
                    
                    if ($planChanged) {
                        $startFrom = Carbon::parse($member->due_date)->isFuture() 
                            ? Carbon::parse($member->due_date) 
                            : now();
                        $newDueDate = $startFrom->addDays($duration);
                    } else {
                        $newDueDate = Carbon::parse($member->due_date)->addDays($duration);
                    }

                    $member->update([
                        'status' => 'Active',
                        'plan_type' => $planType,
                        'due_date' => $newDueDate,
                    ]);

                    // Update buddy member too
                    if ($buddyMember) {
                        $buddyDueDate = Carbon::parse($buddyMember->due_date)->isFuture()
                            ? Carbon::parse($buddyMember->due_date)->addDays($duration)
                            : now()->addDays($duration);
                        $buddyMember->update([
                            'status' => 'Active',
                            'plan_type' => $planType,
                            'due_date' => $buddyDueDate,
                        ]);
                    }
                }
            }

            // Generate receipt number
            $receiptNumber = $this->generateReceiptNumber();

            // Create payment record(s)
            if ($planType === 'GymBuddy' && $buddyMember) {
                // GymBuddy: create TWO payment records (one per member) with split amount
                $halfAmount = $request->amount / 2;
                $buddyNewDueDate = $buddyMember->due_date ?? $newDueDate;

                $payment = MembershipPayment::create([
                    'receipt_number' => $receiptNumber,
                    'membership_id' => $member->id,
                    'member_name' => $member->name,
                    'plan_type' => $planType,
                    'payment_type' => $request->payment_type,
                    'payment_method' => $request->payment_method,
                    'amount' => $halfAmount,
                    'duration_days' => $duration,
                    'previous_due_date' => $previousDueDate,
                    'new_due_date' => $newDueDate,
                    'notes' => $request->notes,
                    'processed_by' => Auth::user()->name ?? 'Admin',
                    'buddy_member_id' => $buddyMember->id,
                    'buddy_name' => $buddyMember->name,
                    'buddy_contact' => $buddyMember->contact ?? $request->buddy_contact,
                ]);

                $buddyPayment = MembershipPayment::create([
                    'receipt_number' => $receiptNumber,
                    'membership_id' => $buddyMember->id,
                    'member_name' => $buddyMember->name,
                    'plan_type' => $planType,
                    'payment_type' => $request->payment_type,
                    'payment_method' => $request->payment_method,
                    'amount' => $halfAmount,
                    'duration_days' => $duration,
                    'previous_due_date' => $buddyPreviousDueDate,
                    'new_due_date' => $buddyNewDueDate,
                    'notes' => $request->notes,
                    'processed_by' => Auth::user()->name ?? 'Admin',
                    'buddy_member_id' => $member->id,
                    'buddy_name' => $member->name,
                    'buddy_contact' => $member->contact ?? $request->new_member_contact,
                ]);
            } else {
                $payment = MembershipPayment::create([
                    'receipt_number' => $receiptNumber,
                    'membership_id' => $member->id,
                    'member_name' => $member->name,
                    'plan_type' => $planType,
                    'payment_type' => $request->payment_type,
                    'payment_method' => $request->payment_method,
                    'amount' => $request->amount,
                    'duration_days' => $duration,
                    'previous_due_date' => $previousDueDate,
                    'new_due_date' => $newDueDate,
                    'notes' => $request->notes,
                    'processed_by' => Auth::user()->name ?? 'Admin',
                ]);
            }

            DB::commit();

            // Send payment notification
            NotificationService::paymentReceived($member->name, $request->amount, 'membership');

            // If new member was created via payment, send membership notification too
            if ($request->payment_type === 'new') {
                NotificationService::newMembership($member->name, $planType, 'member');
            }

            $message = 'Payment processed successfully! ';
            if ($request->payment_type === 'new') {
                $message .= 'New member registered.';
                if ($buddyMember) {
                    $message .= ' Gym buddy also registered.';
                }
            } elseif ($request->payment_type === 'renewal') {
                $message .= 'Membership renewed.';
            } else {
                $message .= 'Membership extended.';
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'payment' => [
                        'id' => $payment->id,
                        'receipt_number' => $payment->receipt_number ?? $payment->id,
                    ]
                ]);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Get receipt data for a specific payment
     */
    public function receiptData($id)
    {
        try {
            date_default_timezone_set('Asia/Manila');

            $payment = MembershipPayment::with('membership')->findOrFail($id);

            // For GymBuddy, find the buddy's linked payment record
            $buddyPaymentData = null;
            if ($payment->plan_type === 'GymBuddy' && $payment->buddy_member_id) {
                $buddyPay = MembershipPayment::where('receipt_number', $payment->receipt_number)
                    ->where('membership_id', $payment->buddy_member_id)
                    ->first();
                if ($buddyPay) {
                    $buddyPaymentData = [
                        'member_name' => $buddyPay->member_name,
                        'amount' => $buddyPay->amount,
                        'new_due_date' => $buddyPay->new_due_date ? Carbon::parse($buddyPay->new_due_date)->format('F d, Y') : null,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'id' => $payment->id,
                'receipt_number' => $payment->receipt_number,
                'member_name' => $payment->member_name,
                'member_contact' => $payment->membership->contact ?? 'N/A',
                'plan_type' => $payment->plan_type,
                'payment_type' => $payment->payment_type,
                'payment_method' => $payment->payment_method,
                'amount' => $payment->amount,
                'duration' => $payment->duration_days,
                'processed_by' => $payment->processed_by,
                'previous_due_date' => $payment->previous_due_date 
                    ? Carbon::parse($payment->previous_due_date)->format('F d, Y') 
                    : null,
                'new_due_date' => $payment->new_due_date 
                    ? Carbon::parse($payment->new_due_date)->format('F d, Y') 
                    : null,
                'notes' => $payment->notes,
                'formatted_date' => Carbon::parse($payment->created_at)->setTimezone('Asia/Manila')->format('F d, Y - h:i A'),
                'is_refunded' => $payment->is_refunded,
                'refund_status' => $payment->refund_status,
                'refunded_amount' => $payment->refunded_amount,
                'refunded_at' => $payment->refunded_at 
                    ? Carbon::parse($payment->refunded_at)->format('F d, Y - h:i A') 
                    : null,
                'refund_reason' => $payment->refund_reason,
                'refunded_by' => $payment->refunded_by,
                'buddy_member_id' => $payment->buddy_member_id,
                'buddy_name' => $payment->buddy_name,
                'buddy_contact' => $payment->buddy_contact,
                'buddy_payment' => $buddyPaymentData,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load receipt: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Generate a unique receipt number
     */
    private function generateReceiptNumber()
    {
        $lastPayment = MembershipPayment::latest('id')->first();
        $nextId = $lastPayment ? $lastPayment->id + 1 : 1;
        return 'MEM-' . date('Ymd') . '-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
    }
}