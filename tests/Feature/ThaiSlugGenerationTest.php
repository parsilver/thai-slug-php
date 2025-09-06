<?php

declare(strict_types=1);

use Farzai\ThaiSlug\ThaiSlug;

describe('Thai Slug Generation', function () {
    it('generates basic slug from thai text', function () {
        $thaiText = 'สวัสดีโลก';
        $expectedSlug = 'swasdiolk';

        $slug = ThaiSlug::make($thaiText);

        expect($slug)->toBe($expectedSlug);
    });

    it('generates slug from thai text with spaces', function () {
        $thaiText = 'สวัสดี โลก สวย';
        $expectedSlug = 'swasdi-olk-swy';

        $slug = ThaiSlug::make($thaiText);

        expect($slug)->toBe($expectedSlug);
    });

    it('handles empty string', function () {
        $slug = ThaiSlug::make('');

        expect($slug)->toBe('');
    });

    it('handles mixed thai and english text', function () {
        $text = 'Hello สวัสดี World โลก';
        $expectedSlug = 'hello-swasdi-world-olk';

        $slug = ThaiSlug::make($text);

        expect($slug)->toBe($expectedSlug);
    });

    it('generates slug using builder pattern', function () {
        $thaiText = 'สวัสดีโลก';
        $expectedSlug = 'swasdiolk';

        $thaiSlug = new ThaiSlug;
        $slug = $thaiSlug->builder()
            ->text($thaiText)
            ->build();

        expect($slug)->toBe($expectedSlug);
    });

    it('generates slug with custom separator', function () {
        $thaiText = 'สวัสดี โลก';
        $expectedSlug = 'swasdi_olk';

        $thaiSlug = new ThaiSlug;
        $slug = $thaiSlug->builder()
            ->text($thaiText)
            ->separator('_')
            ->build();

        expect($slug)->toBe($expectedSlug);
    });

    it('generates slug with max length limit', function () {
        $thaiText = 'สวัสดีโลกสวยงามมาก';
        $maxLength = 10;

        $thaiSlug = new ThaiSlug;
        $slug = $thaiSlug->builder()
            ->text($thaiText)
            ->maxLength($maxLength)
            ->build();

        expect(strlen($slug))->toBeLessThanOrEqual($maxLength);
        expect($slug)->not()->toEndWith('-');
    });
});
