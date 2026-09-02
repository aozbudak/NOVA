@if (! empty($report['headers']))
    <div class="overflow-x-auto rounded-md border border-border bg-card">
        <table class="w-full text-left text-[13px]">
            <thead class="border-b border-border text-[11px] tracking-wide text-muted-foreground uppercase">
                <tr>
                    @foreach ($report['headers'] as $header)
                        <th class="px-3 py-2 font-medium">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($report['table'] as $row)
                    <tr class="border-b border-border last:border-b-0">
                        @foreach ($row as $cell)
                            <td class="px-3 py-2.5 text-foreground">{{ $cell }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($report['headers']) }}" class="px-3 py-6 text-center text-muted-foreground">{{ __('admin.search.empty') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endif
