<?php

declare(strict_types=1);

/**
 * Real-World Scenarios Examples for Thai Slug Library
 *
 * This example demonstrates practical applications in real projects
 * including blogs, e-commerce, batch processing, and content management.
 */

require_once __DIR__.'/../vendor/autoload.php';

use Farzai\ThaiSlug\ThaiSlug;

echo "=== Real-World Scenarios Examples ===\n\n";

// Example 1: Blog Post Management
echo "1. Blog Post Management\n";
echo '-'.str_repeat('-', 60)."\n";

class BlogPost
{
    private ThaiSlug $slugGenerator;

    public function __construct()
    {
        $this->slugGenerator = new ThaiSlug;
    }

    public function createSlug(string $title): string
    {
        return $this->slugGenerator->builder()
            ->text($title)
            ->maxLength(60)
            ->separator('-')
            ->build();
    }

    public function generateUrl(string $title): string
    {
        $slug = $this->createSlug($title);

        return "https://myblog.com/articles/{$slug}";
    }
}

$blogManager = new BlogPost;

$blogTitles = [
    'วิธีการเรียน PHP เบื้องต้นสำหรับมือใหม่',
    'เทคนิคการเพิ่มประสิทธิภาพเว็บไซต์ด้วย Laravel',
    'การใช้งาน MySQL และ PostgreSQL อย่างมีประสิทธิภาพ',
    'การพัฒนา API REST ด้วย PHP สมัยใหม่',
    'ความปลอดภัยของเว็บแอปพลิเคชันในยุคดิจิทัล',
    'การทำ SEO สำหรับเว็บไซต์ภาษาไทย',
    'เทคนิคการออกแบบ UX/UI ที่ดีสำหรับผู้ใช้ไทย',
];

echo "Blog post URL generation:\n\n";
foreach ($blogTitles as $i => $title) {
    $slug = $blogManager->createSlug($title);
    $url = $blogManager->generateUrl($title);

    printf("Post %d:\n", $i + 1);
    printf("  Title: %s\n", $title);
    printf("  Slug:  %s\n", $slug);
    printf("  URL:   %s\n\n", $url);
}

// Example 2: E-commerce Product Management
echo "2. E-commerce Product Management\n";
echo '-'.str_repeat('-', 60)."\n";

class ProductManager
{
    private ThaiSlug $slugGenerator;

    public function __construct()
    {
        $this->slugGenerator = new ThaiSlug;
    }

    public function createProductSlug(string $name, string $category = '', string $brand = ''): string
    {
        $fullText = trim("$category $brand $name");

        return $this->slugGenerator->builder()
            ->text($fullText)
            ->maxLength(50)
            ->separator('-')
            ->build();
    }

    public function generateProductUrl(string $slug): string
    {
        return "https://mystore.com/products/{$slug}";
    }
}

$productManager = new ProductManager;

$products = [
    ['name' => 'เสื้อยืดผ้าฝ้าย 100%', 'category' => 'เสื้อผ้า', 'brand' => 'Cotton Plus'],
    ['name' => 'รองเท้าผ้าใบสีขาว', 'category' => 'รองเท้า', 'brand' => 'Nike'],
    ['name' => 'กระเป๋าหนังแท้สำหรับผู้หญิง', 'category' => 'กระเป๋า', 'brand' => 'Leather Craft'],
    ['name' => 'นาฬิกาข้อมือแบบดิจิทัล', 'category' => 'อุปกรณ์เสริม', 'brand' => 'Casio'],
    ['name' => 'หูฟังไร้สายเก็บเสียงดี', 'category' => 'อิเล็กทรอนิกส์', 'brand' => 'Sony'],
];

echo "Product URL generation:\n\n";
foreach ($products as $i => $product) {
    $slug = $productManager->createProductSlug(
        $product['name'],
        $product['category'],
        $product['brand']
    );
    $url = $productManager->generateProductUrl($slug);

    printf("Product %d:\n", $i + 1);
    printf("  Name:     %s\n", $product['name']);
    printf("  Category: %s\n", $product['category']);
    printf("  Brand:    %s\n", $product['brand']);
    printf("  Slug:     %s\n", $slug);
    printf("  URL:      %s\n\n", $url);
}

