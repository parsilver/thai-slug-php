<?php

declare(strict_types=1);

use Farzai\ThaiSlug\SlugBuilder;
use Farzai\ThaiSlug\ThaiSlug;

describe('Memory Leak Detection', function () {
    it('does not leak memory during repeated operations', function () {
        $thaiSlug = new ThaiSlug;
        $text = 'สวัสดีโลกสวยงาม';

        // Get baseline memory usage
        gc_collect_cycles();
        $memoryBefore = memory_get_usage(true);

        // Perform many operations
        for ($i = 0; $i < 1000; $i++) {
            $result = ThaiSlug::make($text);
            expect($result)->not()->toBeEmpty();

            // Force garbage collection every 100 iterations
            if ($i % 100 === 0) {
                gc_collect_cycles();
            }
        }

        // Final garbage collection
        gc_collect_cycles();
        $memoryAfter = memory_get_usage(true);

        $memoryIncrease = $memoryAfter - $memoryBefore;

        // Memory increase should be minimal (< 1MB for 1000 operations)
        expect($memoryIncrease)->toBeLessThan(1024 * 1024,
            'Memory increased by '.number_format($memoryIncrease / 1024, 2).' KB after 1000 operations');
    });

    it('properly manages built-in cache memory', function () {
        // Get baseline
        gc_collect_cycles();
        $memoryBefore = memory_get_usage(true);

        // Generate many different slugs to test built-in cache management
        for ($i = 0; $i < 150; $i++) {
            $text = "สวัสดี{$i}โลกสวย";
            $result = SlugBuilder::make($text);
            expect($result)->not()->toBeEmpty();
        }

        gc_collect_cycles();
        $memoryAfter = memory_get_usage(true);

        $memoryIncrease = $memoryAfter - $memoryBefore;

        // Built-in cache should limit memory growth (< 2MB for 150 different texts)
        expect($memoryIncrease)->toBeLessThan(2 * 1024 * 1024, // 2MB tolerance
            'Memory increased by '.number_format($memoryIncrease / 1024, 2).' KB after cache operations');
    });

    it('handles large text without excessive memory allocation', function () {
        // Create progressively larger texts
        $baseText = 'ประเทศไทยมีวัฒนธรรมที่หลากหลาย ';

        gc_collect_cycles();
        $memoryBefore = memory_get_usage();

        for ($multiplier = 1; $multiplier <= 10; $multiplier++) {
            $largeText = str_repeat($baseText, $multiplier * 100);
            $result = ThaiSlug::make($largeText);

            expect($result)->not()->toBeEmpty();

            // Check memory hasn't grown excessively
            $currentMemory = memory_get_usage();
            $memoryIncrease = $currentMemory - $memoryBefore;

            // Memory should not exceed 5MB for any single operation
            expect($memoryIncrease)->toBeLessThan(5 * 1024 * 1024,
                "Iteration {$multiplier}: Memory usage {$memoryIncrease} bytes exceeds 5MB limit");

            // Force cleanup
            unset($largeText, $result);
            gc_collect_cycles();
        }
    });
});
