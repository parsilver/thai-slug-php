<?php

declare(strict_types=1);

namespace Farzai\ThaiSlug\Strategies;

/**
 * Phonetic transliteration strategy
 *
 * Uses natural pronunciation-based mapping for converting Thai to Latin.
 * This approach prioritizes readability and pronunciation accuracy.
 */
class PhoneticStrategy extends AbstractStrategy
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
        '๊' => '', '๋' => '', '์' => '', 'ั' => 'a',
    ];

    private const DIGIT_MAP = [
        '๐' => '0', '๑' => '1', '๒' => '2', '๓' => '3', '๔' => '4',
        '๕' => '5', '๖' => '6', '๗' => '7', '๘' => '8', '๙' => '9',
    ];

    public function getName(): string
    {
        return 'phonetic';
    }

    protected function doTransliterate(string $text): string
    {
        // Convert Thai digits
        $text = strtr($text, self::DIGIT_MAP);

        // Convert consonants
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
            'preserve_tone_marks' => false,
            'preserve_digits' => true,
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function validateOptions(array $options): bool
    {
        // Validate specific phonetic options
        $validKeys = ['preserve_tone_marks', 'preserve_digits'];

        foreach (array_keys($options) as $key) {
            if (! in_array($key, $validKeys)) {
                return false;
            }
        }

        return true;
    }
}
