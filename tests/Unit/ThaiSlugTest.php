<?php

declare(strict_types=1);

use Farzai\ThaiSlug\Enums\Strategy;
use Farzai\ThaiSlug\SlugBuilder;
use Farzai\ThaiSlug\ThaiSlug;

describe('ThaiSlug', function () {
    beforeEach(function () {
        /** @var ThaiSlug $thaiSlug */
        $this->thaiSlug = new ThaiSlug;
    });

    describe('Construction', function () {
        it('can be instantiated with default strategy', function () {
            $instance = new ThaiSlug;
            expect($instance)->toBeInstanceOf(ThaiSlug::class);
        });

        it('can be instantiated with custom default strategy', function () {
            $instance = new ThaiSlug(Strategy::ROYAL);
            expect($instance)->toBeInstanceOf(ThaiSlug::class);
        });
    });

    describe('Static make method', function () {
        it('handles string strategy parameter', function () {
            // This tests the string conversion: Strategy::fromString($options)
            $result = ThaiSlug::make('สวัสดี', 'royal');
            expect($result)->toBeString();
            expect($result)->not()->toBeEmpty();
        });

        it('handles Strategy enum parameter', function () {
            $result = ThaiSlug::make('สวัสดี', Strategy::PHONETIC);
            expect($result)->toBeString();
            expect($result)->not()->toBeEmpty();
        });

        it('handles array options parameter', function () {
            // This tests the array branch: (new self)->generate($text, $options)
            $options = [
                'strategy' => Strategy::ROYAL,
                'separator' => '_',
                'maxLength' => 20,
            ];

            $result = ThaiSlug::make('สวัสดี โลก', $options);
            expect($result)->toBeString();
            expect($result)->toContain('_');
        });

        it('handles default parameter correctly', function () {
            $result = ThaiSlug::make('สวัสดี');
            expect($result)->toBeString();
            expect($result)->not()->toBeEmpty();
        });

        it('handles invalid string strategy gracefully', function () {
            // Strategy::fromString falls back to PHONETIC for invalid strategies
            $result = ThaiSlug::make('สวัสดี', 'invalid-strategy');
            expect($result)->toBeString();
            expect($result)->not()->toBeEmpty();
            // Should use fallback PHONETIC strategy
        });

        it('handles empty text in static method', function () {
            $result = ThaiSlug::make('');
            expect($result)->toBe('');
        });

        it('handles complex array options', function () {
            $options = [
                'strategy' => Strategy::CUSTOM,
                'strategyOptions' => ['custom_mapping' => ['ส' => 's', 'ว' => 'w']],
                'separator' => '-',
                'maxLength' => 50,
                'lowercase' => true,
                'removeDuplicates' => true,
                'trimSeparators' => true,
            ];

            $result = ThaiSlug::make('สวัสดี โลก', $options);
            expect($result)->toBeString();
        });
    });

    describe('Instance generate method', function () {
        it('generates slug with default settings', function () {
            $result = $this->thaiSlug->generate('สวัสดี');
            expect($result)->toBeString();
            expect($result)->not()->toBeEmpty();
        });

        it('generates slug with options', function () {
            $options = [
                'strategy' => Strategy::ROYAL,
                'separator' => '_',
                'maxLength' => 15,
            ];

            $result = $this->thaiSlug->generate('สวัสดี โลก', $options);
            expect($result)->toBeString();
            expect($result)->toContain('_');
            expect(strlen($result))->toBeLessThanOrEqual(15);
        });

        it('handles empty text', function () {
            $result = $this->thaiSlug->generate('');
            expect($result)->toBe('');
        });

        it('respects default strategy from constructor', function () {
            $royalInstance = new ThaiSlug(Strategy::ROYAL);
            $result = $royalInstance->generate('สวัสดี');
            expect($result)->toBeString();
        });

        it('allows strategy override in options', function () {
            $phoneticInstance = new ThaiSlug(Strategy::PHONETIC);
            $options = ['strategy' => Strategy::ROYAL];

            $result = $phoneticInstance->generate('สวัสดี', $options);
            expect($result)->toBeString();
        });
    });

    describe('Builder method', function () {
        it('returns SlugBuilder instance', function () {
            $builder = $this->thaiSlug->builder();
            expect($builder)->toBeInstanceOf(SlugBuilder::class);
        });

        it('creates builder with text and options', function () {
            $options = [
                'strategy' => Strategy::ROYAL,
                'separator' => '_',
            ];

            $builder = $this->thaiSlug->builder('สวัสดี', $options);
            expect($builder)->toBeInstanceOf(SlugBuilder::class);

            $result = $builder->build();
            expect($result)->toBeString();
        });

        it('creates builder with empty text and options', function () {
            $builder = $this->thaiSlug->builder();
            expect($builder)->toBeInstanceOf(SlugBuilder::class);

            $result = $builder->build();
            expect($result)->toBe('');
        });

        it('builder uses default strategy from instance', function () {
            $royalInstance = new ThaiSlug(Strategy::ROYAL);
            $builder = $royalInstance->builder('สวัสดี');

            $result = $builder->build();
            expect($result)->toBeString();
        });

        it('handles all builder options', function () {
            $options = [
                'strategy' => Strategy::CUSTOM,
                'strategyOptions' => ['test' => 'value'],
                'maxLength' => 20,
                'separator' => '_',
                'lowercase' => false,
                'removeDuplicates' => false,
                'trimSeparators' => false,
            ];

            $builder = $this->thaiSlug->builder('test text', $options);
            expect($builder)->toBeInstanceOf(SlugBuilder::class);
        });
    });

    describe('Edge Cases and Error Handling', function () {
        it('handles very long text', function () {
            $longText = str_repeat('สวัสดี ', 100);
            $result = $this->thaiSlug->generate($longText);
            expect($result)->toBeString();
        });

        it('handles special characters in text', function () {
            $result = $this->thaiSlug->generate('สวัสดี @#$% โลก');
            expect($result)->toBeString();
        });

        it('handles whitespace-only text', function () {
            $result = $this->thaiSlug->generate('   ');
            expect($result)->toBe('');
        });

        it('handles numeric text', function () {
            $result = $this->thaiSlug->generate('123 456');
            expect($result)->toBe('123-456');
        });

        it('handles mixed text types', function () {
            $result = $this->thaiSlug->generate('Hello สวัสดี 123');
            expect($result)->toBeString();
            expect($result)->toContain('-');
        });
    });

    describe('Integration with SlugBuilder', function () {
        it('builder method creates properly configured SlugBuilder', function () {
            $options = [
                'strategy' => Strategy::ROYAL,
                'maxLength' => 25,
                'separator' => '_',
            ];

            $builder = $this->thaiSlug->builder('สวัสดี โลก', $options);
            $directBuilder = SlugBuilder::fromArray([
                'text' => 'สวัสดี โลก',
                'strategy' => Strategy::ROYAL,
                'maxLength' => 25,
                'separator' => '_',
                'lowercase' => true,
                'removeDuplicates' => true,
                'trimSeparators' => true,
            ]);

            $result1 = $builder->build();
            $result2 = $directBuilder->build();

            expect($result1)->toBe($result2);
        });

        it('generate method produces same result as builder', function () {
            $text = 'สวัสดี โลก';
            $options = ['strategy' => Strategy::PHONETIC, 'separator' => '_'];

            $generateResult = $this->thaiSlug->generate($text, $options);
            $builderResult = $this->thaiSlug->builder($text, $options)->build();

            expect($generateResult)->toBe($builderResult);
        });

        it('static make produces same result as instance methods', function () {
            $text = 'สวัสดี โลก';
            $strategy = Strategy::ROYAL;

            $staticResult = ThaiSlug::make($text, $strategy);
            $instanceResult = (new ThaiSlug)->generate($text, ['strategy' => $strategy]);

            expect($staticResult)->toBe($instanceResult);
        });
    });
});
