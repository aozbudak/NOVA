<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryStorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_category_appears_on_the_storefront_and_api(): void
    {
        $women = Category::factory()->create([
            'name' => 'Kadın',
            'slug' => 'women',
            'is_active' => true,
        ]);

        $this->post(route('admin.categories.store'), [
            'name' => 'Elbise',
            'parent_id' => $women->id,
            'status' => 'active',
        ])->assertRedirect(route('admin.categories.index'));

        $this->assertTrue(Category::query()->where('name', 'Elbise')->where('parent_id', $women->id)->exists());

        $this->get('/')
            ->assertOk()
            ->assertSee('Elbise');

        $this->get(route('api.categories.index'))
            ->assertOk()
            ->assertJsonFragment(['name' => 'Elbise']);
    }

    public function test_same_child_name_can_exist_under_different_parents(): void
    {
        $women = Category::factory()->create(['name' => 'Kadın', 'slug' => 'women']);
        $men = Category::factory()->create(['name' => 'Erkek', 'slug' => 'men']);

        $this->post(route('admin.categories.store'), [
            'name' => 'Tişört',
            'parent_id' => $women->id,
            'status' => 'active',
        ])->assertRedirect(route('admin.categories.index'));

        $this->post(route('admin.categories.store'), [
            'name' => 'Tişört',
            'parent_id' => $men->id,
            'status' => 'active',
        ])->assertRedirect(route('admin.categories.index'));

        $this->assertSame(2, Category::query()->where('name', 'Tişört')->count());
    }

    public function test_product_form_lists_database_categories(): void
    {
        Category::factory()->create(['name' => 'Ayakkabı', 'slug' => 'ayakkabi']);

        $this->get(route('admin.products.create'))
            ->assertOk()
            ->assertSee('Ayakkabı');
    }
}
