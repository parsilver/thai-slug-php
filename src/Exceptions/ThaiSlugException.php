<?php

declare(strict_types=1);

namespace Farzai\ThaiSlug\Exceptions;

use Exception;

class ThaiSlugException extends Exception
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        /** @var array<string, mixed> */
        private readonly array $context = []
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get additional error context
     *
     * @return array<string, mixed>
     *
     * @api
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Create exception with context
     *
     * @param  array<string, mixed>  $context
     *
     * @api
     */
    public static function withContext(string $message, array $context = [], int $code = 0, ?\Throwable $previous = null): self
    {
        return new self($message, $code, $previous, $context);
    }
}
