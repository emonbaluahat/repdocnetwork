<?php

namespace App\Core;

class Response
{
    public static function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }

    public static function back(): void
    {
        self::redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }

    public static function download(string $filePath, ?string $filename = null): void
    {
        if (!file_exists($filePath)) {
            http_response_code(404);
            echo 'File not found.';
            exit;
        }

        $filename = $filename ?: basename($filePath);

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache');

        readfile($filePath);
        exit;
    }

    public static function file(string $filePath, ?string $contentType = null): void
    {
        if (!file_exists($filePath)) {
            http_response_code(404);
            echo 'File not found.';
            exit;
        }

        if ($contentType) {
            header('Content-Type: ' . $contentType);
        }

        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: public, max-age=3600');
        readfile($filePath);
        exit;
    }

    public static function status(int $code): void
    {
        http_response_code($code);
    }

    public static function setHeader(string $key, string $value): void
    {
        header("{$key}: {$value}");
    }
}
