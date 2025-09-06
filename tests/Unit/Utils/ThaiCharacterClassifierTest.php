<?php

declare(strict_types=1);

use Farzai\ThaiSlug\Utils\ThaiCharacterClassifier;

describe('ThaiCharacterClassifier', function () {
    describe('isToneMark', function () {
        it('identifies tone marks correctly', function () {
            expect(ThaiCharacterClassifier::isToneMark('่'))->toBeTrue(); // Mai Ek
            expect(ThaiCharacterClassifier::isToneMark('้'))->toBeTrue(); // Mai Tho
            expect(ThaiCharacterClassifier::isToneMark('๊'))->toBeTrue(); // Mai Tri
            expect(ThaiCharacterClassifier::isToneMark('๋'))->toBeTrue(); // Mai Chattawa
        });

        it('rejects non-tone mark characters', function () {
            expect(ThaiCharacterClassifier::isToneMark('ก'))->toBeFalse(); // consonant
            expect(ThaiCharacterClassifier::isToneMark('า'))->toBeFalse(); // vowel
            expect(ThaiCharacterClassifier::isToneMark('ั'))->toBeFalse(); // combining vowel
            expect(ThaiCharacterClassifier::isToneMark('์'))->toBeFalse(); // diacritic
            expect(ThaiCharacterClassifier::isToneMark('a'))->toBeFalse(); // latin
            expect(ThaiCharacterClassifier::isToneMark('1'))->toBeFalse(); // number
            expect(ThaiCharacterClassifier::isToneMark(' '))->toBeFalse(); // space
            expect(ThaiCharacterClassifier::isToneMark(''))->toBeFalse(); // empty
        });
    });

    describe('isCombiningMark', function () {
        it('identifies combining vowels correctly', function () {
            expect(ThaiCharacterClassifier::isCombiningMark('ั'))->toBeTrue(); // Mai Han-akat
            expect(ThaiCharacterClassifier::isCombiningMark('ิ'))->toBeTrue(); // Sara I
            expect(ThaiCharacterClassifier::isCombiningMark('ี'))->toBeTrue(); // Sara II
            expect(ThaiCharacterClassifier::isCombiningMark('ึ'))->toBeTrue(); // Sara UE
            expect(ThaiCharacterClassifier::isCombiningMark('ื'))->toBeTrue(); // Sara UEE
            expect(ThaiCharacterClassifier::isCombiningMark('ุ'))->toBeTrue(); // Sara U
            expect(ThaiCharacterClassifier::isCombiningMark('ู'))->toBeTrue(); // Sara UU
            expect(ThaiCharacterClassifier::isCombiningMark('ฺ'))->toBeTrue(); // Phinthu
        });

        it('identifies tone marks correctly', function () {
            expect(ThaiCharacterClassifier::isCombiningMark('่'))->toBeTrue(); // Mai Ek
            expect(ThaiCharacterClassifier::isCombiningMark('้'))->toBeTrue(); // Mai Tho
            expect(ThaiCharacterClassifier::isCombiningMark('๊'))->toBeTrue(); // Mai Tri
            expect(ThaiCharacterClassifier::isCombiningMark('๋'))->toBeTrue(); // Mai Chattawa
        });

        it('identifies diacritics correctly', function () {
            expect(ThaiCharacterClassifier::isCombiningMark('์'))->toBeTrue(); // Thanthakhat
            expect(ThaiCharacterClassifier::isCombiningMark('ํ'))->toBeTrue(); // Nikhahit
            expect(ThaiCharacterClassifier::isCombiningMark('๎'))->toBeTrue(); // Yamakkan
        });

        it('rejects non-combining characters', function () {
            expect(ThaiCharacterClassifier::isCombiningMark('ก'))->toBeFalse(); // consonant
            expect(ThaiCharacterClassifier::isCombiningMark('า'))->toBeFalse(); // independent vowel
            expect(ThaiCharacterClassifier::isCombiningMark('เ'))->toBeFalse(); // leading vowel
            expect(ThaiCharacterClassifier::isCombiningMark('a'))->toBeFalse(); // latin
            expect(ThaiCharacterClassifier::isCombiningMark('1'))->toBeFalse(); // number
            expect(ThaiCharacterClassifier::isCombiningMark(' '))->toBeFalse(); // space
            expect(ThaiCharacterClassifier::isCombiningMark(''))->toBeFalse(); // empty
        });
    });

    describe('edge cases and performance', function () {
        it('handles empty strings gracefully', function () {
            expect(ThaiCharacterClassifier::isToneMark(''))->toBeFalse();
            expect(ThaiCharacterClassifier::isCombiningMark(''))->toBeFalse();
        });

        it('handles whitespace correctly', function () {
            expect(ThaiCharacterClassifier::isToneMark(' '))->toBeFalse();
            expect(ThaiCharacterClassifier::isCombiningMark(' '))->toBeFalse();
        });

        it('maintains consistent behavior across calls', function () {
            $char = 'ั'; // Mai Han-akat
            $result1 = ThaiCharacterClassifier::isCombiningMark($char);
            $result2 = ThaiCharacterClassifier::isCombiningMark($char);
            expect($result1)->toBe($result2);
            expect($result1)->toBeTrue();
        });

        it('handles large inputs efficiently', function () {
            $start = microtime(true);
            for ($i = 0; $i < 1000; $i++) {
                ThaiCharacterClassifier::isCombiningMark('ั');
                ThaiCharacterClassifier::isToneMark('่');
            }
            $end = microtime(true);
            expect($end - $start)->toBeLessThan(0.1); // 100ms for 2000 calls
        });
    });
});
