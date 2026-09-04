<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\SiteContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function index(SiteContent $site, ?string $section = null): View
    {
        $section ??= 'about';
        $current = $site->section($section);

        return view('admin.site.index', [
            'section' => $section,
            'sections' => $site->sectionNav(),
            'content' => $current,
        ]);
    }

    public function update(Request $request, string $section, SiteContent $site): RedirectResponse
    {
        abort_unless(isset(SiteContent::SECTIONS[$section]), 404);

        $site->update($section, $request->validate($this->rules($section)));

        return redirect()
            ->route('admin.site.index', $section)
            ->with('status', __('admin.toast.site_saved'));
    }

    /**
     * @return array<string, list<string>>
     */
    private function rules(string $section): array
    {
        $rules = [
            'heading' => ['required', 'string', 'max:80'],
            'pages' => ['required', 'array'],
        ];

        if ($section === 'journal') {
            $rules['text'] = ['required', 'string', 'max:255'];
        }

        foreach (SiteContent::SECTIONS[$section] as $slug) {
            $rules['pages.'.$slug.'.title'] = ['required', 'string', 'max:120'];
            $rules['pages.'.$slug.'.kicker'] = ['required', 'string', 'max:80'];
            $rules['pages.'.$slug.'.p1'] = ['required', 'string', 'max:2000'];
            $rules['pages.'.$slug.'.p2'] = ['nullable', 'string', 'max:2000'];
        }

        return $rules;
    }
}
