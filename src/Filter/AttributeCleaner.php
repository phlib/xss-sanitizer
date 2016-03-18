<?php

namespace Phlib\XssSanitizer\Filter;

use Phlib\XssSanitizer\AttributeFinder;
use Phlib\XssSanitizer\FilterInterface;
use Phlib\XssSanitizer\TagFinderInterface;
use Phlib\XssSanitizer\TagFinder;

/**
 * Class AttributeCleaner
 * @package Phlib\XssSanitizer\Filter
 */
class AttributeCleaner implements FilterInterface
{
    /**
     * @var TagFinderInterface
     */
    protected $tagFinder;

    /**
     * @var AttributeFinder
     */
    protected $attrFinder;

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
     * @param string $attribute
     * @param FilterInterface $attributeContentCleaner
     * @param string|string[]|null $tags
     */
    public function __construct($attribute, FilterInterface $attributeContentCleaner, $tags = null)
    {
        $this->tagFinder  = $tags ? new TagFinder\ByTag($tags) : new TagFinder\ByAttribute($attribute);
        $this->attrFinder = new AttributeFinder($attribute);

        $this->contentRegex = $this->buildContentRegex();

        $this->attributeContentCleaner = $attributeContentCleaner;
    }

    /**
     * Given the tags and attribute to look for, will search for tags with that attribute containing potential XSS
     * exploits, and remove the attribute if found
     *
     * e.g. with $tags='a' and $attr='href'
     *     <a href="javascript:alert('XSS');">
     * should become
     *     <a >
     *
     * @param string $str
     * @return string
     */
    public function filter($str)
    {
        $str = $this->tagFinder->findTags($str, function($fullTag, $attributes) {
            return $this->cleanAttributes($fullTag, $attributes);
        });

        return $str;
    }

    /**
     * Search for the attribute in the tags, and clean it if found
     *
     * @param string $fullTag (e.g. '<a href="javascript:alert('XSS');">')
     * @param string $attributes (e.g. 'a href="javascript:alert('XSS');"')
     * @return string
     */
    protected function cleanAttributes($fullTag, $attributes)
    {
        $replacement = $this->attrFinder->findAttributes($attributes, function($fullAttribute, $attributeContents) {
            return $this->cleanAttribute($fullAttribute, $attributeContents);
        });

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
