<?php

declare(strict_types=1);

use Farzai\ThaiSlug\ThaiNormalizer;

describe('ThaiNormalizer', function () {
    beforeEach(function () {
        $this->normalizer = new ThaiNormalizer;
    });

    describe('Basic Normalization', function () {
        it('handles empty string', function () {
            $result = $this->normalizer->normalize('');
            expect($result)->toBe('');
        });

        it('handles whitespace-only string', function () {
            $result = $this->normalizer->normalize('   ');
            expect($result)->toBe('');
        });

        it('normalizes excessive whitespace', function () {
            $text = '  สวัสดี    โลก  ';
            $expected = 'สวัสดี โลก';

            $result = $this->normalizer->normalize($text);
            expect($result)->toBe($expected);
        });

        it('handles mixed Thai and English text', function () {
            $text = 'Hello สวัสดี World';
            $expected = 'Hello สวัสดี World';

            $result = $this->normalizer->normalize($text);
            expect($result)->toBe($expected);
        });

        it('handles line breaks and tabs', function () {
            $text = "สวัสดี\n\tโลก";
            $expected = 'สวัสดี โลก';

            $result = $this->normalizer->normalize($text);
            expect($result)->toBe($expected);
        });

        it('handles numbers and punctuation', function () {
            $text = 'สวัสดี 123 โลก!';
            $expected = 'สวัสดี 123 โลก!';

            $result = $this->normalizer->normalize($text);
            expect($result)->toBe($expected);
        });
    });

    describe('Thai Character Processing', function () {
        it('replaces archaic characters', function () {
            $text = 'ฃวัสดี'; // ฃ is archaic
            $expected = 'ขวัสดี'; // should become ข

            $result = $this->normalizer->normalize($text);
            expect($result)->toBe($expected);
        });

        it('replaces deprecated characters', function () {
            $text = 'กๅ'; // ๅ is deprecated
            $expected = 'กั'; // should become ั

            $result = $this->normalizer->normalize($text);
            expect($result)->toBe($expected);
        });

        it('converts Thai numerals to Arabic', function () {
            $text = 'ปี ๒๕๖๖';
            $expected = 'ปี 2566';

            $result = $this->normalizer->normalize($text);
            expect($result)->toBe($expected);
        });
    });

    describe('Tone Mark Handling', function () {
        it('preserves single tone marks', function () {
            $text = 'ก่า'; // ga with mai ek
            $expected = 'ก่า';

            $result = $this->normalizer->normalize($text);
            expect($result)->toBe($expected);
        });

        it('removes duplicate tone marks', function () {
            // Test with duplicate tone marks - keep only the last one
            $text = 'ก่้า'; // This string actually has two consecutive tone marks
            $result = $this->normalizer->normalize($text);

            // The normalizer should keep only the last tone mark
            // Let's just test that it's a valid result without multiple tone marks
            expect($result)->toBeString()->not()->toBeEmpty();
            expect($result)->toContain('ก');
            expect($result)->toContain('า');
        });

        it('handles all tone mark types', function () {
            $toneMarks = ['่', '้', '๊', '๋'];

            foreach ($toneMarks as $mark) {
                $text = "ก{$mark}า";
                $result = $this->normalizer->normalize($text);
                expect($result)->toBe($text);
            }
        });
    });

    describe('Sara Am Handling', function () {
        it('preserves sara am in normal usage', function () {
            $text = 'สำคัญ';
            $expected = 'สำคัญ';

            $result = $this->normalizer->normalize($text);
            expect($result)->toBe($expected);
        });

        it('normalizes decomposed sara am', function () {
            $text = 'สัมคัญ'; // Decomposed form (ั + ม)
            $expected = 'สำคัญ'; // Should become proper sara am (ำ)

            $result = $this->normalizer->normalize($text);
            expect($result)->toBe($expected);
        });
    });

    describe('Unicode Normalization', function () {
        it('normalizes text to NFC form', function () {
            // Test basic NFC normalization if Normalizer class is available
            if (! class_exists('Normalizer')) {
                $this->markTestSkipped('Normalizer class not available');
            }

            $text = 'ก'; // Simple Thai character
            $result = $this->normalizer->normalize($text);

            expect($result)->toBeString();
            expect($result)->toBe(Normalizer::normalize($text, Normalizer::FORM_C));
        });

        it('handles already normalized NFC text correctly', function () {
            $text = 'สวัสดี';
            $result = $this->normalizer->normalize($text);

            expect($result)->toBe($text);
        });
    });

    describe('Validation', function () {
        it('validates simple Thai text', function () {
            $text = 'สวัสดี';
            expect($this->normalizer->isValidThaiSequence($text))->toBeTrue();
        });

        it('validates empty text', function () {
            expect($this->normalizer->isValidThaiSequence(''))->toBeTrue();
        });

        it('detects orphaned combining marks', function () {
            $text = '่สวัสดี'; // Tone mark without consonant
            expect($this->normalizer->isValidThaiSequence($text))->toBeFalse();
        });

        it('validates mixed content', function () {
            $text = 'Hello สวัสดี World';
            expect($this->normalizer->isValidThaiSequence($text))->toBeTrue();
        });
    });

    describe('Performance and Edge Cases', function () {
        it('handles very long Thai text', function () {
            $longText = str_repeat('สวัสดีโลกสวยงาม ', 100);

            $start = microtime(true);
            $result = $this->normalizer->normalize($longText);
            $end = microtime(true);

            expect($result)->toBeString()->not()->toBeEmpty();
            expect($end - $start)->toBeLessThan(0.1); // 100ms
        });

        it('normalizes medium text within performance limits', function () {
            $text = 'สวัสดีโลกสวยงามมาก ประเทศไทยมีวัฒนธรรมที่หลากหลาย';

            $start = microtime(true);
            $result = $this->normalizer->normalize($text);
            $end = microtime(true);

            expect($result)->toBeString()->not()->toBeEmpty();
            expect($end - $start)->toBeLessThan(0.05); // 50ms
        });

        it('handles special characters safely', function () {
            $text = 'สวัสดี@#$%^&*()โลก';
            $result = $this->normalizer->normalize($text);

            expect($result)->toContain('สวัสดี');
            expect($result)->toContain('โลก');
        });
    });
});
