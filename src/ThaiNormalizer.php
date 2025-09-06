<?php

declare(strict_types=1);

namespace Farzai\ThaiSlug;

use Farzai\ThaiSlug\Utils\ThaiCharacterClassifier;

/**
 * Simplified Thai text normalizer with inlined logic (performance-optimized)
 *
 * Replaces complex dependency injection with simple, fast inlined operations.
 */
class ThaiNormalizer
{
    /**
     * Comprehensive Thai text normalization (inlined for performance)
     */
    public function normalize(string $text): string
    {
        if (empty($text)) {
            return '';
        }

        // Step 1: Basic cleanup and input validation (inlined)
        $text = trim($text);
        $text = preg_replace('/\s+/', ' ', $text); // normalize whitespace

        if (empty($text)) {
            return '';
        }

        // Step 2: Unicode normalization (inlined NFC - Canonical Composition)
        if (class_exists('Normalizer')) {
            $normalized = \Normalizer::normalize($text, \Normalizer::FORM_C);
            if ($normalized !== false) {
                $text = $normalized;
            }
        }

        // Step 3: Thai-specific normalizations (inlined from ThaiCharacterProcessor)
        $text = $this->processThaiCharacters($text);

        // Step 4: Final whitespace normalization (inlined)
        $text = trim($text);
        $text = preg_replace('/\s+/', ' ', $text) ?: $text;

        return $text;
    }

    /**
     * Validate if the normalized text follows Thai orthographic rules
     *
     * @api
     */
    public function isValidThaiSequence(string $text): bool
    {
        if (empty($text)) {
            return true;
        }

        $textLength = mb_strlen($text, 'UTF-8');

        for ($i = 0; $i < $textLength; $i++) {
            $char = mb_substr($text, $i, 1, 'UTF-8');

            // Basic validation: check for orphaned combining marks
            if (ThaiCharacterClassifier::isCombiningMark($char) && $i === 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Process Thai-specific character normalizations (inlined logic)
     */
    private function processThaiCharacters(string $text): string
    {
        // Replace deprecated characters with modern equivalents
        $replacements = [
            'ๅ' => 'ั', // deprecated character
            'ฃ' => 'ข', // archaic character
            'ฅ' => 'ค', // archaic character
        ];

        $text = strtr($text, $replacements);

        // Normalize duplicate tone marks (keep only the last one)
        $text = $this->normalizeToneMarks($text);

        // Normalize decomposed Sara Am (ั + ม) to proper Sara Am (ำ)
        $text = $this->normalizeSaraAm($text);

        // Normalize Thai numerals to Arabic (optional, can be configured)
        $thaiDigits = ['๐', '๑', '๒', '๓', '๔', '๕', '๖', '๗', '๘', '๙'];
        $arabicDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace($thaiDigits, $arabicDigits, $text);
    }

    /**
     * Normalize duplicate tone marks (keep only the last one)
     */
    private function normalizeToneMarks(string $text): string
    {
        $chars = mb_str_split($text, 1, 'UTF-8');
        $result = [];

        for ($i = 0; $i < count($chars); $i++) {
            $char = $chars[$i];

            // If this is not a tone mark, just add it
            if (! ThaiCharacterClassifier::isToneMark($char)) {
                $result[] = $char;

                continue;
            }

            // This is a tone mark - look ahead for consecutive tone marks
            $toneMarks = [$char];
            $j = $i + 1;

            while ($j < count($chars) && ThaiCharacterClassifier::isToneMark($chars[$j])) {
                $toneMarks[] = $chars[$j];
                $j++;
            }

            // Only keep the last tone mark from the sequence
            $result[] = end($toneMarks);

            // Skip the processed tone marks
            $i = $j - 1;
        }

        return implode('', $result);
    }

    /**
     * Normalize decomposed Sara Am (ั + ม) to proper Sara Am (ำ)
     */
    private function normalizeSaraAm(string $text): string
    {
        // Replace ั followed by ม with proper Sara Am ำ
        // This handles cases where Sara Am is decomposed
        return str_replace('ัม', 'ำ', $text);
    }
}
