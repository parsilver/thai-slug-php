<?php

declare(strict_types=1);

namespace Farzai\ThaiSlug\Processors;

use Farzai\ThaiSlug\Contracts\ProcessorInterface;
use Farzai\ThaiSlug\Enums\Strategy;
use Farzai\ThaiSlug\Transliterator;

/**
 * Transliteration processor
 *
 * Converts Thai text to Latin characters using the configured strategy.
 */
final readonly class TransliterationProcessor implements ProcessorInterface
{
    private Transliterator $transliterator;

    private Strategy $strategy;

    /** @var array<string, mixed> */
    private array $options;

    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        array $context = [],
        ?Transliterator $transliterator = null
    ) {
        $contextStrategy = $context['strategy'] ?? Strategy::PHONETIC;
        $contextOptions = $context['strategyOptions'] ?? [];

        if (! $contextStrategy instanceof Strategy) {
            $contextStrategy = Strategy::PHONETIC;
        }

        if (! is_array($contextOptions)) {
            $contextOptions = [];
        }

        $this->strategy = $contextStrategy;
        $this->options = $contextOptions;
        $this->transliterator = $transliterator ?? new Transliterator($this->strategy, $this->options);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function process(string $text, array $context = []): string
    {
        if (empty($text)) {
            return '';
        }

        // Use context strategy if provided, otherwise use constructor strategy
        $contextStrategy = $context['strategy'] ?? $this->strategy;
        $contextOptions = $context['strategyOptions'] ?? [];

        $strategy = $contextStrategy instanceof Strategy ? $contextStrategy : $this->strategy;
        $strategyOptions = is_array($contextOptions) ? array_merge($this->options, $contextOptions) : $this->options;

        return $this->transliterator->transliterate($text, $strategy->value, $strategyOptions);
    }

    public function getName(): string
    {
        return 'transliteration';
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function shouldProcess(array $context = []): bool
    {
        // Run transliteration unless explicitly disabled
        return ($context['transliterate'] ?? true) === true;
    }

    public function getPriority(): int
    {
        // Medium priority - run after normalization but before URL safety
        return 50;
    }
}
