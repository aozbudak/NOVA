<?php

namespace Tests\Feature;

use App\Enums\StaffRole;
use Tests\TestCase;

class AdminAuditTest extends TestCase
{
    public function test_audit_table_renders_dense_operational_columns(): void
    {
        $response = $this->get(route('admin.audit.index'));

        $response->assertOk();
        $response->assertSee('Audit log');
        $response->assertSee('Date / time');
        $response->assertSee('User');
        $response->assertSee('Action');
        $response->assertSee('Module');
        $response->assertSee('Reference');
        $response->assertSee('IP');
        $response->assertSee('Endpoint');
        $response->assertSee('Updated Product');
        $response->assertSee('#NOVA-1024');
        $response->assertSee('PUT /api/products/1024');
        $response->assertSee('192.168.1.xxx');
        $response->assertSee('Success');
        $response->assertSee('Old value');
        $response->assertSee('New value');
    }

    public function test_audit_detail_shows_old_and_new_values(): void
    {
        $response = $this->get(route('admin.audit.show', 'aud-1024'));

        $response->assertOk();
        $response->assertSee('Updated Product');
        $response->assertSee('Old value');
        $response->assertSee('New value');
        $response->assertSee('₺849');
        $response->assertSee('₺899');
    }

    public function test_unknown_audit_record_returns_404(): void
    {
        $this->get(route('admin.audit.show', 'missing'))->assertNotFound();
    }

    public function test_empty_filters_render_no_audit_records(): void
    {
        $response = $this->get(route('admin.audit.index', ['search' => 'no-such-event']));

        $response->assertOk();
        $response->assertSee('No audit records');
        $response->assertDontSee('Updated Product');
    }

    public function test_cashier_is_forbidden_from_audit(): void
    {
        $this->withSession(['admin.role' => StaffRole::Cashier->value])
            ->get(route('admin.audit.index'))
            ->assertForbidden();
    }
}
