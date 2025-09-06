<?php

declare(strict_types=1);

namespace Farzai\ThaiSlug\Enums;

use Farzai\ThaiSlug\Contracts\TransliterationStrategyInterface;
use Farzai\ThaiSlug\Strategies\CustomStrategy;
use Farzai\ThaiSlug\Strategies\PhoneticStrategy;
use Farzai\ThaiSlug\Strategies\RoyalStrategy;

/**
 * Transliteration strategy enumeration with factory capability
 *
 * Defines the available transliteration strategies for converting Thai text to Latin characters.
 * Each strategy has different characteristics and use cases:
 * - PHONETIC: Natural pronunciation-based transliteration
 * - ROYAL: Official Thai Royal Institute transliteration system
 * - CUSTOM: User-defined custom transliteration rules
 */
enum Strategy: string
{
    case PHONETIC = 'phonetic';
    case ROYAL = 'royal';
    case CUSTOM = 'custom';

    /**
     * Get default options for this strategy
     *
     * @return array<string, mixed>
     */
    public function defaultOptions(): array
    {
        return match ($this) {
            self::PHONETIC => [
                'preserve_tone_marks' => false,
                'preserve_digits' => true,
            ],
            self::ROYAL => [
                'strict_royal' => true,
                'preserve_diacritics' => false,
            ],
            self::CUSTOM => [
                'custom_mapping' => [],
                'fallback_to_phonetic' => true,
            ],
        };
    }

    /**
     * Create from string value with fallback to default
     */
    public static function fromString(string $value, self $default = self::PHONETIC): self
    {
        return self::tryFrom($value) ?? $default;
    }

    /**
     * Create strategy instance with options (replaces StrategyFactory)
     *
     * @param  array<string, mixed>  $options
     */
    public function createInstance(array $options = []): TransliterationStrategyInterface
    {
        return match ($this) {
            self::PHONETIC => new PhoneticStrategy($options),
            self::ROYAL => new RoyalStrategy($options),
            self::CUSTOM => new CustomStrategy($options),
        };
    }
}
