<?php

namespace Phlib\XssSanitizer\Filter;

use Phlib\XssSanitizer\AttributeFinder;
use Phlib\XssSanitizer\FilterInterface;
use Phlib\XssSanitizer\TagFinder;

/**
 * Class MetaRefresh
 * @package Phlib\XssSanitizer\Filter
 */
class MetaRefresh implements FilterInterface
{
    /**
     * @var TagFinder\ByTag
     */
    protected $tagFinder;

    /**
     * @var AttributeFinder
     */
    protected $attrFinder;

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
        $this->tagFinder  = new TagFinder\ByTag('meta');
        $this->attrFinder = new AttributeFinder('http-equiv');

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
        $str = $this->tagFinder->findTags($str, function($fullTag, $attributes) {
            return $this->cleanTag($fullTag, $attributes);
        });
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
        $isRefreshTag = false;

        $this->attrFinder->findAttributes($attributes, function($full, $contents) use (&$isRefreshTag) {
            $cleanedContents = $this->attributeContentCleaner->filter($contents);
            if (preg_match('/refresh/i', $cleanedContents)) {
                $isRefreshTag = true;
            }
            return $full;
        });

        if ($isRefreshTag) {
            $fullTag = '';
        }
        return $fullTag;
    }
}
