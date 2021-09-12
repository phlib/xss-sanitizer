<?php

declare(strict_types=1);

namespace Phlib\XssSanitizer\Test\TagFinder;

use Phlib\XssSanitizer\TagFinder;
use PHPUnit\Framework\TestCase;

/**
 * @package Phlib\XssSanitizer
 */
class ByTagTest extends TestCase
{
    public function testFindTagsCallbackArgs(): void
    {
        $tagFinder = new TagFinder\ByTag('title');

        $str = '<html><body><a title="something"></body></html>';
        $expectedFullTag = '<a title="something">';
        $expectedAttributes = ' title="something"';
        $callback = function ($fullTag, $attributes) use ($expectedFullTag, $expectedAttributes): string {
            static::assertSame($expectedFullTag, $fullTag);
            static::assertSame($expectedAttributes, $attributes);
            return '';
        };
        $tagFinder->findTags($str, $callback);
    }

    public function testFindTagsMultipleTags(): void
    {
        $tagFinder = new TagFinder\ByTag(['a', 'link']);

        $str = '<html><body><a title="something"><br /><link rel="alternate"></body></html>';
        $expectedFullTags = [
            '<a title="something">',
            '<link rel="alternate">',
        ];
        $actualFullTags = [];
        $callback = function ($fullTag) use (&$actualFullTags): string {
            $actualFullTags[] = $fullTag;
            return '';
        };
        $tagFinder->findTags($str, $callback);

        static::assertCount(2, $actualFullTags);
        static::assertSame($expectedFullTags, $actualFullTags);
    }

    /**
     * @dataProvider findTagsReplacementDataProvider
     */
    public function testFindTagsReplacement(string $str, string $replacement, string $expected): void
    {
        $tagFinder = new TagFinder\ByTag('a');

        $replacer = function () use ($replacement): string {
            return $replacement;
        };
        $actual = $tagFinder->findTags($str, $replacer);

        static::assertSame($expected, $actual);
    }

    public function findTagsReplacementDataProvider(): array
    {
        $r = '<!--replacement!-->';
        return [
            'single' => [
                '<html><body><a title="something"></body></html>',
                $r,
                "<html><body>$r</body></html>"
            ],
            'multi' => [
                '<html><body><a title="something"><a title="something"></body></html>',
                $r,
                "<html><body>$r$r</body></html>"
            ],
        ];
    }
}
