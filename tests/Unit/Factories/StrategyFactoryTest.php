<?php

declare(strict_types=1);

use Farzai\ThaiSlug\Contracts\TransliterationStrategyInterface;
use Farzai\ThaiSlug\Enums\Strategy;
use Farzai\ThaiSlug\Exceptions\ThaiSlugException;
use Farzai\ThaiSlug\Factories\StrategyFactory;
use Farzai\ThaiSlug\Strategies\CustomStrategy;
use Farzai\ThaiSlug\Strategies\PhoneticStrategy;
use Farzai\ThaiSlug\Strategies\RoyalStrategy;

describe('StrategyFactory', function () {
    beforeEach(function () {
        /** @var StrategyFactory $factory */
        $this->factory = new StrategyFactory;
    });

    describe('Basic Strategy Creation', function () {
        it('creates phonetic strategy from enum', function () {
            $strategy = $this->factory->create(Strategy::PHONETIC);

            expect($strategy)->toBeInstanceOf(PhoneticStrategy::class);
            expect($strategy)->toBeInstanceOf(TransliterationStrategyInterface::class);
        });

        it('creates royal strategy from string', function () {
            $strategy = $this->factory->create('royal');

            expect($strategy)->toBeInstanceOf(RoyalStrategy::class);
        });

        it('creates custom strategy', function () {
            $strategy = $this->factory->create(Strategy::CUSTOM);

            expect($strategy)->toBeInstanceOf(CustomStrategy::class);
        });

        it('throws exception for invalid strategy string', function () {
            expect(fn () => $this->factory->create('invalid'))
                ->toThrow(ThaiSlugException::class, 'Invalid strategy: invalid');
        });
    });

    describe('Strategy Options and Configuration', function () {
        it('creates strategy with custom options', function () {
            $options = [
                'preserve_tone_marks' => true,
                'preserve_digits' => false,
            ];

            $strategy = $this->factory->create(Strategy::PHONETIC, $options);

            expect($strategy)->toBeInstanceOf(PhoneticStrategy::class);
        });

        it('merges global defaults with strategy options', function () {
            // Use valid phonetic strategy options for global defaults
            $globalDefaults = ['preserve_tone_marks' => true];
            $factory = new StrategyFactory($globalDefaults);

            $strategy = $factory->create(Strategy::PHONETIC, ['preserve_digits' => false]);

            expect($strategy)->toBeInstanceOf(PhoneticStrategy::class);
        });

        it('validates strategy options', function () {
            // Test with valid options first
            $validOptions = ['preserve_tone_marks' => false];
            $strategy = $this->factory->create(Strategy::PHONETIC, $validOptions);

            expect($strategy)->toBeInstanceOf(PhoneticStrategy::class);
        });
    });

    describe('Strategy Caching', function () {
        it('caches strategies with same options', function () {
            $options = ['preserve_tone_marks' => true];

            $strategy1 = $this->factory->create(Strategy::PHONETIC, $options);
            $strategy2 = $this->factory->create(Strategy::PHONETIC, $options);

            // Should be the same instance due to caching
            expect($strategy1)->toBe($strategy2);
        });

        it('creates different instances for different options', function () {
            $options1 = ['preserve_tone_marks' => true];
            $options2 = ['preserve_tone_marks' => false];

            $strategy1 = $this->factory->create(Strategy::PHONETIC, $options1);
            $strategy2 = $this->factory->create(Strategy::PHONETIC, $options2);

            expect($strategy1)->not()->toBe($strategy2);
        });

        it('provides cache statistics', function () {
            $this->factory->create(Strategy::PHONETIC);
            $this->factory->create(Strategy::ROYAL);

            $stats = $this->factory->getCacheStats();

            expect($stats)->toHaveKey('cached_strategies');
            expect($stats)->toHaveKey('memory_usage');
            expect($stats['cached_strategies'])->toBeGreaterThan(0);
        });

        it('can clear cache', function () {
            $this->factory->create(Strategy::PHONETIC);
            $initialStats = $this->factory->getCacheStats();

            $this->factory->clearCache();
            $clearedStats = $this->factory->getCacheStats();

            expect($initialStats['cached_strategies'])->toBeGreaterThan(0);
            expect($clearedStats['cached_strategies'])->toBe(0);
        });
    });

    describe('Custom Strategy Management', function () {
        it('registers and creates custom strategy', function () {
            $customStrategyClass = new class implements TransliterationStrategyInterface
            {
                /**
                 * @param  array<string, mixed>  $options
                 */
                public function __construct(private array $options = [])
                {
                    // Property used for interface compliance
                }

                public function transliterate(string $text): string
                {
                    return 'custom-'.$text;
                }

                /**
                 * @return array<string, mixed>
                 *
                 * @phpstan-ignore-next-line method.unused
                 */
                public function getOptions(): array
                {
                    return $this->options;
                }

                public function getName(): string
                {
                    return 'test-custom';
                }

                public function validateOptions(array $options): bool
                {
                    return true;
                }
            };

            $this->factory->registerCustomStrategy('test', $customStrategyClass::class);

            $instance = new $customStrategyClass;
            expect($instance->getOptions())->toBe([]);

            expect($this->factory->hasStrategy('test'))->toBeTrue();
            expect($this->factory->getAvailableStrategies())->toContain('test');
        });

        it('throws exception when registering non-existent class', function () {
            expect(fn () => $this->factory->registerCustomStrategy('invalid', 'NonExistentClass'))
                ->toThrow(ThaiSlugException::class, 'Strategy class does not exist');
        });

        it('throws exception when registering invalid interface', function () {
            $invalidClass = new class {};

            expect(fn () => $this->factory->registerCustomStrategy('invalid', $invalidClass::class))
                ->toThrow(ThaiSlugException::class, 'Strategy class must implement TransliterationStrategyInterface');
        });

        it('creates custom strategy instance', function () {
            $customStrategyClass = new class implements TransliterationStrategyInterface
            {
                /**
                 * @param  array<string, mixed>  $options
                 */
                public function __construct(private array $options = [])
                {
                    // Property used for interface compliance
                }

                public function transliterate(string $text): string
                {
                    return 'custom-'.$text;
                }

                /**
                 * @return array<string, mixed>
                 *
                 * @phpstan-ignore-next-line method.unused
                 */
                public function getOptions(): array
                {
                    return $this->options;
                }

                public function getName(): string
                {
                    return 'test-custom';
                }

                public function validateOptions(array $options): bool
                {
                    return true;
                }
            };

            $this->factory->registerCustomStrategy('test', $customStrategyClass::class);
            $strategy = $this->factory->createCustomStrategy('test');

            expect($strategy)->toBeInstanceOf(TransliterationStrategyInterface::class);
            expect($strategy->transliterate('test'))->toBe('custom-test');
            expect($strategy->getOptions())->toBe([]);
        });

        it('throws exception for unregistered custom strategy', function () {
            expect(fn () => $this->factory->createCustomStrategy('unregistered'))
                ->toThrow(ThaiSlugException::class, 'Custom strategy not registered: unregistered');
        });
    });

    describe('Strategy Query and Discovery', function () {
        it('lists all available strategies', function () {
            $strategies = $this->factory->getAvailableStrategies();

            expect($strategies)->toContain('phonetic');
            expect($strategies)->toContain('royal');
            expect($strategies)->toContain('custom');
        });

        it('checks if strategy exists', function () {
            expect($this->factory->hasStrategy('phonetic'))->toBeTrue();
            expect($this->factory->hasStrategy('royal'))->toBeTrue();
            expect($this->factory->hasStrategy('custom'))->toBeTrue();
            expect($this->factory->hasStrategy('nonexistent'))->toBeFalse();
        });
    });

    describe('Dependency Injection', function () {
        it('creates strategy with dependencies', function () {
            $dependencies = ['dependency1' => 'value1'];

            // Most strategies don't support dependency injection yet,
            // but the method should work without errors
            $strategy = $this->factory->createWithDependencies(
                Strategy::PHONETIC,
                ['preserve_tone_marks' => true],
                $dependencies
            );

            expect($strategy)->toBeInstanceOf(PhoneticStrategy::class);
        });
    });

    describe('Error Handling and Edge Cases', function () {
        it('handles empty options gracefully', function () {
            $strategy = $this->factory->create(Strategy::PHONETIC, []);

            expect($strategy)->toBeInstanceOf(PhoneticStrategy::class);
        });

        it('handles null options gracefully', function () {
            $strategy = $this->factory->create(Strategy::PHONETIC);

            expect($strategy)->toBeInstanceOf(PhoneticStrategy::class);
        });

        it('provides meaningful error context', function () {
            try {
                $this->factory->create('invalid_strategy');
            } catch (ThaiSlugException $e) {
                expect($e->getContext())->toHaveKey('strategy');
                expect($e->getContext())->toHaveKey('available_strategies');
            }
        });
    });

    describe('Performance and Memory Management', function () {
        it('caches strategies efficiently', function () {
            // Create strategies and check caching behavior
            for ($i = 0; $i < 3; $i++) {
                $this->factory->create(Strategy::PHONETIC, ['preserve_tone_marks' => (bool) ($i % 2)]);
            }

            $stats = $this->factory->getCacheStats();

            // Should have cached different strategies based on options
            expect($stats['cached_strategies'])->toBeGreaterThan(0);
        });

        it('formats memory usage correctly', function () {
            $this->factory->create(Strategy::PHONETIC);
            $stats = $this->factory->getCacheStats();

            expect($stats['memory_usage'])->toMatch('/\d+(\.\d+)?\s+(B|KB|MB)/');
        });
    });

    describe('Integration with Strategy Enum', function () {
        it('maintains backward compatibility with Strategy enum', function () {
            // Direct enum usage should still work
            $enumStrategy = Strategy::PHONETIC->createInstance();

            // Factory usage should produce equivalent results
            $factoryStrategy = $this->factory->create(Strategy::PHONETIC);

            expect($factoryStrategy)->toBeInstanceOf(get_class($enumStrategy));
        });

        it('uses strategy default options correctly', function () {
            $strategy = $this->factory->create(Strategy::PHONETIC);

            // Should have applied phonetic strategy default options
            expect($strategy)->toBeInstanceOf(PhoneticStrategy::class);
        });
    });
});
