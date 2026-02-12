<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\MembershipPayment;
use App\Models\PaymentItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class PaymentHistoryController extends Controller
{
    public function index(Request $request)
    {
        // Build base queries
        $productQuery = Payment::with('items')->orderBy('created_at', 'desc');
        $membershipQuery = MembershipPayment::orderBy('created_at', 'desc');

        // Search filter
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $productQuery->where(function($q) use ($search) {
                $q->where('receipt_number', 'LIKE', "%{$search}%")
                  ->orWhere('customer_name', 'LIKE', "%{$search}%");
            });

            $membershipQuery->where(function($q) use ($search) {
                $q->where('receipt_number', 'LIKE', "%{$search}%")
                  ->orWhere('member_name', 'LIKE', "%{$search}%");
            });
        }

        // Type filter (product/membership/all)
        $filterType = $request->get('filter_type', 'all');

        // Paginate lists
        $productPayments = $productQuery->paginate(10, ['*'], 'product_page')->withQueryString();
        $membershipPayments = $membershipQuery->paginate(10, ['*'], 'membership_page')->withQueryString();

        // Build combined refunded list
        $refProd = Payment::whereNotNull('refunded_at');
        $refMem = MembershipPayment::whereNotNull('refunded_at');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $refProd->where(function($q) use ($search) {
                $q->where('receipt_number', 'LIKE', "%{$search}%")
                  ->orWhere('customer_name', 'LIKE', "%{$search}%");
            });
            $refMem->where(function($q) use ($search) {
                $q->where('receipt_number', 'LIKE', "%{$search}%")
                  ->orWhere('member_name', 'LIKE', "%{$search}%");
            });
        }

        if ($filterType === 'product') {
            $refMem = MembershipPayment::whereRaw('1=0');
        } elseif ($filterType === 'membership') {
            $refProd = Payment::whereRaw('1=0');
        }

        $refProdList = $refProd->get()->map(function($p) {
            return (object)[
                'id' => $p->id,
                'receipt_number' => $p->receipt_number,
                'name' => $p->customer_name,
                'refunded_at' => $p->refunded_at,
                'amount' => $p->total_amount,
                'refund_reason' => $p->refund_reason,
                'refunded_by' => $p->refunded_by,
                'type' => 'Product'
            ];
        });

        $refMemList = $refMem->get()->map(function($m) {
            return (object)[
                'id' => $m->id,
                'receipt_number' => $m->receipt_number,
                'name' => $m->member_name,
                'refunded_at' => $m->refunded_at,
                'amount' => $m->amount,
                'refund_reason' => $m->refund_reason,
                'refunded_by' => $m->refunded_by,
                'type' => 'Membership'
            ];
        });

        $combined = $refProdList->merge($refMemList)->sortByDesc('refunded_at')->values();

        // Simple paginator for combined refunds
        $page = $request->get('refunded_page', 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        $itemsForCurrentPage = $combined->slice($offset, $perPage)->all();

        $combinedRefunds = new LengthAwarePaginator(
            $itemsForCurrentPage,
            $combined->count(),
            $perPage,
            $page,
            ['path' => url()->current(), 'query' => $request->query()]
        );

        return view('PaymentAndBillings.PaymentHistory', compact('productPayments', 'membershipPayments', 'combinedRefunds'));
    }

    public function refund(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $payment = Payment::findOrFail($id);

            if ($payment->refunded_at) {
                throw new \Exception('This payment has already been refunded.');
            }

            // Reverse stock for each item
            foreach ($payment->items as $item) {
                $inventory = \App\Models\InventorySupply::find($item->inventory_supply_id);
                if ($inventory) {
                    $inventory->increment('stock_qty', $item->quantity);
                }
            }

            $payment->update([
                'refunded_at' => now(),
                'refund_reason' => $request->input('reason'),
                'refunded_by' => Auth::user()->name ?? 'Admin',
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Refund processed successfully.']);
            }

            return back()->with('success', 'Refund processed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $paymentRecord = Payment::findOrFail($id);
                $paymentItems = PaymentItem::where('payment_id', $paymentRecord->id)->get();

                foreach ($paymentItems as $item) {
                    $inventory = \App\Models\InventorySupply::find($item->inventory_supply_id);
                    if ($inventory) {
                        $inventory->increment('stock_qty', $item->quantity);
                    }
                }

                PaymentItem::where('payment_id', $paymentRecord->id)->delete();
                $paymentRecord->delete();
            });

            return back()->with('success', 'Transaction deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete transaction: ' . $e->getMessage()]);
        }
    }
}
