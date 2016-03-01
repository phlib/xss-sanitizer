<?php

namespace Phlib\XssSanitizer\Filter;

use Phlib\XssSanitizer\FilterInterface;

/**
 * Class AttributeCleaner
 * @package Phlib\XssSanitizer\Filter
 */
class AttributeCleaner implements FilterInterface
{
    /**
     * @var string
     */
    protected $tag;

    /**
     * @var string
     */
    protected $attribute;

    /**
     * @var string
     */
    protected $attrRegex;

    /**
     * @var string
     */
    protected $contentRegex;

    /**
     * @var FilterInterface
     */
    protected $attributeContentCleaner;

    /**
     * AttributeCleaner constructor
     *
     * @param string|string[] $tag
     * @param string $attribute
     * @param FilterInterface $attributeContentCleaner
     */
    public function __construct($tag, $attribute, FilterInterface $attributeContentCleaner)
    {
        if (is_array($tag)) {
            $tag = '(?:' . implode('|', $tag) . ')';
        }
        $this->tag          = $tag;
        $this->attribute    = $attribute;
        $this->attrRegex    = $this->buildAttrRegex();
        $this->contentRegex = $this->buildContentRegex();

        $this->attributeContentCleaner = $attributeContentCleaner;
    }

    /**
     * Given the tag and attribute to look for, will search for tags with that attribute containing potential XSS
     * exploits, and remove the attribute if found
     *
     * e.g. with $tag='a' and $attr='href'
     *     <a href="javascript:alert('XSS');">
     * should become
     *     <a >
     *
     * @param string $str
     * @return string
     */
    public function filter($str)
    {
        if (preg_match('/<' . $this->tag . '/i', $str)) {
            $str = preg_replace_callback(
                '#<' . $this->tag . '[^a-z0-9>]+([^>]*?)(?:>|$)#i',
                function($matches) {
                    return $this->cleanAttributes($matches[0], $matches[1]);
                },
                $str
            );
        }
        return $str;
    }

    /**
     * Search for the attribute in the tag, and clean it if found
     *
     * @param string $fullTag (e.g. '<a href="javascript:alert('XSS');">')
     * @param string $attributes (e.g. 'a href="javascript:alert('XSS');"')
     * @return string
     */
    protected function cleanAttributes($fullTag, $attributes)
    {
        if (!preg_match('/'. $this->attribute .'/i', $attributes)) {
            return $fullTag;
        }

        $replacement = preg_replace_callback(
            $this->attrRegex,
            function($matches) {
                if (isset($matches[2]) && $matches[2]) {
                    $attributeContents = $matches[2]; // quoted contents
                } else {
                    $attributeContents = $matches[3]; // unquoted contents
                }
                return $this->cleanAttribute($matches[0], $attributeContents);
            },
            $attributes
        );

        return str_ireplace($attributes, $replacement, $fullTag);
    }

    /**
     * Search the attribute content for any potential exploits, and return empty string
     *
     * @param string $fullAttribute (e.g. 'href="javascript:alert('XSS');"')
     * @param string $attributeContents (e.g. 'javascript:alert('XSS');')
     * @return string
     */
    protected function cleanAttribute($fullAttribute, $attributeContents)
    {
        // decode entities, compact words etc.
        $cleanedContents = $this->attributeContentCleaner->filter($attributeContents);

        if (preg_match($this->contentRegex, $cleanedContents)) {
            return '';
        }

        return $fullAttribute;
    }

    /**
     * Build the regex for finding the attribute in the attributes string and returning the attribute content
     *
     * @return string
     */
    protected function buildAttrRegex()
    {
        $attr = $this->attribute;
        return implode('', [
            '#',
            $attr,
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

    /**
     * Build the regex for finding potential exploits in the attribute content
     *
     * @return string
     */
    protected function buildContentRegex()
    {
        $dangerous = [
            'javascript:',
        ];

        return implode('', [
            '#',
                '(', implode('|', $dangerous), ')',
            '#i',
        ]);
    }

}
