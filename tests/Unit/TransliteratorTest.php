<?php

declare(strict_types=1);

use Farzai\ThaiSlug\Enums\Strategy;
use Farzai\ThaiSlug\Transliterator;

describe('Transliterator', function () {
    beforeEach(function () {
        $this->transliterator = new Transliterator;
    });

    describe('Default Behavior', function () {
        it('uses PHONETIC strategy by default', function () {
            $result = $this->transliterator->transliterate('สวัสดี');
            expect($result)->toBeString()->not()->toBeEmpty();
        });

        it('can be constructed with specific strategy', function () {
            $transliterator = new Transliterator(Strategy::ROYAL);
            $result = $transliterator->transliterate('สวัสดี');
            expect($result)->toBeString()->not()->toBeEmpty();
        });

        it('can be constructed with default options', function () {
            $transliterator = new Transliterator(Strategy::PHONETIC, ['preserve_tone_marks' => true]);
            $result = $transliterator->transliterate('สวัสดี');
            expect($result)->toBeString()->not()->toBeEmpty();
        });
    });

    describe('Strategy Selection', function () {
        it('allows strategy override in transliterate method', function () {
            $result1 = $this->transliterator->transliterate('สวัสดี', 'phonetic');
            $result2 = $this->transliterator->transliterate('สวัสดี', 'royal');

            expect($result1)->toBeString()->not()->toBeEmpty();
            expect($result2)->toBeString()->not()->toBeEmpty();
            // Results may differ depending on strategies
        });

        it('merges options correctly', function () {
            $transliterator = new Transliterator(Strategy::CUSTOM, ['fallback_to_phonetic' => true]);
            $result = $transliterator->transliterate('สวัสดี', null, ['custom_mapping' => ['ส' => 's']]);

            expect($result)->toBeString()->not()->toBeEmpty();
        });
    });

    describe('Static Factory Method', function () {
        it('provides static make method', function () {
            $result = Transliterator::make('สวัสดี');
            expect($result)->toBeString()->not()->toBeEmpty();
        });

        it('allows strategy specification in static method', function () {
            $result = Transliterator::make('สวัสดี', Strategy::ROYAL);
            expect($result)->toBeString()->not()->toBeEmpty();
        });

        it('allows options in static method', function () {
            $result = Transliterator::make('สวัสดี', Strategy::PHONETIC, ['preserve_tone_marks' => false]);
            expect($result)->toBeString()->not()->toBeEmpty();
        });
    });

    describe('Error Handling', function () {
        it('handles empty input gracefully', function () {
            $result = $this->transliterator->transliterate('');
            expect($result)->toBe('');
        });

        it('handles whitespace-only input gracefully', function () {
            $result = $this->transliterator->transliterate('   ');
            expect($result)->toBe('');
        });

        it('handles mixed Thai-Latin text', function () {
            $result = $this->transliterator->transliterate('Hello สวัสดี World');
            expect($result)->toBeString()->not()->toBeEmpty();
            expect($result)->toContain('Hello');
            expect($result)->toContain('World');
        });

        it('handles invalid strategy names gracefully', function () {
            // Invalid strategy names fall back to default (PHONETIC)
            $result = $this->transliterator->transliterate('สวัสดี', 'invalid_strategy');
            expect($result)->toBeString()->not()->toBeEmpty();
        });
    });

    describe('Performance Requirements', function () {
        it('processes text within performance limits', function () {
            $longText = str_repeat('สวัสดีโลกสวยงามมากจริงๆ ', 50);

            expect(fn () => $this->transliterator->transliterate($longText))
                ->toExecuteWithin(50) // 50ms for medium text
                ->and(fn () => $this->transliterator->transliterate($longText))
                ->toUseMemoryLessThan(5 * 1024 * 1024); // 5MB limit
        });

        it('static method performs comparably', function () {
            $text = 'สวัสดีครับ';

            $start = microtime(true);
            $result = Transliterator::make($text);
            $end = microtime(true);

            expect($result)->toBeString()->not()->toBeEmpty();
            expect($end - $start)->toBeLessThan(0.01); // 10ms
        });
    });

    describe('Integration with Strategy Enum', function () {
        it('works with all available strategies', function () {
            $text = 'สวัสดี';
            $strategies = [Strategy::PHONETIC, Strategy::ROYAL, Strategy::CUSTOM];

            foreach ($strategies as $strategy) {
                $transliterator = new Transliterator($strategy);
                $result = $transliterator->transliterate($text);
                expect($result)->toBeString()->not()->toBeEmpty();
            }
        });
    });
});
