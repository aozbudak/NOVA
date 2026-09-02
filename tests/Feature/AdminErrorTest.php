<?php

namespace Tests\Feature;

use App\Enums\StaffRole;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminErrorTest extends TestCase
{
    public function test_unknown_admin_page_renders_page_not_found(): void
    {
        $this->get('/admin/missing-module')
            ->assertNotFound()
            ->assertSee('PAGE NOT FOUND', false);
    }

    public function test_cashier_forbidden_page_renders_permission_copy(): void
    {
        $this->withSession(['admin.role' => StaffRole::Cashier->value])
            ->get(route('admin.users.index'))
            ->assertForbidden()
            ->assertSee("YOU DON'T HAVE PERMISSION TO ACCESS THIS PAGE");
    }

    public function test_admin_401_renders_session_expired(): void
    {
        Route::middleware('web')->get('/admin/session-expired', fn () => abort(401));

        $this->get('/admin/session-expired')
            ->assertUnauthorized()
            ->assertSee('SESSION EXPIRED', false)
            ->assertSee('PLEASE LOGIN AGAIN', false);
    }

    public function test_storefront_404_does_not_use_admin_copy(): void
    {
        $this->get('/missing-storefront-page')
            ->assertNotFound()
            ->assertDontSee('PAGE NOT FOUND', false);
    }
}
