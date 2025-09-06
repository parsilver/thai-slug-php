<?php

declare(strict_types=1);

namespace Farzai\ThaiSlug\Tests\Datasets;

class ThaiTextSamples
{
    /**
     * Basic Thai words with expected normalizations
     */
    public static function basicWords(): array
    {
        return [
            // Common Thai words
            ['input' => 'สวัสดี', 'expected' => 'สวัสดี'],
            ['input' => 'โลก', 'expected' => 'โลก'],
            ['input' => 'สวยงาม', 'expected' => 'สวยงาม'],
            ['input' => 'ประเทศไทย', 'expected' => 'ประเทศไทย'],
            ['input' => 'กรุงเทพมหานคร', 'expected' => 'กรุงเทพมหานคร'],
        ];
    }

    /**
     * Thai text with tone marks that need normalization
     */
    public static function toneMarks(): array
    {
        return [
            // Mai Ek (่)
            ['input' => 'ก่า', 'expected' => 'ก่า'],
            ['input' => 'น่า', 'expected' => 'น่า'],
            ['input' => 'ข่าว', 'expected' => 'ข่าว'],

            // Mai Tho (้)
            ['input' => 'ก้า', 'expected' => 'ก้า'],
            ['input' => 'น้อง', 'expected' => 'น้อง'],
            ['input' => 'ข้าว', 'expected' => 'ข้าว'],

            // Mai Tri (๊)
            ['input' => 'ก๊า', 'expected' => 'ก๊า'],
            ['input' => 'น๊อ', 'expected' => 'น๊อ'],

            // Mai Chattawa (๋)
            ['input' => 'ก๋า', 'expected' => 'ก๋า'],
            ['input' => 'น๋อง', 'expected' => 'น๋อง'],
        ];
    }

    /**
     * Complex vowel patterns that require proper normalization
     */
    public static function complexVowels(): array
    {
        return [
            // เ-ะ pattern (short e)
            ['input' => 'เกะ', 'expected' => 'เกะ'],
            ['input' => 'เมะ', 'expected' => 'เมะ'],
            ['input' => 'เนะ', 'expected' => 'เนะ'],

            // โ-ะ pattern (short o)
            ['input' => 'โกะ', 'expected' => 'โกะ'],
            ['input' => 'โมะ', 'expected' => 'โมะ'],
            ['input' => 'โนะ', 'expected' => 'โนะ'],

            // เ-าะ pattern (short o with different spelling)
            ['input' => 'เกาะ', 'expected' => 'เกาะ'],
            ['input' => 'เมาะ', 'expected' => 'เมาะ'],

            // เ-ียะ pattern (short ia)
            ['input' => 'เกียะ', 'expected' => 'เกียะ'],
            ['input' => 'เมียะ', 'expected' => 'เมียะ'],

            // เ-ือะ pattern (short uea)
            ['input' => 'เกือะ', 'expected' => 'เกือะ'],
            ['input' => 'เมือะ', 'expected' => 'เมือะ'],

            // เ-อะ pattern (short er)
            ['input' => 'เกอะ', 'expected' => 'เกอะ'],
            ['input' => 'เมอะ', 'expected' => 'เมอะ'],
        ];
    }

    /**
     * Sara Am (ำ) cases requiring special handling
     */
    public static function saraAm(): array
    {
        return [
            // Common sara am words
            ['input' => 'น้ำ', 'expected' => 'น้ำ'],
            ['input' => 'ข้าม', 'expected' => 'ข้าม'],
            ['input' => 'กำ', 'expected' => 'กำ'],
            ['input' => 'สำคัญ', 'expected' => 'สำคัญ'],
            ['input' => 'ทำ', 'expected' => 'ทำ'],
            ['input' => 'ขำ', 'expected' => 'ขำ'],

            // Decomposed sara am (should be normalized to composed form)
            ['input' => "น้\u{0e31}ม", 'expected' => 'น้ำ'], // น้ + ั + ม → น้ำ
            ['input' => "ก\u{0e31}ม", 'expected' => 'กำ'], // ก + ั + ม → กำ
        ];
    }

    /**
     * Unicode normalization test cases (NFC/NFD)
     */
    public static function unicodeNormalization(): array
    {
        return [
            // Decomposed to composed normalization
            ['input' => "\u{0e01}\u{0e49}", 'expected' => 'ก้'], // ก + ้ → ก้
            ['input' => "\u{0e01}\u{0e48}", 'expected' => 'ก่'], // ก + ่ → ก่
            ['input' => "\u{0e01}\u{0e34}", 'expected' => 'กิ'], // ก + ิ → กิ
            ['input' => "\u{0e01}\u{0e35}", 'expected' => 'กี'], // ก + ี → กี

            // Complex decomposed sequences
            ['input' => "\u{0e01}\u{0e34}\u{0e48}", 'expected' => 'กิ่'], // ก + ิ + ่ → กิ่
            ['input' => "\u{0e01}\u{0e35}\u{0e49}", 'expected' => 'กี้'], // ก + ี + ้ → กี้

            // Leading vowel with decomposition
            ['input' => "\u{0e40}\u{0e01}", 'expected' => 'เก'], // เ + ก → เก
            ['input' => "\u{0e42}\u{0e01}", 'expected' => 'โก'], // โ + ก → โก
        ];
    }

