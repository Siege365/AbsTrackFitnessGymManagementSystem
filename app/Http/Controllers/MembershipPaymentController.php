<?php

namespace App\Http\Controllers;

use App\Models\Membership;
use App\Models\MembershipPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Services\RefundService;

class MembershipPaymentController extends Controller
{
    protected $refundService;

    public function __construct(RefundService $refundService)
    {
        $this->refundService = $refundService;
    }

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

        // Refunded Today Stats
        $refundedToday = MembershipPayment::whereNotNull('refunded_at')
            ->whereDate('refunded_at', today())
            ->sum('amount');

        $refundedTodayCount = MembershipPayment::whereNotNull('refunded_at')
            ->whereDate('refunded_at', today())
            ->count();

        // Total Refunded Stats
        $totalRefunded = MembershipPayment::whereNotNull('refunded_at')
            ->sum('amount');

        $totalRefundedCount = MembershipPayment::whereNotNull('refunded_at')
            ->count();

        $todayRevenue = MembershipPayment::whereNull('refunded_at')
            ->whereDate('created_at', today())
            ->sum('amount');

        // Get transaction history with search and filters
        $query = MembershipPayment::with('membership');

        // Apply search filter
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('receipt_number', 'LIKE', "%{$search}%")
                  ->orWhere('member_name', 'LIKE', "%{$search}%")
                  ->orWhereHas('membership', function($memberQuery) use ($search) {
                      $memberQuery->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Apply plan type filter
        if ($request->has('filter_plan') && $request->filter_plan != '') {
            $query->where('plan_type', $request->filter_plan);
        }

        // Apply payment method filter
        if ($request->has('filter_method') && $request->filter_method != '') {
            $query->where('payment_method', $request->filter_method);
        }

        // Apply sorting
        $sort = $request->get('sort', 'date_newest');
        switch($sort) {
            case 'date_oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'name_asc':
                $query->orderBy('member_name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('member_name', 'desc');
                break;
            case 'date_newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $transactions = $query->paginate(10)->withQueryString();

        return view('PaymentAndBillings.MembershipPayment', compact(
            'monthlyRevenue',
            'refundedToday',
            'refundedTodayCount',
            'totalRefunded',
            'totalRefundedCount',
            'todayRevenue',
            'transactions'
        ));
    }

    /**
     * Store a new membership payment
     */
    public function store(Request $request)
    {
        // Set timezone to Philippine Time
        date_default_timezone_set('Asia/Manila');

        // Validation rules based on payment type
        $rules = [
            'payment_type' => 'required|in:new,renewal,extension',
            'plan_type' => 'required|in:Monthly,Session',
            'payment_method' => 'required|in:Cash,Credit Card,Debit Card,GCash,PayMaya,Bank Transfer',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ];

        // Add conditional validation based on payment type
        if ($request->payment_type === 'new') {
            $rules['new_member_name'] = 'required|string|max:255';
            $rules['new_member_contact'] = ['required', 'regex:/^(09\d{9}|\+639\d{9})$/'];
            $rules['new_member_avatar'] = 'nullable|image|max:2048';
        } else {
            $rules['member_id'] = 'required|exists:memberships,id';
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
            $previousDueDate = null;
            $newDueDate = null;
            $duration = 0;

            // Calculate duration based on plan type
            if ($planType === 'Monthly') {
                $duration = 30;
            } elseif ($planType === 'Session') {
                $duration = 1;
            }

            $member = null;

            // Handle different payment types
            if ($request->payment_type === 'new') {
                // Handle avatar upload
                $avatarPath = null;
                if ($request->hasFile('new_member_avatar')) {
                    $avatarPath = $request->file('new_member_avatar')->store('avatars', 'public');
                }

                // Create new member
                $member = Membership::create([
                    'name' => $request->new_member_name,
                    'contact' => $request->new_member_contact,
                    'avatar' => $avatarPath,
                    'plan_type' => $planType,
                    'start_date' => now(),
                    'due_date' => now()->addDays($duration),
                    'status' => 'Active',
                ]);

                $newDueDate = $member->due_date;

            } else {
                // Get existing member
                $member = Membership::findOrFail($request->member_id);
                $previousDueDate = $member->due_date;
                $previousPlanType = $member->plan_type;

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
                }
            }

            // Generate receipt number
            $receiptNumber = $this->generateReceiptNumber();

            // Create payment record
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

            DB::commit();

            $message = 'Payment processed successfully! ';
            if ($request->payment_type === 'new') {
                $message .= 'New member registered.';
            } elseif ($request->payment_type === 'renewal') {
                $message .= 'Membership renewed.';
            } else {
                $message .= 'Membership extended.';
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'payment' => $payment
                ]);
            }

            return redirect()->route('membership.payment.index')->with('success', $message);

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
     * Process a refund for a payment
     */
    public function refund(Request $request, $id)
    {
        date_default_timezone_set('Asia/Manila');

        $validated = $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        try {
            $result = $this->refundService->refundMembershipPayment($id, [
                'reason' => $validated['reason'] ?? null,
            ]);

            if ($request->expectsJson()) {
                return response()->json($result);
            }

            return redirect()->route('membership.payment.index')
                ->with('success', $result['message']);

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
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

            return response()->json([
                'success' => true,
                'receipt_number' => $payment->receipt_number,
                'member_name' => $payment->member_name,
                'member_contact' => $payment->membership->contact ?? 'N/A',
                'plan_type' => $payment->plan_type,
                'payment_type' => $payment->payment_type,
                'payment_method' => $payment->payment_method,
                'amount' => $payment->amount,
                'duration' => $payment->duration_days,
                'previous_due_date' => $payment->previous_due_date 
                    ? Carbon::parse($payment->previous_due_date)->format('F d, Y') 
                    : null,
                'new_due_date' => $payment->new_due_date 
                    ? Carbon::parse($payment->new_due_date)->format('F d, Y') 
                    : null,
                'notes' => $payment->notes,
                'formatted_date' => Carbon::parse($payment->created_at)->setTimezone('Asia/Manila')->format('F d, Y - h:i A'),
                'refunded_at' => $payment->refunded_at 
                    ? Carbon::parse($payment->refunded_at)->format('F d, Y - h:i A') 
                    : null,
                'refund_reason' => $payment->refund_reason,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load receipt: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Delete a payment transaction
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $payment = MembershipPayment::findOrFail($id);
            $member = Membership::find($payment->membership_id);

            if ($member) {
                // Find the latest non-deleted, non-refunded payment
                $latestPayment = MembershipPayment::where('membership_id', $member->id)
                    ->where('id', '!=', $payment->id)
                    ->whereNull('refunded_at')
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($latestPayment) {
                    $member->update([
                        'due_date' => $latestPayment->new_due_date,
                        'plan_type' => $latestPayment->plan_type,
                        'status' => Carbon::parse($latestPayment->new_due_date)->isFuture() 
                            ? 'Active' 
                            : 'Expired',
                    ]);
                } else {
                    $member->update([
                        'status' => 'Expired',
                        'due_date' => null,
                    ]);
                }
            }

            $payment->delete();

            DB::commit();

            return back()->with('success', 'Transaction deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete transaction: ' . $e->getMessage()]);
        }
    }

    /**
     * Bulk delete payments
     */
    public function bulkDelete(Request $request)
    {
        $ids = json_decode($request->ids, true);

        if (!is_array($ids) || empty($ids)) {
            return back()->withErrors(['error' => 'No transactions selected.']);
        }

        try {
            DB::beginTransaction();

            foreach ($ids as $id) {
                $payment = MembershipPayment::find($id);
                if ($payment) {
                    $member = Membership::find($payment->membership_id);

                    if ($member) {
                        $latestPayment = MembershipPayment::where('membership_id', $member->id)
                            ->where('id', '!=', $payment->id)
                            ->whereNull('refunded_at')
                            ->orderBy('created_at', 'desc')
                            ->first();

                        if ($latestPayment) {
                            $member->update([
                                'due_date' => $latestPayment->new_due_date,
                                'plan_type' => $latestPayment->plan_type,
                                'status' => Carbon::parse($latestPayment->new_due_date)->isFuture() 
                                    ? 'Active' 
                                    : 'Expired',
                            ]);
                        } else {
                            $member->update([
                                'status' => 'Expired',
                                'due_date' => null,
                            ]);
                        }
                    }

                    $payment->delete();
                }
            }

            DB::commit();

            return back()->with('success', count($ids) . ' transaction(s) deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete transactions: ' . $e->getMessage()]);
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