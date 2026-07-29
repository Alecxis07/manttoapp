<?php

namespace Tests\Unit\Enums;

use App\Enums\BillingRequestStatus;
use App\Enums\MaintenanceOrderStatus;
use App\Enums\QuotationStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class StatusEnumsTest extends TestCase
{
    #[DataProvider('maintenanceHappyPathProvider')]
    public function test_maintenance_order_happy_path_transitions(MaintenanceOrderStatus $from, MaintenanceOrderStatus $to): void
    {
        $this->assertTrue($from->canTransitionTo($to));
    }

    /**
     * @return array<string, array{0: MaintenanceOrderStatus, 1: MaintenanceOrderStatus}>
     */
    public static function maintenanceHappyPathProvider(): array
    {
        return [
            'received_to_diagnosing' => [MaintenanceOrderStatus::Received, MaintenanceOrderStatus::Diagnosing],
            'diagnosing_to_pending' => [MaintenanceOrderStatus::Diagnosing, MaintenanceOrderStatus::PendingApproval],
            'pending_to_approved' => [MaintenanceOrderStatus::PendingApproval, MaintenanceOrderStatus::Approved],
            'approved_to_in_progress' => [MaintenanceOrderStatus::Approved, MaintenanceOrderStatus::InProgress],
            'in_progress_to_completed' => [MaintenanceOrderStatus::InProgress, MaintenanceOrderStatus::Completed],
            'completed_to_delivered' => [MaintenanceOrderStatus::Completed, MaintenanceOrderStatus::Delivered],
            'delivered_reopen_to_in_progress' => [MaintenanceOrderStatus::Delivered, MaintenanceOrderStatus::InProgress],
        ];
    }

    public function test_maintenance_order_can_cancel_from_active_states(): void
    {
        $this->assertTrue(MaintenanceOrderStatus::Received->canTransitionTo(MaintenanceOrderStatus::Cancelled));
        $this->assertTrue(MaintenanceOrderStatus::InProgress->canTransitionTo(MaintenanceOrderStatus::Cancelled));
        $this->assertFalse(MaintenanceOrderStatus::Cancelled->canTransitionTo(MaintenanceOrderStatus::Received));
    }

    public function test_maintenance_order_rejects_invalid_skip(): void
    {
        $this->assertFalse(
            MaintenanceOrderStatus::Received->canTransitionTo(MaintenanceOrderStatus::Completed)
        );
    }

    public function test_quotation_draft_is_editable_and_sent_is_not(): void
    {
        $this->assertTrue(QuotationStatus::Draft->isEditable());
        $this->assertFalse(QuotationStatus::Sent->isEditable());
        $this->assertTrue(QuotationStatus::Draft->canTransitionTo(QuotationStatus::Sent));
        $this->assertTrue(QuotationStatus::Sent->canTransitionTo(QuotationStatus::Accepted));
        $this->assertFalse(QuotationStatus::Accepted->canTransitionTo(QuotationStatus::Draft));
    }

    public function test_billing_request_flow_and_immutability(): void
    {
        $this->assertTrue(BillingRequestStatus::Draft->canTransitionTo(BillingRequestStatus::PendingReview));
        $this->assertTrue(BillingRequestStatus::PendingReview->canTransitionTo(BillingRequestStatus::Incomplete));
        $this->assertTrue(BillingRequestStatus::PendingReview->canTransitionTo(BillingRequestStatus::Approved));
        $this->assertTrue(BillingRequestStatus::Approved->canTransitionTo(BillingRequestStatus::Processed));
        $this->assertTrue(BillingRequestStatus::Processed->isImmutable());
        $this->assertFalse(BillingRequestStatus::Processed->canTransitionTo(BillingRequestStatus::Draft));
    }
}
