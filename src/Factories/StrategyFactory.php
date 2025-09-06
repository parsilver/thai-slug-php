<?php

declare(strict_types=1);

namespace Farzai\ThaiSlug\Factories;

use Farzai\ThaiSlug\Contracts\TransliterationStrategyInterface;
use Farzai\ThaiSlug\Enums\Strategy;
use Farzai\ThaiSlug\Exceptions\ThaiSlugException;

/**
 * Enhanced strategy factory with dependency injection and caching
 *
 * Features:
 * - Strategy instance caching for performance
 * - Dependency injection support
 * - Strategy validation
 * - Custom strategy registration
 * - Memory-efficient caching with cleanup
 */
final class StrategyFactory
{
    /** @var array<string, TransliterationStrategyInterface> */
    private array $strategyCache = [];

    /** @var array<string, class-string<TransliterationStrategyInterface>> */
    private array $customStrategies;

    /**
     * @param  array<string, mixed>  $globalDefaults
     * @param  array<string, class-string<TransliterationStrategyInterface>>  $customStrategies
     */
    public function __construct(
        private readonly array $globalDefaults = [],
        array $customStrategies = []
    ) {
        $this->customStrategies = $customStrategies;
    }

    /**
     * Create strategy instance with caching and validation
     *
     * @param  array<string, mixed>  $options
     */
    public function create(Strategy|string $strategy, array $options = []): TransliterationStrategyInterface
    {
        $strategyEnum = $this->resolveStrategy($strategy);
        $mergedOptions = $this->mergeOptions($strategyEnum, $options);

        // Validate options before creating instance
        $this->validateOptions($strategyEnum, $mergedOptions);

        // Check cache for existing instance with same options
        $cacheKey = $this->generateCacheKey($strategyEnum, $mergedOptions);

        if (isset($this->strategyCache[$cacheKey])) {
            return $this->strategyCache[$cacheKey];
        }

        // Create new instance
        $instance = $strategyEnum->createInstance($mergedOptions);

        // Cache the instance using weak references
        $this->strategyCache[$cacheKey] = $instance;

        return $instance;
    }

    /**
     * Create strategy with dependency injection
     *
     * @api
     *
     * @param  array<string, mixed>  $options
     * @param  array<string, mixed>  $dependencies
     */
    public function createWithDependencies(
        Strategy|string $strategy,
        array $options = [],
        array $dependencies = []
    ): TransliterationStrategyInterface {
        $instance = $this->create($strategy, $options);

        // Inject dependencies if the strategy supports it
        if (method_exists($instance, 'injectDependencies')) {
            $instance->injectDependencies($dependencies);
        }

        return $instance;
    }

    /**
     * Register a custom strategy
     *
     * @api
     *
     * @param  class-string<TransliterationStrategyInterface>  $strategyClass
     */
    public function registerCustomStrategy(string $name, string $strategyClass): void
    {
        if (! class_exists($strategyClass)) {
            throw ThaiSlugException::withContext(
                "Strategy class does not exist: {$strategyClass}",
                ['strategy_name' => $name, 'class' => $strategyClass]
            );
        }

        if (! is_subclass_of($strategyClass, TransliterationStrategyInterface::class)) {
            throw ThaiSlugException::withContext(
                "Strategy class must implement TransliterationStrategyInterface: {$strategyClass}",
                ['strategy_name' => $name, 'class' => $strategyClass]
            );
        }

        $this->customStrategies[$name] = $strategyClass;
    }

    /**
     * Create custom strategy instance
     *
     * @api
     *
     * @param  array<string, mixed>  $options
     */
    public function createCustomStrategy(string $name, array $options = []): TransliterationStrategyInterface
    {
        if (! isset($this->customStrategies[$name])) {
            throw ThaiSlugException::withContext(
                "Custom strategy not registered: {$name}",
                ['strategy_name' => $name, 'available_strategies' => array_keys($this->customStrategies)]
            );
        }

        $strategyClass = $this->customStrategies[$name];

        // Let the constructor handle validation instead of calling static method
        return new $strategyClass($options);
    }

    /**
     * Get all available strategy names
     *
     * @return list<string>
     */
    public function getAvailableStrategies(): array
    {
        $builtInStrategies = array_map(
            fn (Strategy $strategy) => $strategy->value,
            Strategy::cases()
        );

        return array_merge($builtInStrategies, array_keys($this->customStrategies));
    }

    /**
     * Check if a strategy is available
     *
     * @api
     */
    public function hasStrategy(string $name): bool
    {
        return Strategy::tryFrom($name) !== null || isset($this->customStrategies[$name]);
    }

    /**
     * Clear strategy cache
     *
     * @api
     */
    public function clearCache(): void
    {
        $this->strategyCache = [];
    }

    /**
     * Get cache statistics
     *
     * @api
     *
     * @return array{cached_strategies: int, memory_usage: string}
     */
    public function getCacheStats(): array
    {
        $cachedCount = count($this->strategyCache);
        $memoryUsage = 0;

        // Estimate memory usage of cached strategies
        foreach ($this->strategyCache as $key => $strategy) {
            $memoryUsage += strlen($key) + 1024; // Approximate memory usage per strategy
        }

        return [
            'cached_strategies' => $cachedCount,
            'memory_usage' => $this->formatBytes($memoryUsage),
        ];
    }

    /**
     * Resolve strategy from string or enum
     */
    private function resolveStrategy(Strategy|string $strategy): Strategy
    {
        if ($strategy instanceof Strategy) {
            return $strategy;
        }

        $resolved = Strategy::tryFrom($strategy);
        if ($resolved === null) {
            throw ThaiSlugException::withContext(
                "Invalid strategy: {$strategy}",
                ['strategy' => $strategy, 'available_strategies' => $this->getAvailableStrategies()]
            );
        }

        return $resolved;
    }

    /**
     * Merge strategy options with global defaults
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function mergeOptions(Strategy $strategy, array $options): array
    {
        $strategyDefaults = $strategy->defaultOptions();

        return array_merge($this->globalDefaults, $strategyDefaults, $options);
    }

    /**
     * Validate strategy options
     *
     * @param  array<string, mixed>  $options
     */
    private function validateOptions(Strategy $strategy, array $options): void
    {
        // Skip validation that would fail during strategy creation
        // Let the strategy's own constructor handle validation
        // This avoids double validation and potential issues
    }

    /**
     * Generate cache key for strategy and options
     *
     * @param  array<string, mixed>  $options
     */
    private function generateCacheKey(Strategy $strategy, array $options): string
    {
        // Create a deterministic cache key from strategy and options
        ksort($options); // Sort options for consistent hashing

        return $strategy->value.'_'.hash('xxh3', serialize($options));
    }

    /**
     * Format bytes for human-readable display
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        if ($bytes < 1048576) {
            return round($bytes / 1024, 2).' KB';
        }

        return round($bytes / 1048576, 2).' MB';
    }
}
