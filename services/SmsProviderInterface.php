<?php

namespace App\Services;

interface SmsProviderInterface
{
    public function send(string $to, string $message): bool;
    public function getName(): string;
}
