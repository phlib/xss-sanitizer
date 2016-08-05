<?php

namespace Phlib\XssSanitizer;

use Phlib\XssSanitizer\Filter;

/**
 * Class Sanitizer
 * @package Phlib\XssSanitizer
 */
class Sanitizer
{
    use FilterRunnerTrait;

    /**
     * @var FilterInterface[]
     */
    protected $filters;

    /**
     * Sanitizer constructor
     */
    public function __construct()
    {
        $this->initFilters();
    }

    /**
     * Forward static calls as non-static calls.
     *
     * @param  string $method to be called
     * @param  array $arguments to be passed to called method
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        return call_user_func_array([self::make(), $method], $arguments);
    }

    /**
     * Make a new instance of Sanitizer
     *
     * @return \Phlib\XssSanitizer\Sanitizer
     */
    public static function make()
    {
        return new static;
    }

    /**
     * Sanitize a HTML string
     *
     * @param string $str
     * @return string
     */
    protected function clean($str)
    {
        return $this->sanitize($str);
    }

    /**
     * Sanitize a HTML string
     *
     * @param string $str
     * @return string
     */
    public function sanitize($str)
    {
        $str = $this->runFilters($str, $this->filters);

        return $str;
    }

    /**
     * Create the filters and add to the filters array
     */
    protected function initFilters()
    {
        $this->filters = [];

        $attributeContentCleaner = new Filter\AttributeContentCleaner();
        $this->filters[] = new Filter\AttributeCleaner('href', $attributeContentCleaner, ['a','link']);
        $this->filters[] = new Filter\AttributeCleaner('src', $attributeContentCleaner, ['img','input', 'bgsound']);
        $this->filters[] = new Filter\AttributeCleaner('action', $attributeContentCleaner, ['form']);
        $this->filters[] = new Filter\AttributeCleaner('background', $attributeContentCleaner);
        $this->filters[] = new Filter\FilterRunner(
            // Keep trying to remove blocks before escaping the tags
            new Filter\RemoveBlocks(['script', 'iframe', 'object'])
        );
        $this->filters[] = new Filter\EscapeTags(['script', 'iframe', 'object']);
        $this->filters[] = new Filter\RemoveAttributes();
        $this->filters[] = new Filter\MetaRefresh($attributeContentCleaner);
    }
}
