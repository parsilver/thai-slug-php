<?php

declare(strict_types=1);

namespace Farzai\ThaiSlug\Strategies;

/**
 * Royal Institute transliteration strategy
 *
 * Follows the official Royal Institute of Thailand transliteration standards.
 * This approach prioritizes consistency with official Thai romanization.
 */
class RoyalStrategy extends AbstractStrategy
{
    private const CONSONANT_MAP = [
        'ก' => 'k', 'ข' => 'kh', 'ฃ' => 'kh', 'ค' => 'kh', 'ฅ' => 'kh', 'ฆ' => 'kh',
        'ง' => 'ng', 'จ' => 'ch', 'ฉ' => 'ch', 'ช' => 'ch', 'ซ' => 's', 'ฌ' => 'ch',
        'ญ' => 'y', 'ฎ' => 'd', 'ฏ' => 't', 'ฐ' => 'th', 'ฑ' => 'th', 'ฒ' => 'th',
        'ณ' => 'n', 'ด' => 'd', 'ต' => 't', 'ถ' => 'th', 'ท' => 'th', 'ธ' => 'th',
        'น' => 'n', 'บ' => 'b', 'ป' => 'p', 'ผ' => 'ph', 'ฝ' => 'f', 'พ' => 'ph',
        'ฟ' => 'f', 'ภ' => 'ph', 'ม' => 'm', 'ย' => 'y', 'ร' => 'r', 'ล' => 'l',
        'ว' => 'w', 'ศ' => 's', 'ษ' => 's', 'ส' => 's', 'ห' => 'h', 'ฬ' => 'l',
        'อ' => '', 'ฮ' => 'h',
    ];

    private const VOWEL_MAP = [
        'ะ' => 'a', 'า' => 'a', 'ิ' => 'i', 'ี' => 'i', 'ึ' => 'ue', 'ื' => 'ue',
        'ุ' => 'u', 'ู' => 'u', 'เ' => 'e', 'แ' => 'ae', 'โ' => 'o', 'ใ' => 'ai',
        'ไ' => 'ai', 'ำ' => 'am', 'ๅ' => '', 'ๆ' => '', '่' => '', '้' => '',
        '๊' => '', '๋' => '', '์' => '', '็' => '', 'ั' => 'a',
    ];

    private const DIGIT_MAP = [
        '๐' => '0', '๑' => '1', '๒' => '2', '๓' => '3', '๔' => '4',
        '๕' => '5', '๖' => '6', '๗' => '7', '๘' => '8', '๙' => '9',
    ];

    public function getName(): string
    {
        return 'royal';
    }

    protected function doTransliterate(string $text): string
    {
        // Convert Thai digits
        $text = strtr($text, self::DIGIT_MAP);

        // Convert consonants (Royal Institute system)
        $text = strtr($text, self::CONSONANT_MAP);

        // Convert vowels and tone marks
        $text = strtr($text, self::VOWEL_MAP);

        // Clean up multiple spaces and normalize
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;
        $text = trim($text);

        return $text;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getDefaultOptions(): array
    {
        return [
            'strict_royal' => true,
            'preserve_diacritics' => false,
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function validateOptions(array $options): bool
    {
        $validKeys = ['strict_royal', 'preserve_diacritics'];

        foreach (array_keys($options) as $key) {
            if (! in_array($key, $validKeys)) {
                return false;
            }
        }

        return true;
    }
}
