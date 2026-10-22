<?php

namespace App\Services;

use Throwable;
use App\Models\ErrorLog;

class ErrorLogger
{
    public static function log(Throwable $e): void
    {
        ErrorLog::create([
            'type'    => get_class($e),
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => substr($e->getTraceAsString(), 0, 10000),
            'url'     => request()->fullUrl(),
            // 'user_id' => auth()->id(),
        ]);
    }
}
