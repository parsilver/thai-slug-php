<?php

declare(strict_types=1);

namespace Farzai\ThaiSlug\Processors;

use Farzai\ThaiSlug\Contracts\ProcessorInterface;
use Farzai\ThaiSlug\UrlSafeMaker;

/**
 * URL safety processor
 *
 * Makes text safe for use in URLs by handling special characters,
 * separators, length limits, and other URL requirements.
 */
final readonly class UrlSafetyProcessor implements ProcessorInterface
{
    private UrlSafeMaker $urlSafeMaker;

    public function __construct(?UrlSafeMaker $urlSafeMaker = null)
    {
        $this->urlSafeMaker = $urlSafeMaker ?? new UrlSafeMaker;
    }

    public function process(string $text, array $context = []): string
    {
        if (empty($text)) {
            return '';
        }

        $options = [
            'separator' => $context['separator'] ?? '-',
            'maxLength' => $context['maxLength'] ?? null,
            'lowercase' => $context['lowercase'] ?? true,
            'removeDuplicates' => $context['removeDuplicates'] ?? true,
            'trimSeparators' => $context['trimSeparators'] ?? true,
        ];

        return $this->urlSafeMaker->makeSafe($text, $options);
    }

    public function getName(): string
    {
        return 'url_safety';
    }

    public function shouldProcess(array $context = []): bool
    {
        // Run URL safety processing unless explicitly disabled
        return ($context['urlSafe'] ?? true) === true;
    }

    public function getPriority(): int
    {
        // Low priority - run after normalization and transliteration
        return 10;
    }
}
