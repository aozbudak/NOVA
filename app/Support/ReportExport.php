<?php

namespace App\Support;

use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ReportExport
{
    /**
     * @param  array<string, mixed>  $report
     */
    public function download(array $report, string $format): StreamedResponse
    {
        abort_unless(in_array($format, ['csv', 'excel', 'pdf'], true), 404);

        $filename = Str::slug((string) $report['key']).'-report.'.$this->extension($format);

        return response()->streamDownload(function () use ($report, $format): void {
            echo match ($format) {
                'csv' => $this->csv($report),
                'excel' => $this->excel($report),
                'pdf' => $this->pdf($report),
            };
        }, $filename, [
            'Content-Type' => $this->contentType($format),
        ]);
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private function csv(array $report): string
    {
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, [(string) $report['title']]);
        fputcsv($handle, []);
        fputcsv($handle, [__('admin.reports.metric'), __('admin.reports.value')]);

        foreach ($report['metrics'] ?? [] as $metric) {
            fputcsv($handle, [$metric['label'], $metric['value']]);
        }

        fputcsv($handle, []);
        fputcsv($handle, $report['headers'] ?? []);

        foreach ($report['table'] ?? [] as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle) ?: '';
        fclose($handle);

        return $csv;
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private function excel(array $report): string
    {
        $cell = function (string $value): string {
            $safe = htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');

            return '<Cell><Data ss:Type="String">'.$safe.'</Data></Cell>';
        };

        $rows = '<Row>'.$cell((string) $report['title']).'</Row>';
        $rows .= '<Row>'.$cell(__('admin.reports.metric')).$cell(__('admin.reports.value')).'</Row>';

        foreach ($report['metrics'] ?? [] as $metric) {
            $rows .= '<Row>'.$cell((string) $metric['label']).$cell((string) $metric['value']).'</Row>';
        }

        $rows .= '<Row></Row><Row>';

        foreach ($report['headers'] ?? [] as $header) {
            $rows .= $cell((string) $header);
        }

        $rows .= '</Row>';

        foreach ($report['table'] ?? [] as $row) {
            $rows .= '<Row>';

            foreach ($row as $value) {
                $rows .= $cell((string) $value);
            }

            $rows .= '</Row>';
        }

        return '<?xml version="1.0"?>'
            .'<?mso-application progid="Excel.Sheet"?>'
            .'<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">'
            .'<Worksheet ss:Name="Report"><Table>'.$rows.'</Table></Worksheet></Workbook>';
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private function pdf(array $report): string
    {
        $lines = [(string) $report['title'], ''];

        foreach ($report['metrics'] ?? [] as $metric) {
            $lines[] = $metric['label'].': '.$metric['value'];
        }

        $lines[] = '';
        $lines[] = implode(' | ', $report['headers'] ?? []);

        foreach ($report['table'] ?? [] as $row) {
            $lines[] = implode(' | ', $row);
        }

        $text = implode("\n", $lines);
        $safe = Str::of($text)
            ->replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'])
            ->ascii()
            ->toString();
        $stream = 'BT /F1 10 Tf 40 750 Td 14 TL ('.str_replace("\n", ') Tj T* (', $safe).') Tj ET';
        $length = strlen($stream);

        return "%PDF-1.4\n"
            ."1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n"
            ."2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n"
            ."3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >> endobj\n"
            ."4 0 obj << /Length {$length} >> stream\n"
            .$stream."\nendstream endobj\n"
            ."5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj\n"
            ."trailer << /Root 1 0 R >>\n%%EOF";
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
