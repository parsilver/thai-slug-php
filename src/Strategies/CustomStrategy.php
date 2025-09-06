<?php

declare(strict_types=1);

namespace Farzai\ThaiSlug\Strategies;

/**
 * Custom transliteration strategy
 *
 * Allows for custom mapping rules and user-defined transliteration patterns.
 * Provides flexibility for specific use cases or domain requirements.
 */
class CustomStrategy extends AbstractStrategy
{
    /** @var array<string, string> */
    private array $customMapping = [];

    public function getName(): string
    {
        return 'custom';
    }

    protected function doTransliterate(string $text): string
    {
        // Use custom mapping if provided, otherwise fall back to phonetic
        $mapping = $this->customMapping ?: $this->getDefaultMapping();

        $text = strtr($text, $mapping);

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
            'custom_mapping' => [],
            'fallback_to_phonetic' => true,
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function validateOptions(array $options): bool
    {
        $validKeys = ['custom_mapping', 'fallback_to_phonetic'];

        foreach (array_keys($options) as $key) {
            if (! in_array($key, $validKeys)) {
                return false;
            }
        }

        // Validate custom mapping if provided
        if (isset($options['custom_mapping'])) {
            if (! is_array($options['custom_mapping'])) {
                return false;
            }
            /** @var array<string, string> $mapping */
            $mapping = $options['custom_mapping'];
            $this->customMapping = $mapping;
        }

        return true;
    }

    /**
     * @return array<string, string>
     */
    private function getDefaultMapping(): array
    {
        // Fallback to phonetic mapping if no custom mapping provided
        return [
            'ก' => 'k', 'ข' => 'kh', 'ฃ' => 'kh', 'ค' => 'kh', 'ฅ' => 'kh', 'ฆ' => 'kh',
            'ง' => 'ng', 'จ' => 'ch', 'ฉ' => 'ch', 'ช' => 'ch', 'ซ' => 's', 'ฌ' => 'ch',
            'ญ' => 'y', 'ฎ' => 'd', 'ฏ' => 't', 'ฐ' => 'th', 'ฑ' => 'th', 'ฒ' => 'th',
            'ณ' => 'n', 'ด' => 'd', 'ต' => 't', 'ถ' => 'th', 'ท' => 'th', 'ธ' => 'th',
            'น' => 'n', 'บ' => 'b', 'ป' => 'p', 'ผ' => 'ph', 'ฝ' => 'f', 'พ' => 'ph',
            'ฟ' => 'f', 'ภ' => 'ph', 'ม' => 'm', 'ย' => 'y', 'ร' => 'r', 'ล' => 'l',
            'ว' => 'w', 'ศ' => 's', 'ษ' => 's', 'ส' => 's', 'ห' => 'h', 'ฬ' => 'l',
            'อ' => '', 'ฮ' => 'h',
            'ะ' => 'a', 'า' => 'a', 'ิ' => 'i', 'ี' => 'i', 'ึ' => 'ue', 'ื' => 'ue',
            'ุ' => 'u', 'ู' => 'u', 'เ' => 'e', 'แ' => 'ae', 'โ' => 'o', 'ใ' => 'ai',
            'ไ' => 'ai', 'ำ' => 'am', 'ๅ' => '', 'ๆ' => '', '่' => '', '้' => '',
            '๊' => '', '๋' => '', '์' => '', 'ั' => 'a',
            '๐' => '0', '๑' => '1', '๒' => '2', '๓' => '3', '๔' => '4',
            '๕' => '5', '๖' => '6', '๗' => '7', '๘' => '8', '๙' => '9',
        ];
    }
}
