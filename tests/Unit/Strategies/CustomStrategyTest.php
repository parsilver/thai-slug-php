<?php

declare(strict_types=1);

use Farzai\ThaiSlug\Strategies\CustomStrategy;

describe('CustomStrategy', function () {
    describe('Strategy Identity', function () {
        it('has correct strategy name', function () {
            $strategy = new CustomStrategy;
            expect($strategy->getName())->toBe('custom');
        });

        it('validates options correctly', function () {
            $strategy = new CustomStrategy;
            expect($strategy->validateOptions([]))->toBeTrue();
            expect($strategy->validateOptions(['custom_mapping' => []]))->toBeTrue();
            expect($strategy->validateOptions(['fallback_to_phonetic' => true]))->toBeTrue();
            expect($strategy->validateOptions(['invalid_option' => true]))->toBeFalse();
        });
    });

    describe('Default Behavior (No Custom Mapping)', function () {
        beforeEach(function () {
            $this->strategy = new CustomStrategy;
        });

        it('falls back to phonetic mapping when no custom mapping provided', function () {
            expect($this->strategy->transliterate('ก'))->toBe('k');
            expect($this->strategy->transliterate('ข'))->toBe('kh');
            expect($this->strategy->transliterate('จ'))->toBe('ch');
            expect($this->strategy->transliterate('ด'))->toBe('d');
            expect($this->strategy->transliterate('ต'))->toBe('t');
        });

        it('handles common Thai words with default mapping', function () {
            expect($this->strategy->transliterate('สวัสดี'))->toBe('swasdi');
            expect($this->strategy->transliterate('ขอบคุณ'))->toBe('khbkhun');
        });

        it('handles Thai digits with default mapping', function () {
            expect($this->strategy->transliterate('๑๒๓'))->toBe('123');
        });
    });

    describe('Custom Mapping Configuration', function () {
        it('uses custom mapping when provided in options', function () {
            $customMapping = [
                'ก' => 'g',
                'ข' => 'k',
                'จ' => 'j',
                'ด' => 'd',
                'ต' => 't',
            ];

            $strategy = new CustomStrategy(['custom_mapping' => $customMapping]);

            expect($strategy->transliterate('ก'))->toBe('g');
            expect($strategy->transliterate('ข'))->toBe('k');
            expect($strategy->transliterate('จ'))->toBe('j');
            expect($strategy->transliterate('ด'))->toBe('d');
            expect($strategy->transliterate('ต'))->toBe('t');
        });

        it('handles partial custom mapping with unmapped characters', function () {
            $customMapping = [
                'ก' => 'G',  // Custom mapping for ก
                'ข' => 'K',  // Custom mapping for ข
            ];

            $strategy = new CustomStrategy(['custom_mapping' => $customMapping]);

            // Characters with custom mapping
            expect($strategy->transliterate('ก'))->toBe('G');
            expect($strategy->transliterate('ข'))->toBe('K');

            // Characters without custom mapping remain unchanged
            expect($strategy->transliterate('จ'))->toBe('จ');
            expect($strategy->transliterate('ด'))->toBe('ด');
        });

        it('handles custom mapping with special characters', function () {
            $customMapping = [
                'ก' => 'GG',
                'ข' => 'KH',
                'จ' => 'CH',
                ' ' => '_',
                '.' => '-',
            ];

            $strategy = new CustomStrategy(['custom_mapping' => $customMapping]);

            expect($strategy->transliterate('ก.ข จ'))->toBe('GG-KH_CH');
        });

        it('handles empty custom mapping array', function () {
            $strategy = new CustomStrategy(['custom_mapping' => []]);

            // Should behave same as default (no custom mapping)
            expect($strategy->transliterate('ก'))->toBe('k');
            expect($strategy->transliterate('ข'))->toBe('kh');
        });
    });

    describe('Domain-Specific Custom Mappings', function () {
        it('supports academic transliteration style', function () {
            $academicMapping = [
                'ก' => 'k',
                'ข' => 'kh',
                'ค' => 'kh',
                'จ' => 'c',    // Different from standard 'ch'
                'ช' => 'ch',
                'ด' => 'd',
                'ต' => 't',
                'ท' => 'th',
                'น' => 'n',
                'บ' => 'b',
                'ป' => 'p',
                'ม' => 'm',
                'ย' => 'y',
                'ร' => 'r',
                'ล' => 'l',
                'ว' => 'v',    // Different from standard 'w'
                'ส' => 's',
                'ห' => 'h',
            ];

            $strategy = new CustomStrategy(['custom_mapping' => $academicMapping]);

            expect($strategy->transliterate('จ'))->toBe('c');
            expect($strategy->transliterate('ว'))->toBe('v');
            expect($strategy->transliterate('จริง'))->toBe('crิง'); // Mixed mapping
        });

        it('supports simplified transliteration', function () {
            $simplifiedMapping = [
                'ก' => 'g',
                'ข' => 'k',
                'ค' => 'k',
                'จ' => 'j',
                'ช' => 'ch',
                'ด' => 'd',
                'ต' => 't',
                'ท' => 't',  // Simplified from 'th'
                'น' => 'n',
                'บ' => 'b',
                'ป' => 'p',
                'ม' => 'm',
                'ย' => 'y',
                'ร' => 'r',
                'ล' => 'l',
                'ว' => 'w',
                'ส' => 's',
                'ห' => 'h',
            ];

            $strategy = new CustomStrategy(['custom_mapping' => $simplifiedMapping]);

            expect($strategy->transliterate('ก'))->toBe('g');
            expect($strategy->transliterate('จ'))->toBe('j');
            expect($strategy->transliterate('ท'))->toBe('t'); // Simplified
        });
    });

    describe('Text Processing and Normalization', function () {
        beforeEach(function () {
            $this->strategy = new CustomStrategy([
                'custom_mapping' => [
                    'ก' => 'k',
                    'ข' => 'kh',
                    'า' => 'a',
                    ' ' => ' ',
                ],
            ]);
        });

        it('handles multiple spaces correctly', function () {
            expect($this->strategy->transliterate('ก   ข'))->toBe('k kh');
            expect($this->strategy->transliterate('ก     ข'))->toBe('k kh');
        });

        it('trims leading and trailing spaces', function () {
            expect($this->strategy->transliterate('  ก  '))->toBe('k');
            expect($this->strategy->transliterate('ก ข  '))->toBe('k kh');
        });

        it('handles empty and whitespace input', function () {
            expect($this->strategy->transliterate(''))->toBe('');
            expect($this->strategy->transliterate('   '))->toBe('');
        });
    });

    describe('Edge Cases and Error Handling', function () {
        it('handles mixed mapped and unmapped characters', function () {
            $customMapping = [
                'ก' => 'K',
                'า' => 'AA',
            ];

            $strategy = new CustomStrategy(['custom_mapping' => $customMapping]);

            // Mix of mapped and unmapped characters
            expect($strategy->transliterate('กาข'))->toBe('KAAข');
        });

        it('handles numeric and punctuation characters', function () {
            $customMapping = [
                'ก' => 'k',
                '1' => 'one',
                '.' => 'DOT',
            ];

            $strategy = new CustomStrategy(['custom_mapping' => $customMapping]);

            expect($strategy->transliterate('ก1.'))->toBe('koneDOT');
        });

        it('handles unicode and special encoding', function () {
            $customMapping = [
                'ก' => '𝕜',  // Unicode mathematical symbol
                'ข' => '→',  // Arrow symbol
            ];

            $strategy = new CustomStrategy(['custom_mapping' => $customMapping]);

            expect($strategy->transliterate('กข'))->toBe('𝕜→');
        });

        it('handles very long text efficiently', function () {
            $customMapping = [
                'ก' => 'k',
                'ร' => 'r',
                'ุ' => 'u',
                'ง' => 'ng',
                'เ' => 'e',
                'ท' => 'th',
                'พ' => 'ph',
                'ม' => 'm',
                'ห' => 'h',
                'า' => 'a',
                'น' => 'n',
                'ค' => 'kh',
            ];

            $strategy = new CustomStrategy(['custom_mapping' => $customMapping]);

            $longText = str_repeat('กรุงเทพมหานคร', 100);
            $result = $strategy->transliterate($longText);

            expect($result)->toContain('krungethphmhankhr');
        });
    });

    describe('Performance Requirements', function () {
        beforeEach(function () {
            $this->strategy = new CustomStrategy([
                'custom_mapping' => [
                    'ก' => 'k', 'ข' => 'kh', 'ค' => 'kh', 'ง' => 'ng',
                    'จ' => 'ch', 'ช' => 'ch', 'ด' => 'd', 'ต' => 't',
                    'ท' => 'th', 'น' => 'n', 'บ' => 'b', 'ป' => 'p',
                    'ม' => 'm', 'ย' => 'y', 'ร' => 'r', 'ล' => 'l',
                    'ว' => 'w', 'ส' => 's', 'ห' => 'h', 'อ' => '',
                    'า' => 'a', 'ิ' => 'i', 'ี' => 'i', 'ุ' => 'u', 'ู' => 'u',
                    'เ' => 'e', 'แ' => 'ae', 'โ' => 'o', 'ำ' => 'am',
                ],
            ]);
        });

        it('processes single words quickly', function () {
            $start = microtime(true);
            $this->strategy->transliterate('กรุงเทพ');
            $end = microtime(true);
            expect($end - $start)->toBeLessThan(0.01); // 10ms
        });

        it('processes medium text within limits', function () {
            $text = str_repeat('กรุงเทพมหานคร ', 10);
            $start = microtime(true);
            $this->strategy->transliterate($text);
            $end = microtime(true);
            expect($end - $start)->toBeLessThan(0.1); // 100ms
        });
    });

    describe('Real-World Use Cases', function () {
        it('supports brand-specific transliteration', function () {
            $brandMapping = [
                'ก' => 'G',     // Brand prefers 'G' over 'k'
                'ร' => 'R',     // Brand prefers uppercase
                'ุ' => 'OO',    // Brand style
                'ง' => 'NG',
                'เ' => 'E',
                'ท' => 'T',     // Simplified from 'th'
                'พ' => 'P',     // Simplified from 'ph'
            ];

            $strategy = new CustomStrategy(['custom_mapping' => $brandMapping]);

            expect($strategy->transliterate('กรุงเทพ'))->toBe('GROONGETP');
        });

        it('supports linguistic research mapping', function () {
            $linguisticMapping = [
                'ก' => 'k̚',    // With phonetic notation
                'ด' => 'd̚',    // With phonetic notation
                'ต' => 't̚',    // With phonetic notation
                'บ' => 'b̚',    // With phonetic notation
                'ป' => 'p̚',    // With phonetic notation
            ];

            $strategy = new CustomStrategy(['custom_mapping' => $linguisticMapping]);

            expect($strategy->transliterate('กบ'))->toBe('k̚b̚');
            expect($strategy->transliterate('ดป'))->toBe('d̚p̚');
        });
    });

    describe('Configuration Flexibility', function () {
        it('allows runtime mapping updates through options validation', function () {
            $strategy = new CustomStrategy;

            // Test that options validation sets the custom mapping
            $newOptions = [
                'custom_mapping' => [
                    'ก' => 'NEW_K',
                    'ข' => 'NEW_KH',
                ],
            ];

            expect($strategy->validateOptions($newOptions))->toBeTrue();

            // After validation, the mapping should be applied
            expect($strategy->transliterate('ก'))->toBe('NEW_K');
            expect($strategy->transliterate('ข'))->toBe('NEW_KH');
        });

        it('preserves fallback behavior when custom mapping is invalid', function () {
            // Invalid mapping (not an array)
            $strategy = new CustomStrategy;
            expect($strategy->validateOptions(['custom_mapping' => 'not-an-array']))->toBeFalse();
        });
    });
});
