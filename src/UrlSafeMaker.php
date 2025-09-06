<?php

declare(strict_types=1);

namespace Farzai\ThaiSlug;

class UrlSafeMaker
{
    /**
     * Unicode transliteration map for common accented characters
     *
     * @var array<string, string>
     */
    private array $transliterationMap = [
        // Basic Latin Extended-A
        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae',
        'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i',
        'î' => 'i', 'ï' => 'i', 'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o',
        'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
        'ý' => 'y', 'þ' => 'th', 'ÿ' => 'y',

        // Extended characters
        'ă' => 'a', 'ą' => 'a', 'ć' => 'c', 'č' => 'c', 'ď' => 'd', 'đ' => 'd', 'ě' => 'e',
        'ę' => 'e', 'ğ' => 'g', 'į' => 'i', 'ı' => 'i', 'ł' => 'l', 'ń' => 'n',
        'ň' => 'n', 'ő' => 'o', 'œ' => 'oe', 'ř' => 'r', 'ś' => 's', 'š' => 's', 'ť' => 't',
        'ű' => 'u', 'ů' => 'u', 'ź' => 'z', 'ž' => 'z',

        // Common diacritics (macrons and other long vowels)
        'ā' => 'a', 'ē' => 'e', 'ī' => 'i', 'ō' => 'o', 'ū' => 'u',
    ];

    /**
     * @param  array<string, mixed>  $options
     */
    public function makeSafe(string $text, array $options = []): string
    {
        $separatorOption = $options['separator'] ?? '-';
        $separator = is_string($separatorOption) ? $separatorOption : '-';
        $maxLength = isset($options['maxLength']) && is_int($options['maxLength']) ? $options['maxLength'] : null;
        $lowercase = (bool) ($options['lowercase'] ?? true);

        $text = trim($text);

        if (empty($text)) {
            return '';
        }

        // Step 1: Convert to lowercase if enabled
        if ($lowercase) {
            $text = strtolower($text);
        }

        // Step 2: Transliterate Unicode characters
        $text = $this->transliterateUnicode($text);

        // Step 3: Replace multiple whitespace with separator FIRST
        $text = preg_replace('/\s+/', $separator, $text) ?? $text;

        // Step 4: Remove or replace special characters for RFC 3986 compliance
        $text = $this->sanitizeForUrl($text, $separator);

        // Step 5: Remove multiple consecutive separators
        if ($separator !== '') {
            $text = preg_replace('/['.preg_quote($separator, '/').']+/', $separator, $text) ?? $text;

            // Remove separators from start and end
            $text = trim($text, $separator);
        }

        // Step 6: Apply intelligent max length truncation
        if ($maxLength !== null && strlen($text) > $maxLength) {
            $text = $this->intelligentTruncate($text, $maxLength, $separator);
        }

        return $text;
    }

    /**
     * Transliterate Unicode characters to ASCII equivalents
     */
    private function transliterateUnicode(string $text): string
    {
        // Apply our custom transliteration map
        $text = strtr($text, $this->transliterationMap);

        // Fallback: use iconv for any remaining accented characters
        if (function_exists('iconv')) {
            $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
            if ($converted !== false) {
                $text = $converted;
            }
        }

        return $text;
    }

    /**
     * Sanitize text for URL safety (RFC 3986 compliance)
     */
    private function sanitizeForUrl(string $text, string $separator): string
    {
        // Remove or replace common special characters
        $replacements = [
            // URL-unsafe characters that should become separators
            '@' => $separator, '#' => $separator, '?' => $separator, '&' => $separator,
            '=' => $separator, '+' => $separator, '%' => $separator, '<' => $separator,
            '>' => $separator, '{' => $separator, '}' => $separator,
            '|' => $separator, '\\' => $separator, '^' => $separator, '`' => $separator,
            '[' => $separator, ']' => $separator, '(' => $separator, ')' => $separator,

            // Punctuation that should be removed or become separators
            ',' => $separator, ';' => $separator, ':' => $separator, '!' => $separator,
            '.' => $separator, "'" => '', '"' => '', '$' => $separator,

            // Keep hyphens and underscores as they are URL-safe
            // '-' and '_' are preserved
        ];

        $text = strtr($text, $replacements);

        // Remove any remaining non-alphanumeric characters except hyphens, underscores, and the separator
        if ($separator !== '') {
            $pattern = '/[^a-zA-Z0-9\-_'.preg_quote($separator, '/').']/';
        } else {
            $pattern = '/[^a-zA-Z0-9\-_]/';
        }

        $text = preg_replace($pattern, '', $text) ?? $text;

        return $text;
    }

    /**
     * Intelligent truncation that prefers word boundaries
     */
    private function intelligentTruncate(string $text, int $maxLength, string $separator): string
    {
        if (strlen($text) <= $maxLength) {
            return $text;
        }

        // First, try basic truncation
        $truncated = substr($text, 0, $maxLength);

        // If the truncated text doesn't end with a separator, it's already good
        if ($separator === '' || ! str_ends_with($truncated, $separator)) {
            return $truncated;
        }

        // If it ends with a separator, try to truncate at word boundary
        if (strlen($separator) > 0 && strpos($text, $separator) !== false) {
            $lastSeparator = strrpos($truncated, $separator);

            // Only truncate at word boundary if it's not too early in the string
            if ($lastSeparator !== false && $lastSeparator >= ($maxLength * 0.4)) {
                return substr($truncated, 0, $lastSeparator);
            }
        }

        // Fall back to character truncation, ensuring we don't end with a separator
        if (strlen($separator) > 0) {
            $truncated = rtrim($truncated, $separator);
        }

        return $truncated;
    }
}
