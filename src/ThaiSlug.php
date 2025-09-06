<?php

declare(strict_types=1);

namespace Farzai\ThaiSlug;

use Farzai\ThaiSlug\Enums\Strategy;

/**
 * Simple Thai slug generator
 *
 * Clean, modern PHP 8.4+ implementation without over-engineering.
 * No complex DI containers or config conversions - just effective slug generation.
 */
class ThaiSlug
{
    public function __construct(
        private Strategy $defaultStrategy = Strategy::PHONETIC
    ) {}

    /**
     * Generate a slug from Thai text
     */
    /**
     * @param  array<string, mixed>  $options
     */
    public function generate(string $text, array $options = []): string
    {
        return $this->builder($text, $options)->build();
    }

    /**
     * Create a new slug builder instance
     */
    /**
     * @param  array<string, mixed>  $options
     */
    public function builder(string $text = '', array $options = []): SlugBuilder
    {
        return SlugBuilder::fromArray([
            'text' => $text,
            'strategy' => $options['strategy'] ?? $this->defaultStrategy,
            'strategyOptions' => $options['strategyOptions'] ?? [],
            'maxLength' => $options['maxLength'] ?? null,
            'separator' => $options['separator'] ?? '-',
            'lowercase' => $options['lowercase'] ?? true,
            'removeDuplicates' => $options['removeDuplicates'] ?? true,
            'trimSeparators' => $options['trimSeparators'] ?? true,
        ]);
    }

    /**
     * Static method to quickly generate a slug
     *
     * @param  Strategy|string|array<string, mixed>  $options
     *
     * @api
     */
    public static function make(string $text, Strategy|string|array $options = Strategy::PHONETIC): string
    {
        // Handle different parameter types for flexibility
        if (is_array($options)) {
            return (new self)->generate($text, $options);
        }

        // Simple strategy-only call
        $strategy = is_string($options) ? Strategy::fromString($options) : $options;

        return SlugBuilder::make($text, $strategy);
    }
}
