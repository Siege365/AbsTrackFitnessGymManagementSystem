<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\MembershipPayment;
use App\Models\Membership;
use App\Models\RefundAuditLog;
use App\Services\RefundService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RefundSystemTest extends TestCase
{
    use RefreshDatabase;

    protected RefundService $refundService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->refundService = new RefundService();
    }

    /**
     * Test processing a refund on a POS payment
     */
    public function test_process_pos_payment_refund()
    {
        // Create a payment with all required fields
        $payment = Payment::create([
            'receipt_number' => 'RCP-001',
            'customer_name' => 'John Doe',
            'transaction_type' => 'sale',
            'payment_method' => 'cash',
            'paid_amount' => 1000.00,
            'total_amount' => 1000.00,
            'return_amount' => 0,
            'total_quantity' => 1,
            'cashier_name' => 'Admin',
            'payment_status' => 'completed',
            'refunded_amount' => 0,
        ]);

        // Process a partial refund
        $refund = $this->refundService->processRefund(
            $payment,
            250.00,
            'Customer requested partial refund',
            'cash'
        );

        // Verify refund was created
        $this->assertNotNull($refund);
        $this->assertEquals(250.00, $refund->refund_amount);
        $this->assertEquals('Customer requested partial refund', $refund->refund_reason);
        $this->assertEquals('cash', $refund->refund_method);
        $this->assertEquals('completed', $refund->status);

        // Verify payment status updated
        $payment->refresh();
        $this->assertEquals('partially_refunded', $payment->payment_status);
        $this->assertEquals(250.00, $payment->refunded_amount);
        $this->assertEquals(250.00, $payment->getTotalRefundedAmount());
        $this->assertEquals(750.00, $payment->getRemainingRefundableAmount());

        // Verify audit log exists
        $this->assertTrue($payment->refunds()->exists());
        $this->assertCount(1, $payment->refunds);
    }

    /**
     * Test processing multiple partial refunds
     */
    public function test_multiple_partial_refunds()
    {
        $payment = Payment::create([
            'receipt_number' => 'RCP-002',
            'customer_name' => 'Jane Smith',
            'transaction_type' => 'sale',
            'payment_method' => 'card',
            'paid_amount' => 1000.00,
            'total_amount' => 1000.00,
            'return_amount' => 0,
            'total_quantity' => 2,
            'cashier_name' => 'Admin',
            'payment_status' => 'completed',
            'refunded_amount' => 0,
        ]);

        // First refund: 300
        $this->refundService->processRefund($payment, 300.00, 'First partial refund', 'cash');
        $payment->refresh();
        $this->assertEquals('partially_refunded', $payment->payment_status);
        $this->assertEquals(300.00, $payment->getTotalRefundedAmount());

        // Second refund: 400
        $this->refundService->processRefund($payment, 400.00, 'Second partial refund', 'card_reversal');
        $payment->refresh();
        $this->assertEquals('partially_refunded', $payment->payment_status);
        $this->assertEquals(700.00, $payment->getTotalRefundedAmount());

        // Third refund: 300 (total now)
        $this->refundService->processRefund($payment, 300.00, 'Final refund', 'store_credit');
        $payment->refresh();
        $this->assertEquals('refunded', $payment->payment_status);
        $this->assertEquals(1000.00, $payment->getTotalRefundedAmount());
        $this->assertEquals(0, $payment->getRemainingRefundableAmount());

        // Verify all three refunds logged
        $this->assertCount(3, $payment->refunds);
    }

    /**
     * Test refund prevents exceeding remaining balance
     */
    public function test_prevents_refund_exceeding_balance()
    {
        $payment = Payment::create([
            'receipt_number' => 'RCP-003',
            'customer_name' => 'Bob Wilson',
            'transaction_type' => 'sale',
            'payment_method' => 'cash',
            'paid_amount' => 500.00,
            'total_amount' => 500.00,
            'return_amount' => 0,
            'total_quantity' => 1,
            'cashier_name' => 'Admin',
            'payment_status' => 'completed',
            'refunded_amount' => 0,
        ]);

        // Try to refund more than available
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('exceeds remaining balance');

        $this->refundService->processRefund($payment, 600.00, 'Too much', 'cash');
    }

    /**
     * Test prevents refund on fully refunded payment
     */
    public function test_prevents_refund_on_fully_refunded_payment()
    {
        $payment = Payment::create([
            'receipt_number' => 'RCP-004',
            'customer_name' => 'Alice Johnson',
            'transaction_type' => 'sale',
            'payment_method' => 'card',
            'paid_amount' => 500.00,
            'total_amount' => 500.00,
            'return_amount' => 0,
            'total_quantity' => 1,
            'cashier_name' => 'Admin',
            'payment_status' => 'completed',
            'refunded_amount' => 0,
        ]);

        // Refund entire amount
        $this->refundService->processRefund($payment, 500.00, 'Full refund', 'cash');
        $payment->refresh();
        $this->assertEquals('refunded', $payment->payment_status);

        // Try to refund again
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('already fully refunded');

        $this->refundService->processRefund($payment, 100.00, 'Extra refund', 'cash');
    }

    /**
     * Test validates refund reason is required
     */
    public function test_validates_refund_reason()
    {
        $payment = Payment::create([
            'receipt_number' => 'RCP-005',
            'customer_name' => 'Charlie Brown',
            'transaction_type' => 'sale',
            'payment_method' => 'cash',
            'paid_amount' => 500.00,
            'total_amount' => 500.00,
            'return_amount' => 0,
            'total_quantity' => 1,
            'cashier_name' => 'Admin',
            'payment_status' => 'completed',
            'refunded_amount' => 0,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Refund reason is required');

        $this->refundService->processRefund($payment, 100.00, '', 'cash');
    }

    /**
     * Test validates refund method
     */
    public function test_validates_refund_method()
    {
        $payment = Payment::create([
            'receipt_number' => 'RCP-006',
            'customer_name' => 'Diana Prince',
            'transaction_type' => 'sale',
            'payment_method' => 'cash',
            'paid_amount' => 500.00,
            'total_amount' => 500.00,
            'return_amount' => 0,
            'total_quantity' => 1,
            'cashier_name' => 'Admin',
            'payment_status' => 'completed',
            'refunded_amount' => 0,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid refund method');

        $this->refundService->processRefund($payment, 100.00, 'Bad method', 'bitcoin');
    }

    /**
     * Test processing refund on membership payment
     */
    public function test_process_membership_payment_refund()
    {
        // Create membership first
        $membership = Membership::create([
            'name' => 'Test Member',
            'age' => 30,
            'plan_type' => 'Monthly',
            'start_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => 'Active',
            'contact' => '1234567890',
        ]);

        // Create membership payment
        $membershipPayment = MembershipPayment::create([
            'receipt_number' => 'MEM-001',
            'membership_id' => $membership->id,
            'member_name' => 'Test Member',
            'plan_type' => 'Monthly',
            'payment_type' => 'new',
            'payment_method' => 'cash',
            'amount' => 500.00,
            'duration_days' => 30,
            'new_due_date' => now()->addDays(30),
            'processed_by' => 'Admin',
            'payment_status' => 'completed',
            'refunded_amount' => 0,
        ]);

        // Process refund
        $refund = $this->refundService->processRefund(
            $membershipPayment,
            150.00,
            'Member requested refund',
            'card_reversal'
        );

        // Verify refund created
        $this->assertNotNull($refund);
        $this->assertEquals('App\Models\MembershipPayment', $refund->refundable_type);
        $this->assertEquals($membershipPayment->id, $refund->refundable_id);
        $this->assertEquals(150.00, $refund->refund_amount);

        // Verify payment status updated
        $membershipPayment->refresh();
        $this->assertEquals('partially_refunded', $membershipPayment->payment_status);
        $this->assertEquals(150.00, $membershipPayment->refunded_amount);

        // Verify membership data NOT modified (important!)
        $membership->refresh();
        $this->assertEquals('Active', $membership->status);
    }

    /**
     * Test cancel refund functionality
     */
    public function test_cancel_refund()
    {
        $payment = Payment::create([
            'receipt_number' => 'RCP-007',
            'customer_name' => 'Eve Davis',
            'transaction_type' => 'sale',
            'payment_method' => 'card',
            'paid_amount' => 1000.00,
            'total_amount' => 1000.00,
            'return_amount' => 0,
            'total_quantity' => 1,
            'cashier_name' => 'Admin',
            'payment_status' => 'completed',
            'refunded_amount' => 0,
        ]);

        // Process refund
        $refund = $this->refundService->processRefund(
            $payment,
            400.00,
            'Test refund',
            'cash'
        );

        $payment->refresh();
        $this->assertEquals('partially_refunded', $payment->payment_status);
        $this->assertEquals(400.00, $payment->getTotalRefundedAmount());

        // Cancel the refund
        $cancelled = $this->refundService->cancelRefund($refund, 'Refund cancelled');
        $this->assertEquals('cancelled', $cancelled->status);

        // Verify payment status reverted
        $payment->refresh();
        $this->assertEquals('completed', $payment->payment_status);
        $this->assertEquals(0, $payment->getTotalRefundedAmount());
    }

    /**
     * Test polymorphic relationships work correctly
     */
    public function test_polymorphic_relationships()
    {
        // Create both payment types
        $posPayment = Payment::create([
            'receipt_number' => 'POS-001',
            'customer_name' => 'POS Customer',
            'transaction_type' => 'sale',
            'payment_method' => 'cash',
            'paid_amount' => 500.00,
            'total_amount' => 500.00,
            'return_amount' => 0,
            'total_quantity' => 1,
            'cashier_name' => 'Admin',
            'payment_status' => 'completed',
            'refunded_amount' => 0,
        ]);

        $membership = Membership::create([
            'name' => 'Mem Member',
            'age' => 25,
            'plan_type' => 'Monthly',
            'start_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => 'Active',
            'contact' => '9876543210',
        ]);

        $memPayment = MembershipPayment::create([
            'receipt_number' => 'MEM-002',
            'membership_id' => $membership->id,
            'member_name' => 'Mem Member',
            'plan_type' => 'Monthly',
            'payment_type' => 'new',
            'payment_method' => 'cash',
            'amount' => 300.00,
            'duration_days' => 30,
            'new_due_date' => now()->addDays(30),
            'processed_by' => 'Admin',
            'payment_status' => 'completed',
            'refunded_amount' => 0,
        ]);

        // Create refunds for both
        $this->refundService->processRefund($posPayment, 100.00, 'POS refund', 'cash');
        $this->refundService->processRefund($memPayment, 75.00, 'Membership refund', 'cash');

        // Verify polymorphic queries work
        $posRefunds = RefundAuditLog::where('refundable_type', 'App\Models\Payment')->get();
        $memRefunds = RefundAuditLog::where('refundable_type', 'App\Models\MembershipPayment')->get();

        $this->assertCount(1, $posRefunds);
        $this->assertCount(1, $memRefunds);

        // Verify reverse polymorphic relationship
        $this->assertEquals($posPayment->id, $posRefunds->first()->refundable->id);
        $this->assertEquals($memPayment->id, $memRefunds->first()->refundable->id);
    }

    /**
     * Test refund service summary method
     */
    public function test_refund_summary()
    {
        $payment = Payment::create([
            'receipt_number' => 'RCP-008',
            'customer_name' => 'Frank Miller',
            'transaction_type' => 'sale',
            'payment_method' => 'cash',
            'paid_amount' => 1000.00,
            'total_amount' => 1000.00,
            'return_amount' => 0,
            'total_quantity' => 2,
            'cashier_name' => 'Admin',
            'payment_status' => 'completed',
            'refunded_amount' => 0,
        ]);

        // Create two refunds
        $this->refundService->processRefund($payment, 200.00, 'Refund 1', 'cash');
        $this->refundService->processRefund($payment, 300.00, 'Refund 2', 'card_reversal');

        // Refresh payment to get updated status from database
        $payment->refresh();

        // Get summary
        $summary = $this->refundService->getRefundSummary($payment);

        $this->assertEquals('RCP-008', $summary['receipt_number']);
        $this->assertEquals(1000.00, $summary['total_amount']);
        $this->assertEquals(500.00, $summary['refunded_amount']);
        $this->assertEquals(500.00, $summary['remaining_refundable']);
        $this->assertEquals('partially_refunded', $summary['payment_status']);
        $this->assertTrue($summary['is_partially_refunded']);
        $this->assertFalse($summary['is_fully_refunded']);
        $this->assertEquals(2, $summary['refund_count']);
    }
}
