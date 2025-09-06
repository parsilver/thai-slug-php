<?php

declare(strict_types=1);

namespace Farzai\ThaiSlug\Contracts;

/**
 * Interface for text processing steps in the pipeline
 *
 * Each processor represents a single step in the text processing pipeline,
 * allowing for composable and testable processing logic.
 */
interface ProcessorInterface
{
    /**
     * Process the input text and return the processed result
     *
     * @param  string  $text  The input text to process
     * @param  array<string, mixed>  $context  Additional context for processing
     * @return string The processed text
     */
    public function process(string $text, array $context = []): string;

    /**
     * Get the processor name for identification and debugging
     */
    public function getName(): string;

    /**
     * Check if this processor should be executed for the given context
     *
     * @param  array<string, mixed>  $context  Processing context
     */
    public function shouldProcess(array $context = []): bool;

    /**
     * Get processor priority (higher numbers execute first)
     *
     * This allows for ordering processors when multiple processors
     * are available for the same processing step.
     */
    public function getPriority(): int;
}
