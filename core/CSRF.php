<?php

namespace App\Core;

class CSRF
{
    private const TOKEN_KEY = '_csrf_token';
    private const TOKEN_LIST_KEY = '_csrf_tokens';

    public static function token(): string
    {
        $token = Session::get(self::TOKEN_KEY);
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::set(self::TOKEN_KEY, $token);
        }
        return $token;
    }

    public static function validate(?string $token): bool
    {
        if ($token === null || $token === '') {
            return false;
        }

        $storedToken = Session::get(self::TOKEN_KEY);
        if ($storedToken && hash_equals($storedToken, $token)) {
            return true;
        }

        $tokens = Session::get(self::TOKEN_LIST_KEY, []);
        foreach ($tokens as $stored) {
            if (hash_equals($stored, $token)) {
                return true;
            }
        }

        return false;
    }

    public static function refresh(): string
    {
        $token = bin2hex(random_bytes(32));
        Session::set(self::TOKEN_KEY, $token);
        return $token;
    }

    public static function getMultiUseToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $tokens = Session::get(self::TOKEN_LIST_KEY, []);
        $tokens[] = $token;

        $maxTokens = 10;
        if (count($tokens) > $maxTokens) {
            $tokens = array_slice($tokens, -$maxTokens);
        }

        Session::set(self::TOKEN_LIST_KEY, $tokens);
        return $token;
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . self::token() . '">';
    }

    public static function meta(): string
    {
        return '<meta name="csrf-token" content="' . self::token() . '">';
    }
}
