<?php

declare(strict_types=1);

use Farzai\ThaiSlug\Exceptions\InvalidConfigurationException;
use Farzai\ThaiSlug\Exceptions\ThaiSlugException;

describe('InvalidConfigurationException', function () {
    it('is an instance of ThaiSlugException', function () {
        $exception = new InvalidConfigurationException('Test message');

        expect($exception)->toBeInstanceOf(ThaiSlugException::class);
    });

    it('extends Exception', function () {
        $exception = new InvalidConfigurationException('Test message');

        expect($exception)->toBeInstanceOf(Exception::class);
    });

    it('stores the error message correctly', function () {
        $message = 'Configuration validation failed';
        $exception = new InvalidConfigurationException($message);

        expect($exception->getMessage())->toBe($message);
    });

    it('stores the error code correctly', function () {
        $code = 1001;
        $exception = new InvalidConfigurationException('Test message', $code);

        expect($exception->getCode())->toBe($code);
    });

    it('stores the previous exception correctly', function () {
        $previous = new Exception('Previous error');
        $exception = new InvalidConfigurationException('Test message', 0, $previous);

        expect($exception->getPrevious())->toBe($previous);
    });

    it('can be created with factory method for field validation', function () {
        $exception = InvalidConfigurationException::forInvalidField('max_length', 'string', 'integer');

        expect($exception->getMessage())->toBe('Invalid field "max_length": expected integer, got string');
    });

    it('can be created with factory method for out of range values', function () {
        $exception = InvalidConfigurationException::forOutOfRange('max_length', 100, 1, 50);

        expect($exception->getMessage())->toBe('Field "max_length" value 100 is out of range (min: 1, max: 50)');
    });

    it('can be created with factory method for missing required fields', function () {
        $exception = InvalidConfigurationException::forMissingField('strategy');

        expect($exception->getMessage())->toBe('Required field "strategy" is missing');
    });

    it('can be created with factory method for unknown options', function () {
        $exception = InvalidConfigurationException::forUnknownOption('invalid_option', ['valid1', 'valid2']);

        expect($exception->getMessage())->toBe('Unknown option "invalid_option". Valid options are: valid1, valid2');
    });

    it('can be created with factory method for invalid array structure', function () {
        $exception = InvalidConfigurationException::forInvalidArrayStructure('rules', 'Expected associative array with string keys');

        expect($exception->getMessage())->toBe('Invalid array structure for "rules": Expected associative array with string keys');
    });

    it('provides context information when available', function () {
        $exception = InvalidConfigurationException::forInvalidField('cache.max_size', '-100', 'positive integer');
        $context = $exception->getContext();

        expect($context)->toBeArray();
        expect($context)->toHaveKey('field');
        expect($context)->toHaveKey('value');
        expect($context)->toHaveKey('expected_type');
        expect($context['field'])->toBe('cache.max_size');
    });

    it('handles null values in context gracefully', function () {
        $exception = InvalidConfigurationException::forInvalidField('test_field', null, 'string');

        expect($exception->getMessage())->toContain('null');
    });

    it('handles boolean values in context correctly', function () {
        $exception = InvalidConfigurationException::forInvalidField('test_field', true, 'string');

        expect($exception->getMessage())->toContain('bool');
    });

    it('handles array values in context correctly', function () {
        $exception = InvalidConfigurationException::forInvalidField('test_field', ['key' => 'value'], 'string');

        expect($exception->getMessage())->toContain('array');
    });

    it('provides helpful suggestions for common mistakes', function () {
        $exception = InvalidConfigurationException::forUnknownOption('max_len', ['max_length', 'separator']);

        expect($exception->getMessage())->toContain('max_length');
        expect($exception->getSuggestion())->toBe('Did you mean "max_length"?');
    });

    it('can chain multiple validation errors', function () {
        $previous = InvalidConfigurationException::forInvalidField('max_length', 'string', 'integer');
        $exception = InvalidConfigurationException::forMissingField('strategy', 0, $previous);

        expect($exception->getPrevious())->toBe($previous);
        expect($exception->getAllErrors())->toHaveCount(2);
    });
});
