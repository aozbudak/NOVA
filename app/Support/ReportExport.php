<?php

namespace App\Support;

use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ReportExport
{
    public function __construct(private AdminStore $store) {}

    /**
     * @param  array<string, mixed>  $report
     */
    public function download(array $report, string $format): StreamedResponse
    {
        abort_unless(in_array($format, ['csv', 'excel', 'pdf'], true), 404);

        $document = $this->document($report);
        $filename = Str::slug((string) $report['key']).'-report.'.$this->extension($format);

        return response()->streamDownload(function () use ($document, $format): void {
            echo match ($format) {
                'csv' => $this->csv($document),
                'excel' => $this->excel($document),
                'pdf' => $this->pdf($document),
            };
        }, $filename, [
            'Content-Type' => $this->contentType($format),
        ]);
    }

    /**
     * @param  array<string, mixed>  $report
     * @return array{
     *     brand: string,
     *     tagline: string,
     *     title: string,
     *     period: string,
     *     generated: string,
     *     footer: string,
     *     metrics: list<array{label: string, value: string}>,
     *     headers: list<string>,
     *     rows: list<list<string>>
     * }
     */
    private function document(array $report): array
    {
        $settings = $this->store->settings();
        $timezone = (string) ($settings['timezone'] ?? config('app.timezone'));

        return [
            'brand' => __('admin.brand'),
            'tagline' => __('admin.brand_sub'),
            'title' => (string) $report['title'],
            'period' => $this->periodLabel($report),
            'generated' => now()->timezone($timezone)->format('Y-m-d H:i'),
            'footer' => collect([
                $settings['store_name'] ?? __('admin.brand'),
                $settings['address'] ?? null,
                $settings['store_email'] ?? null,
            ])->filter()->implode('  ·  '),
            'metrics' => collect($report['metrics'] ?? [])
                ->map(fn (array $metric): array => [
                    'label' => (string) $metric['label'],
                    'value' => (string) $metric['value'],
                ])
                ->values()
                ->all(),
            'headers' => array_map(fn (mixed $header): string => (string) $header, $report['headers'] ?? []),
            'rows' => collect($report['table'] ?? [])
                ->map(fn (array $row): array => array_map(fn (mixed $value): string => (string) $value, $row))
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private function periodLabel(array $report): string
    {
        $from = (string) ($report['from'] ?? '');
        $to = (string) ($report['to'] ?? '');

        if ($from !== '' && $to !== '') {
            $span = $from === $to ? $from : $from.' — '.$to;

            if (filled($report['range'] ?? null) && in_array($report['range'], ['today', 'week', 'month', 'custom'], true)) {
                return __('admin.reports.range.'.$report['range']).' · '.$span;
            }

            return $span;
        }

        if (filled($report['date'] ?? null)) {
            return (string) $report['date'];
        }

        return now()->toDateString();
    }

    /**
     * @param  array{
     *     brand: string,
     *     tagline: string,
     *     title: string,
     *     period: string,
     *     generated: string,
     *     footer: string,
     *     metrics: list<array{label: string, value: string}>,
     *     headers: list<string>,
     *     rows: list<list<string>>
     * }  $document
     */
    private function csv(array $document): string
    {
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, [$document['brand'], $document['tagline']]);
        fputcsv($handle, [$document['title']]);
        fputcsv($handle, [__('admin.reports.period'), $document['period']]);
        fputcsv($handle, [__('admin.reports.generated_at'), $document['generated']]);
        fputcsv($handle, []);
        fputcsv($handle, [__('admin.reports.metric'), __('admin.reports.value')]);

        foreach ($document['metrics'] as $metric) {
            fputcsv($handle, [$metric['label'], $metric['value']]);
        }

        fputcsv($handle, []);
        fputcsv($handle, $document['headers']);

        foreach ($document['rows'] as $row) {
            fputcsv($handle, $row);
        }

        fputcsv($handle, []);
        fputcsv($handle, [$document['footer']]);

        rewind($handle);
        $csv = stream_get_contents($handle) ?: '';
        fclose($handle);

        return "\u{FEFF}".$csv;
    }

    /**
     * @param  array{
     *     brand: string,
     *     tagline: string,
     *     title: string,
     *     period: string,
     *     generated: string,
     *     footer: string,
     *     metrics: list<array{label: string, value: string}>,
     *     headers: list<string>,
     *     rows: list<list<string>>
     * }  $document
     */
    private function excel(array $document): string
    {
        $columns = max(2, count($document['headers']));
        $merge = max(0, $columns - 1);
        $sheet = $this->xml($this->sheetName($document['title']));

        $rows = $this->excelRow([$document['brand']], 'Brand', $merge);
        $rows .= $this->excelRow([$document['title']], 'Title', $merge);
        $rows .= $this->excelRow([__('admin.reports.period').'  '.$document['period']], 'Meta', $merge);
        $rows .= $this->excelRow([__('admin.reports.generated_at').'  '.$document['generated']], 'Meta', $merge);
        $rows .= '<Row></Row>';
        $rows .= $this->excelRow([__('admin.reports.metric'), __('admin.reports.value')], 'ColHeader');

        foreach ($document['metrics'] as $index => $metric) {
            $rows .= $this->excelRow([$metric['label'], $metric['value']], $index % 2 === 0 ? 'Metric' : 'MetricAlt');
        }

        $rows .= '<Row></Row>';
        $rows .= $this->excelRow($document['headers'], 'ColHeader');

        foreach ($document['rows'] as $index => $row) {
            $rows .= $this->excelRow($row, $index % 2 === 0 ? 'Cell' : 'CellAlt');
        }

        if ($document['rows'] === []) {
            $rows .= $this->excelRow([__('admin.search.empty')], 'Meta', $merge);
        }

        $rows .= '<Row></Row>';
        $rows .= $this->excelRow([$document['footer']], 'Footer', $merge);

        $columnXml = '';

        for ($index = 0; $index < $columns; $index++) {
            $columnXml .= '<Column ss:AutoFitWidth="0" ss:Width="'.($index === 0 ? '160' : '110').'"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<?mso-application progid="Excel.Sheet"?>'
            .'<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">'
            .$this->excelStyles()
            .'<Worksheet ss:Name="'.$sheet.'"><Table>'.$columnXml.$rows.'</Table></Worksheet></Workbook>';
    }

    /**
     * @param  list<string>  $values
     */
    private function excelRow(array $values, string $style, int $merge = 0): string
    {
        $cells = '';

        foreach (array_values($values) as $index => $value) {
            $span = $index === 0 && $merge > 0 ? ' ss:MergeAcross="'.$merge.'"' : '';
            $cells .= '<Cell'.$span.' ss:StyleID="'.$style.'"><Data ss:Type="String">'.$this->xml($value).'</Data></Cell>';
        }

        $height = match ($style) {
            'Brand' => ' ss:Height="28"',
            'Title' => ' ss:Height="22"',
            default => '',
        };

        return '<Row'.$height.'>'.$cells.'</Row>';
    }

    private function excelStyles(): string
    {
        return '<Styles>'
            .'<Style ss:ID="Brand"><Font ss:FontName="Georgia" ss:Size="18" ss:Bold="1" ss:Color="#F7F4EE"/><Interior ss:Color="#1C1814" ss:Pattern="Solid"/></Style>'
            .'<Style ss:ID="Title"><Font ss:FontName="Georgia" ss:Size="14" ss:Color="#1C1814"/><Interior ss:Color="#F7F4EE" ss:Pattern="Solid"/></Style>'
            .'<Style ss:ID="Meta"><Font ss:FontName="Calibri" ss:Size="10" ss:Color="#7A7268"/><Interior ss:Color="#F7F4EE" ss:Pattern="Solid"/></Style>'
            .'<Style ss:ID="Footer"><Font ss:FontName="Calibri" ss:Size="9" ss:Color="#7A7268"/><Interior ss:Color="#F7F4EE" ss:Pattern="Solid"/></Style>'
            .'<Style ss:ID="ColHeader"><Font ss:FontName="Calibri" ss:Size="10" ss:Bold="1" ss:Color="#1C1814"/><Interior ss:Color="#EDE8DF" ss:Pattern="Solid"/>'.$this->excelBorders().'</Style>'
            .'<Style ss:ID="Metric"><Font ss:FontName="Calibri" ss:Size="10" ss:Color="#1C1814"/><Interior ss:Color="#F4F1EA" ss:Pattern="Solid"/>'.$this->excelBorders().'</Style>'
            .'<Style ss:ID="MetricAlt"><Font ss:FontName="Calibri" ss:Size="10" ss:Color="#1C1814"/><Interior ss:Color="#FBF9F4" ss:Pattern="Solid"/>'.$this->excelBorders().'</Style>'
            .'<Style ss:ID="Cell"><Font ss:FontName="Calibri" ss:Size="10" ss:Color="#1C1814"/><Interior ss:Color="#FFFCF7" ss:Pattern="Solid"/>'.$this->excelBorders().'</Style>'
            .'<Style ss:ID="CellAlt"><Font ss:FontName="Calibri" ss:Size="10" ss:Color="#1C1814"/><Interior ss:Color="#F7F4EE" ss:Pattern="Solid"/>'.$this->excelBorders().'</Style>'
            .'</Styles>';
    }

    private function excelBorders(): string
    {
        $color = 'ss:Color="#D6CEC2" ss:Weight="1" ss:LineStyle="Continuous"';

        return '<Borders>'
            .'<Border ss:Position="Left" '.$color.'/>'
            .'<Border ss:Position="Right" '.$color.'/>'
            .'<Border ss:Position="Top" '.$color.'/>'
            .'<Border ss:Position="Bottom" '.$color.'/>'
            .'</Borders>';
    }

    /**
     * @param  array{
     *     brand: string,
     *     tagline: string,
     *     title: string,
     *     period: string,
     *     generated: string,
     *     footer: string,
     *     metrics: list<array{label: string, value: string}>,
     *     headers: list<string>,
     *     rows: list<list<string>>
     * }  $document
     */
    private function pdf(array $document): string
    {
        $pageWidth = 842.0;
        $pageHeight = 595.0;
        $margin = 36.0;
        $streams = [];
        $ops = $this->pdfPageChrome($document, $pageWidth, $pageHeight, $margin);
        $cursor = 488.0;

        $metrics = $document['metrics'];
        $perRow = $metrics === [] ? 1 : min(4, count($metrics));
        $gap = 8.0;
        $boxWidth = ($pageWidth - ($margin * 2) - ($gap * ($perRow - 1))) / $perRow;
        $boxHeight = 42.0;

        foreach (array_values($metrics) as $index => $metric) {
            $column = $index % $perRow;
            $row = intdiv($index, $perRow);
            $x = $margin + ($column * ($boxWidth + $gap));
            $y = $cursor - ($row * ($boxHeight + $gap));

            $ops .= $this->pdfFill(0.957, 0.945, 0.918);
            $ops .= $this->pdfRect($x, $y - $boxHeight, $boxWidth, $boxHeight, 'f');
            $ops .= $this->pdfStroke(0.839, 0.808, 0.753);
            $ops .= $this->pdfRect($x, $y - $boxHeight, $boxWidth, $boxHeight, 'S');
            $ops .= $this->pdfText($x + 10, $y - 16, $metric['label'], 8, 'F1', 0.478, 0.447, 0.408);
            $ops .= $this->pdfText($x + 10, $y - 32, $metric['value'], 12, 'F2', 0.110, 0.094, 0.078);
        }

        if ($metrics !== []) {
            $cursor -= (intdiv(count($metrics) - 1, $perRow) + 1) * ($boxHeight + $gap) + 12;
        }

        $headers = $document['headers'] !== [] ? $document['headers'] : [__('admin.search.empty')];
        $columnCount = count($headers);
        $tableWidth = $pageWidth - ($margin * 2);
        $columnWidth = $tableWidth / $columnCount;
        $rowHeight = 18.0;

        $drawHeader = function () use (&$ops, $headers, $margin, &$cursor, $columnWidth, $rowHeight): void {
            $ops .= $this->pdfFill(0.929, 0.910, 0.875);
            $ops .= $this->pdfRect($margin, $cursor - $rowHeight, $columnWidth * count($headers), $rowHeight, 'f');

            foreach ($headers as $index => $header) {
                $x = $margin + ($index * $columnWidth);
                $ops .= $this->pdfStroke(0.839, 0.808, 0.753);
                $ops .= $this->pdfRect($x, $cursor - $rowHeight, $columnWidth, $rowHeight, 'S');
                $ops .= $this->pdfText($x + 6, $cursor - 13, $header, 8, 'F3', 0.110, 0.094, 0.078, $columnWidth - 12);
            }

            $cursor -= $rowHeight;
        };

        $drawHeader();

        $rows = $document['rows'] === []
            ? [array_pad([__('admin.search.empty')], $columnCount, '')]
            : $document['rows'];

        foreach ($rows as $rowIndex => $row) {
            if ($cursor < 64) {
                $ops .= $this->pdfText($margin, 28, $document['footer'].'  ·  '.($streams === [] ? '1' : (string) (count($streams) + 1)), 8, 'F1', 0.478, 0.447, 0.408);
                $streams[] = $ops;
                $ops = $this->pdfPageChrome($document, $pageWidth, $pageHeight, $margin);
                $cursor = 488.0;
                $drawHeader();
            }

            $fill = $rowIndex % 2 === 0
                ? [1.000, 0.988, 0.969]
                : [0.969, 0.957, 0.933];
            $ops .= $this->pdfFill($fill[0], $fill[1], $fill[2]);
            $ops .= $this->pdfRect($margin, $cursor - $rowHeight, $tableWidth, $rowHeight, 'f');

            foreach (array_keys($headers) as $index) {
                $x = $margin + ($index * $columnWidth);
                $ops .= $this->pdfStroke(0.839, 0.808, 0.753);
                $ops .= $this->pdfRect($x, $cursor - $rowHeight, $columnWidth, $rowHeight, 'S');
                $ops .= $this->pdfText($x + 6, $cursor - 13, (string) ($row[$index] ?? ''), 8, 'F1', 0.110, 0.094, 0.078, $columnWidth - 12);
            }

            $cursor -= $rowHeight;
        }

        $ops .= $this->pdfText($margin, 28, $document['footer'].'  ·  '.(string) (count($streams) + 1), 8, 'F1', 0.478, 0.447, 0.408);
        $streams[] = $ops;

        return $this->assemblePdf($streams, $document);
    }

    /**
     * @param  array{brand: string, tagline: string, title: string, period: string, generated: string}  $document
     */
    private function pdfPageChrome(array $document, float $pageWidth, float $pageHeight, float $margin): string
    {
        $ops = $this->pdfFill(0.969, 0.957, 0.933);
        $ops .= $this->pdfRect(0, 0, $pageWidth, $pageHeight, 'f');
        $ops .= $this->pdfFill(0.110, 0.094, 0.078);
        $ops .= $this->pdfRect(0, $pageHeight - 52, $pageWidth, 52, 'f');
        $ops .= $this->pdfText($margin, $pageHeight - 32, $document['brand'], 18, 'F2', 0.969, 0.957, 0.933);
        $ops .= $this->pdfText($pageWidth - 160, $pageHeight - 30, $document['tagline'], 9, 'F1', 0.839, 0.808, 0.753);
        $ops .= $this->pdfText($margin, 528, $document['title'], 16, 'F2', 0.110, 0.094, 0.078);
        $ops .= $this->pdfText($margin, 508, __('admin.reports.period').': '.$document['period'].'    '.__('admin.reports.generated_at').': '.$document['generated'], 9, 'F1', 0.478, 0.447, 0.408);

        return $ops;
    }

    /**
     * @param  list<string>  $streams
     * @param  array{brand: string, title: string}  $document
     */
    private function assemblePdf(array $streams, array $document): string
    {
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            3 => '<< /Title '.$this->pdfLiteral($document['title']).' /Author '.$this->pdfLiteral($document['brand']).' /Creator '.$this->pdfLiteral($document['brand']).' >>',
            4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
            5 => '<< /Type /Font /Subtype /Type1 /BaseFont /Times-Bold /Encoding /WinAnsiEncoding >>',
            6 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
        ];

        $pageIds = [];
        $next = 7;

        foreach ($streams as $stream) {
            $contentId = $next++;
            $pageId = $next++;
            $objects[$contentId] = '<< /Length '.strlen($stream)." >>\nstream\n".$stream."\nendstream";
            $objects[$pageId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Contents '.$contentId.' 0 R /Resources << /Font << /F1 4 0 R /F2 5 0 R /F3 6 0 R >> >> >>';
            $pageIds[] = $pageId;
        }

        $kids = collect($pageIds)->map(fn (int $id): string => $id.' 0 R')->implode(' ');
        $objects[2] = '<< /Type /Pages /Kids ['.$kids.'] /Count '.count($pageIds).' >>';
        ksort($objects);

        $output = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($output);
            $output .= $id." 0 obj\n".$body."\nendobj\n";
        }

        $size = max(array_keys($objects)) + 1;
        $xref = strlen($output);
        $output .= "xref\n0 {$size}\n0000000000 65535 f \n";

        for ($id = 1; $id < $size; $id++) {
            $output .= sprintf("%010d 00000 n \n", $offsets[$id]);
        }

        return $output."trailer << /Size {$size} /Root 1 0 R /Info 3 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }

    private function pdfFill(float $r, float $g, float $b): string
    {
        return sprintf("%.3F %.3F %.3F rg\n", $r, $g, $b);
    }

    private function pdfStroke(float $r, float $g, float $b): string
    {
        return sprintf("%.3F %.3F %.3F RG\n", $r, $g, $b);
    }

    private function pdfRect(float $x, float $y, float $width, float $height, string $mode): string
    {
        return sprintf("%.2F %.2F %.2F %.2F re %s\n", $x, $y, $width, $height, $mode);
    }

    private function pdfText(float $x, float $y, string $text, float $size, string $font, float $r, float $g, float $b, ?float $maxWidth = null): string
    {
        $value = $maxWidth === null ? $text : $this->pdfFit($text, $maxWidth, $size);

        return $this->pdfFill($r, $g, $b)
            .sprintf("BT /%s %.1F Tf %.2F %.2F Td %s Tj ET\n", $font, $size, $x, $y, $this->pdfLiteral($value));
    }

    private function pdfFit(string $text, float $width, float $fontSize): string
    {
        $max = max(4, (int) floor($width / ($fontSize * 0.52)));

        if (mb_strlen($text) <= $max) {
            return $text;
        }

        return mb_substr($text, 0, $max - 1).'…';
    }

    private function pdfLiteral(string $text): string
    {
        $mapped = strtr($text, [
            'ş' => 's',
            'Ş' => 'S',
            'ğ' => 'g',
            'Ğ' => 'G',
            'ı' => 'i',
            'İ' => 'I',
            '₺' => 'TL ',
            '…' => '...',
            '—' => '-',
            '·' => '-',
        ]);
        $encoded = function_exists('iconv')
            ? iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $mapped)
            : false;

        if ($encoded === false) {
            $encoded = Str::ascii($mapped);
        }

        return '('.str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], str_replace(["\r", "\n"], ' ', $encoded)).')';
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function sheetName(string $title): string
    {
        $name = trim(str_replace(['\\', '/', '*', '?', ':', '[', ']'], ' ', $title));

        return $name === '' ? __('admin.brand') : mb_substr($name, 0, 31);
    }

    private function extension(string $format): string
    {
        return match ($format) {
            'excel' => 'xls',
            'pdf' => 'pdf',
            default => 'csv',
        };
    }

    private function contentType(string $format): string
    {
        return match ($format) {
            'excel' => 'application/vnd.ms-excel',
            'pdf' => 'application/pdf',
            default => 'text/csv; charset=UTF-8',
        };
    }
}
