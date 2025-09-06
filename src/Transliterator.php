<?php

declare(strict_types=1);

namespace Farzai\ThaiSlug;

use Farzai\ThaiSlug\Enums\Strategy;

/**
 * Simple, modern Thai text transliterator (performance-optimized)
 *
 * Clean implementation focused on performance and maintainability.
 * Uses strategy pattern for different transliteration approaches.
 */
class Transliterator
{
    public function __construct(
        private Strategy $defaultStrategy = Strategy::PHONETIC,
        /** @var array<string, mixed> */
        private array $defaultOptions = []
    ) {}

    /**
     * @param  array<string, mixed>  $options
     */
    /**
     * @param  array<string, mixed>  $options
     */
    public function transliterate(string $text, ?string $strategy = null, array $options = []): string
    {
        if (empty($text)) {
            return '';
        }

        // Determine strategy to use
        $strategyEnum = $strategy !== null
            ? Strategy::fromString($strategy)
            : $this->defaultStrategy;

        // Merge options with defaults
        $mergedOptions = array_merge($this->defaultOptions, $options);

        // Use new Strategy enum factory method
        $strategyInstance = $strategyEnum->createInstance($mergedOptions);

        return $strategyInstance->transliterate($text);
    }

    /**
     * Quick static factory for one-off transliterations
     *
     * @param  array<string, mixed>  $options
     *
     * @api
     */
    public static function make(string $text, Strategy $strategy = Strategy::PHONETIC, array $options = []): string
    {
        $transliterator = new self($strategy, $options);

        return $transliterator->transliterate($text);
    }
}
