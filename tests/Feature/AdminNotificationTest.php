<?php

namespace Tests\Feature;

use App\Enums\StaffRole;
use Tests\TestCase;

class AdminNotificationTest extends TestCase
{
    public function test_header_panel_lists_unread_and_read_notifications(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Notifications');
        $response->assertSee('Low stock: Basic T-Shirt');
        $response->assertSee('New return created');
        $response->assertSee('Cash register requires closing');
        $response->assertSee('New sale completed');
        $response->assertSee('Mark all read');
        $response->assertSee('Mark read');
        $response->assertSee('Read');
    }

    public function test_marking_all_notifications_read_clears_unread_actions(): void
    {
        $this->from(route('admin.dashboard'))
            ->post(route('admin.notifications.read-all'))
            ->assertRedirect(route('admin.dashboard'));

        $response = $this->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Low stock: Basic T-Shirt');
        $response->assertDontSee('Mark all read');
        $response->assertDontSee('Mark read');
        $response->assertSee('Read');
    }

    public function test_unknown_notification_returns_404(): void
    {
        $this->post(route('admin.notifications.read', 'missing'))->assertNotFound();
    }

    public function test_cashier_can_open_the_notification_panel_on_pos(): void
    {
        $this->withSession(['admin.role' => StaffRole::Cashier->value])
            ->get(route('admin.pos.index'))
            ->assertOk()
            ->assertSee('Notifications')
            ->assertSee('Low stock: Basic T-Shirt');
    }
}
