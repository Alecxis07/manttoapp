<?php

namespace Tests\Unit\Enums;

use App\Enums\MaintenanceOrderStatus;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MaintenanceOrderStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    #[DataProvider('happyPathProvider')]
    public function test_structural_happy_path(MaintenanceOrderStatus $from, MaintenanceOrderStatus $to): void
    {
        $this->assertTrue($from->canTransitionTo($to));
    }

    /**
     * @return array<string, array{0: MaintenanceOrderStatus, 1: MaintenanceOrderStatus}>
     */
    public static function happyPathProvider(): array
    {
        return [
            'received_to_diagnosing' => [MaintenanceOrderStatus::Received, MaintenanceOrderStatus::Diagnosing],
            'diagnosing_to_pending' => [MaintenanceOrderStatus::Diagnosing, MaintenanceOrderStatus::PendingApproval],
            'pending_to_approved' => [MaintenanceOrderStatus::PendingApproval, MaintenanceOrderStatus::Approved],
            'approved_to_in_progress' => [MaintenanceOrderStatus::Approved, MaintenanceOrderStatus::InProgress],
            'in_progress_to_completed' => [MaintenanceOrderStatus::InProgress, MaintenanceOrderStatus::Completed],
            'completed_to_delivered' => [MaintenanceOrderStatus::Completed, MaintenanceOrderStatus::Delivered],
            'delivered_reopen' => [MaintenanceOrderStatus::Delivered, MaintenanceOrderStatus::InProgress],
        ];
    }

    public function test_invalid_skip_is_rejected(): void
    {
        $this->assertFalse(
            MaintenanceOrderStatus::Received->canTransitionTo(MaintenanceOrderStatus::Completed)
        );
    }

    public function test_admin_can_cancel_and_reopen(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(RolesAndPermissionsSeeder::ROLE_ADMIN);

        $this->assertTrue(
            MaintenanceOrderStatus::InProgress->canTransitionTo(MaintenanceOrderStatus::Cancelled, $admin)
        );
        $this->assertTrue(
            MaintenanceOrderStatus::Delivered->canTransitionTo(MaintenanceOrderStatus::InProgress, $admin)
        );
    }

    public function test_administrativo_cannot_cancel_or_reopen(): void
    {
        $user = User::factory()->create();
        $user->assignRole(RolesAndPermissionsSeeder::ROLE_ADMINISTRATIVO);

        $this->assertFalse(
            MaintenanceOrderStatus::Received->canTransitionTo(MaintenanceOrderStatus::Cancelled, $user)
        );
        $this->assertFalse(
            MaintenanceOrderStatus::Delivered->canTransitionTo(MaintenanceOrderStatus::InProgress, $user)
        );
        $this->assertTrue(
            MaintenanceOrderStatus::PendingApproval->canTransitionTo(MaintenanceOrderStatus::Approved, $user)
        );
        $this->assertFalse(
            MaintenanceOrderStatus::Approved->canTransitionTo(MaintenanceOrderStatus::InProgress, $user)
        );
    }

    public function test_tecnico_role_transitions(): void
    {
        $user = User::factory()->create();
        $user->assignRole(RolesAndPermissionsSeeder::ROLE_TECNICO);

        $this->assertTrue(
            MaintenanceOrderStatus::Diagnosing->canTransitionTo(MaintenanceOrderStatus::PendingApproval, $user)
        );
        $this->assertTrue(
            MaintenanceOrderStatus::Approved->canTransitionTo(MaintenanceOrderStatus::InProgress, $user)
        );
        $this->assertFalse(
            MaintenanceOrderStatus::PendingApproval->canTransitionTo(MaintenanceOrderStatus::Approved, $user)
        );
        $this->assertFalse(
            MaintenanceOrderStatus::Completed->canTransitionTo(MaintenanceOrderStatus::Delivered, $user)
        );
    }
}
