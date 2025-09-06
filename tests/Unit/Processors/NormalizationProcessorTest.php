<?php

declare(strict_types=1);

use Farzai\ThaiSlug\Contracts\ProcessorInterface;
use Farzai\ThaiSlug\Processors\NormalizationProcessor;

describe('NormalizationProcessor', function () {
    beforeEach(function () {
        /** @var NormalizationProcessor $processor */
        $this->processor = new NormalizationProcessor;
    });

    describe('Interface Implementation', function () {
        it('implements ProcessorInterface', function () {
            expect($this->processor)->toBeInstanceOf(ProcessorInterface::class);
        });

        it('has correct processor name', function () {
            expect($this->processor->getName())->toBe('normalization');
        });

        it('has high priority for first execution', function () {
            expect($this->processor->getPriority())->toBe(100);
        });

        it('should process by default', function () {
            expect($this->processor->shouldProcess())->toBeTrue();
        });

        it('respects context disable flag', function () {
            expect($this->processor->shouldProcess(['normalize' => false]))->toBeFalse();
        });
    });

    describe('Text Processing', function () {
        it('processes Thai text normalization', function () {
            $result = $this->processor->process('สวัสดี   โลก');

            expect($result)->toBeString();
            expect($result)->not()->toContain('   '); // Should normalize excessive spaces
        });

        it('handles empty text', function () {
            $result = $this->processor->process('');

            expect($result)->toBe('');
        });

        it('handles whitespace-only text', function () {
            $result = $this->processor->process('   ');

            expect($result)->toBeString();
        });

        it('normalizes Thai character sequences', function () {
            // Test with complex Thai text that needs normalization
            $complexText = 'ก็อปปี้  ข้าว   โฮม';
            $result = $this->processor->process($complexText);

            expect($result)->toBeString();
            expect(strlen($result))->toBeLessThan(strlen($complexText)); // Should be more compact
        });

        it('preserves Latin text', function () {
            $result = $this->processor->process('Hello World');

            expect($result)->toContain('Hello');
            expect($result)->toContain('World');
        });
    });

    describe('Context Processing', function () {
        it('processes with empty context', function () {
            $result = $this->processor->process('สวัสดี', []);

            expect($result)->toBeString();
        });

        it('processes with additional context data', function () {
            $context = [
                'language' => 'thai',
                'extra_data' => 'test',
            ];

            $result = $this->processor->process('สวัสดี', $context);

            expect($result)->toBeString();
        });
    });

    describe('Performance', function () {
        it('processes short text quickly', function () {
            expect(fn () => $this->processor->process('สวัสดี'))
                ->toExecuteWithin(10); // 10ms
        });

        it('processes medium text efficiently', function () {
            $mediumText = str_repeat('สวัสดีโลก ', 50);

            expect(fn () => $this->processor->process($mediumText))
                ->toExecuteWithin(50); // 50ms
        });
    });

    describe('Error Handling', function () {
        it('handles mixed encoding gracefully', function () {
            // Mix of valid and potentially problematic characters
            $mixedText = 'สวัสดี Hello Мир 🌍';

            $result = $this->processor->process($mixedText);

            expect($result)->toBeString();
        });

        it('handles very long text', function () {
            $longText = str_repeat('สวัสดีโลกทดสอบ ', 200);

            $result = $this->processor->process($longText);

            expect($result)->toBeString();
            expect(strlen($result))->toBeGreaterThan(0);
        });
    });
});
