<?php

namespace App\Support;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Catalog
{
    /**
     * @var Collection<int, array<string, mixed>>|null
     */
    private ?Collection $items = null;

    public function __construct(private DiscountService $discounts = new DiscountService) {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function all(): Collection
    {
        return $this->items ??= $this->resolve();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        return $this->all()->firstWhere('id', $id);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function resolve(): Collection
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'catalog_code')) {
            return $this->databaseProducts();
        }

        return collect($this->products());
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function sourceItems(): Collection
    {
        return collect($this->products());
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function databaseProducts(): Collection
    {
        if (! Schema::hasTable('products') || ! Schema::hasColumn('products', 'catalog_code')) {
            return collect();
        }

        return Product::query()
            ->with(['category.parent', 'brandRecord', 'images', 'variants.stock'])
            ->where('is_active', true)
            ->orderByRaw('catalog_code is null')
            ->orderBy('catalog_code')
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product): array => $this->mapFromDatabase($product))
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function mapFromDatabase(Product $product): array
    {
        $attributes = $product->attributes ?? [];
        $activeVariants = $product->variants->where('is_active', true);
        $pricingVariant = $activeVariants->first();
        $quote = $this->discounts->quote($product, $pricingVariant instanceof ProductVariant ? $pricingVariant : null);
        $onSale = $quote->hasDiscount();
        $colors = $attributes['colors'] ?? $this->colorsFromVariants($activeVariants);
        $sizes = $attributes['sizes'] ?? $this->sizesFromVariants($activeVariants, $product);
        $images = $product->images->pluck('image_url')->values()->all();
        $brand = $product->brandRecord;
        $variantPrices = $activeVariants
            ->map(function ($variant) use ($product): array {
                $quote = $this->discounts->quote($product, $variant);

                return [
                    'size' => (string) $variant->size,
                    'color' => (string) $variant->color,
                    'price' => (float) $quote->unitFinal,
                    'oldPrice' => $quote->hasDiscount() ? (float) $quote->unitOriginal : null,
                    'percent' => $quote->percent,
                    'discountName' => $quote->name,
                ];
            })
            ->values()
            ->all();

        return [
            'id' => (int) ($product->catalog_code ?: sprintf('%u', crc32((string) $product->id))),
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => (float) $quote->unitFinal,
            'oldPrice' => $onSale ? (float) $quote->unitOriginal : null,
            'discountPercent' => $quote->percent,
            'discountName' => $quote->name,
            'discountId' => $quote->discountId,
            'vat_rate' => (float) ($product->vat_rate ?? 20),
            'currency' => $product->currency,
            'images' => $images,
            'colors' => $colors,
            'sizes' => $sizes,
            'variantPrices' => $variantPrices,
            'category' => $attributes['department'] ?? $product->category?->parent?->slug ?? $product->category?->slug,
            'type' => $attributes['type'] ?? $this->leafType($product),
            'brand' => $brand?->slug ?? Str::slug((string) $product->brand),
            'brand_name' => $brand?->name ?? (string) $product->brand,
            'collection' => $attributes['collection'] ?? '',
            'stock' => (int) ($attributes['stock'] ?? $activeVariants->sum(fn ($variant): int => (int) ($variant->stock?->quantity ?? 0))),
            'description' => (string) $product->description,
            'featured' => $product->is_featured,
            'isNew' => $product->is_new,
            'material' => $attributes['material'] ?? $this->t('product.material_see_details'),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->all()->firstWhere('slug', $slug);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function featured(): Collection
    {
        return $this->all()->where('featured', true)->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function search(string $query): Collection
    {
        $needle = Str::lower(trim($query));

        if ($needle === '') {
            return collect();
        }

        return $this->all()
            ->filter(function (array $product) use ($needle): bool {
                $haystack = Str::lower(implode(' ', [
                    $product['name'],
                    $product['category'],
                    $product['collection'],
                    $product['description'],
                    $product['brand_name'] ?? '',
                    $product['brand'] ?? '',
                ]));

                return Str::contains($haystack, $needle);
            })
            ->values();
    }

    /**
     * @return list<array{id: int, name: string, slug: string, price: float, currency: string, image: string, category: string}>
     */
    public function searchIndex(): array
    {
        return $this->all()
            ->map(fn (array $product): array => [
                'id' => $product['id'],
                'name' => $product['name'],
                'slug' => $product['slug'],
                'price' => $product['price'],
                'currency' => $product['currency'],
                'image' => $product['images'][0] ?? '',
                'category' => $product['category'],
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $product
     * @return Collection<int, array<string, mixed>>
     */
    public function related(array $product, int $limit = 4): Collection
    {
        return $this->all()
            ->where('id', '!=', $product['id'])
            ->where('category', $product['category'])
            ->take($limit)
            ->values();
    }

    /**
     * @return list<array{slug: string, title: string, eyebrow: string, image: string, href: string}>
     */
    public function campaigns(): array
    {
        return [
            [
                'slug' => 'women',
                'title' => $this->t('nav.women'),
                'eyebrow' => $this->t('collection.aw26'),
                'image' => $this->image('photo-1469334031218-e382a71b716b', 1600),
                'href' => route('shop.show', ['department' => 'women']),
            ],
            [
                'slug' => 'men',
                'title' => $this->t('nav.men'),
                'eyebrow' => $this->t('collection.aw26'),
                'image' => $this->image('photo-1490578474895-699cd4e2cf59', 1600),
                'href' => route('shop.show', ['department' => 'men']),
            ],
            [
                'slug' => 'new-collection',
                'title' => $this->t('campaign.new_collection'),
                'eyebrow' => $this->t('featured.season_edit'),
                'image' => $this->image('photo-1490481651871-ab68de25d43d', 2000),
                'href' => route('shop.show', ['department' => 'new-in']),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function departmentMeta(string $department, ?string $category = null): array
    {
        $departments = [
            'women' => ['title' => $this->t('nav.women'), 'label' => $this->t('nav.women')],
            'men' => ['title' => $this->t('nav.men'), 'label' => $this->t('nav.men')],
            'kids' => ['title' => $this->t('nav.kids'), 'label' => $this->t('nav.kids')],
            'sport' => ['title' => $this->t('nav.sport'), 'label' => $this->t('nav.sport')],
            'new-in' => ['title' => $this->t('nav.new-in'), 'label' => $this->t('nav.new-in')],
            'sale' => ['title' => $this->t('nav.sale'), 'label' => $this->t('nav.sale')],
            'collections' => ['title' => $this->t('nav.collections'), 'label' => $this->t('nav.collections')],
        ];

        $fromDatabase = $this->databaseCategoryName($department);
        $meta = filled($fromDatabase)
            ? ['title' => $fromDatabase, 'label' => $fromDatabase]
            : ($departments[$department] ?? ['title' => $this->t('nav.shop'), 'label' => $this->t('nav.shop')]);

        if ($category !== null && $category !== '') {
            $categoryLabel = $this->categoryLabel($category);
            $meta['title'] = $categoryLabel;
            $meta['breadcrumb'] = $meta['label'].' / '.$categoryLabel;
        } else {
            $meta['breadcrumb'] = $meta['label'];
        }

        return $meta;
    }

    /**
     * @param  array{
     *     department: string,
     *     category?: string|null,
     *     brand?: string|null,
     *     size?: string|null,
     *     color?: string|null,
     *     collection?: string|null,
     *     availability?: string|null,
     *     price?: string|null,
     *     sort?: string|null,
     *     discount?: string|null
     * }  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function browse(array $filters): Collection
    {
        $products = $this->all();
        $department = $filters['department'];
        $category = $filters['category'] ?? null;

        $products = match ($department) {
            'women', 'men', 'kids', 'sport' => $products->where('category', $department),
            'new-in' => $products->where('isNew', true),
            'sale' => $products->filter(function (array $product) use ($filters): bool {
                if ($product['oldPrice'] === null) {
                    return false;
                }

                $discountId = $filters['discount'] ?? null;

                return $discountId === null || ($product['discountId'] ?? null) === $discountId;
            }),
            'collections' => $products,
            default => $products->filter(function (array $product) use ($department): bool {
                $leaf = $this->leafSlug($department);

                return ($product['category'] ?? '') === $department
                    || ($product['type'] ?? '') === $department
                    || ($product['type'] ?? '') === $leaf;
            }),
        };

        if (is_string($category) && $category !== '') {
            $products = $products->where('type', $category);
        }

        if (filled($filters['brand'] ?? null)) {
            $brand = Str::lower((string) $filters['brand']);
            $products = $products->filter(function (array $product) use ($brand): bool {
                return Str::lower((string) ($product['brand'] ?? '')) === $brand
                    || Str::slug((string) ($product['brand_name'] ?? '')) === $brand;
            });
        }

        if (filled($filters['size'] ?? null)) {
            $size = $filters['size'];
            $products = $products->filter(function (array $product) use ($size): bool {
                return collect($product['sizes'])->contains(
                    fn (array $option): bool => $option['code'] === $size && $option['in_stock']
                );
            });
        }

        if (filled($filters['color'] ?? null)) {
            $color = Str::lower($filters['color']);
            $products = $products->filter(function (array $product) use ($color): bool {
                return collect($product['colors'])->contains(
                    fn (array $option): bool => Str::lower($option['name']) === $color
                );
            });
        }

        if (filled($filters['collection'] ?? null)) {
            $products = $products->where('collection', $filters['collection']);
        }

        if (($filters['availability'] ?? null) === 'in-stock') {
            $products = $products->where('stock', '>', 0);
        }

        if (filled($filters['price'] ?? null)) {
            $products = $this->filterByPrice($products, $filters['price']);
        }

        return $this->sortProducts($products, $filters['sort'] ?? 'recommended')->values();
    }

    /**
     * @return list<string>
     */
    public function departments(): array
    {
        $departments = ['women', 'men', 'kids', 'sport', 'new-in', 'collections', 'sale'];

        if (! Schema::hasTable('categories')) {
            return $departments;
        }

        foreach (Category::query()->active()->orderBy('slug')->pluck('slug') as $slug) {
            $departments[] = $slug;
            $leaf = $this->leafSlug($slug);

            if ($leaf !== $slug) {
                $departments[] = $leaf;
            }
        }

        return array_values(array_unique($departments));
    }

    /**
     * @return list<array{
     *     label: string,
     *     department: string,
     *     href?: string,
     *     columns: list<array{title: string, links: list<array{label: string, category?: string, href: string}>}>,
     *     featured: array{title: string, image: string, href: string}
     * }>
     */
    public function navigation(): array
    {
        $header = $this->headerNavigation();

        if ($header !== []) {
            $brands = $this->brandNavItem();

            if ($brands !== null) {
                $header[] = $brands;
            }

            $header[] = $this->discountNavItem();

            return $header;
        }

        $items = [
            $this->navItem($this->t('nav.women'), 'women', [
                ['title' => $this->t('nav.clothing'), 'categories' => [
                    'dresses' => $this->t('nav.dresses'),
                    'blazers' => $this->t('nav.blazers'),
                    'knitwear' => $this->t('nav.knitwear'),
                    'outerwear' => $this->t('nav.outerwear'),
                    'trousers' => $this->t('nav.trousers'),
                    'skirts' => $this->t('nav.skirts'),
                    'denim' => $this->t('nav.denim'),
                ]],
                ['title' => $this->t('nav.accessories'), 'categories' => [
                    'accessories' => $this->t('nav.bags'),
                    'shoes' => $this->t('nav.shoes'),
                ]],
            ], $this->t('collection.aw26'), 'photo-1469334031218-e382a71b716b'),
            $this->navItem($this->t('nav.men'), 'men', [
                ['title' => $this->t('nav.clothing'), 'categories' => [
                    'outerwear' => $this->t('nav.outerwear'),
                    'shirts' => $this->t('nav.shirts'),
                    'knitwear' => $this->t('nav.knitwear'),
                    'trousers' => $this->t('nav.trousers'),
                    'denim' => $this->t('nav.denim'),
                ]],
                ['title' => $this->t('nav.accessories'), 'categories' => [
                    'shoes' => $this->t('nav.shoes'),
                    'accessories' => $this->t('nav.accessories'),
                ]],
            ], $this->t('featured.tailored_edit'), 'photo-1490578474895-699cd4e2cf59'),
            $this->navItem($this->t('nav.kids'), 'kids', [
                ['title' => $this->t('nav.clothing'), 'categories' => [
                    'dresses' => $this->t('nav.dresses'),
                    'outerwear' => $this->t('nav.outerwear'),
                    'knitwear' => $this->t('nav.knitwear'),
                ]],
            ], $this->t('featured.mini_nova'), 'photo-1503453342920-ded826308edf'),
            $this->navItem($this->t('nav.sport'), 'sport', [
                ['title' => $this->t('nav.activity'), 'categories' => [
                    'running' => $this->t('nav.running'),
                    'training' => $this->t('nav.training'),
                ]],
                ['title' => $this->t('nav.shop'), 'categories' => [
                    'knitwear' => $this->t('nav.knitwear'),
                    'trousers' => $this->t('nav.trousers'),
                    'outerwear' => $this->t('nav.outerwear'),
                ]],
            ], $this->t('featured.move_well'), 'photo-1517836357463-d25dfeac3438'),
            $this->navItem($this->t('nav.new-in'), 'new-in', [
                ['title' => $this->t('nav.just_arrived'), 'links' => [
                    ['label' => $this->t('nav.women'), 'href' => route('shop.show', 'women')],
                    ['label' => $this->t('nav.men'), 'href' => route('shop.show', 'men')],
                    ['label' => $this->t('nav.kids'), 'href' => route('shop.show', 'kids')],
                    ['label' => $this->t('nav.sport'), 'href' => route('shop.show', 'sport')],
                ]],
            ], $this->t('featured.season_edit'), 'photo-1490481651871-ab68de25d43d'),
            $this->navItem($this->t('nav.collections'), 'collections', [
                ['title' => $this->t('nav.this_season'), 'links' => [
                    ['label' => $this->t('collection.aw26'), 'href' => route('shop.show', 'new-in')],
                    ['label' => $this->t('collection.essentials'), 'href' => route('shop.show', ['department' => 'women', 'category' => 'knitwear'])],
                    ['label' => $this->t('nav.sport'), 'href' => route('shop.show', 'sport')],
                ]],
            ], $this->t('featured.house_collections'), 'photo-1483985988355-763728e1935b'),
            $this->discountNavItem(),
        ];

        return $this->mergeDatabaseNavigation($items);
    }

    /**
     * @return list<array{
     *     label: string,
     *     department: string,
     *     href: string,
     *     columns: list<array{title: string, links: list<array{label: string, category?: string, href: string}>}>,
     *     featured: array{title: string, image: string, href: string}
     * }>
     */
    private function headerNavigation(): array
    {
        if (! Schema::hasTable('categories') || ! Schema::hasColumn('categories', 'show_in_header')) {
            return [];
        }

        $categories = Category::query()
            ->active()
            ->inHeader()
            ->with([
                'parent',
                'children' => fn ($query) => $query->active()->orderBy('sort_order')->orderBy('name'),
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        if ($categories->isEmpty()) {
            return [];
        }

        return $categories
            ->map(fn (Category $category): array => $this->categoryNavItem($category))
            ->all();
    }

    /**
     * @return array{
     *     label: string,
     *     department: string,
     *     href: string,
     *     columns: list<array{title: string, links: list<array{label: string, category?: string, href: string}>}>,
     *     featured: array{title: string, image: string, href: string}
     * }
     */
    private function categoryNavItem(Category $category): array
    {
        $department = $category->slug;
        $parentSlug = $category->parent?->slug;
        $href = $parentSlug !== null
            ? route('shop.show', ['department' => $parentSlug, 'category' => $this->leafSlug($category->slug)])
            : route('shop.show', $department);

        $columns = [];

        if ($category->children->isNotEmpty()) {
            $columns[] = [
                'title' => $this->t('nav.shop'),
                'links' => $category->children
                    ->map(fn (Category $child): array => [
                        'label' => $child->name,
                        'category' => $this->leafSlug($child->slug),
                        'href' => route('shop.show', ['department' => $department, 'category' => $this->leafSlug($child->slug)]),
                    ])
                    ->values()
                    ->all(),
            ];
        }

        return [
            'label' => $category->name,
            'department' => $department,
            'href' => $href,
            'columns' => $columns,
            'featured' => [
                'title' => $category->name,
                'image' => filled($category->image) ? $category->image : $this->image('photo-1469334031218-e382a71b716b', 900),
                'href' => $href,
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function categoriesFor(string $department): array
    {
        $item = collect($this->navigation())->firstWhere('department', $department);

        if (! is_array($item)) {
            return [];
        }

        $categories = [];

        foreach ($item['columns'] as $column) {
            foreach ($column['links'] as $link) {
                if (isset($link['category'])) {
                    $categories[$link['category']] = $link['label'];
                }
            }
        }

        return $categories;
    }

    /**
     * @return Collection<int, Brand>
     */
    public function brands(): Collection
    {
        if (! Schema::hasTable('brands')) {
            return collect();
        }

        return Brand::query()
            ->active()
            ->orderBy('name')
            ->get();
    }

    public function findBrand(string $slug): ?Brand
    {
        return $this->brands()->first(
            fn (Brand $brand): bool => $brand->slug === $slug || Str::slug($brand->name) === $slug,
        );
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function browseBrand(Brand $brand, array $filters = []): Collection
    {
        return $this->browse([
            ...$filters,
            'department' => 'collections',
            'brand' => $brand->slug,
        ]);
    }

    public function findDiscount(string $id): ?Discount
    {
        if (! Schema::hasTable('discounts')) {
            return null;
        }

        return Discount::query()->active()->find($id);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function browseDiscount(Discount $discount, array $filters = []): Collection
    {
        return $this->browse([
            ...$filters,
            'department' => 'sale',
            'discount' => $discount->id,
        ]);
    }

    /**
     * @return array{label: string, department: string, href: string, columns: list<array{title: string, links: list<array{label: string, href: string}>}>, featured: array{title: string, image: string, href: string}}
     */
    private function discountNavItem(): array
    {
        $discounts = $this->headerDiscounts();
        $saleHref = route('shop.show', 'sale');
        $links = $discounts
            ->map(fn (Discount $discount): array => [
                'label' => $discount->name,
                'href' => route('discounts.show', $discount),
            ])
            ->values()
            ->all();

        if ($links === []) {
            $links[] = [
                'label' => $this->t('nav.sale'),
                'href' => $saleHref,
            ];
        }

        return [
            'label' => $this->t('nav.discounts'),
            'department' => 'sale',
            'href' => $saleHref,
            'columns' => [[
                'title' => $this->t('nav.reduced'),
                'links' => $links,
            ]],
            'featured' => [
                'title' => $this->t('nav.discounts'),
                'image' => $this->image('photo-1487222477894-8943e31ef7b2', 900),
                'href' => $saleHref,
            ],
        ];
    }

    /**
     * @return Collection<int, Discount>
     */
    private function headerDiscounts(): Collection
    {
        if (! Schema::hasTable('discounts')) {
            return collect();
        }

        return Discount::query()
            ->active()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get(['id', 'name', 'type', 'value']);
    }

    /**
     * @param  Collection<int, mixed>  $variants
     * @return list<array{name: string, hex: string}>
     */
    private function colorsFromVariants(Collection $variants): array
    {
        return $variants
            ->pluck('color')
            ->filter()
            ->unique()
            ->map(fn (string $name): array => [
                'name' => $name,
                'hex' => $this->colorHex($name),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, mixed>  $variants
     * @return list<array{code: string, in_stock: bool, price?: float, oldPrice?: float|null}>
     */
    private function sizesFromVariants(Collection $variants, ?Product $product = null): array
    {
        return $variants
            ->filter(fn ($variant): bool => filled($variant->size))
            ->unique('size')
            ->map(function ($variant) use ($product): array {
                $row = [
                    'code' => (string) $variant->size,
                    'in_stock' => (int) ($variant->stock?->quantity ?? 0) > 0,
                ];

                if ($product instanceof Product) {
                    $quote = $this->discounts->quote($product, $variant);
                    $row['price'] = (float) $quote->unitFinal;
                    $row['oldPrice'] = $quote->hasDiscount() ? (float) $quote->unitOriginal : null;
                }

                return $row;
            })
            ->values()
            ->all();
    }

    private function colorHex(string $name): string
    {
        $map = [
            'black' => '#1a1a1a',
            'siyah' => '#1a1a1a',
            'white' => '#f5f5f5',
            'beyaz' => '#f5f5f5',
            'navy' => '#1c2430',
            'lacivert' => '#1c2430',
            'grey' => '#6e6e6e',
            'gray' => '#6e6e6e',
            'ivory' => '#f3efe6',
            'camel' => '#c4a574',
        ];

        return $map[Str::lower($name)] ?? '#8a8a8a';
    }

    private function leafType(Product $product): string
    {
        $slug = $product->category?->slug ?? '';

        foreach (['women-', 'men-', 'kids-', 'sport-', 'admin-'] as $prefix) {
            if (str_starts_with($slug, $prefix)) {
                return substr($slug, strlen($prefix));
            }
        }

        return $slug;
    }

    /**
     * @param  list<array{label: string, department: string, columns: list<array{title: string, links: list<array{label: string, category?: string, href: string}>}>, featured: array{title: string, image: string, href: string}}>  $items
     * @return list<array{label: string, department: string, columns: list<array{title: string, links: list<array{label: string, category?: string, href: string}>}>, featured: array{title: string, image: string, href: string}}>
     */
    private function mergeDatabaseNavigation(array $items): array
    {
        $items = $this->mergeDatabaseCategories($items);
        $brands = $this->brandNavItem();

        if ($brands !== null) {
            $items[] = $brands;
        }

        return $items;
    }

    /**
     * @param  list<array{label: string, department: string, columns: list<array{title: string, links: list<array{label: string, category?: string, href: string}>}>, featured: array{title: string, image: string, href: string}}>  $items
     * @return list<array{label: string, department: string, columns: list<array{title: string, links: list<array{label: string, category?: string, href: string}>}>, featured: array{title: string, image: string, href: string}}>
     */
    private function mergeDatabaseCategories(array $items): array
    {
        if (! Schema::hasTable('categories')) {
            return $items;
        }

        $tree = Category::query()
            ->active()
            ->with(['children' => fn ($query) => $query->active()->orderBy('sort_order')->orderBy('name')])
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        foreach ($items as $index => $item) {
            $department = $item['department'];
            $root = $tree->first(function (Category $category) use ($department): bool {
                $slug = Str::lower($category->slug);
                $name = Str::lower($category->name);

                return $slug === $department
                    || str_contains($slug, $department)
                    || ($department === 'women' && (str_contains($name, 'kadın') || str_contains($name, 'kadin') || str_contains($name, 'women')))
                    || ($department === 'men' && (str_contains($name, 'erkek') || str_contains($name, 'men')))
                    || ($department === 'kids' && (str_contains($name, 'çocuk') || str_contains($name, 'cocuk') || str_contains($name, 'kids')))
                    || ($department === 'sport' && (str_contains($name, 'spor') || str_contains($name, 'sport')));
            });

            if ($root === null || $root->children->isEmpty()) {
                continue;
            }

            $existing = collect($item['columns'])->flatMap(fn (array $column) => collect($column['links'])->pluck('category'))->filter()->all();
            $extra = $root->children
                ->reject(fn (Category $child): bool => in_array($child->slug, $existing, true) || in_array($this->leafSlug($child->slug), $existing, true))
                ->map(fn (Category $child): array => [
                    'label' => $child->name,
                    'category' => $this->leafSlug($child->slug),
                    'href' => route('shop.show', ['department' => $department, 'category' => $this->leafSlug($child->slug)]),
                ])
                ->values()
                ->all();

            if ($extra === []) {
                continue;
            }

            $items[$index]['columns'][] = [
                'title' => $this->t('nav.shop'),
                'links' => $extra,
            ];
        }

        return $items;
    }

    /**
     * @return array{label: string, department: string, columns: list<array{title: string, links: list<array{label: string, href: string}>}>, featured: array{title: string, image: string, href: string}}|null
     */
    private function brandNavItem(): ?array
    {
        $brands = $this->brands();

        if ($brands->isEmpty()) {
            return null;
        }

        $chunks = $brands->chunk(8)->values();
        $columns = $chunks->map(function (Collection $chunk, int $index): array {
            return [
                'title' => $index === 0 ? $this->t('nav.brands') : ' ',
                'links' => $chunk->map(fn (Brand $brand): array => [
                    'label' => $brand->name,
                    'href' => route('brands.show', $brand->slug),
                ])->values()->all(),
            ];
        })->all();

        return [
            'label' => $this->t('nav.brands'),
            'department' => 'brands',
            'href' => route('brands.show', $brands->first()->slug),
            'columns' => $columns,
            'featured' => [
                'title' => $this->t('nav.brands'),
                'image' => $this->image('photo-1441986300917-64674bd600d8', 900),
                'href' => route('brands.show', $brands->first()->slug),
            ],
        ];
    }

    private function leafSlug(string $slug): string
    {
        foreach (['women-', 'men-', 'kids-', 'sport-', 'admin-'] as $prefix) {
            if (str_starts_with($slug, $prefix)) {
                return substr($slug, strlen($prefix));
            }
        }

        return $slug;
    }

    /**
     * @param  list<array{title: string, categories?: array<string, string>, links?: list<array{label: string, href: string}>}>  $columns
     * @return array{
     *     label: string,
     *     department: string,
     *     columns: list<array{title: string, links: list<array{label: string, category?: string, href: string}>}>,
     *     featured: array{title: string, image: string, href: string}
     * }
     */
    private function navItem(string $label, string $department, array $columns, string $featuredTitle, string $featuredPhoto): array
    {
        return [
            'label' => $label,
            'department' => $department,
            'columns' => collect($columns)
                ->map(function (array $column) use ($department): array {
                    $links = $column['links'] ?? collect($column['categories'] ?? [])
                        ->map(fn (string $name, string $slug): array => [
                            'label' => $name,
                            'category' => $slug,
                            'href' => route('shop.show', ['department' => $department, 'category' => $slug]),
                        ])
                        ->values()
                        ->all();

                    return [
                        'title' => $column['title'],
                        'links' => $links,
                    ];
                })
                ->all(),
            'featured' => [
                'title' => $featuredTitle,
                'image' => $this->image($featuredPhoto, 900),
                'href' => route('shop.show', ['department' => $department]),
            ],
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function filterByPrice(Collection $products, string $range): Collection
    {
        return match ($range) {
            'under-150' => $products->where('price', '<', 150),
            '150-250' => $products->filter(fn (array $product): bool => $product['price'] >= 150 && $product['price'] <= 250),
            'over-250' => $products->where('price', '>', 250),
            default => $products,
        };
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $products
     * @return Collection<int, array<string, mixed>>
     */
    private function sortProducts(Collection $products, string $sort): Collection
    {
        return match ($sort) {
            'newest' => $products->sortByDesc('id'),
            'price_asc' => $products->sortBy('price'),
            'price_desc' => $products->sortByDesc('price'),
            default => $products->sortBy('id'),
        };
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function products(): array
    {
        return [
            $this->product(
                id: 1,
                name: 'Structured Wool Coat',
                price: 389,
                oldPrice: null,
                photos: ['photo-1539109136881-3be0616acf4b', 'photo-1487222477894-8943e31ef7b2'],
                colors: [['name' => 'Camel', 'hex' => '#c4a574'], ['name' => 'Black', 'hex' => '#1a1a1a']],
                category: 'women',
                type: 'outerwear',
                collection: 'Autumn Winter 2026',
                stock: 12,
                featured: true,
                isNew: true,
                outOfStockSizes: [],
                description: 'A double-faced wool coat with a clean shoulder and concealed fastening. Cut for a straight, architectural line.',
            ),
            $this->product(
                id: 2,
                name: 'Fluid Silk Midi Dress',
                price: 229,
                oldPrice: null,
                photos: ['photo-1515886657613-9f3515b0c78f', 'photo-1525507119028-7c04d6cd9174'],
                colors: [['name' => 'Ivory', 'hex' => '#f3efe6'], ['name' => 'Black', 'hex' => '#1a1a1a']],
                category: 'women',
                type: 'dresses',
                collection: 'Autumn Winter 2026',
                stock: 8,
                featured: true,
                isNew: true,
                outOfStockSizes: ['XL'],
                description: 'Bias-cut silk with a softly draped neckline. Designed to move with a precise, unforced elegance.',
            ),
            $this->product(
                id: 3,
                name: 'Tailored Crepe Blazer',
                price: 259,
                oldPrice: 319,
                photos: ['photo-1591369822096-ffd140ec948f', 'photo-1487222477894-8943e31ef7b2'],
                colors: [['name' => 'Black', 'hex' => '#1a1a1a'], ['name' => 'Stone', 'hex' => '#c8c0b4']],
                category: 'women',
                type: 'blazers',
                collection: 'Essentials',
                stock: 10,
                featured: true,
                isNew: false,
                outOfStockSizes: ['XS'],
                description: 'A single-breasted crepe blazer with a narrow lapel and lightly padded shoulder. Fully lined.',
            ),
            $this->product(
                id: 4,
                name: 'Fine Cashmere Crew',
                price: 179,
                oldPrice: null,
                photos: ['photo-1434389677669-e08b4cac3105', 'photo-1576566588028-4147f3842f27'],
                colors: [['name' => 'Oat', 'hex' => '#d9cbb8'], ['name' => 'Charcoal', 'hex' => '#3a3a3a']],
                category: 'women',
                type: 'knitwear',
                collection: 'Essentials',
                stock: 18,
                featured: true,
                isNew: true,
                outOfStockSizes: [],
                description: 'Twelve-gauge cashmere with a refined crew neck and set-in sleeve. Light enough to layer, substantial enough to stand alone.',
            ),
            $this->product(
                id: 5,
                name: 'Wide-Leg Wool Trousers',
                price: 159,
                oldPrice: null,
                photos: ['photo-1594633312681-425c7b97ccd1', 'photo-1509631179647-0177331693ae'],
                colors: [['name' => 'Black', 'hex' => '#1a1a1a'], ['name' => 'Grey', 'hex' => '#6e6e6e']],
                category: 'women',
                type: 'trousers',
                collection: 'Autumn Winter 2026',
                stock: 14,
                featured: true,
                isNew: true,
                outOfStockSizes: ['M'],
                description: 'Full-length wool trousers with a high rise and pressed crease. Cut to fall cleanly over the shoe.',
            ),
            $this->product(
                id: 6,
                name: 'Leather Shoulder Bag',
                price: 249,
                oldPrice: null,
                photos: ['photo-1584917865442-de89df76afd3', 'photo-1590874103328-eac38a94180c'],
                colors: [['name' => 'Black', 'hex' => '#1a1a1a'], ['name' => 'Cognac', 'hex' => '#8a5a32']],
                category: 'women',
                type: 'accessories',
                collection: 'Essentials',
                stock: 6,
                featured: true,
                isNew: false,
                outOfStockSizes: [],
                sizes: [['code' => 'ONE', 'in_stock' => true]],
                description: 'Vegetable-tanned leather with a structured silhouette and adjustable strap. Unlined, with a single interior pocket.',
            ),
            $this->product(
                id: 7,
                name: 'Merino Rib Knit',
                price: 129,
                oldPrice: 159,
                photos: ['photo-1576566588028-4147f3842f27', 'photo-1434389677669-e08b4cac3105'],
                colors: [['name' => 'Ivory', 'hex' => '#f3efe6'], ['name' => 'Navy', 'hex' => '#1c2430']],
                category: 'women',
                type: 'knitwear',
                collection: 'Essentials',
                stock: 20,
                featured: true,
                isNew: false,
                outOfStockSizes: [],
                description: 'A close rib merino knit with a slight stretch. Designed as a precise second-skin layer.',
            ),
            $this->product(
                id: 8,
                name: 'Pleated Satin Skirt',
                price: 139,
                oldPrice: null,
                photos: ['photo-1485968579580-b6d095142e6e', 'photo-1515886657613-9f3515b0c78f'],
                colors: [['name' => 'Black', 'hex' => '#1a1a1a'], ['name' => 'Wine', 'hex' => '#5c2a32']],
                category: 'women',
                type: 'skirts',
                collection: 'Autumn Winter 2026',
                stock: 9,
                featured: true,
                isNew: true,
                outOfStockSizes: ['S'],
                description: 'Knife-pleated satin with a concealed side zip. Mid-calf length with a fluid, even fall.',
            ),
            $this->product(
                id: 9,
                name: 'Italian Wool Overcoat',
                price: 429,
                oldPrice: null,
                photos: ['photo-1617137968427-85924c800a22', 'photo-1617127365659-c47fa864d8bc'],
                colors: [['name' => 'Charcoal', 'hex' => '#3a3a3a'], ['name' => 'Navy', 'hex' => '#1c2430']],
                category: 'men',
                type: 'outerwear',
                collection: 'Autumn Winter 2026',
                stock: 7,
                featured: true,
                isNew: true,
                outOfStockSizes: [],
                description: 'A knee-length overcoat in compact Italian wool. Notch lapel, welt pockets, and a single-breasted close.',
            ),
            $this->product(
                id: 10,
                name: 'Oxford Cotton Shirt',
                price: 89,
                oldPrice: null,
                photos: ['photo-1602810318383-e386cc2a3ce0', 'photo-1596755094514-f87e34085b2c'],
                colors: [['name' => 'White', 'hex' => '#f5f5f0'], ['name' => 'Sky', 'hex' => '#c5d0d8']],
                category: 'men',
                type: 'shirts',
                collection: 'Essentials',
                stock: 24,
                featured: true,
                isNew: true,
                outOfStockSizes: [],
                description: 'A button-down oxford in compact cotton. Regular collar, mother-of-pearl buttons, and a clean placket.',
            ),
            $this->product(
                id: 11,
                name: 'Tapered Wool Trousers',
                price: 149,
                oldPrice: 189,
                photos: ['photo-1473966968600-fa801b869a1a', 'photo-1617137968427-85924c800a22'],
                colors: [['name' => 'Charcoal', 'hex' => '#3a3a3a'], ['name' => 'Black', 'hex' => '#1a1a1a']],
                category: 'men',
                type: 'trousers',
                collection: 'Essentials',
                stock: 11,
                featured: false,
                isNew: false,
                outOfStockSizes: ['XL'],
                description: 'Tapered wool trousers with a medium rise and unfinished hem, intended to be tailored.',
            ),
            $this->product(
                id: 12,
                name: 'Cotton Knit Polo',
                price: 99,
                oldPrice: null,
                photos: ['photo-1617127365659-c47fa864d8bc', 'photo-1516257984-b1b4d707412e'],
                colors: [['name' => 'Navy', 'hex' => '#1c2430'], ['name' => 'Ecru', 'hex' => '#ece6d8']],
                category: 'men',
                type: 'knitwear',
                collection: 'Autumn Winter 2026',
                stock: 16,
                featured: true,
                isNew: true,
                outOfStockSizes: [],
                description: 'A fine-gauge cotton polo with a knitted collar and a slightly relaxed torso.',
            ),
            $this->product(
                id: 13,
                name: 'Suede Chelsea Boot',
                price: 279,
                oldPrice: null,
                photos: ['photo-1638247025967-b4cc0c10d0d4', 'photo-1608256246200-53eacdea99c0'],
                colors: [['name' => 'Cognac', 'hex' => '#8a5a32'], ['name' => 'Black', 'hex' => '#1a1a1a']],
                category: 'men',
                type: 'shoes',
                collection: 'Essentials',
                stock: 5,
                featured: false,
                isNew: false,
                outOfStockSizes: ['XS'],
                description: 'A suede Chelsea boot on a stacked leather sole. Elastic gusset and a discreet pull tab.',
            ),
            $this->product(
                id: 14,
                name: 'Merino V-Neck',
                price: 119,
                oldPrice: 149,
                photos: ['photo-1516257984-b1b4d707412e', 'photo-1617127365659-c47fa864d8bc'],
                colors: [['name' => 'Grey', 'hex' => '#6e6e6e'], ['name' => 'Navy', 'hex' => '#1c2430']],
                category: 'men',
                type: 'knitwear',
                collection: 'Essentials',
                stock: 13,
                featured: false,
                isNew: false,
                outOfStockSizes: [],
                description: 'A lightweight merino V-neck with a neat rib trim. Cut to sit cleanly under a shirt collar.',
            ),
            $this->product(
                id: 15,
                name: 'Cropped Wool Jacket',
                price: 289,
                oldPrice: null,
                photos: ['photo-1490481651871-ab68de25d43d', 'photo-1483985988355-763728e1935b'],
                colors: [['name' => 'Camel', 'hex' => '#c4a574']],
                category: 'women',
                type: 'outerwear',
                collection: 'Autumn Winter 2026',
                stock: 4,
                featured: false,
                isNew: true,
                outOfStockSizes: ['L', 'XL'],
                description: 'A cropped wool jacket with a stand collar and welt pockets. Intended to sit at the high hip.',
            ),
            $this->product(
                id: 16,
                name: 'Relaxed Denim',
                price: 119,
                oldPrice: null,
                photos: ['photo-1541099649105-f69ad21f3246', 'photo-1542272604-787c3835535d'],
                colors: [['name' => 'Indigo', 'hex' => '#2c3a4a'], ['name' => 'Stone', 'hex' => '#c8c0b4']],
                category: 'women',
                type: 'denim',
                collection: 'Essentials',
                stock: 15,
                featured: false,
                isNew: true,
                outOfStockSizes: [],
                description: 'A relaxed straight jean in organic cotton denim. Mid rise, with a clean unfinished hem.',
            ),
            $this->product(
                id: 17,
                name: 'Cotton Jersey Dress',
                price: 59,
                oldPrice: null,
                photos: ['photo-1503453342920-ded826308edf', 'photo-1471286174890-9c112ffca5b4'],
                colors: [['name' => 'Ivory', 'hex' => '#f3efe6'], ['name' => 'Navy', 'hex' => '#1c2430']],
                category: 'kids',
                type: 'dresses',
                collection: 'Autumn Winter 2026',
                stock: 10,
                featured: false,
                isNew: true,
                outOfStockSizes: [],
                description: 'A soft cotton jersey dress with a gentle A-line and a clean neckline. Cut for ease of movement.',
            ),
            $this->product(
                id: 18,
                name: 'Wool Overshirt',
                price: 89,
                oldPrice: null,
                photos: ['photo-1471286174890-9c112ffca5b4', 'photo-1503453342920-ded826308edf'],
                colors: [['name' => 'Camel', 'hex' => '#c4a574'], ['name' => 'Charcoal', 'hex' => '#3a3a3a']],
                category: 'kids',
                type: 'outerwear',
                collection: 'Essentials',
                stock: 8,
                featured: false,
                isNew: true,
                outOfStockSizes: [],
                description: 'A compact wool overshirt with a straight hem and patch pockets. Intended as a light extra layer.',
            ),
            $this->product(
                id: 19,
                name: 'Performance Knit',
                price: 129,
                oldPrice: null,
                photos: ['photo-1517836357463-d25dfeac3438', 'photo-1571019614242-c5c5dee9f50b'],
                colors: [['name' => 'Black', 'hex' => '#1a1a1a'], ['name' => 'Grey', 'hex' => '#6e6e6e']],
                category: 'sport',
                type: 'knitwear',
                collection: 'Autumn Winter 2026',
                stock: 14,
                featured: false,
                isNew: true,
                outOfStockSizes: [],
                description: 'A fine technical knit with a close rib cuff. Designed to sit cleanly under a training layer.',
            ),
            $this->product(
                id: 20,
                name: 'Training Trousers',
                price: 99,
                oldPrice: 129,
                photos: ['photo-1571019614242-c5c5dee9f50b', 'photo-1517836357463-d25dfeac3438'],
                colors: [['name' => 'Black', 'hex' => '#1a1a1a'], ['name' => 'Navy', 'hex' => '#1c2430']],
                category: 'sport',
                type: 'trousers',
                collection: 'Essentials',
                stock: 11,
                featured: false,
                isNew: false,
                outOfStockSizes: [],
                description: 'Tapered training trousers in a compact stretch weave. Side pockets and a clean elastic waist.',
            ),
        ];
    }

    /**
     * @param  list<string>  $photos
     * @param  list<array{name: string, hex: string}>  $colors
     * @param  list<string>  $outOfStockSizes
     * @param  list<array{code: string, in_stock: bool}>|null  $sizes
     * @return array<string, mixed>
     */
    private function product(
        int $id,
        string $name,
        float $price,
        ?float $oldPrice,
        array $photos,
        array $colors,
        string $category,
        string $type,
        string $collection,
        int $stock,
        bool $featured,
        bool $isNew,
        array $outOfStockSizes,
        string $description,
        ?array $sizes = null,
    ): array {
        return [
            'id' => $id,
            'name' => $name,
            'slug' => Str::slug($name),
            'price' => $price,
            'oldPrice' => $oldPrice,
            'discountPercent' => $oldPrice !== null && $oldPrice > 0
                ? (int) round((($oldPrice - $price) / $oldPrice) * 100)
                : null,
            'discountName' => null,
            'vat_rate' => 20,
            'currency' => 'EUR',
            'images' => array_map(fn (string $photo): string => $this->image($photo, 1400), $photos),
            'colors' => $colors,
            'sizes' => $sizes ?? $this->sizes($outOfStockSizes),
            'variantPrices' => [],
            'category' => $category,
            'type' => $type,
            'collection' => $collection,
            'stock' => $stock,
            'description' => $description,
            'featured' => $featured,
            'isNew' => $isNew,
            'material' => $this->t('product.material_see_details'),
        ];
    }

    private function categoryLabel(string $category): string
    {
        $name = $this->databaseCategoryName($category);

        if (filled($name)) {
            return $name;
        }

        $key = 'nav.'.$category;

        if (trans()->has('storefront.'.$key)) {
            return $this->t($key);
        }

        return Str::headline(str_replace('-', ' ', $category));
    }

    private function databaseCategoryName(string $slug): ?string
    {
        if (! Schema::hasTable('categories')) {
            return null;
        }

        $categories = Category::query()
            ->where(function (Builder $query) use ($slug): void {
                $query->where('slug', $slug)
                    ->orWhere('slug', 'like', '%-'.$slug);
            })
            ->get(['slug', 'name']);

        $exact = $categories->firstWhere('slug', $slug);

        if ($exact !== null) {
            return $exact->name;
        }

        return $categories
            ->first(fn (Category $category): bool => $this->leafSlug($category->slug) === $slug)
            ?->name;
    }

    /**
     * @param  array<string, string>  $replace
     */
    private function t(string $key, array $replace = []): string
    {
        return __('storefront.'.$key, $replace);
    }

    /**
     * @param  list<string>  $outOfStock
     * @return list<array{code: string, in_stock: bool}>
     */
    private function sizes(array $outOfStock = []): array
    {
        return collect(['XS', 'S', 'M', 'L', 'XL'])
            ->map(fn (string $code): array => [
                'code' => $code,
                'in_stock' => ! in_array($code, $outOfStock, true),
            ])
            ->all();
    }

    private function image(string $photo, int $width): string
    {
        return 'https://images.unsplash.com/'.$photo.'?auto=format&fit=crop&w='.$width.'&q=80';
    }
}
