<?php

declare(strict_types=1);

namespace Farzai\ThaiSlug\Exceptions;

class InvalidConfigurationException extends ThaiSlugException
{
    /** @var array<string, mixed> */
    private array $context = [];

    private ?string $suggestion = null;

    /** @var array<string> */
    private array $allErrors = [];

    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        // Collect all errors in the chain
        $this->allErrors[] = $message;
        if ($previous instanceof self) {
            $this->allErrors = array_merge($this->allErrors, $previous->getAllErrors());
        }
    }

    /**
     * @api
     */
    public static function forInvalidField(string $field, mixed $value, string $expectedType): self
    {
        $actualType = get_debug_type($value);
        $message = sprintf('Invalid field "%s": expected %s, got %s', $field, $expectedType, $actualType);

        $exception = new self($message);
        $exception->context = [
            'field' => $field,
            'value' => $value,
            'expected_type' => $expectedType,
            'actual_type' => $actualType,
        ];

        return $exception;
    }

    /**
     * @api
     */
    public static function forOutOfRange(string $field, mixed $value, int $min, int $max): self
    {
        $valueString = is_object($value) && method_exists($value, '__toString')
            ? (string) $value
            : (is_scalar($value) ? (string) $value : gettype($value));
        $message = sprintf('Field "%s" value %s is out of range (min: %d, max: %d)', $field, $valueString, $min, $max);

        $exception = new self($message);
        $exception->context = [
            'field' => $field,
            'value' => $value,
            'min' => $min,
            'max' => $max,
        ];

        return $exception;
    }

    /**
     * @api
     */
    public static function forMissingField(string $field, int $code = 0, ?\Throwable $previous = null): self
    {
        $message = sprintf('Required field "%s" is missing', $field);

        $exception = new self($message, $code, $previous);
        $exception->context = [
            'field' => $field,
            'type' => 'missing_field',
        ];

        return $exception;
    }

    /**
     * @param  array<string>  $validOptions
     *
     * @api
     */
    public static function forUnknownOption(string $option, array $validOptions, ?string $customMessage = null): self
    {
        $message = $customMessage ?? sprintf(
            'Unknown option "%s". Valid options are: %s',
            $option,
            implode(', ', $validOptions)
        );

        $exception = new self($message);
        $exception->context = [
            'option' => $option,
            'valid_options' => $validOptions,
        ];

        // Try to suggest a similar option
        $exception->suggestion = self::findSimilarOption($option, $validOptions);

        return $exception;
    }

    /**
     * @api
     */
    public static function forInvalidArrayStructure(string $field, string $expectedStructure): self
    {
        $message = sprintf('Invalid array structure for "%s": %s', $field, $expectedStructure);

        $exception = new self($message);
        $exception->context = [
            'field' => $field,
            'expected_structure' => $expectedStructure,
        ];

        return $exception;
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @api
     */
    public function getSuggestion(): ?string
    {
        return $this->suggestion;
    }

    /**
     * @return array<string>
     */
    public function getAllErrors(): array
    {
        return $this->allErrors;
    }

    /**
     * @param  array<string>  $validOptions
     */
    private static function findSimilarOption(string $option, array $validOptions): ?string
    {
        $similarities = [];

        foreach ($validOptions as $validOption) {
            similar_text($option, (string) $validOption, $percent);
            if ($percent > 60) { // 60% similarity threshold
                $similarities[(string) $validOption] = $percent;
            }
        }

        if (empty($similarities)) {
            return null;
        }

        arsort($similarities);
        $mostSimilar = array_key_first($similarities);

        return "Did you mean \"{$mostSimilar}\"?";
    }
}
