<?php

declare(strict_types=1);

use Farzai\ThaiSlug\UrlSafeMaker;

describe('UrlSafeMaker', function () {
    beforeEach(function () {
        /** @var UrlSafeMaker $urlSafeMaker */
        $this->urlSafeMaker = new UrlSafeMaker;
    });

    describe('Basic URL Safety', function () {
        it('converts text to lowercase', function () {
            expect($this->urlSafeMaker->makeSafe('Hello World'))->toBe('hello-world');
            expect($this->urlSafeMaker->makeSafe('UPPERCASE TEXT'))->toBe('uppercase-text');
            expect($this->urlSafeMaker->makeSafe('MiXeD CaSe'))->toBe('mixed-case');
        });

        it('replaces spaces with separator', function () {
            expect($this->urlSafeMaker->makeSafe('hello world'))->toBe('hello-world');
            expect($this->urlSafeMaker->makeSafe('multiple   spaces'))->toBe('multiple-spaces');
            expect($this->urlSafeMaker->makeSafe('  leading and trailing  '))->toBe('leading-and-trailing');
        });

        it('handles empty and whitespace input', function () {
            expect($this->urlSafeMaker->makeSafe(''))->toBe('');
            expect($this->urlSafeMaker->makeSafe('   '))->toBe('');
            expect($this->urlSafeMaker->makeSafe("\t\n\r"))->toBe('');
        });

        it('removes multiple consecutive separators', function () {
            expect($this->urlSafeMaker->makeSafe('hello---world', ['separator' => '-']))->toBe('hello-world');
            expect($this->urlSafeMaker->makeSafe('a--b--c', ['separator' => '-']))->toBe('a-b-c');
        });

        it('trims separators from start and end', function () {
            expect($this->urlSafeMaker->makeSafe('-hello-world-', ['separator' => '-']))->toBe('hello-world');
            expect($this->urlSafeMaker->makeSafe('___test___', ['separator' => '_']))->toBe('test');
        });
    });

    describe('Custom Separator Configuration', function () {
        it('uses custom separator when specified', function () {
            expect($this->urlSafeMaker->makeSafe('hello world', ['separator' => '_']))->toBe('hello_world');
            expect($this->urlSafeMaker->makeSafe('test case', ['separator' => '.']))->toBe('test.case');
            expect($this->urlSafeMaker->makeSafe('another example', ['separator' => '']))->toBe('anotherexample');
        });

        it('handles special separators correctly', function () {
            expect($this->urlSafeMaker->makeSafe('hello world', ['separator' => '+']))->toBe('hello+world');
            expect($this->urlSafeMaker->makeSafe('test  case', ['separator' => '|']))->toBe('test|case');
        });
    });

    describe('Length Limitations', function () {
        it('respects max length parameter', function () {
            $text = 'this is a very long text that should be truncated';
            expect($this->urlSafeMaker->makeSafe($text, ['maxLength' => 10]))->toBe('this-is-a'); // Should not end with separator
            expect($this->urlSafeMaker->makeSafe($text, ['maxLength' => 20]))->toBe('this-is-a-very-long');
        });

        it('does not truncate when text is shorter than max length', function () {
            expect($this->urlSafeMaker->makeSafe('short text', ['maxLength' => 50]))->toBe('short-text');
            expect($this->urlSafeMaker->makeSafe('hello', ['maxLength' => 10]))->toBe('hello');
        });

        it('handles edge cases with max length', function () {
            expect($this->urlSafeMaker->makeSafe('', ['maxLength' => 10]))->toBe('');
            expect($this->urlSafeMaker->makeSafe('a', ['maxLength' => 0]))->toBe('');
            expect($this->urlSafeMaker->makeSafe('hello', ['maxLength' => 1]))->toBe('h');
        });

        it('ensures truncated text does not end with separator', function () {
            expect($this->urlSafeMaker->makeSafe('hello-world-test', ['maxLength' => 11]))->toBe('hello-world'); // Exactly 11, no trailing separator
            expect($this->urlSafeMaker->makeSafe('a-b-c-d-e-f', ['maxLength' => 5]))->toBe('a-b-c'); // Should end cleanly
        });
    });

    describe('RFC 3986 Compliance (Enhanced Requirements)', function () {
        it('should remove or encode special characters for URL safety', function () {
            // These tests will initially fail and drive implementation
            expect($this->urlSafeMaker->makeSafe('hello@world.com'))->toBe('hello-world-com');
            expect($this->urlSafeMaker->makeSafe('test#fragment'))->toBe('test-fragment');
            expect($this->urlSafeMaker->makeSafe('query?param=value'))->toBe('query-param-value');
        });

        it('should handle punctuation and special characters', function () {
            expect($this->urlSafeMaker->makeSafe('hello, world!'))->toBe('hello-world');
            expect($this->urlSafeMaker->makeSafe('test: subtitle'))->toBe('test-subtitle');
            expect($this->urlSafeMaker->makeSafe('price $99.99'))->toBe('price-99-99');
            expect($this->urlSafeMaker->makeSafe('100% guaranteed'))->toBe('100-guaranteed');
        });

        it('should handle parentheses and brackets', function () {
            expect($this->urlSafeMaker->makeSafe('article (part 1)'))->toBe('article-part-1');
            expect($this->urlSafeMaker->makeSafe('data [important]'))->toBe('data-important');
            expect($this->urlSafeMaker->makeSafe('code {snippet}'))->toBe('code-snippet');
        });

        it('should preserve numbers and basic alphanumeric characters', function () {
            expect($this->urlSafeMaker->makeSafe('test123'))->toBe('test123');
            expect($this->urlSafeMaker->makeSafe('version 2.0'))->toBe('version-2-0');
            expect($this->urlSafeMaker->makeSafe('item-001'))->toBe('item-001');
        });
    });

    describe('Thai Text Integration', function () {
        it('works with transliterated Thai text', function () {
            expect($this->urlSafeMaker->makeSafe('sawatdee lok'))->toBe('sawatdee-lok');
            expect($this->urlSafeMaker->makeSafe('suay ngaam maak'))->toBe('suay-ngaam-maak');
        });

        it('handles mixed Thai transliteration patterns', function () {
            expect($this->urlSafeMaker->makeSafe('krung thep mahanakhon'))->toBe('krung-thep-mahanakhon');
            expect($this->urlSafeMaker->makeSafe('phasa thai'))->toBe('phasa-thai');
        });
    });

    describe('Performance Requirements', function () {
        it('processes short text quickly', function () {
            expect(fn () => $this->urlSafeMaker->makeSafe('hello world'))
                ->toExecuteWithin(1); // 1ms
        });

        it('processes medium text efficiently', function () {
            $mediumText = str_repeat('hello world test case ', 50);

            expect(fn () => $this->urlSafeMaker->makeSafe($mediumText))
                ->toExecuteWithin(10) // 10ms
                ->and(fn () => $this->urlSafeMaker->makeSafe($mediumText))
                ->toUseMemoryLessThan(1 * 1024 * 1024); // 1MB
        });

        it('processes large text within limits', function () {
            $largeText = str_repeat('Lorem ipsum dolor sit amet consectetur adipiscing elit ', 200);

            expect(fn () => $this->urlSafeMaker->makeSafe($largeText))
                ->toExecuteWithin(50) // 50ms
                ->and(fn () => $this->urlSafeMaker->makeSafe($largeText))
                ->toUseMemoryLessThan(2 * 1024 * 1024); // 2MB
        });
    });

    describe('Edge Cases and Error Handling', function () {
        it('handles very long strings gracefully', function () {
            $veryLongText = str_repeat('a', 10000);
            $result = $this->urlSafeMaker->makeSafe($veryLongText, ['maxLength' => 100]);

            expect($result)->toBeString();
            expect(strlen($result))->toBeLessThanOrEqual(100);
        });

        it('handles unicode and special encoding', function () {
            expect($this->urlSafeMaker->makeSafe('café résumé'))->toBe('cafe-resume');
            expect($this->urlSafeMaker->makeSafe('naïve coöperation'))->toBe('naive-cooperation');
        });

        it('handles empty separator in sanitization', function () {
            // This tests the else branch in sanitizeForUrl when separator is empty
            $text = 'hello@world#test$special%chars';
            $result = $this->urlSafeMaker->makeSafe($text, ['separator' => '']);

            expect($result)->toBeString();
            expect($result)->toBe('helloworldtestspecialchars');
        });

        it('handles iconv transliteration edge cases', function () {
            // Test Unicode characters that would trigger iconv fallback
            $text = 'ăąćčďđěęğįłńňőœřśšťűůźž';
            $result = $this->urlSafeMaker->makeSafe($text);

            expect($result)->toBeString();
            expect($result)->toMatch('/^[a-z\-]+$/'); // Should be ASCII safe
        });

        it('handles numbers and mixed content', function () {
            expect($this->urlSafeMaker->makeSafe('article 123: introduction'))->toBe('article-123-introduction');
            expect($this->urlSafeMaker->makeSafe('2024 new year celebration'))->toBe('2024-new-year-celebration');
        });
    });

    describe('Consistency Requirements', function () {
        it('produces consistent output for same input', function () {
            $text = 'hello world test case';

            $result1 = $this->urlSafeMaker->makeSafe($text);
            $result2 = $this->urlSafeMaker->makeSafe($text);
            $result3 = $this->urlSafeMaker->makeSafe($text);

            expect($result1)->toBe($result2);
            expect($result2)->toBe($result3);
        });

        it('maintains consistency with different configurations', function () {
            $text = 'test case with options';
            $options = ['separator' => '_', 'maxLength' => 20];

            $result1 = $this->urlSafeMaker->makeSafe($text, $options);
            $result2 = $this->urlSafeMaker->makeSafe($text, $options);

            expect($result1)->toBe($result2);
        });
    });

    describe('Word Boundary Intelligence (Advanced Feature)', function () {
        it('should truncate at word boundaries when possible', function () {
            // This will drive implementation of intelligent truncation
            $text = 'this is a very long sentence that needs intelligent truncation';
            $result = $this->urlSafeMaker->makeSafe($text, ['maxLength' => 25]);

            // Should prefer breaking at word boundaries
            expect($result)->not()->toEndWith('-a'); // Don't break mid-word
            expect(strlen($result))->toBeLessThanOrEqual(25);
        });

        it('should fall back to character truncation if no good word boundary', function () {
            $text = 'superlongwordwithoutanyspacesorbreaks';
            $result = $this->urlSafeMaker->makeSafe($text, ['maxLength' => 20]);

            expect(strlen($result))->toBe(20);
            expect($result)->toBe('superlongwordwithout');
        });

        it('handles word boundary truncation when separator is far enough from end', function () {
            // This tests the condition: $lastSeparator >= ($maxLength * 0.4)
            $text = 'this-is-a-very-long-text-that-should-truncate-at-word-boundary';
            $result = $this->urlSafeMaker->makeSafe($text, ['maxLength' => 25, 'separator' => '-']);

            expect(strlen($result))->toBeLessThanOrEqual(25);
            expect($result)->not()->toEndWith('-');
        });

        it('handles truncation with separator cleanup at end', function () {
            // This specifically tests the rtrim separator logic in intelligentTruncate
            $text = 'word-ending-with-separator-extra-text-here';
            $result = $this->urlSafeMaker->makeSafe($text, ['maxLength' => 22, 'separator' => '-']);

            expect($result)->not()->toEndWith('-');
            expect(strlen($result))->toBeLessThanOrEqual(22);
        });

        it('handles edge case where word boundary is too early', function () {
            // Test when lastSeparator < (maxLength * 0.4), should fall back to character truncation
            $text = 'a-very-very-very-very-very-long-text-without-separators-near-end';
            $result = $this->urlSafeMaker->makeSafe($text, ['maxLength' => 30, 'separator' => '-']);

            expect(strlen($result))->toBeLessThanOrEqual(30);
            expect($result)->not()->toEndWith('-');
        });
    });
});
