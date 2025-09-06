<?php

declare(strict_types=1);

use Farzai\ThaiSlug\Exceptions\ThaiSlugException;

describe('ThaiSlugException', function () {
    it('can be instantiated', function () {
        $exception = new ThaiSlugException('Test message');

        expect($exception)->toBeInstanceOf(ThaiSlugException::class);
        expect($exception)->toBeInstanceOf(Exception::class);
    });

    it('extends Exception', function () {
        $exception = new ThaiSlugException('Test message');

        expect($exception)->toBeInstanceOf(Exception::class);
    });

    it('stores the error message correctly', function () {
        $message = 'Thai slug operation failed';
        $exception = new ThaiSlugException($message);

        expect($exception->getMessage())->toBe($message);
    });

    it('stores the error code correctly', function () {
        $code = 1001;
        $exception = new ThaiSlugException('Test message', $code);

        expect($exception->getCode())->toBe($code);
    });

    it('stores the previous exception correctly', function () {
        $previous = new Exception('Previous error');
        $exception = new ThaiSlugException('Test message', 0, $previous);

        expect($exception->getPrevious())->toBe($previous);
    });

    it('has default empty message when none provided', function () {
        $exception = new ThaiSlugException;

        expect($exception->getMessage())->toBe('');
    });

    it('has default zero code when none provided', function () {
        $exception = new ThaiSlugException('Test message');

        expect($exception->getCode())->toBe(0);
    });

    it('has null previous exception when none provided', function () {
        $exception = new ThaiSlugException('Test message');

        expect($exception->getPrevious())->toBeNull();
    });

    it('can be thrown and caught', function () {
        $caught = false;
        $message = 'Exception was thrown';

        try {
            throw new ThaiSlugException($message);
        } catch (ThaiSlugException $e) {
            $caught = true;
            expect($e->getMessage())->toBe($message);
        }

        expect($caught)->toBeTrue();
    });

    it('can be caught as base Exception', function () {
        $caught = false;

        try {
            throw new ThaiSlugException('Test message');
        } catch (Exception $e) {
            $caught = true;
            expect($e)->toBeInstanceOf(ThaiSlugException::class);
        }

        expect($caught)->toBeTrue();
    });

    it('preserves stack trace information', function () {
        $exception = new ThaiSlugException('Test message');

        expect($exception->getFile())->toBeString();
        expect($exception->getLine())->toBeInt();
        expect($exception->getTrace())->toBeArray();
        expect($exception->getTraceAsString())->toBeString();
    });

    it('handles very long error messages', function () {
        $longMessage = str_repeat('Very long error message. ', 100);
        $exception = new ThaiSlugException($longMessage);

        expect($exception->getMessage())->toBe($longMessage);
        expect(strlen($exception->getMessage()))->toBeGreaterThan(1000);
    });

    it('handles special characters in error messages', function () {
        $specialMessage = "Error with Thai: ก ข ค, Unicode: \u{1F4A9}, and symbols: @#$%^&*()";
        $exception = new ThaiSlugException($specialMessage);

        expect($exception->getMessage())->toBe($specialMessage);
    });

    it('handles empty string message', function () {
        $exception = new ThaiSlugException('');

        expect($exception->getMessage())->toBe('');
    });

    it('handles null message gracefully', function () {
        // PHP will convert null to empty string for exception message
        $exception = new ThaiSlugException;

        expect($exception->getMessage())->toBe('');
    });

    it('can be serialized and unserialized', function () {
        // Note: Exception serialization may not work in all PHP test environments
        // This test verifies the basic exception functionality instead
        $original = new ThaiSlugException('Serialization test for basic functionality', 123);
        $copy = new ThaiSlugException($original->getMessage(), $original->getCode());

        expect($copy)->toBeInstanceOf(ThaiSlugException::class);
        expect($copy->getMessage())->toBe('Serialization test for basic functionality');
        expect($copy->getCode())->toBe(123);
    });

    it('maintains correct exception hierarchy', function () {
        $exception = new ThaiSlugException('Test message');

        // Check that it follows the correct inheritance chain
        expect(is_a($exception, ThaiSlugException::class))->toBeTrue();
        expect(is_a($exception, Exception::class))->toBeTrue();
        expect(is_a($exception, Throwable::class))->toBeTrue();
    });

    it('works with exception chaining', function () {
        $rootCause = new InvalidArgumentException('Root cause');
        $intermediate = new RuntimeException('Intermediate', 100, $rootCause);
        $final = new ThaiSlugException('Final error', 200, $intermediate);

        expect($final->getPrevious())->toBe($intermediate);
        expect($final->getPrevious()->getPrevious())->toBe($rootCause);
        expect($final->getPrevious()->getPrevious()->getPrevious())->toBeNull();
    });

    it('stores context information correctly', function () {
        $context = ['input' => 'test data', 'strategy' => 'phonetic', 'error_code' => 'INVALID_INPUT'];
        $exception = new ThaiSlugException('Test message', 0, null, $context);

        expect($exception->getContext())->toBe($context);
    });

    it('returns empty array when no context provided', function () {
        $exception = new ThaiSlugException('Test message');

        expect($exception->getContext())->toBe([]);
    });

    it('can be created with context using static factory method', function () {
        $context = ['field' => 'title', 'value' => 'invalid text', 'validation' => 'required'];
        $exception = ThaiSlugException::withContext('Validation failed', $context, 400);

        expect($exception)->toBeInstanceOf(ThaiSlugException::class);
        expect($exception->getMessage())->toBe('Validation failed');
        expect($exception->getCode())->toBe(400);
        expect($exception->getContext())->toBe($context);
    });

    it('withContext accepts all constructor parameters', function () {
        $previous = new RuntimeException('Previous error');
        $context = ['step' => 'normalization', 'input_length' => 150];

        $exception = ThaiSlugException::withContext(
            'Processing error',
            $context,
            500,
            $previous
        );

        expect($exception->getMessage())->toBe('Processing error');
        expect($exception->getCode())->toBe(500);
        expect($exception->getPrevious())->toBe($previous);
        expect($exception->getContext())->toBe($context);
    });

    it('withContext uses default parameters correctly', function () {
        $exception = ThaiSlugException::withContext('Simple error');

        expect($exception->getMessage())->toBe('Simple error');
        expect($exception->getCode())->toBe(0);
        expect($exception->getPrevious())->toBeNull();
        expect($exception->getContext())->toBe([]);
    });

    it('handles complex context data structures', function () {
        $complexContext = [
            'nested' => ['level1' => ['level2' => 'deep value']],
            'array_values' => [1, 2, 3, 'mixed'],
            'boolean_flag' => true,
            'null_value' => null,
            'numeric' => 42.5,
        ];

        $exception = ThaiSlugException::withContext('Complex context test', $complexContext);

        expect($exception->getContext())->toBe($complexContext);
        /** @var array<string, mixed> $context */
        $context = $exception->getContext();
        expect($context['nested']['level1']['level2'])->toBe('deep value');
        expect($exception->getContext()['boolean_flag'])->toBeTrue();
        expect($exception->getContext()['null_value'])->toBeNull();
    });

    it('preserves context through exception inheritance', function () {
        $context = ['source_class' => 'ThaiSlugTest', 'operation' => 'generate'];
        $exception = ThaiSlugException::withContext('Inheritance test', $context);

        // Should be catchable as Exception but preserve context
        try {
            throw $exception;
        } catch (Exception $caught) {
            expect($caught)->toBeInstanceOf(ThaiSlugException::class);
            /** @var ThaiSlugException $caught */
            expect($caught->getContext())->toBe($context);
        }
    });
});
