<?php

declare(strict_types=1);

use Farzai\ThaiSlug\Enums\Strategy;
use Farzai\ThaiSlug\SlugBuilder;

describe('SlugBuilder', function () {
    beforeEach(function () {
        /** @var SlugBuilder $builder */
        $this->builder = new SlugBuilder;
    });

    describe('Builder Pattern Interface', function () {
        it('implements fluent interface for text method', function () {
            $result = $this->builder->text('hello world');
            expect($result)->toBeInstanceOf(SlugBuilder::class);
            expect($result)->toBe($this->builder); // Mutable builder returns same instance
        });

        it('implements fluent interface for strategy method', function () {
            $result = $this->builder->strategy(Strategy::PHONETIC);
            expect($result)->toBeInstanceOf(SlugBuilder::class);
            expect($result)->toBe($this->builder); // Mutable builder returns same instance
        });

        it('implements fluent interface for separator method', function () {
            $result = $this->builder->separator('_');
            expect($result)->toBeInstanceOf(SlugBuilder::class);
            expect($result)->toBe($this->builder); // Mutable builder returns same instance
        });

        it('implements fluent interface for maxLength method', function () {
            $result = $this->builder->maxLength(50);
            expect($result)->toBeInstanceOf(SlugBuilder::class);
            expect($result)->toBe($this->builder);
        });

        it('implements fluent interface for lowercase method', function () {
            $result = $this->builder->lowercase(false);
            expect($result)->toBeInstanceOf(SlugBuilder::class);
            expect($result)->toBe($this->builder);
        });

        it('implements fluent interface for removeDuplicates method', function () {
            $result = $this->builder->removeDuplicates(false);
            expect($result)->toBeInstanceOf(SlugBuilder::class);
            expect($result)->toBe($this->builder);
        });

        it('implements fluent interface for trimSeparators method', function () {
            $result = $this->builder->trimSeparators(false);
            expect($result)->toBeInstanceOf(SlugBuilder::class);
            expect($result)->toBe($this->builder);
        });
    });

    describe('Configuration Methods', function () {
        it('accepts strategy configuration', function () {
            $slug = $this->builder
                ->text('สวัสดี โลก')
                ->strategy(Strategy::ROYAL)
                ->build();

            expect($slug)->toBeString()->not()->toBeEmpty();
        });

        it('accepts separator configuration', function () {
            $slug = $this->builder
                ->text('hello world')
                ->separator('_')
                ->build();

            expect($slug)->toContain('_');
        });

        it('handles max length configuration', function () {
            $slug = $this->builder
                ->text('this is a very long text that should be truncated')
                ->maxLength(20)
                ->build();

            expect(strlen($slug))->toBeLessThanOrEqual(20);
        });

        it('handles lowercase configuration', function () {
            $slug = $this->builder
                ->text('HELLO World')
                ->lowercase(true)
                ->build();

            expect($slug)->toBe(strtolower($slug));
        });

        it('handles removeDuplicates configuration', function () {
            $slug = $this->builder
                ->text('test----text')
                ->separator('-')
                ->removeDuplicates(true)
                ->build();

            expect($slug)->not()->toContain('--');
        });

        it('handles trimSeparators configuration', function () {
            $slug = $this->builder
                ->text('  test text  ')
                ->separator('-')
                ->trimSeparators(true)
                ->build();

            expect($slug)->not()->toStartWith('-');
            expect($slug)->not()->toEndWith('-');
        });

        it('can disable lowercase', function () {
            $slug = $this->builder
                ->text('TEST')
                ->lowercase(false)
                ->build();

            expect($slug)->toContain('TEST');
        });
    });

    describe('Build Functionality', function () {
        it('builds slugs from Thai text', function () {
            $slug = $this->builder
                ->text('สวัสดี โลก')
                ->build();

            expect($slug)->toBeString()->not()->toBeEmpty();
        });

        it('handles empty text', function () {
            $slug = $this->builder
                ->text('')
                ->build();

            expect($slug)->toBe('');
        });

        it('processes complex configurations', function () {
            $slug = $this->builder
                ->text('สวัสดี โลก สวยงาม')
                ->strategy(Strategy::PHONETIC)
                ->separator('_')
                ->maxLength(30)
                ->lowercase(true)
                ->build();

            expect($slug)->toBeString()->not()->toBeEmpty();
            expect($slug)->toContain('_');
            expect(strlen($slug))->toBeLessThanOrEqual(30);
        });
    });

    describe('Static Factory Methods', function () {
        it('provides static make method', function () {
            $slug = SlugBuilder::make('สวัสดี โลก');
            expect($slug)->toBeString()->not()->toBeEmpty();
        });

        it('allows strategy specification in static method', function () {
            $slug = SlugBuilder::make('สวัสดี โลก', Strategy::ROYAL);
            expect($slug)->toBeString()->not()->toBeEmpty();
        });

        it('provides fromArray factory method', function () {
            $config = [
                'text' => 'สวัสดี โลก',
                'strategy' => Strategy::PHONETIC,
                'separator' => '_',
                'maxLength' => 50,
                'lowercase' => true,
            ];

            $builder = SlugBuilder::fromArray($config);
            expect($builder)->toBeInstanceOf(SlugBuilder::class);

            $slug = $builder->build();
            expect($slug)->toBeString()->not()->toBeEmpty();
            expect($slug)->toContain('_');
        });

        it('fromArray handles empty config with defaults', function () {
            $builder = SlugBuilder::fromArray([]);
            expect($builder)->toBeInstanceOf(SlugBuilder::class);

            $slug = $builder->build();
            expect($slug)->toBe(''); // Empty text should produce empty slug
        });

        it('fromArray handles partial config correctly', function () {
            $config = ['text' => 'test'];
            $builder = SlugBuilder::fromArray($config);

            $slug = $builder->build();
            expect($slug)->toBe('test');
        });

        it('fromArray uses type-safe casting for all parameters', function () {
            $config = [
                'text' => 'test text',
                'strategy' => 'not-a-strategy', // Should fallback to PHONETIC
                'strategyOptions' => 'not-an-array', // Should fallback to []
                'maxLength' => 'not-an-int', // Should fallback to null
                'separator' => 123, // Should fallback to '-'
                'lowercase' => 'not-a-bool', // Should fallback to true
                'removeDuplicates' => 'not-a-bool', // Should fallback to true
                'trimSeparators' => 'not-a-bool', // Should fallback to true
            ];

            $builder = SlugBuilder::fromArray($config);
            expect($builder)->toBeInstanceOf(SlugBuilder::class);

            // Should not throw and produce valid output
            $slug = $builder->build();
            expect($slug)->toBeString();
        });

        it('fromArray handles complex strategy options correctly', function () {
            $strategyOptions = ['custom' => 'value', 'nested' => ['key' => 'val']];
            $config = [
                'text' => 'test',
                'strategyOptions' => $strategyOptions,
            ];

            $builder = SlugBuilder::fromArray($config);
            expect($builder)->toBeInstanceOf(SlugBuilder::class);
        });

        it('fromArray handles dependency injection parameters', function () {
            $normalizer = new \Farzai\ThaiSlug\ThaiNormalizer;
            $transliterator = new \Farzai\ThaiSlug\Transliterator(Strategy::PHONETIC);
            $urlSafeMaker = new \Farzai\ThaiSlug\UrlSafeMaker;

            $config = [
                'text' => 'test',
                'normalizer' => $normalizer,
                'transliterator' => $transliterator,
                'urlSafeMaker' => $urlSafeMaker,
            ];

            $builder = SlugBuilder::fromArray($config);
            expect($builder)->toBeInstanceOf(SlugBuilder::class);

            $slug = $builder->build();
            expect($slug)->toBe('test');
        });

        it('fromArray handles invalid dependency injection gracefully', function () {
            $config = [
                'text' => 'test',
                'normalizer' => 'not-a-normalizer',
                'transliterator' => 'not-a-transliterator',
                'urlSafeMaker' => 'not-a-urlsafemaker',
            ];

            $builder = SlugBuilder::fromArray($config);
            expect($builder)->toBeInstanceOf(SlugBuilder::class);

            // Should fallback to defaults and still work
            $slug = $builder->build();
            expect($slug)->toBe('test');
        });
    });

    describe('Error Handling and Edge Cases', function () {
        it('handles invalid max length gracefully', function () {
            expect(fn () => $this->builder->maxLength(-1))
                ->toThrow(InvalidArgumentException::class);
        });

        it('handles zero max length as invalid', function () {
            expect(fn () => $this->builder->maxLength(0))
                ->toThrow(InvalidArgumentException::class, 'Max length must be positive');
        });

        it('accepts null max length correctly', function () {
            $result = $this->builder->maxLength(null);
            expect($result)->toBeInstanceOf(SlugBuilder::class);
        });

        it('builds correctly after configuration changes', function () {
            $builder = $this->builder
                ->text('first text')
                ->strategy(Strategy::PHONETIC);

            $slug1 = $builder->build();
            expect($slug1)->toBeString()->not()->toBeEmpty();

            // Change configuration and build again
            $slug2 = $builder
                ->text('second text')
                ->strategy(Strategy::ROYAL)
                ->build();

            expect($slug2)->toBeString()->not()->toBeEmpty();
            expect($slug1)->not()->toBe($slug2);
        });

        it('maintains builder state correctly', function () {
            $builder = $this->builder
                ->text('test')
                ->separator('_')
                ->maxLength(20);

            $slug1 = $builder->build();
            $slug2 = $builder->build(); // Should produce same result

            expect($slug1)->toBe($slug2);
        });
    });

    describe('Performance and Memory', function () {
        it('handles large text efficiently', function () {
            $largeText = str_repeat('สวัสดีโลก ', 100);

            $start = microtime(true);
            $slug = $this->builder
                ->text($largeText)
                ->build();
            $end = microtime(true);

            expect($slug)->toBeString()->not()->toBeEmpty();
            expect($end - $start)->toBeLessThan(0.1); // 100ms
        });
    });
});