// Example 3: Category and Tag Management
echo "3. Category and Tag Management\n";
echo '-'.str_repeat('-', 60)."\n";

class ContentTaxonomy
{
    private ThaiSlug $slugGenerator;

    public function __construct()
    {
        $this->slugGenerator = new ThaiSlug;
    }

    public function createCategorySlug(string $category): string
    {
        return $this->slugGenerator->builder()
            ->text($category)
            ->maxLength(40)
            ->separator('-')
            ->build();
    }

    public function createTagSlug(string $tag): string
    {
        return $this->slugGenerator->builder()
            ->text($tag)
            ->maxLength(30)
            ->separator('-')
            ->build();
    }
}

$taxonomy = new ContentTaxonomy;

$categories = [
    'เทคโนโลยีสารสนเทศ',
    'การพัฒนาเว็บไซต์',
    'ความปลอดภัยไซเบอร์',
    'ปัญญาประดิษฐ์และแมชชีนเลิร์นนิง',
    'การจัดการฐานข้อมูล',
];

$tags = [
    'PHP', 'Laravel', 'MySQL', 'JavaScript', 'React',
    'เบื้องต้น', 'ขั้นสูง', 'มือใหม่', 'ผู้เชียวชาญ', 'ทิปส์',
];

echo "Categories:\n";
foreach ($categories as $category) {
    $slug = $taxonomy->createCategorySlug($category);
    printf("  %-40s → /category/%s\n", $category, $slug);
}

echo "\nTags:\n";
foreach ($tags as $tag) {
    $slug = $taxonomy->createTagSlug($tag);
    printf("  %-20s → /tag/%s\n", $tag, $slug);
}

echo "\n";

// Example 4: Batch Processing
echo "4. Batch Processing\n";
echo '-'.str_repeat('-', 60)."\n";

class BatchSlugProcessor
{
    private ThaiSlug $slugGenerator;

    private array $results = [];

    public function __construct()
    {
        $this->slugGenerator = new ThaiSlug;
    }

    public function processBatch(array $texts, array $options = []): array
    {
        $results = [];
        $startTime = microtime(true);

        foreach ($texts as $text) {
            $slug = $this->slugGenerator->builder()
                ->text($text)
                ->maxLength($options['maxLength'] ?? null)
                ->separator($options['separator'] ?? '-')
                ->build();

            $results[] = [
                'original' => $text,
                'slug' => $slug,
                'length' => strlen($slug),
            ];
        }

        $processingTime = microtime(true) - $startTime;

        $this->results = [
            'items' => $results,
            'total_count' => count($texts),
            'processing_time' => $processingTime,
            'items_per_second' => count($texts) / $processingTime,
        ];

        return $this->results;
    }

    public function getStats(): array
    {
        return $this->results;
    }
}

$batchProcessor = new BatchSlugProcessor;

// Load a large batch of content
$batchContent = [
    'การพัฒนาแอปพลิเคชันมือถือด้วย React Native',
    'เทคนิคการเขียน SQL ที่มีประสิทธิภาพ',
    'การใช้งาน Docker สำหรับการพัฒนา',
    'ความปลอดภัยของ API และการป้องกันการโจมตี',
    'การทำ Unit Testing ใน PHP',
    'การใช้งาน Redis สำหรับ Caching',
    'การพัฒนา Microservices ด้วย PHP',
    'เทคนิคการเพิ่มความเร็วเว็บไซต์',
    'การใช้งาน Elasticsearch สำหรับการค้นหา',
    'การพัฒนา Real-time Application ด้วย WebSocket',
];

// Duplicate content to simulate larger batch
$largeBatch = array_merge($batchContent, $batchContent, $batchContent);

$results = $batchProcessor->processBatch($largeBatch, [
    'maxLength' => 50,
    'separator' => '-',
]);

echo "Batch processing results:\n";
printf("Total items processed: %d\n", $results['total_count']);
printf("Processing time: %.4f seconds\n", $results['processing_time']);
printf("Items per second: %.1f\n\n", $results['items_per_second']);

echo "Sample results:\n";
foreach (array_slice($results['items'], 0, 5) as $i => $item) {
    printf("%d. %s\n", $i + 1, $item['original']);
    printf("   → %s (%d chars)\n\n", $item['slug'], $item['length']);
}

