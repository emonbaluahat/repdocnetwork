<?php

namespace App\Core;

class Security
{
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function generateToken(int $length = 64): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    public static function generateOtp(int $length = 6): string
    {
        $otp = '';
        for ($i = 0; $i < $length; $i++) {
            $otp .= random_int(0, 9);
        }
        return $otp;
    }

    public static function sanitizeInput(string $input): string
    {
        $input = trim($input);
        $input = strip_tags($input);
        return $input;
    }

    public static function sanitizeHtml(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
    }

    public static function sanitizeFilename(string $filename): string
    {
        $filename = preg_replace('/[^\w\-\.\p{L} ]/u', '', $filename);
        $filename = preg_replace('/\s+/', '_', $filename);
        return trim($filename, '._');
    }

    public static function validateFile(array $file, array $allowedTypes, int $maxSize = 5242880): array
    {
        $errors = [];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'errors' => ['File upload failed.']];
        }

        if ($file['size'] > $maxSize) {
            $maxMb = $maxSize / 1048576;
            return ['valid' => false, 'errors' => ["File exceeds maximum size of {$maxMb}MB."]];
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedTypes)) {
            $allowedStr = implode(', ', $allowedTypes);
            return ['valid' => false, 'errors' => ["File type '{$extension}' is not allowed. Allowed: {$allowedStr}"]];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $mimeMap = [
            'pdf' => ['application/pdf'],
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'csv' => ['text/csv', 'text/plain'],
        ];

        if (isset($mimeMap[$extension]) && !in_array($mimeType, $mimeMap[$extension])) {
            return ['valid' => false, 'errors' => ['File content does not match extension.']];
        }

        return ['valid' => true, 'errors' => []];
    }

    public static function sanitizePhone(string $phone): string
    {
        return preg_replace('/[^\d+]/', '', $phone);
    }

    public static function sanitizeNid(string $nid): string
    {
        return preg_replace('/[^0-9]/', '', $nid);
    }

    public static function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) return $email;

        $name = $parts[0];
        $domain = $parts[1];

        $maskedName = substr($name, 0, 2) . str_repeat('*', max(0, strlen($name) - 2));
        return $maskedName . '@' . $domain;
    }

    public static function maskPhone(string $phone): string
    {
        if (strlen($phone) < 8) return $phone;
        return substr($phone, 0, 4) . '****' . substr($phone, -2);
    }

    public static function generateStrongPassword(int $length = 16): string
    {
        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lower = 'abcdefghijklmnopqrstuvwxyz';
        $digits = '0123456789';
        $special = '!@#$%^&*()-_=+';

        $chars = $upper . $lower . $digits . $special;
        $password = '';

        $password .= $upper[random_int(0, strlen($upper) - 1)];
        $password .= $lower[random_int(0, strlen($lower) - 1)];
        $password .= $digits[random_int(0, strlen($digits) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];

        for ($i = strlen($password); $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return str_shuffle($password);
    }

    public static function validatePasswordStrength(string $password): array
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter.';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter.';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number.';
        }
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character.';
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }
}
