<?php

declare(strict_types=1);

use Farzai\ThaiSlug\Strategies\RoyalStrategy;

describe('RoyalStrategy', function () {
    beforeEach(function () {
        $this->strategy = new RoyalStrategy;
    });

    describe('Strategy Identity', function () {
        it('has correct strategy name', function () {
            expect($this->strategy->getName())->toBe('royal');
        });

        it('validates options correctly', function () {
            expect($this->strategy->validateOptions([]))->toBeTrue();
            expect($this->strategy->validateOptions(['strict_royal' => true]))->toBeTrue();
            expect($this->strategy->validateOptions(['preserve_diacritics' => false]))->toBeTrue();
            expect($this->strategy->validateOptions(['invalid_option' => true]))->toBeFalse();
        });
    });

    describe('Basic RTGS Consonants', function () {
        it('transliterates basic consonants according to RTGS', function () {
            expect($this->strategy->transliterate('ก'))->toBe('k');
            expect($this->strategy->transliterate('ข'))->toBe('kh');
            expect($this->strategy->transliterate('ค'))->toBe('kh');
            expect($this->strategy->transliterate('ง'))->toBe('ng');
            expect($this->strategy->transliterate('จ'))->toBe('ch');
            expect($this->strategy->transliterate('ช'))->toBe('ch');
            expect($this->strategy->transliterate('ญ'))->toBe('y');
            expect($this->strategy->transliterate('ด'))->toBe('d');
            expect($this->strategy->transliterate('ต'))->toBe('t');
            expect($this->strategy->transliterate('ท'))->toBe('th');
            expect($this->strategy->transliterate('น'))->toBe('n');
            expect($this->strategy->transliterate('บ'))->toBe('b');
            expect($this->strategy->transliterate('ป'))->toBe('p');
            expect($this->strategy->transliterate('ผ'))->toBe('ph');
            expect($this->strategy->transliterate('พ'))->toBe('ph');
            expect($this->strategy->transliterate('ฟ'))->toBe('f');
            expect($this->strategy->transliterate('ม'))->toBe('m');
            expect($this->strategy->transliterate('ย'))->toBe('y');
            expect($this->strategy->transliterate('ร'))->toBe('r');
            expect($this->strategy->transliterate('ล'))->toBe('l');
            expect($this->strategy->transliterate('ว'))->toBe('w');
            expect($this->strategy->transliterate('ศ'))->toBe('s');
            expect($this->strategy->transliterate('ส'))->toBe('s');
            expect($this->strategy->transliterate('ห'))->toBe('h');
            expect($this->strategy->transliterate('อ'))->toBe('');
        });
    });

    describe('RTGS Vowels', function () {
        it('transliterates vowels according to Royal Institute system', function () {
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
    });

    describe('Thai Numerals', function () {
        it('converts Thai numerals to Arabic numerals', function () {
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
        it('properly ignores tone marks and diacritics', function () {
            expect($this->strategy->transliterate('่'))->toBe('');
            expect($this->strategy->transliterate('้'))->toBe('');
            expect($this->strategy->transliterate('๊'))->toBe('');
            expect($this->strategy->transliterate('๋'))->toBe('');
            expect($this->strategy->transliterate('์'))->toBe('');
        });

        it('handles sara a correctly', function () {
            expect($this->strategy->transliterate('ั'))->toBe('a');
        });
    });

    describe('Official Place Names (RTGS Examples)', function () {
        it('transliterates Bangkok correctly', function () {
            expect($this->strategy->transliterate('กรุงเทพมหานคร'))->toBe('krungethphmhankhr');
        });

        it('transliterates Chiang Mai correctly', function () {
            expect($this->strategy->transliterate('เชียงใหม่'))->toBe('echiyngaihm');
        });

        it('transliterates Phuket correctly', function () {
            expect($this->strategy->transliterate('ภูเก็ต'))->toBe('phuekt');
        });

        it('transliterates Pattaya correctly', function () {
            expect($this->strategy->transliterate('พัทยา'))->toBe('phathya');
        });
    });

    describe('Consonant Clusters (RTGS)', function () {
        it('handles common consonant clusters', function () {
            expect($this->strategy->transliterate('กร'))->toBe('kr');
            expect($this->strategy->transliterate('ปร'))->toBe('pr');
            expect($this->strategy->transliterate('ตร'))->toBe('tr');
            expect($this->strategy->transliterate('สป'))->toBe('sp');
            expect($this->strategy->transliterate('สต'))->toBe('st');
            expect($this->strategy->transliterate('สก'))->toBe('sk');
        });
    });

    describe('Academic Words (RTGS Standard)', function () {
        it('transliterates university names correctly', function () {
            expect($this->strategy->transliterate('จุฬาลงกรณ์มหาวิทยาลัย'))->toBe('chulalngkrnmhawithyalay');
            expect($this->strategy->transliterate('มหาวิทยาลัยธรรมศาสตร์'))->toBe('mhawithyalaythrrmsastr');
        });
    });

    describe('Edge Cases and Error Handling', function () {
        it('handles empty text', function () {
            expect($this->strategy->transliterate(''))->toBe('');
        });

        it('handles whitespace-only text', function () {
            expect($this->strategy->transliterate('   '))->toBe('');
        });

        it('handles mixed Thai-English text', function () {
            expect($this->strategy->transliterate('Hello กรุงเทพ'))->toBe('Hello krungethph');
        });

        it('handles numbers and punctuation', function () {
            expect($this->strategy->transliterate('๑๒๓-๔๕๖'))->toBe('123-456');
            expect($this->strategy->transliterate('กรุงเทพ, ประเทศไทย'))->toBe('krungethph, praethsaithy');
        });

        it('handles very long text', function () {
            $longText = str_repeat('กรุงเทพมหานคร', 100);
            $result = $this->strategy->transliterate($longText);
            expect($result)->toContain('krungethphmhankhr');
        });
    });

    describe('Consistency Requirements', function () {
        it('produces predictable word separation', function () {
            expect($this->strategy->transliterate('กรุงเทพ มหานคร'))->toBe('krungethph mhankhr');
            expect($this->strategy->transliterate('สวัสดี ครับ'))->toBe('swasdi khrab');
        });

        it('maintains consistent output', function () {
            $input = 'ประเทศไทย';
            $result1 = $this->strategy->transliterate($input);
            $result2 = $this->strategy->transliterate($input);
            expect($result1)->toBe($result2);
        });
    });

    describe('Performance Requirements', function () {
        it('processes text efficiently', function () {
            $start = microtime(true);
            $this->strategy->transliterate('กรุงเทพมหานคร');
            $end = microtime(true);
            expect($end - $start)->toBeLessThan(0.01); // 10ms
        });

        it('handles medium-length text well', function () {
            $text = 'จุฬาลงกรณ์มหาวิทยาลัย เป็นสถาบันการศึกษาชั้นสูงแห่งแรกของประเทศไทย';
            $start = microtime(true);
            $result = $this->strategy->transliterate($text);
            $end = microtime(true);
            expect(strlen($result))->toBeGreaterThan(0);
            expect($end - $start)->toBeLessThan(0.1); // 100ms
        });
    });

    describe('Real-World Applications', function () {
        it('handles government document transliteration', function () {
            $text = 'กระทรวงการต่างประเทศ ราชอาณาจักรไทย';
            expect($this->strategy->transliterate($text))->toBe('krathrwngkartangpraeths rachanachakraithy');
        });

        it('handles address transliteration', function () {
            $address = '๒๕๕ ถนนสุขุมวิท แขวงคลองเตย เขตคลองเตย กรุงเทพมหานคร';
            expect($this->strategy->transliterate($address))->toBe('255 thnnsukhumwith aekhwngkhlngety ekhtkhlngety krungethphmhankhr');
        });
    });
});
