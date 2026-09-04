<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
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

    public function test_storefront_shows_database_category_name_instead_of_missing_translation_key(): void
    {
        $women = Category::factory()->create([
            'name' => 'Kadın',
            'slug' => 'women',
            'is_active' => true,
        ]);

        $this->post(route('admin.categories.store'), [
            'name' => 'Gözlük',
            'parent_id' => $women->id,
            'status' => 'active',
        ])->assertRedirect(route('admin.categories.index'));

        $this->get(route('shop.show', ['department' => 'women', 'category' => 'gozluk']))
            ->assertOk()
            ->assertSee('Gözlük')
            ->assertDontSee('storefront.nav.gozluk');
    }

    public function test_category_can_be_created_under_multiple_parents(): void
    {
        $women = Category::factory()->create(['name' => 'Kadın', 'slug' => 'women']);
        $men = Category::factory()->create(['name' => 'Erkek', 'slug' => 'men']);

        $this->post(route('admin.categories.store'), [
            'name' => 'Gözlük',
            'parent_ids' => [$women->id, $men->id],
            'status' => 'active',
        ])->assertRedirect(route('admin.categories.index'));

        $this->assertSame(2, Category::query()->where('name', 'Gözlük')->count());
        $this->assertTrue(Category::query()->where('name', 'Gözlük')->where('parent_id', $women->id)->exists());
        $this->assertTrue(Category::query()->where('name', 'Gözlük')->where('parent_id', $men->id)->exists());
    }

    public function test_category_can_be_updated(): void
    {
        $women = Category::factory()->create(['name' => 'Kadın', 'slug' => 'women']);
        $men = Category::factory()->create(['name' => 'Erkek', 'slug' => 'men']);
        $category = Category::factory()->create([
            'name' => 'Gözlük',
            'slug' => 'gozluk',
            'parent_id' => $women->id,
            'is_active' => true,
        ]);

        $this->put(route('admin.categories.update', $category->id), [
            'name' => 'Güneş Gözlüğü',
            'parent_id' => $men->id,
            'status' => 'inactive',
        ])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('status', 'Category updated successfully.');

        $category->refresh();

        $this->assertSame('Güneş Gözlüğü', $category->name);
        $this->assertSame($men->id, $category->parent_id);
        $this->assertFalse($category->is_active);

        $this->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('Güneş Gözlüğü')
            ->assertSee('Erkek');
    }

    public function test_category_can_be_deleted(): void
    {
        $category = Category::factory()->create(['name' => 'Gözlük', 'slug' => 'gozluk']);

        $this->delete(route('admin.categories.destroy', $category->id))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('status', 'Category deleted successfully.');

        $this->assertModelMissing($category);

        $this->get(route('admin.categories.index'))
            ->assertOk()
            ->assertDontSee('Gözlük');
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $category = Category::factory()->create(['name' => 'Gözlük', 'slug' => 'gozluk']);
        Product::factory()->create(['category_id' => $category->id]);

        $this->from(route('admin.categories.index'))
            ->delete(route('admin.categories.destroy', $category->id))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('error', 'This category still has products and cannot be deleted.');

        $this->assertModelExists($category);
    }

    public function test_header_categories_appear_on_the_storefront(): void
    {
        $category = Category::factory()->create([
            'name' => 'Gözlük',
            'slug' => 'gozluk',
            'is_active' => true,
            'show_in_header' => false,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Gözlük');

        $this->post(route('admin.categories.header.store'), [
            'category_id' => $category->id,
        ])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('status', 'Header categories updated.');

        $this->assertTrue($category->fresh()->show_in_header);

        $this->get('/')
            ->assertOk()
            ->assertSee('Gözlük');

        $this->delete(route('admin.categories.header.destroy', $category->id))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('status', 'Header categories updated.');

        $this->assertFalse($category->fresh()->show_in_header);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Gözlük');
    }
}
