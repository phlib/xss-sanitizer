<?php

namespace Phlib\XssSanitizer\Filter;

use Phlib\XssSanitizer\FilterInterface;

/**
 * Class ScriptTag
 *
 * @package Phlib\XssSanitizer\Filter
 */
class ScriptTag implements FilterInterface
{

    /**
     * Filter script tags by html encoding the opening angle bracket
     *
     * e.g.
     *     <script type="text/javascript">alert('XSS');</script>
     * becomes
     *     &lt;script type="text/javascript">alert('XSS');&lt;/script>
     *
     * @param string $str
     * @return string
     */
    public function filter($str)
    {
        $str = preg_replace('#<(/?script)#si', '&lt;\1', $str);

        return $str;
    }

}
