<?php

declare(strict_types=1);

/**
 * Basic Usage Examples for Thai Slug Library
 *
 * This example demonstrates the simplest way to use the Thai Slug library
 * for converting Thai text to URL-friendly slugs.
 */

require_once __DIR__.'/../vendor/autoload.php';

use Farzai\ThaiSlug\ThaiSlug;

echo "=== Basic Usage Examples ===\n\n";

// Example 1: Simple static method usage
echo "1. Simple Static Method Usage\n";
echo '-'.str_repeat('-', 30)."\n";

$thaiTexts = [
    'สวัสดีชาวโลก',
    'ภาษาไทยสำหรับการพัฒนา',
    'เทคโนโลยีสารสนเทศ',
    'การเขียนโปรแกรม PHP',
    'ฐานข้อมูล MySQL',
];

foreach ($thaiTexts as $text) {
    $slug = ThaiSlug::make($text);
    printf("Text: %-40s → Slug: %s\n", $text, $slug);
}

echo "\n";

// Example 2: Common website content
echo "2. Common Website Content\n";
echo '-'.str_repeat('-', 30)."\n";

$websiteContent = [
    'บทความเกี่ยวกับการพัฒนาเว็บไซต์',
    'วิธีการใช้งาน Laravel Framework',
    'การออกแบบฐานข้อมูลที่มีประสิทธิภาพ',
    'เทคนิคการเพิ่มความเร็วเว็บไซต์',
    'การรักษาความปลอดภัยของเว็บแอปพลิเคชัน',
];

foreach ($websiteContent as $content) {
    $slug = ThaiSlug::make($content);
    printf("Title: %-50s\n", $content);
    printf("URL:   /articles/%s\n", $slug);
    echo "\n";
}

// Example 3: Different types of Thai text
echo "3. Different Types of Thai Text\n";
echo '-'.str_repeat('-', 30)."\n";

$textTypes = [
    'ชื่อบุคคล' => [
        'สมชาย รักดี',
        'วิภาวี สุขสบาย',
        'ประยุทธ์ จันทร์โอชา',
    ],
    'ชื่อสถานที่' => [
        'กรุงเทพมหานคร',
        'เชียงใหม่',
        'ภูเก็ต',
        'ปราจีนบุรี',
    ],
    'ชื่อสินค้า' => [
        'โทรศัพท์มือถือ iPhone',
        'แล็ปท็อป MacBook Pro',
        'รถยนต์ BMW X5',
        'นาฬิกา Rolex',
    ],
];

foreach ($textTypes as $type => $texts) {
    echo "Type: $type\n";
    foreach ($texts as $text) {
        $slug = ThaiSlug::make($text);
        printf("  %-30s → %s\n", $text, $slug);
    }
    echo "\n";
}

// Example 4: Mixed Thai and English text
echo "4. Mixed Thai and English Text\n";
echo '-'.str_repeat('-', 30)."\n";

$mixedTexts = [
    'การใช้ React.js ในโปรเจค',
    'เรียน Python ออนไลน์',
    'Node.js สำหรับ Backend',
    'WordPress บนเซิร์ฟเวอร์ Linux',
    'Docker และ Kubernetes',
];

foreach ($mixedTexts as $text) {
    $slug = ThaiSlug::make($text);
    printf("Text: %-35s → Slug: %s\n", $text, $slug);
}

echo "\n";

// Example 5: Special characters and numbers
echo "5. Special Characters and Numbers\n";
echo '-'.str_repeat('-', 30)."\n";

$specialTexts = [
    'โปรแกรม PHP 8.4',
    'ราคา 1,500 บาท',
    'วันที่ 15 ธันวาคม 2567',
    'อีเมล: admin@example.com',
    'เว็บไซต์ (www.example.co.th)',
];

foreach ($specialTexts as $text) {
    $slug = ThaiSlug::make($text);
    printf("Text: %-35s → Slug: %s\n", $text, $slug);
}

echo "\n";

// Example 6: Empty and edge cases
echo "6. Empty and Edge Cases\n";
echo '-'.str_repeat('-', 30)."\n";

$edgeCases = [
    '',           // Empty string
    '   ',        // Only spaces
    '!!!',        // Only special chars
    'a',          // Single char
    'ก',          // Single Thai char
    'ก ข ค',      // Spaced Thai chars
];

foreach ($edgeCases as $i => $text) {
    $slug = ThaiSlug::make($text);
    $displayText = $text === '' ? '(empty)' : ($text === '   ' ? '(spaces)' : $text);
    printf("Case %d: %-20s → Slug: '%s'\n", $i + 1, $displayText, $slug);
}

echo "\n=== Basic Usage Examples Complete ===\n";
