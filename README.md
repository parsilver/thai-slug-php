# Thai Slug PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/farzai/thai-slug.svg?style=flat-square)](https://packagist.org/packages/farzai/thai-slug)
[![Tests](https://img.shields.io/github/actions/workflow/status/parsilver/thai-slug-php/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/parsilver/thai-slug-php/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/farzai/thai-slug.svg?style=flat-square)](https://packagist.org/packages/farzai/thai-slug)
[![PHP Version Require](http://poser.pugx.org/farzai/thai-slug/require/php?style=flat-square)](https://packagist.org/packages/farzai/thai-slug)

A modern, high-performance PHP library for generating URL-friendly slugs from Thai text with multiple transliteration strategies.

## 🚀 Features

- **Multiple Transliteration Strategies**: Phonetic, Royal Thai (RTGS), and Custom rule-based
- **High Performance**: Optimized for fast processing of Thai text
- **UTF-8 Safe**: Proper Thai Unicode normalization and character handling
- **Framework Agnostic**: Works with any PHP framework or standalone

## 📋 Requirements

- PHP 8.4 or higher
- mbstring extension
- intl extension (recommended)
- iconv extension

## 📦 Installation

You can install the package via Composer:

```bash
composer require farzai/thai-slug
```

## 🔥 Quick Start

### Basic Usage

```php
use Farzai\ThaiSlug\ThaiSlug;

// Simple slug generation
$slug = ThaiSlug::make('สวัสดีชาวโลก');
// Output: "sawasdee-chao-lok"

$slug = ThaiSlug::make('ภาษาไทยสำหรับการพัฒนา');
// Output: "phasa-thai-samrap-kan-phattana"
```

### Advanced Configuration

```php
use Farzai\ThaiSlug\ThaiSlug;
use Farzai\ThaiSlug\Enums\Strategy;

$thaiSlug = new ThaiSlug();

// Using the fluent builder interface
$slug = $thaiSlug->builder()
    ->text('เทคโนโลยีและนวัตกรรม')
    ->strategy(Strategy::ROYAL)    // Use Royal Thai transliteration (RTGS)
    ->maxLength(50)                // Limit slug length
    ->separator('_')               // Use underscore separator
    ->build();

// Output: "theknoloyi_lae_nawatkam"
```

## 💡 Transliteration Strategies

### 1. Phonetic Strategy (Default)
Converts Thai text to phonetically similar Latin characters:

```php
use Farzai\ThaiSlug\Enums\Strategy;

$slug = ThaiSlug::make('กรุงเทพมหานคร'); 
// Output: "krung-thep-maha-nakhon"

$thaiSlug->builder()
    ->text('โปรแกรมเมอร์')
    ->strategy(Strategy::PHONETIC)
    ->build();
// Output: "program-mer"
```

### 2. Royal Thai Strategy (RTGS)
Follows the Royal Thai General System of Transcription (official standard):

```php
$thaiSlug->builder()
    ->text('สถาบันเทคโนโลยี')
    ->strategy(Strategy::ROYAL)
    ->build();
// Output: "sathaban-theknoloyi"
```

### 3. Custom Strategy
Define your own transliteration rules:

```php
use Farzai\ThaiSlug\Enums\Strategy;

$thaiSlug->builder()
    ->text('ไอทีและเทคโนโลยี')
    ->strategy(Strategy::CUSTOM)
    ->strategyOptions([
        'rules' => [
            'ไอที' => 'IT',
            'เทค' => 'tech',
        ]
    ])
    ->build();
// Output: "IT-lae-tech-noloyi"
```

## ⚡ Performance

The library is optimized for high performance with Thai text processing:

- **Fast Processing**: Optimized algorithms for Thai text transliteration
- **Memory Efficient**: Minimal memory footprint for typical use cases
- **Modern PHP**: Built with PHP 8.4+ features for optimal performance

### Performance Characteristics

| Text Size | Typical Processing Time | Memory Usage |
|-----------|------------------------|--------------|
| Small (<100 chars) | < 1ms | < 1MB |
| Medium (100-1K chars) | < 5ms | < 2MB |
| Large (1K+ chars) | < 50ms | < 5MB |

## 🔧 Configuration Options

### Slug Builder Options

The fluent builder interface provides the following configuration options:

```php
use Farzai\ThaiSlug\Enums\Strategy;

$slug = $thaiSlug->builder()
    ->text('ข้อความภาษาไทย')           // Text to convert
    ->strategy(Strategy::PHONETIC)      // Transliteration strategy
    ->maxLength(100)                    // Maximum slug length
    ->separator('-')                    // Separator character
    ->lowercase(true)                   // Convert to lowercase
    ->removeDuplicates(true)            // Remove duplicate separators
    ->trimSeparators(true)              // Remove leading/trailing separators
    ->strategyOptions([])               // Additional strategy-specific options
    ->build();
```

### Available Strategies

- `Strategy::PHONETIC` - Default phonetic transliteration
- `Strategy::ROYAL` - Royal Thai General System (RTGS) 
- `Strategy::CUSTOM` - Custom transliteration rules

## 🏗️ Architecture

### SOLID Principles & Design Patterns

This library follows modern software engineering principles:

- **Strategy Pattern**: Interchangeable transliteration algorithms
- **Builder Pattern**: Fluent configuration interface  
- **Facade Pattern**: Simplified API for complex operations
- **Dependency Injection**: Fully testable and extensible
- **Enum-Based Configuration**: Type-safe strategy selection

### Key Components

- **ThaiSlug**: Main facade class providing simple API
- **SlugBuilder**: Fluent builder for advanced configuration
- **Strategy Enum**: Type-safe strategy selection
- **Transliteration Strategies**: Pluggable transliteration algorithms
- **Processing Pipeline**: Modular text processing workflow

## 🧪 Testing

Run the test suite and code quality tools:

```bash
# Run all tests
composer test

# Run tests with coverage
composer test-coverage

# Run tests with HTML coverage report
composer test-coverage-html

# Code analysis with PHPStan
composer analyse

# Format code with Laravel Pint
composer format

# Check code formatting
composer format-check

# Run all quality checks
composer check-code

# Complete CI pipeline
composer ci
```

## 📊 Real-World Examples

### Blog Post Slugs
```php
$titles = [
    'วิธีการเขียนโค้ด PHP ที่มีประสิทธิภาพ',
    'เทคนิคการออกแบบฐานข้อมูลที่ดี',
    'การใช้ Laravel สำหรับโปรเจคใหญ่',
];

foreach ($titles as $title) {
    $slug = ThaiSlug::make($title);
    echo "Title: $title\n";
    echo "Slug: $slug\n\n";
}

// Output:
// Title: วิธีการเขียนโค้ด PHP ที่มีประสิทธิภาพ
// Slug: withee-kan-khian-kho-php-thee-mee-prasitthiphap

// Title: เทคนิคการออกแบบฐานข้อมูลที่ดี
// Slug: theknit-kan-ok-baep-than-kho-mul-thee-dee

// Title: การใช้ Laravel สำหรับโปรเจคใหญ่
// Slug: kan-chai-laravel-samrap-project-yai
```

### E-commerce Product Names
```php
$products = [
    'เสื้อยืดผู้ชาย สีน้ำเงิน ไซส์ L',
    'กระเป๋าหนังแท้ สำหรับผู้หญิง',
    'รองเท้าผ้าใบ Nike Air Max',
];

$thaiSlug = new ThaiSlug();

foreach ($products as $product) {
    $slug = $thaiSlug->builder()
        ->text($product)
        ->maxLength(60)
        ->build();
    
    echo "Product: $product\n";
    echo "Slug: $slug\n\n";
}
```

### URL Generation in Frameworks

#### Laravel Integration
```php
// In a Laravel controller
use Farzai\ThaiSlug\ThaiSlug;

class ArticleController extends Controller
{
    public function store(Request $request)
    {
        $slug = ThaiSlug::make($request->input('title'));
        
        Article::create([
            'title' => $request->input('title'),
            'slug' => $slug,
            'content' => $request->input('content'),
        ]);
        
        return redirect()->route('articles.show', ['slug' => $slug]);
    }
}
```

#### WordPress Plugin Usage
```php
// WordPress hook
add_filter('wp_unique_post_slug', function ($slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug) {
    if ($post_type === 'post' && preg_match('/[\u{0E00}-\u{0E7F}]/u', $original_slug)) {
        return \Farzai\ThaiSlug\ThaiSlug::make($original_slug);
    }
    return $slug;
}, 10, 6);
```

## 🛡️ Security & Best Practices

### Input Validation
```php
use Farzai\ThaiSlug\Exceptions\InvalidArgumentException;

try {
    // The library automatically validates and sanitizes input
    $slug = ThaiSlug::make($userInput);
} catch (InvalidArgumentException $e) {
    // Handle invalid input gracefully
    $slug = 'default-slug';
}
```

### Production Best Practices
```php
use Farzai\ThaiSlug\ThaiSlug;
use Farzai\ThaiSlug\Enums\Strategy;

// Production setup with consistent configuration
$thaiSlug = new ThaiSlug(Strategy::PHONETIC);

// Process user content with validation
$slug = $thaiSlug->builder()
    ->text($userContent)
    ->maxLength(100)
    ->separator('-')
    ->lowercase(true)
    ->build();
```

## 🔄 Migration Guide

### From Other Libraries
If you're migrating from other Thai slug libraries:

```php
// Old library
$slug = some_old_thai_slug_function('ข้อความไทย');

// New library
$slug = \Farzai\ThaiSlug\ThaiSlug::make('ข้อความไทย');
```

## 🤝 Contributing

We welcome contributions! Please follow these guidelines:

### Development Setup

```bash
# Clone the repository
git clone https://github.com/parsilver/thai-slug-php.git

# Install dependencies
composer install

# Run tests
composer test

# Run code analysis
composer analyse

# Format code
composer format
```

## 📝 Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for more information on what has changed recently.

## 🔒 Security

If you discover any security-related issues, please email parkorn@farzai.com instead of using the issue tracker.

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## 🎯 Performance Tips

1. **Choose appropriate strategy** based on your needs:
   - **Phonetic**: Best for readability and general use
   - **Royal**: Best for official/academic use (RTGS compliant)
   - **Custom**: Best for specialized terminology or domain-specific rules
2. **Set reasonable length limits** using `maxLength()` to prevent overly long slugs
3. **Batch operations** when processing multiple texts to reduce overhead
4. **Use consistent configuration** across your application for predictable results

## 🙏 Credits

- [parsilver](https://github.com/parsilver) - Creator and maintainer
- [All Contributors](../../contributors) - Community contributors

Built with ❤️ for the Thai developer community.