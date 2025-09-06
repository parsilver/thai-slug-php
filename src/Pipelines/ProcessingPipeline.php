<?php

declare(strict_types=1);

namespace Farzai\ThaiSlug\Pipelines;

use Farzai\ThaiSlug\Contracts\ProcessorInterface;
use Farzai\ThaiSlug\Exceptions\ThaiSlugException;

/**
 * Processing pipeline for chaining text processors
 *
 * Provides a flexible, extensible way to process text through multiple steps.
 * Supports conditional execution, processor priorities, and error handling.
 */
final class ProcessingPipeline
{
    /** @var array<ProcessorInterface> */
    private array $processors = [];

    /** @var array<string, mixed> */
    private array $defaultContext;

    private bool $haltOnError = false;

    private bool $enableProfiling = false;

    /** @var array<string, array{duration: float, memory: int}> */
    private array $profilingData = [];

    /**
     * @param  ProcessorInterface[]  $processors
     * @param  array<string, mixed>  $defaultContext
     */
    public function __construct(
        array $processors = [],
        array $defaultContext = []
    ) {
        foreach ($processors as $processor) {
            $this->addProcessor($processor);
        }

        $this->defaultContext = $defaultContext;
    }

    /**
     * Add a processor to the pipeline
     */
    public function addProcessor(ProcessorInterface $processor): self
    {
        $this->processors[] = $processor;

        // Re-sort processors by priority
        $this->sortProcessorsByPriority();

        return $this;
    }

    /**
     * Remove a processor by name
     *
     * @api
     */
    public function removeProcessor(string $name): self
    {
        $this->processors = array_filter(
            $this->processors,
            fn (ProcessorInterface $processor) => $processor->getName() !== $name
        );

        return $this;
    }

    /**
     * Get all processors in the pipeline
     *
     * @return ProcessorInterface[]
     *
     * @api
     */
    public function getProcessors(): array
    {
        return $this->processors;
    }

    /**
     * Get a processor by name
     */
    public function getProcessor(string $name): ?ProcessorInterface
    {
        foreach ($this->processors as $processor) {
            if ($processor->getName() === $name) {
                return $processor;
            }
        }

        return null;
    }

    /**
     * Check if a processor exists in the pipeline
     *
     * @api
     */
    public function hasProcessor(string $name): bool
    {
        return $this->getProcessor($name) !== null;
    }

    /**
     * Set whether to halt processing on the first error
     *
     * @api
     */
    public function haltOnError(bool $halt = true): self
    {
        $this->haltOnError = $halt;

        return $this;
    }

    /**
     * Enable profiling to track processor performance
     *
     * @api
     */
    public function enableProfiling(bool $enable = true): self
    {
        $this->enableProfiling = $enable;

        if ($enable && ! $this->profilingData) {
            $this->profilingData = [];
        }

        return $this;
    }

    /**
     * Process text through the entire pipeline
     *
     * @param  array<string, mixed>  $additionalContext
     *
     * @api
     */
    public function process(string $text, array $additionalContext = []): string
    {
        $context = array_merge($this->defaultContext, $additionalContext);
        $result = $text;

        foreach ($this->processors as $processor) {
            if (! $processor->shouldProcess($context)) {
                continue;
            }

            try {
                if ($this->enableProfiling) {
                    $result = $this->processWithProfiling($processor, $result, $context);
                } else {
                    $result = $processor->process($result, $context);
                }
            } catch (\Throwable $e) {
                if ($this->haltOnError) {
                    throw ThaiSlugException::withContext(
                        "Processing failed in processor: {$processor->getName()}",
                        [
                            'processor' => $processor->getName(),
                            'input_text' => $text,
                            'current_result' => $result,
                            'context' => $context,
                            'error' => $e->getMessage(),
                        ],
                        previous: $e
                    );
                }

                // Continue processing - error handling is left to the user
                // Users can implement their own error handling by wrapping the pipeline
            }
        }

        return $result;
    }

