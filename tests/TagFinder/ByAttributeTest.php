<?php

namespace Phlib\XssSanitizer\Test\TagFinder;

use Phlib\XssSanitizer\TagFinder;
use PHPUnit\Framework\TestCase;

/**
 * @package Phlib\XssSanitizer
 */
class ByAttributeTest extends TestCase
{
    public function testFindTagsCallbackArgs()
    {
        $tagFinder = new TagFinder\ByAttribute('title');

        $str = '<html><body><a title="something"></body></html>';
        $expectedFullTag = '<a title="something">';
        $expectedAttributes = ' title="something"';
        $callback = function ($fullTag, $attributes) use ($expectedFullTag, $expectedAttributes) {
            static::assertEquals($expectedFullTag, $fullTag);
            static::assertEquals($expectedAttributes, $attributes);
        };
        $tagFinder->findTags($str, $callback);
    }

    public function testFindTagsMultipleAttributes()
    {
        $tagFinder = new TagFinder\ByAttribute(['title', 'name']);

        $str = '<html><body><a title="something"><br /><a name="thename"></body></html>';
        $expectedFullTags = [
            '<a title="something">',
            '<a name="thename">',
        ];
        $actualFullTags = [];
        $callback = function ($fullTag) use (&$actualFullTags) {
            $actualFullTags[] = $fullTag;
        };
        $tagFinder->findTags($str, $callback);

        static::assertCount(2, $actualFullTags);
        static::assertEquals($expectedFullTags, $actualFullTags);
    }

    /**
     * @dataProvider findTagsReplacementDataProvider
     * @param string $str
     * @param string $replacement
     * @param string $expected
     */
    public function testFindTagsReplacement($str, $replacement, $expected)
    {
        $tagFinder = new TagFinder\ByAttribute('title');

        $replacer = function () use ($replacement) {
            return $replacement;
        };
        $actual = $tagFinder->findTags($str, $replacer);

        static::assertEquals($expected, $actual);
    }

    public function findTagsReplacementDataProvider()
    {
        $r = '<!--replacement!-->';
        return [
            ['<html><body><a title="something"></body></html>', $r, "<html><body>{$r}</body></html>"],
            ['<html><body><a title="something"><a title="something"></body></html>', $r, "<html><body>{$r}{$r}</body></html>"],
        ];
    }
}
