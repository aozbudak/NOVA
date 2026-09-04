<?php

namespace App\Support;

final class SiteContent
{
    /**
     * @var array<string, list<string>>
     */
    public const SECTIONS = [
        'about' => ['about', 'careers', 'sustainability'],
        'help' => ['contact', 'shipping', 'returns', 'faq', 'size-guide'],
        'journal' => ['privacy', 'terms', 'cookies'],
    ];

    /**
     * @return list<array{key: string, label: string}>
     */
    public function sectionNav(): array
    {
        return collect(array_keys(self::SECTIONS))
            ->map(fn (string $key): array => [
                'key' => $key,
                'label' => __('admin.site.sections.'.$key),
            ])
            ->all();
    }

    /**
     * @return array<string, array{heading: string, text?: string, pages: array<string, array{title: string, kicker: string, p1: string, p2: string}>}>
     */
    public function all(): array
    {
        $content = $this->defaults();
        $saved = session('admin.site_content', []);

        foreach ($content as $section => $config) {
            if (! is_array($saved[$section] ?? null)) {
                continue;
            }

            if (isset($saved[$section]['heading']) && is_string($saved[$section]['heading'])) {
                $content[$section]['heading'] = $saved[$section]['heading'];
            }

            if ($section === 'journal' && isset($saved[$section]['text']) && is_string($saved[$section]['text'])) {
                $content[$section]['text'] = $saved[$section]['text'];
            }

            foreach ($config['pages'] as $slug => $page) {
                if (! is_array($saved[$section]['pages'][$slug] ?? null)) {
                    continue;
                }

                foreach (['title', 'kicker', 'p1', 'p2'] as $field) {
                    $value = $saved[$section]['pages'][$slug][$field] ?? null;

                    if (is_string($value)) {
                        $content[$section]['pages'][$slug][$field] = $value;
                    }
                }
            }
        }

        return $content;
    }

    /**
     * @return array{heading: string, text?: string, pages: array<string, array{title: string, kicker: string, p1: string, p2: string}>}
     */
    public function section(string $section): array
    {
        abort_unless(isset(self::SECTIONS[$section]), 404);

        return $this->all()[$section];
    }

    /**
     * @return array{title: string, kicker: string, p1: string, p2: string}|null
     */
    public function page(string $slug): ?array
    {
        foreach ($this->all() as $section) {
            if (isset($section['pages'][$slug])) {
                return $section['pages'][$slug];
            }
        }

        return null;
    }

    /**
     * @return array{about: array{heading: string, links: list<array{slug: string, label: string}>}, help: array{heading: string, links: list<array{slug: string, label: string}>}, journal: array{heading: string, text: string, links: list<array{slug: string, label: string}>}}
     */
    public function footer(): array
    {
        $content = $this->all();

        return [
            'about' => [
                'heading' => $content['about']['heading'],
                'links' => $this->links($content['about']['pages']),
            ],
            'help' => [
                'heading' => $content['help']['heading'],
                'links' => $this->links($content['help']['pages']),
            ],
            'journal' => [
                'heading' => $content['journal']['heading'],
                'text' => $content['journal']['text'],
                'links' => $this->links($content['journal']['pages']),
            ],
        ];
    }

    /**
     * @param  array{heading: string, text?: string, pages: array<string, array{title: string, kicker: string, p1: string, p2?: string}>}  $data
     * @return array{heading: string, text?: string, pages: array<string, array{title: string, kicker: string, p1: string, p2: string}>}
     */
    public function update(string $section, array $data): array
    {
        abort_unless(isset(self::SECTIONS[$section]), 404);

        $content = $this->all();
        $pages = [];

        foreach (self::SECTIONS[$section] as $slug) {
            $page = $data['pages'][$slug] ?? [];

            $pages[$slug] = [
                'title' => $page['title'] ?? $content[$section]['pages'][$slug]['title'],
                'kicker' => $page['kicker'] ?? $content[$section]['pages'][$slug]['kicker'],
                'p1' => $page['p1'] ?? $content[$section]['pages'][$slug]['p1'],
                'p2' => $page['p2'] ?? $content[$section]['pages'][$slug]['p2'],
            ];
        }

        $content[$section] = [
            'heading' => $data['heading'] ?? $content[$section]['heading'],
            'pages' => $pages,
        ];

        if ($section === 'journal') {
            $content[$section]['text'] = $data['text'] ?? $content[$section]['text'];
        }

        session(['admin.site_content' => $content]);

        return $content[$section];
    }

    /**
     * @return array<string, array{heading: string, text?: string, pages: array<string, array{title: string, kicker: string, p1: string, p2: string}>}>
     */
    private function defaults(): array
    {
        $sections = [];

        foreach (self::SECTIONS as $section => $slugs) {
            $pages = [];

            foreach ($slugs as $slug) {
                $pages[$slug] = [
                    'title' => __('storefront.pages.'.$slug.'.title'),
                    'kicker' => __('storefront.pages.'.$slug.'.kicker'),
                    'p1' => __('storefront.pages.'.$slug.'.p1'),
                    'p2' => trans()->has('storefront.pages.'.$slug.'.p2')
                        ? __('storefront.pages.'.$slug.'.p2')
                        : '',
                ];
            }

            $sections[$section] = [
                'heading' => __('storefront.footer.'.$section),
                'pages' => $pages,
            ];
        }

        $sections['journal']['text'] = __('storefront.footer.journal_text');

        return $sections;
    }

    /**
     * @param  array<string, array{title: string, kicker: string, p1: string, p2: string}>  $pages
     * @return list<array{slug: string, label: string}>
     */
    private function links(array $pages): array
    {
        $links = [];

        foreach ($pages as $slug => $page) {
            $links[] = [
                'slug' => $slug,
                'label' => $page['title'],
            ];
        }

        return $links;
    }
}
