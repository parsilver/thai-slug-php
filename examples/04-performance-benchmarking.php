<?php

declare(strict_types=1);

/**
 * Performance Benchmarking Examples for Thai Slug Library
 *
 * This example demonstrates performance characteristics and optimization
 * techniques for Thai slug generation in different scenarios.
 */

require_once __DIR__.'/../vendor/autoload.php';

use Farzai\ThaiSlug\Enums\Strategy;
use Farzai\ThaiSlug\ThaiSlug;

echo "=== Performance Benchmarking Examples ===\n\n";

// Test data
$shortTexts = [
    'เทค',
    'ไอที',
    'โค้ด',
    'เว็บ',
    'แอป',
];

$mediumTexts = [
    'การพัฒนาเว็บไซต์',
    'เทคโนโลยีสารสนเทศ',
    'ระบบฐานข้อมูล',
    'การเขียนโปรแกรม',
    'ความปลอดภัยออนไลน์',
];

$longTexts = [
    'การพัฒนาเว็บแอปพลิเคชันด้วยภาษาไทยและเทคโนโลยีสมัยใหม่',
    'ระบบการจัดการเนื้อหาสำหรับเว็บไซต์องค์กรขนาดใหญ่',
    'การใช้เทคโนโลยีปัญญาประดิษฐ์ในการประมวลผลภาษาธรรมชาติ',
    'การพัฒนาแอปพลิเคชันมือถือสำหรับธุรกิจอีคอมเมิร์ซ',
    'การรักษาความปลอดภัยของข้อมูลในระบบคลาวด์คอมพิวติ้ง',
];

$thaiSlug = new ThaiSlug;

// Example 1: Text length impact on performance
echo "1. Performance by Text Length\n";
echo '-'.str_repeat('-', 60)."\n";

$testSets = [
    'Short' => $shortTexts,
    'Medium' => $mediumTexts,
    'Long' => $longTexts,
];

foreach ($testSets as $setName => $texts) {
    $iterations = 100;
    $start = microtime(true);
    $memoryStart = memory_get_usage(true);

    for ($i = 0; $i < $iterations; $i++) {
        foreach ($texts as $text) {
            $slug = $thaiSlug->generate($text);
        }
    }

    $time = microtime(true) - $start;
    $memoryUsed = memory_get_usage(true) - $memoryStart;
    $totalOperations = $iterations * count($texts);

    printf("%-8s texts: %.4f sec, %.2f KB memory, %d ops/sec\n",
        $setName,
        $time,
        $memoryUsed / 1024,
        (int) ($totalOperations / $time)
    );
}

echo "\n";

// Example 2: Strategy performance comparison
echo "2. Strategy Performance Comparison\n";
echo '-'.str_repeat('-', 60)."\n";

$testText = 'การพัฒนาเว็บแอปพลิเคชันด้วย PHP และ MySQL';
$strategies = [Strategy::PHONETIC, Strategy::ROYAL];
$iterations = 1000;

foreach ($strategies as $strategy) {
    $start = microtime(true);
    $memoryStart = memory_get_usage(true);

    for ($i = 0; $i < $iterations; $i++) {
        $slug = $thaiSlug->builder()
            ->text($testText)
            ->strategy($strategy)
            ->build();
    }

    $time = microtime(true) - $start;
    $memoryUsed = memory_get_usage(true) - $memoryStart;

    printf("%-10s strategy: %.4f sec, %.2f KB, %d ops/sec\n",
        ucfirst($strategy->value),
        $time,
        $memoryUsed / 1024,
        (int) ($iterations / $time)
    );
}

echo "\n";

// Example 3: Builder vs static method performance
echo "3. Builder vs Static Method Performance\n";
echo '-'.str_repeat('-', 60)."\n";

$testText = 'การเขียนโปรแกรมด้วยภาษาไทย';
$iterations = 1000;

// Static method performance
$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $slug = ThaiSlug::make($testText);
}
$staticTime = microtime(true) - $start;

// Builder method performance
$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $slug = $thaiSlug->builder()
        ->text($testText)
        ->build();
}
$builderTime = microtime(true) - $start;

printf("Static method:  %.4f sec (%d ops/sec)\n",
    $staticTime, (int) ($iterations / $staticTime));
printf("Builder method: %.4f sec (%d ops/sec)\n",
    $builderTime, (int) ($iterations / $builderTime));
printf("Performance difference: %.1fx\n\n", $staticTime / $builderTime);

// Example 4: Configuration impact on performance
echo "4. Configuration Impact on Performance\n";
echo '-'.str_repeat('-', 60)."\n";

