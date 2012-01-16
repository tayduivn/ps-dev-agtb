<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2012 SugarCRM, Inc.; All Rights
 *Reserved.
 ********************************************************************************/


/**
 * Highlighter
 */
class SugarSearchEngineHighlighter
{
    protected $_maxLen;
    protected $_maxHits;
    protected $_preTag;
    protected $_postTag;
    protected $_elasticaResult = null;

    public function __construct($maxLen=80, $maxHits=2, $preTag = '<em>', $postTag = '</em>')
    {
        $this->_maxLen = $maxLen;
        $this->_maxHits = $maxHits;
        $this->_preTag = $preTag;
        $this->_postTag = $postTag;
    }

    protected function castrate($string, $maxLen) {

        // length is ok, no further process needed
        if (strlen($string) <= $maxLen) {
            return $string;
        }

        // not much room left, just return ...
        if ($maxLen <= 10) {
            return ' ... ';
        }

        // when a string truncate is to happen, this is the length remained on both sides of the string
        // for example, "this is a very long string" becomes
        // "thi ... ing" if $remainder is 3
        $remainder = ($maxLen - 5) / 2;

        return mb_strcut($string, 0, $remainder, 'UTF-8') . ' ... ' . mb_strcut($string, -$remainder, $remainder, 'UTF-8');
    }

    protected function postProcessHighlights($original) {

        // length is ok, no further process needed
        if (strlen($original) <= $this->_maxLen) {
            return $original;
        }

        $pattern = "(" . $this->_preTag . ".*?" . $this->_postTag . ")";
        $pattern = str_replace('/', '\/', $pattern); //escaping
        $pattern = '/' . $pattern . '/';

        // this breaks down the string and the odd indexed elements will be
        // highlighted strings and the even ones are non-highlighted
        $a = preg_split($pattern, $original, -1, PREG_SPLIT_OFFSET_CAPTURE|PREG_SPLIT_DELIM_CAPTURE);

        $hitCount = (count($a) - 1) / 2;

        // hit count already under limit, need to trim some fat
        if ($hitCount <= $this->_maxHits) {

            // the total length of highlighted words
            $len = 0;
            for ($i=1; $i<=count($a)-1; $i=$i+2) {
                $len += strlen($a[$i][0]);
            }

            // available length for the non-highlighted strings
            $availableLen = $this->_maxLen - $len;
            if ($availableLen < 0) {
                // this should not happen, unless a very tiny maxLen is given
                return $a[1][0];
            }

            // available length for each non-highlighted string
            $availPerStr = $availableLen / ($hitCount+1);

            // shorten the non-highlighted strings if needed
            for ($i=0; $i<count($a); $i=$i+2) {
                if (strlen($a[$i][0]) > $availPerStr) {
                    $a[$i][0] = $this->castrate($a[$i][0], $availPerStr);
                }
            }

            // final string
            $final = '';
            foreach ($a as $hit) {
                $final .= $hit[0];
            }

            return $final;
        }
        // hit count over the limit, try removing extra hits first then process again
        else {
            $newStr = substr($original, 0, $a[($this->_maxHits*2)+1][1]);

            return $this->postProcessHighlights($newStr);
        }
    }

    public function highlightCallback($matches) {
        // escape user input before display to avoid XSS
        return $this->_preTag . htmlspecialchars(trim($matches[0])) . $this->_postTag;
    }

    public function getHighlightedHitText($resultArray)
    {
        $ret = array();

        // this is the word to be searched
        if (!isset($_REQUEST['q'])) {
            return $ret;
        }
        $q = html_entity_decode(trim($_REQUEST['q']), ENT_QUOTES);

        $searches = explode(' ', $q);

        foreach ($resultArray as $field=>$value) {
            foreach ($searches as $search) {
                if (empty($search)) {
                    continue;
                }
                $pattern = '/\b' . str_replace('*', '.*?', $search) . '\b/i';
                $value = preg_replace_callback($pattern, array($this, 'highlightCallback'), $value, -1, $count);
                if ($count > 0) {
                    $ret[$field] = $this->postProcessHighlights($value);
                }
            }
        }

        return $ret;
    }

}