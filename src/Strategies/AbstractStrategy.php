<?php

declare(strict_types=1);

namespace Farzai\ThaiSlug\Strategies;

use Farzai\ThaiSlug\Contracts\TransliterationStrategyInterface;

/**
 * Abstract base class for transliteration strategies
 *
 * Provides common functionality and template method pattern implementation.
 * Uses modern PHP 8.4+ patterns for clean, maintainable code.
 */
abstract class AbstractStrategy implements TransliterationStrategyInterface
{
    /** @var array<string, mixed> */
    protected array $options = [];

    /**
     * @param  array<string, mixed>  $options
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->getDefaultOptions(), $options);

        if (! $this->validateOptions($this->options)) {
            throw new \InvalidArgumentException('Invalid options for strategy: '.$this->getName());
        }
    }

    public function transliterate(string $text): string
    {
        if (empty($text)) {
            return '';
        }

        return $this->doTransliterate($text);
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function validateOptions(array $options): bool
    {
        // Basic validation - subclasses can override for specific validation
        return true;
    }

    /**
     * Get default options for this strategy
     *
     * @return array<string, mixed>
     */
    protected function getDefaultOptions(): array
    {
        return [];
    }

    /**
     * Perform the actual transliteration (implemented by subclasses)
     */
    abstract protected function doTransliterate(string $text): string;
}
