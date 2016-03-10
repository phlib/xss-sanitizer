<?php

namespace Phlib\XssSanitizer\TagFinder;

use Phlib\XssSanitizer\TagFinderInterface;

/**
 * Class ByAttribute
 * @package Phlib\XssSanitizer\TagFinder
 */
class ByAttribute implements TagFinderInterface
{

    /**
     * @var string
     */
    protected $initialSearchRegex;

    /**
     * ByAttribute constructor
     * @param string|string[] $attributes
     */
    public function __construct($attributes)
    {
        $this->initialSearchRegex = $this->initInitialSearchRegex($attributes);
    }

    /**
     * Given a full html string, finds the required tags by attribute and calls the callback,
     * providing the full tag string and the attributes string
     *
     * @param string $str
     * @param callable $callback
     * @return string
     */
    public function findTags($str, callable $callback)
    {
        $searchOffset = 0;
        while (preg_match($this->initialSearchRegex, $str, $matches, PREG_OFFSET_CAPTURE, $searchOffset)) {
            $attr   = $matches[0][0];
            $offset = $matches[0][1];

            $searchOffset = $offset + strlen($attr);

            $startOfTag = $this->findStartOfTag(substr($str, 0, $offset));
            if (!$startOfTag) {
                continue;
            }
            $endOfTag = $this->findEndOfTag(substr($str, $offset + strlen($attr)));
            if (!$endOfTag) {
                continue;
            }

            $fullTag    = implode('', [$startOfTag[0], $attr, $endOfTag[0]]);
            $attributes = implode('', [$startOfTag[1], $attr, $endOfTag[1]]);

            $replacement = $callback($fullTag, $attributes);

            $tagOffset = $offset - strlen($startOfTag[0]);
            $str = substr_replace($str, $replacement, $tagOffset, strlen($fullTag));

            // continue searching from after the end of the replaced tag
            $searchOffset = $tagOffset + strlen($replacement);
        }

        return $str;
    }

    /**
     * Build the search regex based on the attributes specified
     *
     * @param string|string[] $attributes
     * @return string
     */
    protected function initInitialSearchRegex($attributes)
    {
        if (is_array($attributes)) {
            $attributes = '(?:' . implode('|', $attributes) . ')';
        }

        return implode('', [
            '#',
                '(?<!\w)',
                $attributes,
                '[^0-9a-z"\'=>]*', // https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Non-alpha-non-digit_XSS
                '=',
            '#si',
        ]);
    }

    /**
     * Finds the start of the tag from the attribute found
     *
     * If the start of the tag is found, returns the matches array with the full tag start and attributes start
     * If not found, returns null
     *
     * @param string $beforeStr
     * @return array|null
     */
    protected function findStartOfTag($beforeStr)
    {
        // Searching backwards from the found attribute
        $startTag = preg_match('#^([^>]+)[a-z]<#si', strrev($beforeStr), $matches);
        if (!$startTag) {
            return null;
        } else {
            // reverse back again
            return array_map('strrev', $matches);
        }
    }

    /**
     * Finds the end of the tag from the attribute found
     *
     * If the end of the tag is found, returns the matches array with the full tag end and attributes end
     * If not found, returns null
     *
     * @param string $afterStr
     * @return array|null
     */
    protected function findEndOfTag($afterStr)
    {
        $endTag = preg_match('#^([^>]+)>#si', $afterStr, $matches);
        if (!$endTag) {
            return null;
        } else {
            return $matches;
        }
    }

}