    /**
     * Character reordering test cases
     */
    public static function characterReordering(): array
    {
        return [
            // Wrong order: tone mark before vowel (should be corrected)
            ['input' => "\u{0e01}\u{0e48}\u{0e34}", 'expected' => 'กิ่'], // ก + ่ + ิ → กิ่
            ['input' => "\u{0e01}\u{0e49}\u{0e35}", 'expected' => 'กี้'], // ก + ้ + ี → กี้

            // Multiple marks in wrong order
            ['input' => "\u{0e01}\u{0e49}\u{0e34}\u{0e48}", 'expected' => 'กิ้'], // Complex reordering

            // Leading vowel reordering
            ['input' => "\u{0e40}\u{0e01}\u{0e48}\u{0e34}\u{0e19}", 'expected' => 'เกิ่น'],
        ];
    }

    /**
     * Edge cases and malformed input
     */
    public static function edgeCases(): array
    {
        return [
            // Empty and whitespace
            ['input' => '', 'expected' => ''],
            ['input' => '   ', 'expected' => ''],
            ['input' => "\t\n\r ", 'expected' => ''],

            // Mixed content
            ['input' => 'Hello สวัสดี World', 'expected' => 'Hello สวัสดี World'],
            ['input' => '123 ข้อ สวัสดี 456', 'expected' => '123 ข้อ สวัสดี 456'],

            // Multiple whitespace normalization
            ['input' => 'สวัสดี    โลก', 'expected' => 'สวัสดี โลก'],
            ['input' => "สวัสดี\n\tโลก\r\n", 'expected' => 'สวัสดี โลก'],

            // Duplicate combining marks (invalid)
            ['input' => "\u{0e01}\u{0e48}\u{0e49}", 'expected' => 'ก้'], // Keep last tone mark
            ['input' => "\u{0e01}\u{0e34}\u{0e34}", 'expected' => 'กิ'], // Remove duplicate vowel

            // Invalid sequences
            ['input' => "\u{0e48}สวัสดี", 'expected' => 'สวัสดี'], // Tone mark without consonant
            ['input' => "\u{0e34}\u{0e01}", 'expected' => 'กิ'], // Vowel before consonant

            // Null bytes and control characters
            ['input' => "สวัสดี\0โลก", 'expected' => 'สวัสดี โลก'],
            ['input' => "สวัสดี\x01\x02โลก", 'expected' => 'สวัสดี โลก'],
        ];
    }

    /**
     * Performance test data
     */
    public static function performanceText(): array
    {
        return [
            // Short text (< 100 characters)
            'short' => 'สวัสดีโลกสวยงาม',

            // Medium text (100-1000 characters)
            'medium' => str_repeat('สวัสดีโลกสวยงามมากจริงๆ ', 50),

            // Large text (1000+ characters)
            'large' => str_repeat('ประเทศไทยเป็นประเทศที่สวยงามและมีวัฒนธรรมที่ยาวนาน ', 100),

            // Very large text (10000+ characters)
            'very_large' => str_repeat('ภาษาไทยเป็นภาษาราชการของประเทศไทยและเป็นภาษาหลักที่ใช้สื่อสารกันในชีวิตประจำวันของคนไทย ', 200),
        ];
    }

    /**
     * Real-world Thai text samples
     */
    public static function realWorldSamples(): array
    {
        return [
            // News headline
            'news' => 'นายกรัฐมนตรีเดินทางเยือนต่างประเทศเพื่อหารือความร่วมมือทางเศรษฐกิจ',

            // Literature excerpt
            'literature' => 'กาลครั้งหนึ่งนานมาแล้ว มีเจ้าหญิงองค์หนึ่งที่สวยงามและใจดีมาก',

            // Technical term
            'technical' => 'เทคโนโลยีสารสนเทศและการสื่อสารในยุคดิจิทัล',

            // Address
            'address' => '123/45 ถนนสุขุมวิท แขวงคลองเตย เขตคลองเตย กรุงเทพมหานคร 10110',

            // Recipe
            'recipe' => 'ส้มตำไทย: มะละกอดิบ พริกขี้หนู กระเทียม ปลาร้า น้ำปลา น้ำตาลปี๊บ',

            // Academic text
            'academic' => 'การศึกษาวิจัยเกี่ยวกับผลกระทบของการเปลี่ยนแปลงสภาพภูมิอากาศต่อการเกษตรในภูมิภาคเอเชียตะวันออกเฉียงใต้',
        ];
    }

    /**
     * Get all datasets combined
     */
    public static function all(): array
    {
        return [
            'basic_words' => self::basicWords(),
            'tone_marks' => self::toneMarks(),
            'complex_vowels' => self::complexVowels(),
            'sara_am' => self::saraAm(),
            'unicode_normalization' => self::unicodeNormalization(),
            'character_reordering' => self::characterReordering(),
            'edge_cases' => self::edgeCases(),
            'performance' => self::performanceText(),
            'real_world' => self::realWorldSamples(),
        ];
    }
}
