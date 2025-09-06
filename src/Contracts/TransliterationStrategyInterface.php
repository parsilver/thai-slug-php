<?php

declare(strict_types=1);

namespace Farzai\ThaiSlug\Contracts;

/**
 * Contract for transliteration strategy implementations
 *
 * Simple interface for different Thai transliteration approaches
 */
interface TransliterationStrategyInterface
{
    /**
     * Transliterate Thai text to Latin characters
     */
    public function transliterate(string $text): string;

    /**
     * Get strategy name/type
     */
    public function getName(): string;

    /**
     * Validate strategy options
     *
     * @param  array<string, mixed>  $options
     */
    public function validateOptions(array $options): bool;
}
