<?php

namespace App\Support;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class AdminList
{
    /**
     * @param  Collection<int, mixed>  $items
     * @param  list<string>  $sortable
     */
    public static function apply(Collection $items, array $sortable = [], int $perPage = 20): LengthAwarePaginator
    {
        $sort = request()->string('sort')->toString();
        $direction = request()->string('dir')->toString() === 'desc' ? 'desc' : 'asc';

        if ($sort !== '' && in_array($sort, $sortable, true)) {
            $items = $items->sortBy($sort, SORT_NATURAL | SORT_FLAG_CASE, $direction === 'desc')->values();
        }

        $page = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => request()->query(),
            ],
        );
    }

    /**
     * @param  array<string, string>  $filters
     * @param  array<string, array{label: string, value?: string}>  $definitions
     * @return list<array{key: string, label: string, value: string, url: string}>
     */
    public static function chips(array $filters, array $definitions): array
    {
        $chips = [];

        foreach ($definitions as $key => $definition) {
            $raw = trim((string) ($filters[$key] ?? ''));

            if ($raw === '') {
                continue;
            }

            $chips[] = [
                'key' => $key,
                'label' => $definition['label'],
                'value' => $definition['value'] ?? $raw,
                'url' => request()->fullUrlWithoutQuery($key),
            ];
        }

        return $chips;
    }
}
