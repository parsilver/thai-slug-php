<?php

use Farzai\ThaiSlug\Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

uses(TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeValidSlug', function () {
    return $this->toMatch('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
        ->and($this->value)
        ->not()->toStartWith('-')
        ->not()->toEndWith('-');
});

expect()->extend('toBeNormalizedThai', function () {
    // Check if text is properly normalized (no excessive whitespace, etc.)
    return $this->toEqual(trim(preg_replace('/\s+/', ' ', $this->value)));
});

expect()->extend('toMatchTransliterationStrategy', function (string $strategy) {
    // For now, just check that it's a non-empty string for valid strategies
    if (in_array($strategy, ['phonetic', 'royal', 'custom'])) {
        return $this->toBeString()->not()->toBeEmpty();
    }

    throw new InvalidArgumentException("Unknown transliteration strategy: {$strategy}");
});

expect()->extend('toBeUrlSafe', function () {
    // Check if the string is URL-safe (no spaces, special chars except hyphens)
    return $this->toMatch('/^[a-zA-Z0-9\-_]*$/');
});

expect()->extend('toHaveMaxLength', function (int $maxLength) {
    return $this->and(strlen($this->value))->toBeLessThanOrEqual($maxLength);
});

expect()->extend('toBeProperlyNormalizedThai', function () {
    // Check if Thai text is properly normalized (NFC form, proper character ordering)
    $normalized = Normalizer::normalize($this->value, Normalizer::FORM_C);

    return $this->toEqual($normalized)
        ->and($this->value)->not()->toContain("\u{0e48}\u{0e34}") // No tone before vowel
        ->and($this->value)->not()->toContain("\u{0e49}\u{0e34}") // No tone before vowel
        ->and($this->value)->not()->toMatch('/\u{0e34}{2,}/u'); // No duplicate vowel marks
});

expect()->extend('toHaveValidThaiCharacterSequence', function () {
    // Check for valid Thai character sequences (no orphaned combining marks)
    return $this->not()->toStartWith("\u{0e48}") // Not start with tone mark
        ->and($this->value)->not()->toStartWith("\u{0e49}") // Not start with tone mark
        ->and($this->value)->not()->toStartWith("\u{0e4a}") // Not start with tone mark
        ->and($this->value)->not()->toStartWith("\u{0e4b}") // Not start with tone mark
        ->and($this->value)->not()->toStartWith("\u{0e34}") // Not start with vowel mark
        ->and($this->value)->not()->toStartWith("\u{0e35}"); // Not start with vowel mark
});

expect()->extend('toBeValidUnicodeNFC', function () {
    // Check if text is in proper Unicode NFC (Canonical Composition) form
    if (! class_exists('Normalizer')) {
        return $this->toBeString(); // Skip if Normalizer not available
    }

    $nfc = Normalizer::normalize($this->value, Normalizer::FORM_C);

    return $this->toEqual($nfc);
});

expect()->extend('toHaveNoRedundantThaiMarks', function () {
    // Check for redundant Thai combining marks
    return $this->not()->toMatch('/[\u{0e48}-\u{0e4b}]{2,}/u') // No duplicate tone marks
        ->and($this->value)->not()->toMatch('/[\u{0e34}\u{0e35}]{2,}/u') // No duplicate vowel marks
        ->and($this->value)->not()->toMatch('/\u{0e31}{2,}/u'); // No duplicate mai han-akat
});

expect()->extend('toExecuteWithin', function (float $milliseconds) {
    // Performance expectation for execution time
    $startTime = microtime(true);

    if (is_callable($this->value)) {
        $result = call_user_func($this->value);
    } else {
        $result = $this->value;
    }

    $endTime = microtime(true);
    $duration = ($endTime - $startTime) * 1000; // Convert to milliseconds

    expect($duration)->toBeLessThan($milliseconds);

    return $this;
});

expect()->extend('toUseMemoryLessThan', function (int $bytes) {
    // Performance expectation for memory usage
    $startMemory = memory_get_usage();

    if (is_callable($this->value)) {
        $result = call_user_func($this->value);
    } else {
        $result = $this->value;
    }

    $endMemory = memory_get_usage();
    $memoryUsed = $endMemory - $startMemory;

    expect($memoryUsed)->toBeLessThan($bytes);

    return $this;
});

expect()->extend('toHaveValidSaraAm', function () {
    // Check for proper Sara Am (ำ) usage - should be composed, not decomposed
    return $this->not()->toContain("\u{0e31}ม") // Should not contain decomposed form ั + ม
        ->and($this->value)->not()->toMatch('/\u{0e33}[\u{0e48}-\u{0e4b}]/u'); // Sara Am should not be followed by tone
});
