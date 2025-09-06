<?php

declare(strict_types=1);

use Farzai\ThaiSlug\Enums\Strategy;
use Farzai\ThaiSlug\SlugBuilder;
use Farzai\ThaiSlug\ThaiSlug;

describe('Performance Benchmarks', function () {
    beforeEach(function () {
        $this->slugBuilder = new SlugBuilder;
    });

    it('processes small text (< 100 chars) within 1ms', function () {
        $smallTexts = [
            'สวัสดี',
            'โลกสวยงาม',
            'การทำงาน',
            'ประเทศไทย',
            'เทคโนโลยี',
            'น้ำใส ใจเซี้ยว',
            'กิน อยู่ ดู ฟัง',
            'สบายใจ มีความสุข',
        ];

        foreach ($smallTexts as $text) {
            $start = hrtime(true);
            $result = ThaiSlug::make($text);
            $duration = (hrtime(true) - $start) / 1e6; // Convert to milliseconds

            expect($result)->not()->toBeEmpty()
                ->and($duration)->toBeLessThan(1.0, "Processing '{$text}' took {$duration}ms, expected < 1ms");
        }
    });

    it('processes medium text (100-1000 chars) within 10ms', function () {
        $mediumTexts = [
            str_repeat('สวัสดีโลกสวยงาม ', 10), // ~200 chars
            'ประเทศไทยมีวัฒนธรรมที่หลากหลาย มีความเป็นมาอันยาวนาน และมีประวัติศาสตร์ที่น่าสนใจ รวมถึงศิลปะการแสดง อาหาร และประเพณีต่างๆ ที่สวยงาม', // ~500 chars
            str_repeat('เทคโนโลยีสารสนเทศในยุคปัจจุบัน ', 20), // ~800 chars
        ];

        foreach ($mediumTexts as $text) {
            $start = hrtime(true);
            $result = ThaiSlug::make($text);
            $duration = (hrtime(true) - $start) / 1e6; // Convert to milliseconds

            $charCount = mb_strlen($text);
            expect($result)->not()->toBeEmpty()
                ->and($duration)->toBeLessThan(10.0, "Processing {$charCount} chars took {$duration}ms, expected < 10ms");
        }
    });

    it('processes large text (1000+ chars) within 50ms (OPTIMIZED)', function () {
        $largeTexts = [
            str_repeat('ประเทศไทยมีวัฒนธรรมที่หลากหลาย มีความเป็นมาอันยาวนาน ', 50), // ~5000 chars - much larger
            str_repeat('เทคโนโลยีสารสนเทศในยุคปัจจุบัน พัฒนาการทางด้านคอมพิวเตอร์ ', 50), // ~5000 chars - much larger
            str_repeat('การศึกษาและการเรียนรู้ในยุคดิจิทัล สื่อการสอนแบบใหม่ ', 100), // ~10000 chars - very large
        ];

        foreach ($largeTexts as $text) {
            $start = hrtime(true);
            $result = ThaiSlug::make($text);
            $duration = (hrtime(true) - $start) / 1e6; // Convert to milliseconds

            $charCount = mb_strlen($text);
            expect($result)->not()->toBeEmpty()
                ->and($duration)->toBeLessThan(50.0, "Processing {$charCount} chars took {$duration}ms, expected < 50ms (O(n) optimization)");
        }
    });

    it('uses reasonable memory for large inputs (FIXED - O(n) optimization)', function () {
        $memoryBefore = memory_get_usage(true);

        // Test with much larger input now that O(n²) issue is fixed
        $baseText = 'ประเทศไทยมีวัฒนธรรมที่หลากหลาย มีความเป็นมาอันยาวนาน และมีประวัติศาสตร์ที่น่าสนใจ ';
        $largeText = str_repeat($baseText, 5000); // ~500KB - much larger test

        $start = hrtime(true);
        $result = ThaiSlug::make($largeText);
        $duration = (hrtime(true) - $start) / 1e6; // Convert to milliseconds

        $memoryAfter = memory_get_usage(true);
        $memoryUsed = $memoryAfter - $memoryBefore;

        expect($result)->not()->toBeEmpty()
            ->and($memoryUsed)->toBeLessThan(100 * 1024 * 1024, 'Memory usage: '.number_format($memoryUsed / 1024 / 1024, 2).'MB for 500KB input')
            ->and($duration)->toBeLessThan(2000.0, "Processing 500KB took {$duration}ms, expected < 2000ms (2 seconds)");
    });

    it('validates cache performance improvement', function () {
        $text = 'สวัสดีโลกสวยงาม การทำงานในยุคใหม่';

        // Test without cache (first call)
        $start1 = hrtime(true);
        $result1 = SlugBuilder::make($text);
        $duration1 = (hrtime(true) - $start1) / 1e6;

        // Test with built-in cache (second call - should be cached)
        $start2 = hrtime(true);
        $result2 = SlugBuilder::make($text);
        $duration2 = (hrtime(true) - $start2) / 1e6;

        // Test with built-in cache (third call - should be even faster)
        $start3 = hrtime(true);
        $result3 = SlugBuilder::make($text);
        $duration3 = (hrtime(true) - $start3) / 1e6;

        expect($result1)->toEqual($result2)->toEqual($result3)
            ->and($duration2)->toBeLessThan($duration1, "Cached call ({$duration2}ms) should be faster than uncached ({$duration1}ms)");
    });

    it('benchmarks different transliteration strategies', function () {
        $text = 'สวัสดีโลกสวยงาม ประเทศไทยมีวัฒนธรรมที่หลากหลาย';
        $strategies = [
            'phonetic' => Strategy::PHONETIC,
            'royal' => Strategy::ROYAL,
        ];
        $results = [];

        foreach ($strategies as $strategyName => $strategyEnum) {
            $start = hrtime(true);
            // Use SlugBuilder with strategy
            $result = $this->slugBuilder->text($text)->strategy($strategyEnum)->build();
            $duration = (hrtime(true) - $start) / 1e6;

            $results[$strategyName] = [
                'result' => $result,
                'duration' => $duration,
            ];

            expect($result)->not()->toBeEmpty()
                ->and($duration)->toBeLessThan(50.0, "Strategy '{$strategyName}' took {$duration}ms, expected < 50ms");
        }

        // All strategies should produce valid results
        foreach ($results as $strategyName => $data) {
            expect($data['result'])->toMatch('/^[a-z0-9\-\s]+$/', "Strategy '{$strategyName}' should produce URL-safe output");
        }
    });

    it('measures built-in cache performance', function () {
        // Generate multiple slugs to populate built-in cache
        $texts = [
            'สวัสดี',
            'โลกสวย',
            'การทำงาน',
            'ประเทศไทย',
            'เทคโนโลยี',
        ];

        // First pass - populate built-in cache
        foreach ($texts as $text) {
            SlugBuilder::make($text);
        }

        // Second pass - should hit cache
        $start = hrtime(true);
        foreach ($texts as $text) {
            $result = SlugBuilder::make($text);
            expect($result)->not()->toBeEmpty();
        }
        $totalTime = (hrtime(true) - $start) / 1e6;

        expect($totalTime)->toBeLessThan(5.0, "Cached operations took {$totalTime}ms, expected < 5ms");
    });

    it('validates O(n) linear algorithm complexity (OPTIMIZED)', function () {
        // Test with much larger increasing text sizes to verify O(n) complexity
        $baseSizes = [1000, 5000, 10000, 20000]; // Much larger test sizes
        $times = [];

        foreach ($baseSizes as $size) {
            $text = str_repeat('สวัสดีโลกสวยงาม ประเทศไทยมีวัฒนธรรม ', $size / 50);

            $start = hrtime(true);
            $result = ThaiSlug::make($text);
            $duration = (hrtime(true) - $start) / 1e6;

            $actualSize = mb_strlen($text);
            $times[] = ['size' => $actualSize, 'duration' => $duration];

            expect($result)->not()->toBeEmpty()
                ->and($duration)->toBeLessThan($actualSize * 0.05, "Processing {$actualSize} chars took {$duration}ms, should be roughly linear (< 0.05ms per char)");
        }

        // Check that performance is truly linear O(n) - much stricter test
        for ($i = 1; $i < count($times); $i++) {
            $prevTime = $times[$i - 1];
            $currentTime = $times[$i];

            $sizeRatio = $currentTime['size'] / $prevTime['size'];
            $timeRatio = $currentTime['duration'] / max($prevTime['duration'], 0.001);

            // O(n) optimization: time increase should be close to size increase (within 1.5x for overhead)
            expect($timeRatio)->toBeLessThan($sizeRatio * 1.5,
                "O(n) complexity verified: size x{$sizeRatio}, time x{$timeRatio} (should be nearly linear)");
        }
    });
});
