<?php

declare(strict_types=1);

namespace Farzai\ThaiSlug\Processors;

use Farzai\ThaiSlug\Contracts\ProcessorInterface;
use Farzai\ThaiSlug\ThaiNormalizer;

/**
 * Text normalization processor
 *
 * Handles Thai text normalization including Unicode normalization,
 * whitespace cleanup, and character replacement.
 */
final readonly class NormalizationProcessor implements ProcessorInterface
{
    private ThaiNormalizer $normalizer;

    public function __construct(?ThaiNormalizer $normalizer = null)
    {
        $this->normalizer = $normalizer ?? new ThaiNormalizer;
    }

    public function process(string $text, array $context = []): string
    {
        if (empty($text)) {
            return '';
        }

        return $this->normalizer->normalize($text);
    }

    public function getName(): string
    {
        return 'normalization';
    }

    public function shouldProcess(array $context = []): bool
    {
        // Always run normalization unless explicitly disabled
        return ($context['normalize'] ?? true) === true;
    }

    public function getPriority(): int
    {
        // High priority - normalization should run first
        return 100;
    }
}
