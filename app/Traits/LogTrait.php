<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait LogTrait
{
    protected function logInfo(string $message, array $context = []): void
    {
        Log::channel('activity')->info($message, $context);
    }

    protected function logError(string $message, array $context = []): void
    {
        Log::channel('activity')->error($message, $context);
    }
}
