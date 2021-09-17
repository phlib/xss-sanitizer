<?php

declare(strict_types=1);

namespace Phlib\XssSanitizer;

/**
 * @package Phlib\XssSanitizer
 */
class Sanitizer
{
    use FilterRunnerTrait;

    /**
     * @var FilterInterface[]
     */
    private array $filters;

    public function __construct()
    {
        $this->initFilters();
    }

    public function sanitize(string $str): string
    {
        $str = $this->runFilters($str, $this->filters);

        return $str;
    }

    /**
     * Sanitize an array of HTML strings
     *
     * @param string[] $strings
     * @return string[]
     */
    public function sanitizeArray(array $strings): array
    {
        foreach ($strings as &$str) {
            $str = $this->sanitize($str);
        }

        return $strings;
    }

    private function initFilters(): void
    {
        $this->filters = [];

        $attributeContentCleaner = new Filter\AttributeContentCleaner();
        $this->filters[] = new Filter\AttributeCleaner('href', $attributeContentCleaner, ['a', 'link']);
        $this->filters[] = new Filter\AttributeCleaner('src', $attributeContentCleaner, ['img', 'input', 'bgsound']);
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
