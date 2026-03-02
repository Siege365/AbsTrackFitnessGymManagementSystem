<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\PTPayment;
use App\Models\Membership;
use App\Models\GymPlan;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PTpaymentController extends Controller
{
    /**
     * PT Plan configuration - dynamically from database.
     */
    public static function planConfig()
    {
        $plans = GymPlan::active()->personalTraining()->ordered()->get();

        $config = [];
        foreach ($plans as $plan) {
            $config[$plan->plan_key] = [
                'price'     => (float) $plan->price,
                'duration'  => $plan->duration_days,
                'label'     => $plan->plan_name,
            ];
        }

        return $config;
    }

    /**
     * Store a new PT payment (New / Renew / Extend)
     */
    public function store(Request $request)
    {
        date_default_timezone_set('Asia/Manila');

        $validPlanTypes = implode(',', array_keys(self::planConfig()));

        // For 'new' payment type (first-time PT enrollment of existing gym member),
        // member_id refers to their Membership ID, not a Client ID
        $isNewPtClient = $request->payment_type === 'new' || $request->boolean('is_new_pt_client');

        $rules = [
            'payment_type' => 'required|in:new,renewal,extension',
            'plan_type'    => "required|in:{$validPlanTypes}",
            'payment_method' => 'required|in:Cash,Credit Card,Debit Card,GCash,PayMaya,Bank Transfer',
            'amount'       => 'required|numeric|min:0',
            'notes'        => 'nullable|string|max:1000',
        ];

        if ($isNewPtClient) {
            $rules['member_id'] = 'required|exists:memberships,id';
            $rules['member_name'] = 'required|string|max:255';
        } else {
            $rules['member_id'] = 'required|exists:clients,id';
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

            $planType   = $request->plan_type;
            $planConfig = self::planConfig()[$planType];
            $duration   = $planConfig['duration'];

            // First-time PT enrollment: create a Client (PT) record from the existing membership data
            if ($isNewPtClient) {
                $membership = Membership::findOrFail($request->member_id);

                // Check if a PT client record already exists for this gym member
                $client = Client::where('name', $membership->name)->first();

                if (!$client) {
                    // Find or create the customer link
                    $customer = \App\Models\Customer::where('name', $membership->name)->first();
                    if (!$customer) {
                        $customer = \App\Models\Customer::create([
                            'name'    => $membership->name,
                            'contact' => $membership->contact,
                        ]);
                    }

                    $client = Client::create([
                        'name'        => $membership->name,
                        'age'         => $request->member_age ?? $membership->age,
                        'sex'         => $request->member_sex ?? $membership->sex,
                        'contact'     => $request->member_contact ?? $membership->contact,
                        'plan_type'   => $planType,
                        'start_date'  => now(),
                        'due_date'    => now()->addDays($duration),
                        'customer_id' => $customer->id,
                    ]);
                }
            } else {
                $client = Client::findOrFail($request->member_id);
            }
            $previousDueDate = $client->due_date;
            $newDueDate = null;

            if ($request->payment_type === 'new') {
                // New PT enrollment: member's first PT plan, starts from today
                $newDueDate = $client->due_date; // already set during client creation above

            } elseif ($request->payment_type === 'renewal') {
                // Renewal: starts from today
                $newDueDate = now()->addDays($duration);

                $client->update([
                    'plan_type'  => $planType,
                    'start_date' => now(),
                    'due_date'   => $newDueDate,
                ]);

            } elseif ($request->payment_type === 'extension') {
                if (!$client->due_date) {
                    throw new \Exception('Cannot extend: Client has no due date.');
                }

                $previousPlanType = $client->plan_type;
                $planChanged = $previousPlanType !== $planType;

                if ($planChanged) {
                    $startFrom = Carbon::parse($client->due_date)->isFuture()
                        ? Carbon::parse($client->due_date)
                        : now();
                    $newDueDate = $startFrom->addDays($duration);
                } else {
                    $newDueDate = Carbon::parse($client->due_date)->addDays($duration);
                }

                $client->update([
                    'plan_type' => $planType,
                    'due_date'  => $newDueDate,
                ]);
            }

            // Generate receipt number
            $receiptNumber = $this->generateReceiptNumber();

            // Create payment record
            $payment = PTPayment::create([
                'receipt_number'    => $receiptNumber,
                'client_id'         => $client->id,
                'member_name'       => $client->name,
                'plan_type'         => $planType,
                'payment_type'      => $request->payment_type,
                'payment_method'    => $request->payment_method,
                'amount'            => $request->amount,
                'duration_days'     => $duration,
                'previous_due_date' => $previousDueDate,
                'new_due_date'      => $newDueDate,
                'notes'             => $request->notes,
                'processed_by'      => Auth::user()->name ?? 'Admin',
            ]);

            DB::commit();

            ActivityLog::log('created', 'pt_payment', "Processed PT payment #{$payment->receipt_number} for {$payment->member_name} ({$request->payment_type}) — ₱" . number_format($payment->amount, 2), $payment->receipt_number, $payment->member_name, $payment, ['plan_type' => $payment->plan_type, 'payment_type' => $request->payment_type, 'amount' => $payment->amount, 'duration_days' => $payment->duration_days]);

            $messages = [
                'new'       => 'New PT client enrolled successfully!',
                'renewal'   => 'PT plan renewed successfully!',
                'extension' => 'PT plan extended successfully!',
            ];
            $message = $messages[$request->payment_type] ?? 'PT Payment processed successfully!';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'payment' => [
                        'id'             => $payment->id,
                        'receipt_number' => $payment->receipt_number,
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
     * Get receipt data for a specific PT payment
     */
    public function receiptData($id)
    {
        try {
            date_default_timezone_set('Asia/Manila');

            $payment = PTPayment::with('client')->findOrFail($id);

            return response()->json([
                'success'          => true,
                'receipt_number'   => $payment->receipt_number,
                'member_name'      => $payment->member_name,
                'member_contact'   => $payment->client->contact ?? 'N/A',
                'plan_type'        => $payment->plan_type,
                'payment_type'     => $payment->payment_type,
                'payment_method'   => $payment->payment_method,
                'amount'           => $payment->amount,
                'duration'         => $payment->duration_days,
                'previous_due_date'=> $payment->previous_due_date
                    ? Carbon::parse($payment->previous_due_date)->format('F d, Y')
                    : null,
                'new_due_date'     => $payment->new_due_date
                    ? Carbon::parse($payment->new_due_date)->format('F d, Y')
                    : null,
                'notes'            => $payment->notes,
                'formatted_date'   => Carbon::parse($payment->created_at)->setTimezone('Asia/Manila')->format('F d, Y - h:i A'),
                'is_refunded'      => $payment->is_refunded,
                'refund_status'    => $payment->refund_status,
                'refunded_amount'  => $payment->refunded_amount,
                'refunded_at'      => $payment->refunded_at
                    ? Carbon::parse($payment->refunded_at)->format('F d, Y - h:i A')
                    : null,
                'refund_reason'    => $payment->refund_reason,
                'refunded_by'      => $payment->refunded_by,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load receipt: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Search for PT clients (only members with active membership)
     */
    public function searchClients(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        // Get active membership member IDs (only active membership holders can avail PT)
        $activeMembershipNames = Membership::where(function ($q) {
            $q->whereDate('due_date', '>=', now());
        })->pluck('name')->toArray();

        $clients = Client::where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('contact', 'LIKE', "%{$query}%");
            })
            ->whereIn('name', $activeMembershipNames)
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(function ($client) {
                return [
                    'id'        => $client->id,
                    'name'      => $client->name,
                    'contact'   => $client->contact,
                    'plan_type' => $client->plan_type,
                    'due_date'  => $client->due_date ? $client->due_date->format('Y-m-d') : null,
                    'status'    => $client->status,
                ];
            });

        return response()->json($clients);
    }

    /**
     * Unified PT client search.
     *
     * Returns two kinds of results:
     *  1. Existing PT clients (from the `clients` table) — standalone or previously enrolled.
     *     These are returned with source = 'client' and has_pt_client = true.
     *  2. Gym members (from `memberships`) who have NO PT client record yet — eligible for
     *     new PT enrollment.  Returned with source = 'membership' and has_pt_client = false.
     *
     * This way both walk-in/standalone PT clients and regular gym members appear together.
     */
    public function searchActiveMembers(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $results = collect();

        // ── 1. Existing PT clients ──────────────────────────────────────────
        $clients = Client::where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('contact', 'LIKE', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(function (Client $client) {
                // Check if they also have a membership record
                $membership = Membership::where('name', $client->name)->first();
                return [
                    'source'              => 'client',
                    'id'                  => $client->id,   // clients.id — used as member_id when is_new_pt_client=0
                    'name'                => $client->name,
                    'contact'             => $client->contact,
                    'age'                 => $client->age,
                    'sex'                 => $client->sex,
                    'has_pt_client'       => true,
                    'pt_client_id'        => $client->id,
                    'pt_plan_type'        => $client->plan_type,
                    'pt_due_date'         => $client->due_date ? $client->due_date->format('Y-m-d') : null,
                    'pt_status'           => $client->status,
                    'membership_status'   => $membership?->status ?? null,
                    'membership_due_date' => $membership?->due_date ? $membership->due_date->format('Y-m-d') : null,
                ];
            });

        $results = $results->concat($clients);

        // ── 2. Gym members WITHOUT an existing PT client record ─────────────
        // Collect names already covered by the clients query so we don't duplicate.
        $existingClientNames = $clients->pluck('name')->map('strtolower')->toArray();

        $members = Membership::where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('contact', 'LIKE', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->filter(function (Membership $member) use ($existingClientNames) {
                // Skip if already represented as a PT client above
                return !in_array(strtolower($member->name), $existingClientNames, true)
                    && !Client::where('name', $member->name)->exists();
            })
            ->map(function (Membership $member) {
                return [
                    'source'              => 'membership',
                    'id'                  => $member->id,   // memberships.id — used when is_new_pt_client=1
                    'name'                => $member->name,
                    'contact'             => $member->contact,
                    'age'                 => $member->age,
                    'sex'                 => $member->sex,
                    'has_pt_client'       => false,
                    'pt_client_id'        => null,
                    'pt_plan_type'        => null,
                    'pt_due_date'         => null,
                    'pt_status'           => null,
                    'membership_status'   => $member->status,
                    'membership_due_date' => $member->due_date ? $member->due_date->format('Y-m-d') : null,
                ];
            });

        $results = $results->concat($members)->sortBy('name')->values();

        return response()->json($results);
    }

    /**
     * Generate a unique receipt number for PT payments
     */
    private function generateReceiptNumber()
    {
        $lastPayment = PTPayment::latest('id')->first();
        $nextId = $lastPayment ? $lastPayment->id + 1 : 1;
        return 'PT-' . date('Ymd') . '-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
    }
}
