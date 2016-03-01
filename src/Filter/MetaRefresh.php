<?php

namespace Phlib\XssSanitizer\Filter;

use Phlib\XssSanitizer\FilterInterface;

/**
 * Class MetaRefresh
 * @package Phlib\XssSanitizer\Filter
 */
class MetaRefresh implements FilterInterface
{
    /**
     * @var string
     */
    protected $attrRegex;

    /**
     * @var FilterInterface
     */
    protected $attributeContentCleaner;

    /**
     * MetaRefresh constructor
     * @param FilterInterface $attributeContentCleaner
     */
    public function __construct(FilterInterface $attributeContentCleaner)
    {
        $this->attrRegex = $this->buildAttrRegex();

        $this->attributeContentCleaner = $attributeContentCleaner;
    }

    /**
     * Removes refresh meta tags
     * @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#META
     *
     * e.g.
     *     <meta http-equiv="refresh" content="0;url=javascript:alert('XSS');">
     * would be removed
     *
     * @param string $str
     * @return string
     */
    public function filter($str)
    {
        if (preg_match('/<meta/i', $str)) {
            $str = preg_replace_callback(
                '#<meta[^a-z0-9>]+([^>]*?)(?:>|$)#i',
                function($matches) {
                    return $this->cleanTag($matches[0], $matches[1]);
                },
                $str
            );
        }
        return $str;
    }

    /**
     * Replaces the tag with an empty string if the 'http-equiv' is set to 'refresh'
     *
     * @param string $fullTag (e.g. '<meta http-equiv="refresh">')
     * @param string $attributes (e.g. 'meta http-equiv="refresh"')
     * @return string
     */
    protected function cleanTag($fullTag, $attributes)
    {
        if (preg_match($this->attrRegex, $attributes, $matches)) {
            if (isset($matches[2]) && $matches[2]) {
                $attributeContents = $matches[2]; // quoted contents
            } else {
                $attributeContents = $matches[3]; // unquoted contents
            }
            $cleanedContents = $this->attributeContentCleaner->filter($attributeContents);
            if (preg_match('/refresh/i', $cleanedContents)) {
                $fullTag = '';
            }
        }
        return $fullTag;
    }

    /**
     * Build the regex for getting the content of the 'http-equiv' attribute
     *
     * @return string
     */
    protected function buildAttrRegex()
    {
        return implode('', [
            '#',
            '(?:http-equiv=)',
            '(?:',
                '(["\'`])', // quoted
                '(.*?)',
                '\1', // quote character
            '|',
                '(?<!["\'`])', // unqouted
                '((?:[^ >])*)', // everything up to space or '>'
            ')',
            '#si',
        ]);
    }
}
