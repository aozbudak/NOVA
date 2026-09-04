<?php

namespace Tests\Feature;

use App\Enums\StaffRole;
use App\Support\SiteContent;
use Tests\TestCase;

class AdminSiteTest extends TestCase
{
    public function test_site_page_lists_footer_sections(): void
    {
        $response = $this->get(route('admin.site.index'));

        $response->assertOk();
        $response->assertSee('Site management');
        $response->assertSee('About NOVA');
        $response->assertSee('Help');
        $response->assertSee('Journal');
        $response->assertSee('Section heading');
        $response->assertSee('The House');
        $response->assertSee('contemporary fashion house');
    }

    public function test_each_site_section_renders_its_pages(): void
    {
        $this->get(route('admin.site.index', 'help'))
            ->assertOk()
            ->assertSee('Contact')
            ->assertSee('Shipping')
            ->assertSee('Returns')
            ->assertSee('FAQ')
            ->assertSee('Size Guide');

        $this->get(route('admin.site.index', 'journal'))
            ->assertOk()
            ->assertSee('Journal text')
            ->assertSee('New collections, quietly announced.')
            ->assertSee('Privacy')
            ->assertSee('Terms')
            ->assertSee('Cookies');
    }

    public function test_about_section_persists_and_updates_the_storefront(): void
    {
        $this->put(route('admin.site.update', 'about'), $this->aboutPayload([
            'heading' => 'The House',
            'pages' => [
                'about' => [
                    'title' => 'Our atelier',
                    'p1' => 'NOVA designs in Berlin.',
                ],
            ],
        ]))
            ->assertRedirect(route('admin.site.index', 'about'))
            ->assertSessionHas('status', 'Site content saved successfully.');

        $this->get(route('admin.site.index', 'about'))
            ->assertOk()
            ->assertSee('The House')
            ->assertSee('Our atelier')
            ->assertSee('NOVA designs in Berlin.');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('The House')
            ->assertSee('Our atelier');

        $this->get(route('pages.show', 'about'))
            ->assertOk()
            ->assertSee('Our atelier')
            ->assertSee('NOVA designs in Berlin.');
    }

    public function test_journal_text_updates_the_footer(): void
    {
        $section = (new SiteContent)->section('journal');

        $this->put(route('admin.site.update', 'journal'), [
            'heading' => 'Journal',
            'text' => 'Lookbook notes, sent quietly.',
            'pages' => $section['pages'],
        ])->assertRedirect(route('admin.site.index', 'journal'));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Lookbook notes, sent quietly.');
    }

    public function test_empty_about_payload_is_rejected(): void
    {
        $this->from(route('admin.site.index', 'about'))
            ->put(route('admin.site.update', 'about'), [])
            ->assertRedirect(route('admin.site.index', 'about'))
            ->assertSessionHasErrors(['heading', 'pages']);
    }

    public function test_unknown_site_section_returns_404(): void
    {
        $this->get(route('admin.site.index', 'billing'))->assertNotFound();
    }

    public function test_cashier_is_forbidden_from_site_management(): void
    {
        $this->withSession(['admin.role' => StaffRole::Cashier->value])
            ->get(route('admin.site.index'))
            ->assertForbidden();
    }

    public function test_site_content_is_escaped_on_the_storefront(): void
    {
        $this->put(route('admin.site.update', 'about'), $this->aboutPayload([
            'heading' => "<script>alert('xss')</script>",
            'pages' => [
                'about' => [
                    'title' => "<script>alert('title')</script>",
                    'p1' => "<script>alert('body')</script>",
                ],
            ],
        ]));

        $home = $this->get(route('home'));

        $home->assertSee("<script>alert('xss')</script>");
        $home->assertDontSee("<script>alert('xss')</script>", false);
        $home->assertDontSee("<script>alert('title')</script>", false);

        $page = $this->get(route('pages.show', 'about'));

        $page->assertSee("<script>alert('title')</script>");
        $page->assertDontSee("<script>alert('title')</script>", false);
        $page->assertDontSee("<script>alert('body')</script>", false);
    }

    /**
     * @param  array{heading?: string, pages?: array<string, array{title?: string, kicker?: string, p1?: string, p2?: string}>}  $overrides
     * @return array{heading: string, pages: array<string, array{title: string, kicker: string, p1: string, p2: string}>}
     */
    private function aboutPayload(array $overrides = []): array
    {
        $section = (new SiteContent)->section('about');
        $pages = [];

        foreach ($section['pages'] as $slug => $page) {
            $pages[$slug] = [
                ...$page,
                ...($overrides['pages'][$slug] ?? []),
            ];
        }

        return [
            'heading' => $overrides['heading'] ?? $section['heading'],
            'pages' => $pages,
        ];
    }
}
