<?php

declare(strict_types=1);

use Farzai\ThaiSlug\Strategies\PhoneticStrategy;

describe('PhoneticStrategy', function () {
    beforeEach(function () {
        $this->strategy = new PhoneticStrategy;
    });

    describe('Strategy Interface', function () {
        it('implements strategy interface correctly', function () {
            expect($this->strategy->getName())->toBe('phonetic');
        });

        it('validates options correctly', function () {
            expect($this->strategy->validateOptions([]))->toBeTrue();
            expect($this->strategy->validateOptions(['preserve_tone_marks' => true]))->toBeTrue();
            expect($this->strategy->validateOptions(['preserve_digits' => false]))->toBeTrue();
            expect($this->strategy->validateOptions(['invalid_option' => true]))->toBeFalse();
        });
    });

    describe('Basic Character Transliteration', function () {
        it('transliterates basic Thai consonants', function () {
            expect($this->strategy->transliterate('ก'))->toBe('k');
            expect($this->strategy->transliterate('ข'))->toBe('kh');
            expect($this->strategy->transliterate('จ'))->toBe('ch');
            expect($this->strategy->transliterate('ด'))->toBe('d');
            expect($this->strategy->transliterate('ต'))->toBe('t');
            expect($this->strategy->transliterate('บ'))->toBe('b');
            expect($this->strategy->transliterate('ป'))->toBe('p');
            expect($this->strategy->transliterate('ม'))->toBe('m');
            expect($this->strategy->transliterate('ย'))->toBe('y');
            expect($this->strategy->transliterate('ร'))->toBe('r');
            expect($this->strategy->transliterate('ล'))->toBe('l');
            expect($this->strategy->transliterate('ว'))->toBe('w');
            expect($this->strategy->transliterate('ส'))->toBe('s');
            expect($this->strategy->transliterate('ห'))->toBe('h');
            expect($this->strategy->transliterate('น'))->toBe('n');
            expect($this->strategy->transliterate('ง'))->toBe('ng');
        });

        it('transliterates basic Thai vowels', function () {
            expect($this->strategy->transliterate('ะ'))->toBe('a');
            expect($this->strategy->transliterate('า'))->toBe('a');
            expect($this->strategy->transliterate('ิ'))->toBe('i');
            expect($this->strategy->transliterate('ี'))->toBe('i');
            expect($this->strategy->transliterate('ึ'))->toBe('ue');
            expect($this->strategy->transliterate('ื'))->toBe('ue');
            expect($this->strategy->transliterate('ุ'))->toBe('u');
            expect($this->strategy->transliterate('ู'))->toBe('u');
            expect($this->strategy->transliterate('เ'))->toBe('e');
            expect($this->strategy->transliterate('แ'))->toBe('ae');
            expect($this->strategy->transliterate('โ'))->toBe('o');
            expect($this->strategy->transliterate('ใ'))->toBe('ai');
            expect($this->strategy->transliterate('ไ'))->toBe('ai');
            expect($this->strategy->transliterate('ำ'))->toBe('am');
        });

        it('handles aspirated consonants correctly', function () {
            expect($this->strategy->transliterate('ผ'))->toBe('ph');
            expect($this->strategy->transliterate('ฝ'))->toBe('f');
            expect($this->strategy->transliterate('พ'))->toBe('ph');
            expect($this->strategy->transliterate('ฟ'))->toBe('f');
            expect($this->strategy->transliterate('ถ'))->toBe('th');
            expect($this->strategy->transliterate('ท'))->toBe('th');
        });
    });

    describe('Thai Numbers', function () {
        it('transliterates Thai digits', function () {
            expect($this->strategy->transliterate('๐'))->toBe('0');
            expect($this->strategy->transliterate('๑'))->toBe('1');
            expect($this->strategy->transliterate('๒'))->toBe('2');
            expect($this->strategy->transliterate('๓'))->toBe('3');
            expect($this->strategy->transliterate('๔'))->toBe('4');
            expect($this->strategy->transliterate('๕'))->toBe('5');
            expect($this->strategy->transliterate('๖'))->toBe('6');
            expect($this->strategy->transliterate('๗'))->toBe('7');
            expect($this->strategy->transliterate('๘'))->toBe('8');
            expect($this->strategy->transliterate('๙'))->toBe('9');
        });
    });

    describe('Tone Marks and Diacritics', function () {
        it('handles tone marks appropriately', function () {
            expect($this->strategy->transliterate('่'))->toBe('');
            expect($this->strategy->transliterate('้'))->toBe('');
            expect($this->strategy->transliterate('๊'))->toBe('');
            expect($this->strategy->transliterate('๋'))->toBe('');
        });

        it('handles mai han-akat correctly', function () {
            expect($this->strategy->transliterate('์'))->toBe('');
        });

        it('handles sara a correctly', function () {
            expect($this->strategy->transliterate('ั'))->toBe('a');
        });
    });

    describe('Common Word Transliteration', function () {
        it('transliterates common Thai words accurately', function () {
            expect($this->strategy->transliterate('สวัสดี'))->toBe('swasdi');
            expect($this->strategy->transliterate('ขอบคุณ'))->toBe('khbkhun');
            expect($this->strategy->transliterate('กรุงเทพ'))->toBe('krungethph');
            expect($this->strategy->transliterate('ประเทศไทย'))->toBe('praethsaithy');
        });

        it('handles multiple words correctly', function () {
            expect($this->strategy->transliterate('สวัสดี ครับ'))->toBe('swasdi khrab');
        });
    });

    describe('Consonant Clusters', function () {
        it('handles initial consonant clusters correctly', function () {
            expect($this->strategy->transliterate('กร'))->toBe('kr');
            expect($this->strategy->transliterate('ปล'))->toBe('pl');
            expect($this->strategy->transliterate('ตร'))->toBe('tr');
        });

        it('handles complex words with clusters', function () {
            expect($this->strategy->transliterate('กรรม'))->toBe('krrm');
            expect($this->strategy->transliterate('ปลา'))->toBe('pla');
        });

        it('handles three-consonant clusters', function () {
            expect($this->strategy->transliterate('สกร'))->toBe('skr');
        });
    });

    describe('Edge Cases', function () {
        it('handles empty input', function () {
            expect($this->strategy->transliterate(''))->toBe('');
        });

        it('handles whitespace input', function () {
            expect($this->strategy->transliterate('   '))->toBe('');
            expect($this->strategy->transliterate(' ก '))->toBe('k');
        });

        it('handles numbers and punctuation', function () {
            expect($this->strategy->transliterate('123'))->toBe('123');
            expect($this->strategy->transliterate('ก,ข'))->toBe('k,kh');
        });

        it('handles mixed Thai and Latin text', function () {
            expect($this->strategy->transliterate('Hello กรุงเทพ'))->toBe('Hello krungethph');
        });

        it('handles very long text', function () {
            $longText = str_repeat('กรุงเทพมหานคร', 100);
            $result = $this->strategy->transliterate($longText);
            expect($result)->toContain('krungethphmhankhr');
        });
    });

    describe('Consistency Requirements', function () {
        it('produces consistent output for same input', function () {
            $input = 'กรุงเทพมหานคร';
            $result1 = $this->strategy->transliterate($input);
            $result2 = $this->strategy->transliterate($input);
            expect($result1)->toBe($result2);
        });

        it('maintains word boundaries correctly', function () {
            expect($this->strategy->transliterate('กรุงเทพ มหานคร'))->toBe('krungethph mhankhr');
        });
    });

    describe('Performance Requirements', function () {
        it('processes single words quickly', function () {
            $start = microtime(true);
            $this->strategy->transliterate('กรุงเทพ');
            $end = microtime(true);
            expect($end - $start)->toBeLessThan(0.01); // 10ms
        });

        it('processes medium text within limits', function () {
            $text = str_repeat('กรุงเทพมหานคร ', 10);
            $start = microtime(true);
            $this->strategy->transliterate($text);
            $end = microtime(true);
            expect($end - $start)->toBeLessThan(0.1); // 100ms
        });

        it('processes large text efficiently', function () {
            $text = str_repeat('สวัสดีครับผมชื่อจอห์น ', 50);
            $start = microtime(true);
            $result = $this->strategy->transliterate($text);
            $end = microtime(true);
            expect(strlen($result))->toBeGreaterThan(0);
            expect($end - $start)->toBeLessThan(0.5); // 500ms
        });
    });

    describe('Real-World Text Samples', function () {
        it('transliterates addresses correctly', function () {
            $address = '๑๒๓ ถนนสุขุมวิท กรุงเทพมหานคร';
            expect($this->strategy->transliterate($address))->toBe('123 thnnsukhumwith krungethphmhankhr');
        });

        it('transliterates news headlines correctly', function () {
            $headline = 'ข่าวด่วน รัฐบาลไทย ประกาศนโยบายใหม่';
            expect($this->strategy->transliterate($headline))->toBe('khawdwn rathbalaithy prakasnoybayaihm');
        });

        it('transliterates technical terms correctly', function () {
            $technical = 'เทคโนโลยีคอมพิวเตอร์';
            expect($this->strategy->transliterate($technical))->toBe('ethkhonolyikhmphiwetr');
        });
    });
});
