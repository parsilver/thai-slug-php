<?php

declare(strict_types=1);

/**
 * Transliteration Strategies Examples for Thai Slug Library
 *
 * This example demonstrates different transliteration strategies available
 * and when to use each one.
 */

require_once __DIR__.'/../vendor/autoload.php';

use Farzai\ThaiSlug\Enums\Strategy;
use Farzai\ThaiSlug\ThaiSlug;

echo "=== Transliteration Strategies Examples ===\n\n";

$thaiSlug = new ThaiSlug;

// Sample texts for comparison
$sampleTexts = [
    'กรุงเทพมหานคร',
    'สถาบันเทคโนโลยี',
    'โปรแกรมเมอร์',
    'ภาษาไทยสำหรับการพัฒนา',
    'เชียงใหม่',
    'ประยุทธ์',
    'วิทยาศาสตร์คอมพิวเตอร์',
];

// Example 1: Strategy comparison
echo "1. Strategy Comparison\n";
echo '-'.str_repeat('-', 80)."\n";
printf("%-30s %-25s %-25s\n", 'Thai Text', 'Phonetic (Default)', 'Royal (RTGS)');
echo str_repeat('-', 80)."\n";

foreach ($sampleTexts as $text) {
    $phoneticSlug = $thaiSlug->builder()
        ->text($text)
        ->strategy(Strategy::PHONETIC)
        ->build();

    $royalSlug = $thaiSlug->builder()
        ->text($text)
        ->strategy(Strategy::ROYAL)
        ->build();

    printf("%-30s %-25s %-25s\n", $text, $phoneticSlug, $royalSlug);
}

echo "\n";

// Example 2: Phonetic Strategy Details
echo "2. Phonetic Strategy (Default) - Natural Pronunciation\n";
echo '-'.str_repeat('-', 60)."\n";

$phoneticExamples = [
    'ข้าว' => 'rice',
    'น้ำ' => 'water',
    'ไก่' => 'chicken',
    'หมา' => 'dog',
    'แมว' => 'cat',
    'ปลา' => 'fish',
    'ดอกไม้' => 'flower',
    'ต้นไม้' => 'tree',
];

echo "Common words with phonetic transliteration:\n\n";
foreach ($phoneticExamples as $thai => $meaning) {
    $slug = $thaiSlug->builder()
        ->text($thai)
        ->strategy(Strategy::PHONETIC)
        ->build();

    printf("%-15s → %-15s (meaning: %s)\n", $thai, $slug, $meaning);
}

echo "\n";

// Example 3: Royal Thai Strategy Details
echo "3. Royal Thai Strategy (RTGS) - Official Standard\n";
echo '-'.str_repeat('-', 60)."\n";

$royalExamples = [
    'กรุงเทพมหานคร' => 'Capital city',
    'สถาบันเทคโนโลยี' => 'Technology institute',
    'มหาวิทยาลัย' => 'University',
    'กระทรวงศึกษาธิการ' => 'Ministry of Education',
    'ราชภัฏ' => 'Rajabhat',
    'พระราชวัง' => 'Royal palace',
];

echo "Official/academic terms with Royal Thai transliteration:\n\n";
foreach ($royalExamples as $thai => $meaning) {
    $slug = $thaiSlug->builder()
        ->text($thai)
        ->strategy(Strategy::ROYAL)
        ->build();

    printf("%-20s → %-25s (%s)\n", $thai, $slug, $meaning);
}

echo "\n";

// Example 4: Strategy Comparison - Phonetic vs Royal
echo "4. Strategy Comparison - Phonetic vs Royal\n";
echo '-'.str_repeat('-', 60)."\n";

$techTexts = [
    'ไอทีและบิ๊กดาต้า',
    'คลาวด์เซิร์ฟเวอร์',
    'เทคโนโลยีดิจิทัล',
    'เว็บไซต์และแอปโมบาย',
    'ซอฟต์แวร์ออนไลน์',
];

echo "IT/Tech terms with different strategies:\n\n";
foreach ($techTexts as $text) {
    $phoneticSlug = $thaiSlug->builder()
        ->text($text)
        ->strategy(Strategy::PHONETIC)
        ->build();

    $royalSlug = $thaiSlug->builder()
        ->text($text)
        ->strategy(Strategy::ROYAL)
        ->build();

    printf("Text:     %s\n", $text);
    printf("Phonetic: %s\n", $phoneticSlug);
    printf("Royal:    %s\n\n", $royalSlug);
}

