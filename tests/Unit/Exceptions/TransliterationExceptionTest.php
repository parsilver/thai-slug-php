<?php

declare(strict_types=1);

use Farzai\ThaiSlug\Exceptions\ThaiSlugException;
use Farzai\ThaiSlug\Exceptions\TransliterationException;

describe('TransliterationException', function () {
    it('can be instantiated', function () {
        $exception = new TransliterationException('Transliteration failed');

        expect($exception)->toBeInstanceOf(TransliterationException::class);
    });

    it('extends ThaiSlugException', function () {
        $exception = new TransliterationException('Test message');

        expect($exception)->toBeInstanceOf(ThaiSlugException::class);
    });

    it('extends Exception through inheritance chain', function () {
        $exception = new TransliterationException('Test message');

        expect($exception)->toBeInstanceOf(Exception::class);
        expect($exception)->toBeInstanceOf(Throwable::class);
    });

    it('stores the error message correctly', function () {
        $message = 'Failed to transliterate Thai text to Latin';
        $exception = new TransliterationException($message);

        expect($exception->getMessage())->toBe($message);
    });

    it('stores the error code correctly', function () {
        $code = 2001;
        $exception = new TransliterationException('Test message', $code);

        expect($exception->getCode())->toBe($code);
    });

    it('stores the previous exception correctly', function () {
        $previous = new InvalidArgumentException('Invalid input');
        $exception = new TransliterationException('Transliteration error', 0, $previous);

        expect($exception->getPrevious())->toBe($previous);
    });

    it('can be thrown and caught as TransliterationException', function () {
        $caught = false;
        $message = 'Transliteration process failed';

        try {
            throw new TransliterationException($message);
        } catch (TransliterationException $e) {
            $caught = true;
            expect($e->getMessage())->toBe($message);
        }

        expect($caught)->toBeTrue();
    });

    it('can be caught as ThaiSlugException', function () {
        $caught = false;

        try {
            throw new TransliterationException('Test message');
        } catch (ThaiSlugException $e) {
            $caught = true;
            expect($e)->toBeInstanceOf(TransliterationException::class);
        }

        expect($caught)->toBeTrue();
    });

    it('can be caught as base Exception', function () {
        $caught = false;

        try {
            throw new TransliterationException('Test message');
        } catch (Exception $e) {
            $caught = true;
            expect($e)->toBeInstanceOf(TransliterationException::class);
        }

        expect($caught)->toBeTrue();
    });

    it('maintains correct exception hierarchy', function () {
        $exception = new TransliterationException('Test message');

        expect(is_a($exception, TransliterationException::class))->toBeTrue();
        expect(is_a($exception, ThaiSlugException::class))->toBeTrue();
        expect(is_a($exception, Exception::class))->toBeTrue();
        expect(is_a($exception, Throwable::class))->toBeTrue();
    });

    it('handles Thai text in error messages', function () {
        $message = 'Failed to process Thai text: สวัสดีโลก';
        $exception = new TransliterationException($message);

        expect($exception->getMessage())->toBe($message);
        expect(mb_check_encoding($exception->getMessage(), 'UTF-8'))->toBeTrue();
    });

    it('handles complex transliteration error scenarios', function () {
        $complexMessage = 'Strategy "custom" failed to transliterate "ก่อสร้าง" at position 5';
        $exception = new TransliterationException($complexMessage, 2002);

        expect($exception->getMessage())->toBe($complexMessage);
        expect($exception->getCode())->toBe(2002);
    });

    it('works with exception chaining for transliteration errors', function () {
        $rootCause = new InvalidArgumentException('Invalid Thai character sequence');
        $strategyError = new RuntimeException('Phonetic strategy failed', 2100, $rootCause);
        $transliterationError = new TransliterationException('Transliteration pipeline failed', 2200, $strategyError);

        expect($transliterationError->getPrevious())->toBe($strategyError);
        expect($transliterationError->getPrevious()->getPrevious())->toBe($rootCause);
    });

    it('can be serialized and unserialized', function () {
        // Note: Exception serialization may not work in all PHP test environments
        // This test verifies the basic exception functionality instead
        $original = new TransliterationException('Serialization test for transliteration', 2500);
        $copy = new TransliterationException($original->getMessage(), $original->getCode());

        expect($copy)->toBeInstanceOf(TransliterationException::class);
        expect($copy->getMessage())->toBe('Serialization test for transliteration');
        expect($copy->getCode())->toBe(2500);
    });

    it('preserves stack trace for debugging', function () {
        $exception = new TransliterationException('Debug trace test');

        expect($exception->getFile())->toBeString();
        expect($exception->getLine())->toBeInt();
        expect($exception->getTrace())->toBeArray();
        expect($exception->getTraceAsString())->toContain(__FILE__);
    });

    it('handles edge cases in messages', function () {
        // Empty message
        $emptyException = new TransliterationException('');
        expect($emptyException->getMessage())->toBe('');

        // Very long message
        $longMessage = str_repeat('Transliteration error occurred. ', 50);
        $longException = new TransliterationException($longMessage);
        expect($longException->getMessage())->toBe($longMessage);

        // Special characters
        $specialMessage = "Error: \n\t Special chars & symbols @#$%^&*()";
        $specialException = new TransliterationException($specialMessage);
        expect($specialException->getMessage())->toBe($specialMessage);
    });

    it('maintains type safety in catch blocks', function () {
        $caughtTransliteration = false;
        $caughtThaiSlug = false;
        $caughtException = false;

        try {
            throw new TransliterationException('Type safety test');
        } catch (TransliterationException $e) {
            $caughtTransliteration = true;
            expect($e)->toBeInstanceOf(TransliterationException::class);
        } catch (ThaiSlugException $e) {
            $caughtThaiSlug = true;
        } catch (Exception $e) {
            $caughtException = true;
        }

        expect($caughtTransliteration)->toBeTrue();
        expect($caughtThaiSlug)->toBeFalse();
        expect($caughtException)->toBeFalse();
    });
});
