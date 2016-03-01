<?php

namespace Phlib\XssSanitizer\Filter\AttributeContent;

use Phlib\XssSanitizer\FilterInterface;

/**
 * Class DecodeEntities
 * @package Phlib\XssSanitizer\Filter\AttributeContent
 */
class DecodeEntities implements FilterInterface
{

    /**
     * @var string
     */
    protected $entityRegex;

    /**
     * DecodeEntities constructor
     */
    public function __construct()
    {
        $this->entityRegex = $this->buildEntityRegex();
    }

    /**
     * Decode HTML entities in an attribute content string
     *
     * e.g.
     *     java&#115;cript:alert('XSS');
     * becomes
     *     javascript:alert('XSS');
     *
     * @param string $str
     * @return string
     */
    public function filter($str)
    {
        $str = preg_replace_callback(
            $this->entityRegex,
            function ($matches) {
                if ($matches[1]) {
                    $entity = "&#{$matches[1]};";
                } else {
                    $entity = "&#x{$matches[2]};";
                }
                return mb_convert_encoding($entity, "UTF-8", "HTML-ENTITIES");
            },
            $str
        );
        return $str;
    }

    /**
     * Build the regex for finding entities in the attribute content
     *
     * @return string
     */
    protected function buildEntityRegex()
    {
        return implode('', [
            '/',
                '&#',
                '(?:',
                    // decimal
                    '(?:0*)', // ignore zero padding
                    '([0-9]+)',
                '|',
                    // hexadecimal
                    'x(?:0*)', // ignore zero padding
                    '([0-9a-f]+)',
                ')',
                '(;)?',
            '/i',
        ]);
    }

}
