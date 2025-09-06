<?php

declare(strict_types=1);

namespace Farzai\ThaiSlug\Utils;

/**
 * Thai Character Classification Utility (Simplified)
 *
 * Provides essential classification for Thai Unicode characters
 * Only includes methods actually used in the codebase
 */
class ThaiCharacterClassifier
{
    /**
     * Thai combining vowel marks (dependent vowels)
     */
    private const COMBINING_VOWELS = [
        "\u{0E31}", // ั Mai Han-akat
        "\u{0E34}", // ิ Sara I
        "\u{0E35}", // ี Sara II
        "\u{0E36}", // ึ Sara UE
        "\u{0E37}", // ื Sara UEE
        "\u{0E38}", // ุ Sara U
        "\u{0E39}", // ู Sara UU
        "\u{0E3A}", // ฺ Phinthu
    ];

    /**
     * Thai tone marks
     */
    private const TONE_MARKS = [
        "\u{0E48}", // ่ Mai Ek
        "\u{0E49}", // ้ Mai Tho
        "\u{0E4A}", // ๊ Mai Tri
        "\u{0E4B}", // ๋ Mai Chattawa
    ];

    /**
     * Thai diacritics (other marks)
     */
    private const DIACRITICS = [
        "\u{0E4C}", // ์ Thanthakhat
        "\u{0E4D}", // ํ Nikhahit
        "\u{0E4E}", // ๎ Yamakkan
    ];

    /**
     * Check if character is a Thai tone mark
     */
    public static function isToneMark(string $char): bool
    {
        return in_array($char, self::TONE_MARKS, true);
    }

    /**
     * Check if character is a Thai combining vowel
     */
    private static function isCombiningVowel(string $char): bool
    {
        return in_array($char, self::COMBINING_VOWELS, true);
    }

    /**
     * Check if character is a Thai diacritic
     */
    private static function isDiacritic(string $char): bool
    {
        return in_array($char, self::DIACRITICS, true);
    }

    /**
     * Check if character is a combining mark (vowel, tone, or diacritic)
     * Used by ThaiNormalizer to detect orphaned combining marks
     */
    public static function isCombiningMark(string $char): bool
    {
        return self::isCombiningVowel($char) || self::isToneMark($char) || self::isDiacritic($char);
    }
}