    /**
     * Create a new pipeline with conditional processors
     *
     * @param  array<string, ProcessorInterface>  $conditionalProcessors
     * @param  array<string, mixed>  $context
     * @return static
     *
     * @api
     */
    public static function createConditional(array $conditionalProcessors, array $context = []): self
    {
        $pipeline = new self([], $context);

        foreach ($conditionalProcessors as $condition => $processor) {
            if (self::evaluateCondition($condition, $context)) {
                $pipeline->addProcessor($processor);
            }
        }

        return $pipeline;
    }

    /**
     * Create a pipeline for slug generation with default processors
     *
     * @param  array<string, mixed>  $context
     * @return static
     *
     * @api
     */
    public static function createSlugPipeline(array $context = []): self
    {
        $processors = [
            new \Farzai\ThaiSlug\Processors\NormalizationProcessor,
            new \Farzai\ThaiSlug\Processors\TransliterationProcessor($context),
            new \Farzai\ThaiSlug\Processors\UrlSafetyProcessor,
        ];

        return new self($processors, $context);
    }

    /**
     * Get profiling data for performance analysis
     *
     * @api
     *
     * @return array<string, array{duration: float, memory: int, executions: int}>
     */
    public function getProfilingData(): array
    {
        if (! $this->enableProfiling) {
            return [];
        }

        // Add execution counts
        $summary = [];
        foreach ($this->profilingData as $processorName => $data) {
            if (! isset($summary[$processorName])) {
                $summary[$processorName] = [
                    'duration' => 0.0,
                    'memory' => 0,
                    'executions' => 0,
                ];
            }

            $summary[$processorName]['duration'] += $data['duration'];
            $summary[$processorName]['memory'] = max($summary[$processorName]['memory'], $data['memory']);
            $summary[$processorName]['executions']++;
        }

        return $summary;
    }

    /**
     * Clear profiling data
     *
     * @api
     */
    public function clearProfilingData(): self
    {
        $this->profilingData = [];

        return $this;
    }

    /**
     * Get pipeline statistics
     *
     * @api
     *
     * @return array{processor_count: int, active_processors: int, profiling_enabled: bool, halt_on_error: bool}
     */
    public function getStatistics(): array
    {
        $activeCount = 0;
        foreach ($this->processors as $processor) {
            if ($processor->shouldProcess($this->defaultContext)) {
                $activeCount++;
            }
        }

        return [
            'processor_count' => count($this->processors),
            'active_processors' => $activeCount,
            'profiling_enabled' => $this->enableProfiling,
            'halt_on_error' => $this->haltOnError,
        ];
    }

    /**
     * Sort processors by priority (higher priority first)
     */
    private function sortProcessorsByPriority(): void
    {
        usort(
            $this->processors,
            fn (ProcessorInterface $a, ProcessorInterface $b) => $b->getPriority() <=> $a->getPriority()
        );
    }

    /**
     * Process with performance profiling
     *
     * @param  array<string, mixed>  $context
     */
    private function processWithProfiling(ProcessorInterface $processor, string $text, array $context): string
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        $result = $processor->process($text, $context);

        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);

        $this->profilingData[$processor->getName()] = [
            'duration' => $endTime - $startTime,
            'memory' => $endMemory - $startMemory,
        ];

        return $result;
    }

    /**
     * Evaluate a simple condition string
     *
     * @param  array<string, mixed>  $context
     */
    private static function evaluateCondition(string $condition, array $context): bool
    {
        // Simple condition evaluation - can be expanded as needed
        if (str_starts_with($condition, 'context.')) {
            $key = substr($condition, 8); // Remove 'context.' prefix

            return isset($context[$key]) && (bool) $context[$key];
        }

        if ($condition === 'always') {
            return true;
        }

        if ($condition === 'never') {
            return false;
        }

        return false;
    }
}