// Example 5: Content Management System Integration
echo "5. Content Management System Integration\n";
echo '-'.str_repeat('-', 60)."\n";

class CMSIntegration
{
    private ThaiSlug $slugGenerator;

    private array $existingSlugs = [];

    public function __construct()
    {
        $this->slugGenerator = new ThaiSlug;

        // Simulate existing slugs in database
        $this->existingSlugs = [
            'kan-phatthana-web-application',
            'theknit-kan-khian-php',
            'kwam-plodphai-cyber',
        ];
    }

    public function createUniqueSlug(string $text): string
    {
        $baseSlug = $this->slugGenerator->builder()
            ->text($text)
            ->maxLength(50)
            ->separator('-')
            ->build();

        return $this->ensureUnique($baseSlug);
    }

    private function ensureUnique(string $slug): string
    {
        $originalSlug = $slug;
        $counter = 1;

        while (in_array($slug, $this->existingSlugs)) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        // Add to existing slugs to simulate database storage
        $this->existingSlugs[] = $slug;

        return $slug;
    }

    public function bulkCreateSlugs(array $titles): array
    {
        $results = [];

        foreach ($titles as $title) {
            $results[] = [
                'title' => $title,
                'slug' => $this->createUniqueSlug($title),
            ];
        }

        return $results;
    }
}

$cms = new CMSIntegration;

$cmsTitles = [
    'การพัฒนาเว็บแอปพลิเคชัน',  // Similar to existing slug
    'เทคนิคการเขียน PHP',       // Similar to existing slug
    'ความปลอดภัยไซเบอร์',        // Similar to existing slug
    'การใช้งาน Framework',
    'การทำ SEO ภาษาไทย',
];

$cmsResults = $cms->bulkCreateSlugs($cmsTitles);

echo "CMS unique slug generation:\n\n";
foreach ($cmsResults as $result) {
    printf("Title: %s\n", $result['title']);
    printf("Slug:  %s\n\n", $result['slug']);
}

// Example 6: URL Routing and Navigation
echo "6. URL Routing and Navigation\n";
echo '-'.str_repeat('-', 60)."\n";

class NavigationBuilder
{
    private ThaiSlug $slugGenerator;

    public function __construct()
    {
        $this->slugGenerator = new ThaiSlug;
    }

    public function createBreadcrumb(array $segments): array
    {
        $breadcrumb = [];
        $path = '';

        foreach ($segments as $segment) {
            $slug = $this->slugGenerator->builder()
                ->text($segment)
                ->maxLength(30)
                ->separator('-')
                ->build();

            $path .= '/'.$slug;

            $breadcrumb[] = [
                'title' => $segment,
                'slug' => $slug,
                'path' => $path,
            ];
        }

        return $breadcrumb;
    }

    public function createSitemap(array $pages): array
    {
        $sitemap = [];

        foreach ($pages as $page) {
            $slug = $this->slugGenerator->builder()
                ->text($page)
                ->maxLength(40)
                ->separator('-')
                ->build();

            $sitemap[] = [
                'title' => $page,
                'slug' => $slug,
                'url' => "https://example.com/{$slug}",
                'lastmod' => date('Y-m-d'),
            ];
        }

        return $sitemap;
    }
}

$navigation = new NavigationBuilder;

// Create breadcrumb navigation
$breadcrumbSegments = ['หน้าแรก', 'บทความ', 'เทคโนโลยี', 'การพัฒนาเว็บ'];
$breadcrumb = $navigation->createBreadcrumb($breadcrumbSegments);

echo "Breadcrumb navigation:\n";
foreach ($breadcrumb as $crumb) {
    printf("  %s → %s\n", $crumb['title'], $crumb['path']);
}

// Create sitemap
$sitemapPages = [
    'เกี่ยวกับเรา',
    'บริการของเรา',
    'ผลงานของเรา',
    'บทความและข่าวสาร',
    'ติดต่อเรา',
];

$sitemap = $navigation->createSitemap($sitemapPages);

echo "\nSitemap generation:\n";
foreach ($sitemap as $page) {
    printf("  %-20s → %s\n", $page['title'], $page['url']);
}

echo "\n=== Real-World Scenarios Examples Complete ===\n";
