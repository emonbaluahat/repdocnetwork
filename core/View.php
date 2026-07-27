<?php

namespace App\Core;

class View
{
    private static string $layout = 'layouts/main';

    public static function render(string $view, array $data = [], string $layout = null): void
    {
        $viewPath = static::resolveViewPath($view);

        if (!file_exists($viewPath)) {
            if (APP_DEBUG) {
                throw new \RuntimeException("View not found: {$view}");
            }
            http_response_code(500);
            echo 'View not found.';
            exit;
        }

        $layoutToUse = $layout ?? static::$layout;

        $content = static::renderFile($viewPath, $data, false);
        $data['content'] = $content;

        $layoutPath = static::resolveViewPath($layoutToUse);
        if (file_exists($layoutPath)) {
            static::renderFile($layoutPath, $data, true);
        } else {
            echo $content;
        }
    }

    public static function renderRaw(string $view, array $data = []): string
    {
        $viewPath = static::resolveViewPath($view);
        if (!file_exists($viewPath)) {
            return '';
        }
        return static::renderFile($viewPath, $data, false, true);
    }

    public static function component(string $component, array $data = []): void
    {
        $path = static::resolveViewPath('components/' . $component);
        if (file_exists($path)) {
            static::renderFile($path, $data);
        }
    }

    public static function setLayout(string $layout): void
    {
        static::$layout = $layout;
    }

    private static function renderFile(string $file, array $data, bool $output = true, bool $return = false): string
    {
        extract($data, EXTR_SKIP);

        ob_start();
        include $file;
        $content = ob_get_clean();

        if ($return) {
            return $content;
        }

        if ($output) {
            echo $content;
        }

        return $content;
    }

    private static function resolveViewPath(string $view): string
    {
        $file = APP_ROOT . '/views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($file)) {
            $altFile = APP_ROOT . '/views/' . $view . '.php';
            if (file_exists($altFile)) {
                return $altFile;
            }
        }
        return $file;
    }
}
