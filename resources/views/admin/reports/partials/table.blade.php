@if (! empty($report['headers']))
    <x-admin.table>
        <x-slot:head>
            @foreach ($report['headers'] as $header)
                <x-admin.th>{{ $header }}</x-admin.th>
            @endforeach
        </x-slot:head>
        <x-slot:body>
            @forelse ($report['table'] as $row)
                <tr class="border-b border-border last:border-b-0">
                    @foreach ($row as $index => $cell)
                        <x-admin.td :label="$report['headers'][$index] ?? ''">{{ $cell }}</x-admin.td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($report['headers']) }}" class="px-3 py-6 text-center text-muted-foreground">{{ __('admin.search.empty') }}</td>
                </tr>
            @endforelse
        </x-slot:body>
    </x-admin.table>
@endif
