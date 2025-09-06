<?php

declare(strict_types=1);

namespace Farzai\ThaiSlug\Exceptions;

/**
 * Exception thrown when cache operations fail
 *
 * @api Public exception methods for consumers
 */
class CacheException extends ThaiSlugException
{
    /**
     * @api
     */
    public static function operationFailed(string $operation, string $reason = ''): self
    {
        $message = "Cache operation '{$operation}' failed";
        if ($reason) {
            $message .= ": {$reason}";
        }

        return new self($message);
    }

    /**
     * @api
     */
    public static function keyTooLong(string $key, int $maxLength): self
    {
        return new self(
            'Cache key is too long: '.strlen($key)." characters, max {$maxLength}"
        );
    }

    /**
     * @api
     */
    public static function invalidKey(string $key): self
    {
        return new self("Invalid cache key: '{$key}'");
    }

    /**
     * @api
     */
    public static function capacityExceeded(int $current, int $maximum): self
    {
        return new self(
            "Cache capacity exceeded: {$current}/{$maximum} items"
        );
    }
}