$testText = 'การพัฒนาซอฟต์แวร์เว็บแอปพลิเคชันด้วยเทคโนโลยีโอเพนซอร์ส';
$iterations = 500;

$configs = [
    'Basic' => fn ($b) => $b,
    'Max length' => fn ($b) => $b->maxLength(50),
    'Custom separator' => fn ($b) => $b->separator('_'),
    'All options' => fn ($b) => $b->maxLength(50)->separator('_')->lowercase(true),
];

foreach ($configs as $configName => $configFn) {
    $start = microtime(true);

    for ($i = 0; $i < $iterations; $i++) {
        $builder = $thaiSlug->builder()->text($testText);
        $slug = $configFn($builder)->build();
    }

    $time = microtime(true) - $start;

    printf("%-20s: %.4f sec (%d ops/sec)\n",
        $configName,
        $time,
        (int) ($iterations / $time)
    );
}

echo "\n";

// Example 5: Memory usage analysis
echo "5. Memory Usage Analysis\n";
echo '-'.str_repeat('-', 60)."\n";

$memoryTests = [
    '10 operations' => 10,
    '100 operations' => 100,
    '1000 operations' => 1000,
];

foreach ($memoryTests as $testName => $count) {
    $memoryBefore = memory_get_usage(true);
    $peakBefore = memory_get_peak_usage(true);

    for ($i = 0; $i < $count; $i++) {
        $slug = $thaiSlug->generate("ข้อความทดสอบ $i");
    }

    $memoryAfter = memory_get_usage(true);
    $peakAfter = memory_get_peak_usage(true);

    printf("%-15s: %6.2f KB used, %6.2f KB peak\n",
        $testName,
        ($memoryAfter - $memoryBefore) / 1024,
        ($peakAfter - $peakBefore) / 1024
    );
}

echo "\n";

// Example 6: Real-world scenario simulation
echo "6. Real-world Scenario Simulation\n";
echo '-'.str_repeat('-', 60)."\n";

// Simulate blog post processing
$blogPosts = [
    'วิธีการเรียน PHP เบื้องต้นสำหรับผู้เริ่มต้น',
    'เทคนิคการออกแบบฐานข้อมูลที่มีประสิทธิภาพ',
    'การใช้ Laravel Framework ในการพัฒนาเว็บไซต์',
    'ความปลอดภัยของเว็บแอปพลิเคชันและวิธีป้องกัน',
    'การพัฒนา RESTful API ด้วย PHP และ MySQL',
];

$start = microtime(true);
$slugs = [];

foreach ($blogPosts as $title) {
    $slugs[] = $thaiSlug->builder()
        ->text($title)
        ->maxLength(60)
        ->separator('-')
        ->build();
}

$time = microtime(true) - $start;

echo "Blog post processing simulation:\n";
printf("Processed %d titles in %.4f seconds\n", count($blogPosts), $time);
printf("Average: %.4f seconds per title\n", $time / count($blogPosts));

echo "\nGenerated slugs:\n";
foreach ($blogPosts as $i => $title) {
    printf("  %s\n  → %s\n\n", $title, $slugs[$i]);
}

// Example 7: Performance recommendations
echo "7. Performance Recommendations\n";
echo '-'.str_repeat('-', 60)."\n";

echo "Based on benchmarks:\n\n";

echo "• Use ThaiSlug::make() for simple, one-off conversions\n";
echo "• Use builder pattern when you need configuration options\n";
echo "• Phonetic strategy is typically faster than Royal strategy\n";
echo "• Setting maxLength has minimal performance impact\n";
echo "• Custom separators have negligible performance cost\n";
echo "• Memory usage scales linearly with text length\n";
echo "• For high-volume applications, consider:\n";
echo "  - Reusing ThaiSlug instances\n";
echo "  - Batch processing similar texts\n";
echo "  - Using appropriate text length limits\n\n";

// Example 8: System information
echo "8. System Information\n";
echo '-'.str_repeat('-', 60)."\n";

printf("PHP Version: %s\n", PHP_VERSION);
printf("Memory Limit: %s\n", ini_get('memory_limit'));
printf("Max Execution Time: %s seconds\n", ini_get('max_execution_time'));

$extensions = ['mbstring', 'intl', 'iconv'];
echo "\nRequired Extensions:\n";
foreach ($extensions as $ext) {
    printf("• %s: %s\n", $ext, extension_loaded($ext) ? '✓ loaded' : '✗ missing');
}

echo "\n=== Performance Benchmarking Complete ===\n";
