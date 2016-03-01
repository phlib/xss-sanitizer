<?php

namespace Phlib\XssSanitizer;

/**
 * Class AttributeFinder
 * @package Phlib\XssSanitizer
 */
class AttributeFinder
{
    /**
     * @var string
     */
    protected $searchRegex;

    /**
     * AttributeFinder constructor
     * @param string|string[] $attributes
     */
    public function __construct($attributes)
    {
        $this->searchRegex = $this->initSearchRegex($attributes);
    }

    /**
     * Given the attributes string of an element, finds the required attribute(s) and calls the callback, providing the
     * full attribute string and the content (value) of the attribute
     *
     * The return value is used to replace the full attribute string
     *
     * e.g. for an attribute finder which is looking for the 'href' attribute
     * for the string
     *     'a href="something" id="link"'
     * the callback will provide
     *     $fullAttribute:    'href="something"'
     *     $attributeContent: 'something'
     * and the return from the callback would replace the $fullAttribute in the original string
     *
     * @param string $attributes
     * @param callable $callback
     * @return string
     */
    public function findAttributes($attributes, callable $callback)
    {
        return preg_replace_callback(
            $this->searchRegex,
            function($matches) use ($callback) {
                if (isset($matches[2]) && $matches[2]) {
                    $attributeContents = $matches[2]; // quoted contents
                } else {
                    $attributeContents = $matches[3]; // unquoted contents
                }
                return $callback($matches[0], $attributeContents);
            },
            $attributes
        );
    }

    /**
     * Build the search regex based on the attributes specified
     *
     * @param string $attributes
     * @return string
     */
    protected function initSearchRegex($attributes)
    {
        if (is_array($attributes)) {
            $attributes = '(?:' . implode('|', $attributes) . ')';
        }
        return implode('', [
            '#',
            '(?<!\w)',
            $attributes,
            '[^0-9a-z"\'=]*', // https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Non-alpha-non-digit_XSS
            '=',
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
