<?php

namespace App\Services;

interface AIServiceInterface
{
    public function chat(array $messages, string $model, string $mode, ?array $attachment = null): string;
}
