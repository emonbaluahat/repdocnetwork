<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

class DocumentGenerator
{
    private static ?Dompdf $dompdf = null;

    private static function getDompdf(): Dompdf
    {
        if (self::$dompdf === null) {
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', false);
            $options->set('defaultFont', 'Noto Sans Bengali');
            $options->set('isFontSubsettingEnabled', true);
            $options->set('tempDir', APP_ROOT . '/storage/tmp');

            $fontDir = APP_ROOT . '/storage/fonts';
            if (!is_dir($fontDir)) {
                mkdir($fontDir, 0755, true);
            }
            if (!is_dir(APP_ROOT . '/storage/tmp')) {
                mkdir(APP_ROOT . '/storage/tmp', 0755, true);
            }
            if (!is_dir(APP_ROOT . '/storage/documents')) {
                mkdir(APP_ROOT . '/storage/documents', 0755, true);
            }

            self::$dompdf = new Dompdf($options);
            self::$dompdf->setPaper('A4', 'portrait');
        }
        return self::$dompdf;
    }

    public static function renderTemplate(string $content, array $data): string
    {
        $result = $content;

        foreach ($data as $key => $value) {
            if (is_string($value) || is_numeric($value)) {
                $result = str_replace('{{' . $key . '}}', (string) $value, $result);
            }
        }

        $result = preg_replace_callback('/\{\{date:(\w+)\}\}/', function ($m) use ($data) {
            $key = $m[1];
            $val = $data[$key] ?? '';
            if ($val && strtotime($val)) {
                return date('d/m/Y', strtotime($val));
            }
            return date('d/m/Y');
        }, $result);

        $result = preg_replace_callback('/\{\{number:(\w+)\}\}/', function ($m) use ($data) {
            $key = $m[1];
            $val = $data[$key] ?? 0;
            return number_format((float) $val, 2);
        }, $result);

        $result = preg_replace('/\{\{if:(\w+)\}\}(.*?)\{\{endif\}\}/s', function ($m) use ($data) {
            $key = $m[1];
            $val = $data[$key] ?? '';
            if (!empty($val)) {
                return $m[2];
            }
            return '';
        }, $result);

        $result = preg_replace('/\{\{\w+(:\w+)?\}\}/', '', $result);

        return $result;
    }

    public static function generatePdf(string $html, string $filename): string
    {
        $dompdf = self::getDompdf();
        $dompdf->loadHtml($html);
        $dompdf->render();

        $outputDir = APP_ROOT . '/storage/documents';
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $filePath = $outputDir . '/' . $filename;
        file_put_contents($filePath, $dompdf->output());

        return 'storage/documents/' . $filename;
    }

    public static function generateFromTemplate(string $content, array $data, string $documentNumber): string
    {
        $html = self::renderTemplate($content, $data);

        $fullHtml = '<!DOCTYPE html><html><head><meta charset="utf-8">';
        $fullHtml .= '<style>
            body { font-family: "Noto Sans Bengali", sans-serif; font-size: 14px; line-height: 1.6; color: #1a1a1a; }
            @page { margin: 20mm 15mm; }
        </style></head><body>';
        $fullHtml .= $html;
        $fullHtml .= '</body></html>';

        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $documentNumber);
        $filename = $safeName . '_' . date('Ymd') . '.pdf';

        return self::generatePdf($fullHtml, $filename);
    }

    public static function paperSize(string $size): void
    {
        $dompdf = self::getDompdf();
        $dompdf->setPaper($size, 'portrait');
    }
}
