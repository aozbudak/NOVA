<?php

namespace Tests\Feature;

use App\Enums\StaffRole;
use App\Enums\StorefrontCoverSlot;
use App\Models\StorefrontCover;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class StorefrontCoverTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_uses_the_winter_women_cover_by_default(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(asset('images/covers/women-winter.png'), false);
    }

    public function test_admin_can_replace_a_storefront_cover_and_the_home_page_shows_it(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('women-cover.jpg', 800, 1000);

        $this->put(route('admin.categories.covers.update'), [
            'covers' => [
                'women' => $file,
            ],
        ])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('status', 'Cover photos updated.');

        $cover = StorefrontCover::query()->where('slot', StorefrontCoverSlot::Women)->first();
        $this->assertNotNull($cover);

        $url = $cover->image_url;
        $this->assertStringContainsString('/storage/covers/', $url);
        Storage::disk('public')->assertExists(Str::after($url, '/storage/'));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($url, false);
    }

    public function test_empty_cover_payload_is_rejected(): void
    {
        $this->from(route('admin.categories.index'))
            ->put(route('admin.categories.covers.update'), [])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHasErrors('covers');
    }

    public function test_cover_upload_without_a_file_is_rejected(): void
    {
        $this->from(route('admin.categories.index'))
            ->put(route('admin.categories.covers.update'), [
                'covers' => [],
            ])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHasErrors('covers');
    }

    public function test_non_image_cover_upload_is_rejected(): void
    {
        $this->from(route('admin.categories.index'))
            ->put(route('admin.categories.covers.update'), [
                'covers' => [
                    'women' => UploadedFile::fake()->create('notes.pdf', 20, 'application/pdf'),
                ],
            ])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHasErrors('covers.women');
    }

    public function test_cashier_is_forbidden_from_updating_covers(): void
    {
        $this->withSession(['admin.role' => StaffRole::Cashier->value])
            ->put(route('admin.categories.covers.update'), [
                'covers' => [
                    'women' => UploadedFile::fake()->image('women.jpg', 80, 100),
                ],
            ])
            ->assertForbidden();
    }
}
