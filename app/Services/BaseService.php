<?php

namespace App\Services;

abstract class BaseService
{
    /**
     * Handle service operations and return result.
     *
     * @param callable $callback
     * @return mixed
     */
    protected function handle(callable $callback)
    {
        try {
            return $callback();
        } catch (\Exception $e) {
            \Log::error('Service error: ' . $e->getMessage(), [
                'exception' => $e,
                'service' => static::class,
            ]);
            throw $e;
        }
    }
}
