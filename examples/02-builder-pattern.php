<?php

declare(strict_types=1);

/**
 * Builder Pattern Examples for Thai Slug Library
 *
 * This example demonstrates the fluent builder API for advanced configuration
 * and customization of slug generation.
 */

require_once __DIR__.'/../vendor/autoload.php';

use Farzai\ThaiSlug\ThaiSlug;

echo "=== Builder Pattern Examples ===\n\n";

$thaiSlug = new ThaiSlug;
$sampleText = 'การพัฒนาเว็บแอปพลิเคชันด้วยภาษาไทยและเทคโนโลยีสมัยใหม่';

// Example 1: Basic builder usage
echo "1. Basic Builder Usage\n";
echo '-'.str_repeat('-', 40)."\n";

$slug = $thaiSlug->builder()
    ->text($sampleText)
    ->build();

printf("Original: %s\n", $sampleText);
printf("Default:  %s\n\n", $slug);

// Example 2: Custom separator
echo "2. Custom Separators\n";
echo '-'.str_repeat('-', 40)."\n";

$separators = ['-', '_', '.', '~'];

foreach ($separators as $separator) {
    $slug = $thaiSlug->builder()
        ->text($sampleText)
        ->separator($separator)
        ->build();

    printf("Separator '%s': %s\n", $separator, $slug);
}

echo "\n";

// Example 3: Maximum length limits
echo "3. Maximum Length Limits\n";
echo '-'.str_repeat('-', 40)."\n";

$lengths = [20, 50, 100, null];

foreach ($lengths as $length) {
    $builder = $thaiSlug->builder()->text($sampleText);

    if ($length !== null) {
        $builder->maxLength($length);
    }

    $slug = $builder->build();

    $lengthDisplay = $length === null ? 'unlimited' : $length;
    printf("Max length %s: %s (actual: %d chars)\n", $lengthDisplay, $slug, strlen($slug));
}

echo "\n";

// Example 4: Method chaining combinations
echo "4. Method Chaining Combinations\n";
echo '-'.str_repeat('-', 40)."\n";

$configurations = [
    [
        'name' => 'Short with underscores',
        'config' => fn ($builder) => $builder->separator('_')->maxLength(30),
    ],
    [
        'name' => 'Dots with medium length',
        'config' => fn ($builder) => $builder->separator('.')->maxLength(50),
    ],
    [
        'name' => 'Dashes unlimited',
        'config' => fn ($builder) => $builder->separator('-'),
    ],
];

foreach ($configurations as $config) {
    $builder = $thaiSlug->builder()->text($sampleText);
    $slug = $config['config']($builder)->build();

    printf("%s:\n  %s\n\n", $config['name'], $slug);
}

// Example 5: Performance Testing
echo "5. Performance Testing\n";
echo '-'.str_repeat('-', 40)."\n";

// Measure time for repeated operations
$start = microtime(true);
for ($i = 0; $i < 100; $i++) {
    $slug = $thaiSlug->builder()
        ->text($sampleText)
        ->build();
}
$processingTime = microtime(true) - $start;

printf("100 iterations: %.4f seconds\n", $processingTime);
printf("Average per iteration: %.4f seconds\n", $processingTime / 100);
printf("Performance: %d slugs per second\n\n", (int) (100 / $processingTime));

// Example 6: Real-world content examples
echo "6. Real-world Content Examples\n";
echo '-'.str_repeat('-', 40)."\n";

$contentExamples = [
    [
        'title' => 'Blog Post Slug',
        'text' => 'วิธีการเพิ่มประสิทธิภาพการทำงานของเว็บไซต์ด้วย PHP และ MySQL',
        'config' => fn ($b) => $b->maxLength(60)->separator('-'),
    ],
    [
        'title' => 'Product Slug',
        'text' => 'รองเท้าผ้าใบสีขาว Nike Air Max สำหรับผู้ชาย ไซส์ 42',
        'config' => fn ($b) => $b->maxLength(50)->separator('_'),
    ],
    [
        'title' => 'Category Slug',
        'text' => 'เครื่องใช้ไฟฟ้าและอิเล็กทรอนิกส์',
        'config' => fn ($b) => $b->separator('-'),
    ],
    [
        'title' => 'User Profile Slug',
        'text' => 'นาย สมชาย ใจดี - นักพัฒนาเว็บไซต์',
        'config' => fn ($b) => $b->maxLength(40)->separator('.'),
    ],
];

foreach ($contentExamples as $example) {
    $builder = $thaiSlug->builder()->text($example['text']);
    $slug = $example['config']($builder)->build();

    printf("%s:\n", $example['title']);
    printf("  Original: %s\n", $example['text']);
    printf("  Slug:     %s\n", $slug);
    printf("  URL:      /%s\n\n", $slug);
}

// Example 7: Builder with different text lengths
echo "7. Builder with Different Text Lengths\n";
echo '-'.str_repeat('-', 40)."\n";

$textLengths = [
    'Short' => 'เทค',
    'Medium' => 'เทคโนโลยีสารสนเทศ',
    'Long' => 'การพัฒนาเว็บแอปพลิเคชันด้วยภาษาไทยและเทคโนโลยีสมัยใหม่สำหรับธุรกิจออนไลน์',
    'Very Long' => 'การพัฒนาและการจัดการเว็บแอปพลิเคชันเพื่อธุรกิจอีคอมเมิร์ซด้วยเทคโนโลยีภาษาไทยและการประมวลผลภาษาธรรมชาติที่ทันสมัยและมีประสิทธิภาพสูง',
];

foreach ($textLengths as $label => $text) {
    $slug = $thaiSlug->builder()
        ->text($text)
        ->maxLength(50)
        ->separator('-')
        ->build();

    printf("%s text (%d chars):\n", $label, strlen($text));
    printf("  Input:  %s%s\n",
        strlen($text) > 60 ? substr($text, 0, 60).'...' : $text,
        strlen($text) > 60 ? sprintf(' (%d chars total)', strlen($text)) : ''
    );
    printf("  Output: %s (%d chars)\n\n", $slug, strlen($slug));
}

// Example 8: Builder pattern with method variations
echo "8. Builder Method Variations\n";
echo '-'.str_repeat('-', 40)."\n";

$text = 'ระบบการจัดการเนื้อหาเว็บไซต์';

// Different ways to configure the same result
$methods = [
    'Method 1 - All at once' => function () use ($thaiSlug, $text) {
        return $thaiSlug->builder()
            ->text($text)
            ->separator('_')
            ->maxLength(40)
            ->build();
    },
    'Method 2 - Step by step' => function () use ($thaiSlug, $text) {
        $builder = $thaiSlug->builder();
        $builder->text($text);
        $builder->separator('_');
        $builder->maxLength(40);

        return $builder->build();
    },
    'Method 3 - Conditional' => function () use ($thaiSlug, $text) {
        $builder = $thaiSlug->builder()->text($text);

        if (strlen($text) > 20) {
            $builder->maxLength(40);
        }

        if (strpos($text, ' ') !== false) {
            $builder->separator('_');
        }

        return $builder->build();
    },
];

foreach ($methods as $methodName => $method) {
    $slug = $method();
    printf("%s:\n  Result: %s\n\n", $methodName, $slug);
}

echo "=== Builder Pattern Examples Complete ===\n";