// Example 5: Strategy selection based on use case
echo "5. Strategy Selection Guidelines\n";
echo '-'.str_repeat('-', 60)."\n";

$useCases = [
    [
        'title' => 'Blog Articles',
        'text' => 'วิธีเรียน PHP เบื้องต้น',
        'recommended' => Strategy::PHONETIC,
        'reason' => 'Natural, readable URLs for general audience',
    ],
    [
        'title' => 'Academic Papers',
        'text' => 'วิทยาศาสตร์คอมพิวเตอร์',
        'recommended' => Strategy::ROYAL,
        'reason' => 'Official standard for academic contexts',
    ],
    [
        'title' => 'Tech Products',
        'text' => 'แอปโมบายไอที',
        'recommended' => Strategy::PHONETIC,
        'reason' => 'Natural pronunciation for tech terms',
    ],
    [
        'title' => 'Government Sites',
        'text' => 'กระทรวงดิจิทัลเพื่อเศรษฐกิจและสังคม',
        'recommended' => Strategy::ROYAL,
        'reason' => 'Official government standard',
    ],
    [
        'title' => 'E-commerce',
        'text' => 'รองเท้าผ้าใบแบรนด์ดัง',
        'recommended' => Strategy::PHONETIC,
        'reason' => 'SEO-friendly for consumer searches',
    ],
];

foreach ($useCases as $case) {
    echo "Use Case: {$case['title']}\n";
    echo "Text: {$case['text']}\n";
    echo "Recommended: {$case['recommended']->value}\n";
    echo "Reason: {$case['reason']}\n";

    $slug = $thaiSlug->builder()
        ->text($case['text'])
        ->strategy($case['recommended'])
        ->build();

    echo "Result: $slug\n\n";
}

// Example 6: Performance comparison
echo "6. Performance Comparison\n";
echo '-'.str_repeat('-', 60)."\n";

$testText = 'การพัฒนาเว็บแอปพลิเคชันด้วยภาษาไทยและเทคโนโลยีสมัยใหม่';
$iterations = 1000;

$strategies = [Strategy::PHONETIC, Strategy::ROYAL];
$results = [];

foreach ($strategies as $strategy) {
    $start = microtime(true);

    for ($i = 0; $i < $iterations; $i++) {
        $slug = $thaiSlug->builder()
            ->text($testText)
            ->strategy($strategy)
            ->build();
    }

    $time = microtime(true) - $start;
    $results[$strategy->value] = $time;

    printf("%s strategy: %.4f seconds (%d iterations)\n",
        ucfirst($strategy->value), $time, $iterations);
}

// Baseline comparison with default strategy
$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $slug = ThaiSlug::make($testText);
}
$defaultTime = microtime(true) - $start;
$results['default'] = $defaultTime;

printf("Default method: %.4f seconds (%d iterations)\n", $defaultTime, $iterations);

echo "\nPerformance ranking (fastest to slowest):\n";
asort($results);
$rank = 1;
foreach ($results as $strategy => $time) {
    printf("%d. %s (%.4f sec)\n", $rank++, ucfirst($strategy), $time);
}

echo "\n";

// Example 7: Strategy mixing and advanced usage
echo "7. Advanced Strategy Usage\n";
echo '-'.str_repeat('-', 60)."\n";

$advancedTexts = [
    'Mr. สมชาย ใจดี - CEO',
    'Dr. วิภาวี PhD in AI',
    'บริษัท ABC (Thailand) Ltd.',
    'iPhone 15 Pro Max 256GB',
];

echo "Mixed Thai-English content with different strategies:\n\n";

foreach ($advancedTexts as $text) {
    foreach ([Strategy::PHONETIC, Strategy::ROYAL] as $strategy) {
        $slug = $thaiSlug->builder()
            ->text($text)
            ->strategy($strategy)
            ->maxLength(50)
            ->build();

        printf("%-30s [%s]: %s\n", $text, $strategy->value, $slug);
    }
    echo "\n";
}

echo "=== Transliteration Strategies Examples Complete ===\n";
