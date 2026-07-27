<?php

namespace App\Core;

abstract class Controller
{
    protected function render(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    protected function json(mixed $data, int $status = 200): void
    {
        Response::json($data, $status);
    }

    protected function redirect(string $path): void
    {
        Response::redirect($path);
    }

    protected function redirectWith(string $path, string $key, string $message): void
    {
        flash($key, $message);
        $this->redirect($path);
    }

    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }

    protected function backWith(string $key, string $message): void
    {
        flash($key, $message);
        $this->back();
    }

    protected function validate(array $rules, array $messages = []): array
    {
        $validator = new Validator($_POST, $rules, $messages);
        if ($validator->fails()) {
            $_SESSION['_old_input'] = $_POST;
            $_SESSION['_errors'] = $validator->errors();
            $this->back();
            exit;
        }
        return $validator->validated();
    }

    protected function authorize(string $permission): void
    {
        $user = AuthContext::user();
        if (!$user || !AuthContext::hasPermission($permission)) {
            $this->redirect('/');
            exit;
        }
    }

    protected function authorizeRole(string $role): void
    {
        $user = AuthContext::user();
        if (!$user || AuthContext::role() !== $role) {
            $this->redirect('/');
            exit;
        }
    }
}
