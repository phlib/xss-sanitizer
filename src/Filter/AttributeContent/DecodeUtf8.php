<?php

namespace Phlib\XssSanitizer\Filter\AttributeContent;

use Phlib\XssSanitizer\FilterInterface;

/**
 * Class DecodeUtf8
 * @package Phlib\XssSanitizer\Filter\AttributeContent
 */
class DecodeUtf8 implements FilterInterface
{

    /**
     * Decode UTF-8 encoded characters in an attribute content string
     *
     * e.g.
     *     \u006Aavascript:alert('XSS');
     * becomes
     *     javascript:alert('XSS');
     *
     * @param string $str
     * @return string
     */
    public function filter($str)
    {
        $str = preg_replace_callback(
            '#\\\\u([0-9a-f]{4})#i',
            function($matches) {
                return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UCS-2BE');
            },
            $str
        );

        return $str;
    }

}
