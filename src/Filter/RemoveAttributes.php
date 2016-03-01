<?php

namespace Phlib\XssSanitizer\Filter;

use Phlib\XssSanitizer\FilterInterface;

/**
 * Class RemoveAttributes
 * @package Phlib\XssSanitizer\Filter
 */
class RemoveAttributes implements FilterInterface
{
    /**
     * @var string
     */
    protected $searchRegex;

    /**
     * @var string
     */
    protected $replaceRegex;

    /**
     * RemoveAttributes constructor
     */
    public function __construct()
    {
        $this->attributes = [
            'on\w+',
            'fscommand',
            'seeksegmenttime',
        ];

        $this->searchRegex  = $this->buildSearchRegex();
        $this->replaceRegex = $this->buildReplaceRegex();
    }

    /**
     * Filter unwanted attributes from tags
     *
     * This includes event handler attributes ('onload', 'onclick' etc.)
     * e.g. '<body onload="alert('XSS');">'
     *
     * @param string $str
     * @return string
     */
    public function filter($str)
    {
        $str = preg_replace_callback(
            $this->searchRegex,
            function ($matches) {
                return $this->removeAttribute($matches[0], $matches[1]);
            },
            $str
        );

        return $str;
    }

    /**
     * Removes unwanted attributes from a particular tag
     *
     * @param string $fullTag (e.g. '<a onclick="alert('XSS');">')
     * @param string $attributes (e.g. 'a onclick="alert('XSS');"')
     * @return string
     */
    protected function removeAttribute($fullTag, $attributes)
    {
        $replacement = preg_replace($this->replaceRegex, '', $attributes);

        return str_ireplace($attributes, $replacement, $fullTag);
    }

    /**
     * Build the regex for finding tags which contain one or more of the unwanted attributes
     *
     * @return string
     */
    protected function buildSearchRegex()
    {
        return implode('',[
            '#',
                '<([^>]+',
                '(?<!\w)',
                    '(?:', implode('|', $this->attributes), ')',
                '[^0-9a-z"\'=]*', // https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Non-alpha-non-digit_XSS
                '=[^>]+)>',
            '#si',
        ]);
    }

    /**
     * Build the regex for replacing unwanted attributes
     *
     * @return string
     */
    protected function buildReplaceRegex()
    {
        return implode('', [
            '#',
            '(?<!\w)',
                '(?:', implode('|', $this->attributes), ')',
            '[^0-9a-z"\'=]*', // https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Non-alpha-non-digit_XSS
            '=',
            '(?:',
                '(["\'`])', // quoted
                '.*?',
                '\1', // quote character
            '|',
                '(?<!["\'`])', // unqouted
                '((?:[^ >;])*;?)', // everything up to space, '>' or ';'
            ')',
            '#si',
        ]);
    }

}
