<?php

declare(strict_types=1);

namespace Farzai\ThaiSlug;

use Farzai\ThaiSlug\Enums\Strategy;

/**
 * Fluent slug builder for Thai text (simplified and performance-optimized)
 *
 * Mutable builder pattern for optimal performance:
 * - Single Responsibility: Focused on building slugs only
 * - High Performance: No unnecessary object creation
 * - Simple API: Clean fluent interface
 */
class SlugBuilder
{
    /**
     * @param  array<string, mixed>  $strategyOptions
     */
    public function __construct(
        private string $text = '',
        private Strategy $strategy = Strategy::PHONETIC,
        private array $strategyOptions = [],
        private ?int $maxLength = null,
        private string $separator = '-',
        private bool $lowercase = true,
        private bool $removeDuplicates = true,
        private bool $trimSeparators = true,
        private ?ThaiNormalizer $normalizer = null,
        private ?Transliterator $transliterator = null,
        private ?UrlSafeMaker $urlSafeMaker = null,
    ) {}

    public function text(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function strategy(Strategy $strategy): self
    {
        $this->strategy = $strategy;
        $this->strategyOptions = $strategy->defaultOptions();

        return $this;
    }

    /**
     * @api
     */
    public function maxLength(?int $maxLength): self
    {
        if ($maxLength !== null && $maxLength < 1) {
            throw new \InvalidArgumentException('Max length must be positive');
        }

        $this->maxLength = $maxLength;

        return $this;
    }

    /**
     * @api
     */
    public function separator(string $separator): self
    {
        $this->separator = $separator;

        return $this;
    }

    /**
     * @api
     */
    public function lowercase(bool $lowercase = true): self
    {
        $this->lowercase = $lowercase;

        return $this;
    }

    /**
     * @api
     */
    public function removeDuplicates(bool $remove = true): self
    {
        $this->removeDuplicates = $remove;

        return $this;
    }

    /**
     * @api
     */
    public function trimSeparators(bool $trim = true): self
    {
        $this->trimSeparators = $trim;

        return $this;
    }

    public function build(): string
    {
        if (empty($this->text)) {
            return '';
        }

        return $this->processPipeline($this->text);
    }

    /**
     * Process text through the complete slug generation pipeline
     */
    private function processPipeline(string $text): string
    {
        // Step 1: Normalize Thai text
        $normalizer = $this->normalizer ?? new ThaiNormalizer;
        $normalizedText = $normalizer->normalize($text);

        // Step 2: Transliterate to Latin characters
        $transliterator = $this->transliterator ?? new Transliterator($this->strategy, $this->strategyOptions);
        $transliteratedText = $transliterator->transliterate($normalizedText);

        // Step 3: Make URL-safe
        $urlSafeMaker = $this->urlSafeMaker ?? new UrlSafeMaker;

        return $urlSafeMaker->makeSafe($transliteratedText, [
            'separator' => $this->separator,
            'maxLength' => $this->maxLength,
            'lowercase' => $this->lowercase,
            'removeDuplicates' => $this->removeDuplicates,
            'trimSeparators' => $this->trimSeparators,
        ]);
    }

    /**
     * Static factory for quick slug generation
     */
    public static function make(string $text, Strategy $strategy = Strategy::PHONETIC): string
    {
        return (new self)
            ->text($text)
            ->strategy($strategy)
            ->build();
    }

    /**
     * Create builder from configuration array
     *
     * @param  array<string, mixed>  $config
     */
    public static function fromArray(array $config): self
    {
        // Type-safe extraction with proper casting
        $text = match (true) {
            isset($config['text']) && is_string($config['text']) => $config['text'],
            default => ''
        };

        $strategy = ($config['strategy'] ?? null) instanceof Strategy
            ? $config['strategy']
            : Strategy::PHONETIC;

        $strategyOptions = is_array($config['strategyOptions'] ?? null)
            ? $config['strategyOptions']
            : [];

        $maxLength = isset($config['maxLength']) && is_int($config['maxLength'])
            ? $config['maxLength']
            : null;

        $separator = is_string($config['separator'] ?? null)
            ? $config['separator']
            : '-';

        $lowercase = is_bool($config['lowercase'] ?? null)
            ? $config['lowercase']
            : true;

        $removeDuplicates = is_bool($config['removeDuplicates'] ?? null)
            ? $config['removeDuplicates']
            : true;

        $trimSeparators = is_bool($config['trimSeparators'] ?? null)
            ? $config['trimSeparators']
            : true;

        $normalizer = ($config['normalizer'] ?? null) instanceof ThaiNormalizer
            ? $config['normalizer']
            : null;

        $transliterator = ($config['transliterator'] ?? null) instanceof Transliterator
            ? $config['transliterator']
            : null;

        $urlSafeMaker = ($config['urlSafeMaker'] ?? null) instanceof UrlSafeMaker
            ? $config['urlSafeMaker']
            : null;

        return new self(
            text: $text,
            strategy: $strategy,
            strategyOptions: $strategyOptions,
            maxLength: $maxLength,
            separator: $separator,
            lowercase: $lowercase,
            removeDuplicates: $removeDuplicates,
            trimSeparators: $trimSeparators,
            normalizer: $normalizer,
            transliterator: $transliterator,
            urlSafeMaker: $urlSafeMaker,
        );
    }
}
