<?php

namespace Phlib\XssSanitizer\Test\TagFinder;

use Phlib\XssSanitizer\TagFinder;

/**
 * @package Phlib\XssSanitizer
 */
class ByTagTest extends \PHPUnit_Framework_TestCase
{
    public function testFindTagsCallbackArgs()
    {
        $tagFinder = new TagFinder\ByTag('title');

        $str = '<html><body><a title="something"></body></html>';
        $expectedFullTag = '<a title="something">';
        $expectedAttributes = ' title="something"';
        $callback = function ($fullTag, $attributes) use ($expectedFullTag, $expectedAttributes) {
            static::assertSame($expectedFullTag, $fullTag);
            static::assertSame($expectedAttributes, $attributes);
            return '';
        };
        $tagFinder->findTags($str, $callback);
    }

    public function testFindTagsMultipleTags()
    {
        $tagFinder = new TagFinder\ByTag(['a', 'link']);

        $str = '<html><body><a title="something"><br /><link rel="alternate"></body></html>';
        $expectedFullTags = [
            '<a title="something">',
            '<link rel="alternate">',
        ];
        $actualFullTags = [];
        $callback = function ($fullTag) use (&$actualFullTags) {
            $actualFullTags[] = $fullTag;
            return '';
        };
        $tagFinder->findTags($str, $callback);

        static::assertCount(2, $actualFullTags);
        static::assertSame($expectedFullTags, $actualFullTags);
    }

    /**
     * @dataProvider findTagsReplacementDataProvider
     * @param string $str
     * @param string $replacement
     * @param string $expected
     */
    public function testFindTagsReplacement($str, $replacement, $expected)
    {
        $tagFinder = new TagFinder\ByTag('a');

        $replacer = function () use ($replacement) {
            return $replacement;
        };
        $actual = $tagFinder->findTags($str, $replacer);

        static::assertSame($expected, $actual);
    }

    public function findTagsReplacementDataProvider()
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
