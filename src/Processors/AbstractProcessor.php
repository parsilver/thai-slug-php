<?php

declare(strict_types=1);

namespace Farzai\ThaiSlug\Processors;

use Farzai\ThaiSlug\Contracts\ProcessorInterface;

/**
 * Abstract base class for processors
 *
 * Provides common functionality and sensible defaults for custom processors.
 */
abstract class AbstractProcessor implements ProcessorInterface
{
    /** @var array<string, mixed> */
    protected array $options = [];

    /**
     * @param  array<string, mixed>  $options
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->getDefaultOptions(), $options);
        $this->validateOptions($this->options);
    }

    /**
     * Get default options for this processor
     *
     * @return array<string, mixed>
     */
    protected function getDefaultOptions(): array
    {
        return [];
    }

    /**
     * Validate processor options
     *
     * @param  array<string, mixed>  $options
     */
    protected function validateOptions(array $options): bool
    {
        // Default implementation - always valid
        return true;
    }

    public function shouldProcess(array $context = []): bool
    {
        // Check if this processor is enabled in context
        $processorKey = str_replace('_', '', $this->getName());

        if (isset($context["enable_{$processorKey}"])) {
            return (bool) $context["enable_{$processorKey}"];
        }

        if (isset($context["disable_{$processorKey}"])) {
            return ! (bool) $context["disable_{$processorKey}"];
        }

        // Default: process if not explicitly disabled
        return true;
    }

    public function getPriority(): int
    {
        // Default priority
        return 50;
    }

    /**
     * Get processor option value
     */
    protected function getOption(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * Check if processor has option
     */
    protected function hasOption(string $key): bool
    {
        return isset($this->options[$key]);
    }

    /**
     * Get all processor options
     *
     * @return array<string, mixed>
     */
    protected function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Merge context values with processor options
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function mergeContextOptions(array $context): array
    {
        return array_merge($this->options, $context);
    }
}
