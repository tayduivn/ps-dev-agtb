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
    protected $_module;
    private $_currentCount;
    protected static $operatorMap = array (
        'and' => 1,
        'or' => 1,
    );

    public function __construct($maxLen=80, $maxHits=2, $preTag = '<em>', $postTag = '</em>')
    {
        $this->_maxLen = $maxLen;
        $this->_maxHits = $maxHits;
        $this->_preTag = $preTag;
        $this->_postTag = $postTag;
    }

    public function setMaxLen($maxLen) {
        $this->_maxLen = $maxLen;
    }

    public function setMaxHits($maxHits) {
        $this->_maxHits = $maxHits;
    }

    public function setTags($preTag, $postTag) {
        $this->_preTag = $preTag;
        $this->_postTag = $postTag;
    }

    /**
     * Setter for module name
     *
     * @param $module
     */
    public function setModule($module)
    {
        $this->_module = $module;
    }

    protected function castrate($string, $maxLen) {

        // length is ok, no further process needed
        $len = mb_strlen($string, 'UTF-8');
        if ($len <= $maxLen) {
            return $string;
        }

        // not much room left, just return ...
        if ($maxLen <= 10) {
            return ' ... ';
        }

        // when a string truncate is to happen, this is the length remained on both sides of the string
        // for example, "this is a very long string" becomes
        // "this ... string" if $remainder is 3 (try not to cut in the middle of a word
        $remainder = round(($maxLen - 5) / 2);

        $front = mb_substr($string, 0, $remainder, 'UTF-8');
        $middle = mb_substr($string, $remainder, $len-$remainder*2, 'UTF-8');
        $rear = mb_substr($string, -$remainder, $remainder, 'UTF-8');

        // In order not to cut in the middle of words, we search for space before/after the cutting point,
        // but if the space is too far from the cutting point, we may still just cut the words.
        // This is necessary especially for languages like CJK, which do not have space between characters
        // so the nearest space could be one paragraph away.
        $maxDistance = 10;
        if ($string[$remainder] != ' ' && $string[$remainder] != ' ')
        {
            // search for space between $string[0] and $string[$remainder-1]
            $pos = mb_strrpos($front, ' ', 0, 'UTF-8');
            if ($pos && (mb_strlen($front, 'UTF-8') - $pos < $maxDistance)) // found a space
            {
                $front = mb_substr($front, 0, $pos, 'UTF-8');
            }
            else
            {
                // search space in $middle
                $pos = mb_strpos($middle, ' ', 0, 'UTF-8');
                if ($pos && $pos < $maxDistance)
                {
                    $front .= mb_substr($middle, 0, $pos, 'UTF-8');
                }
            }
        }
        $toCheck = mb_strlen($string, 'UTF-8') - $remainder;
        if ($string[$toCheck] != ' ' && $string[$toCheck-1] != ' ')
        {
            // search for space in $rear
            $pos = mb_strpos($rear, ' ', 0, 'UTF-8');
            if ($pos && $pos < $maxDistance) // found a space
            {
                $i = mb_strlen($rear, 'UTF-8') - $pos - 1;
                $rear = mb_substr($rear, -$i, $i, 'UTF-8');
            }
            else
            {
                $pos = mb_strrpos($middle, ' ', 0, 'UTF-8');
                if ($pos && (mb_strlen($middle, 'UTF-8') - $pos < $maxDistance))
                {
                    $i = mb_strlen($middle, 'UTF-8') - $pos - 1;
                    $rear = mb_substr($middle, -$i, $i, 'UTF-8') . $rear;
                }
            }
        }
        return $front . ' ... ' . $rear;
    }

    protected function postProcessHighlights($original) {

        // subtract the tag length when calculating the total length
        $tagLength = strlen($this->_preTag) + strlen($this->_postTag);
        $totalTagLen = $this->_currentCount * $tagLength;

        // length is ok, no further process needed
        if (strlen($original) - $totalTagLen <= $this->_maxLen) {
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
                $len += strlen($a[$i][0]) - $tagLength;
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

    protected function highlightCallback($matches) {
        // escape user input before display to avoid XSS
        return $this->_preTag . htmlspecialchars(trim($matches[0])) . $this->_postTag;
    }

    protected function isOperator($search)
    {
        $search = strtolower($search);
        if (isset(self::$operatorMap[$search]))
        {
            return true;
        }

        return false;
    }

    /**
     *
     * This function returns an array of highlighted strings.
     *
     * @param $resultArray array returned from search engine
     * @param $searchString string the string to be searched and highlighted
     *
     * @return array of key value pairs
     */
    public function getHighlightedHitText($resultArray, $searchString)
    {
        $ret = array();

        // it may contain multiple words
        $searches = preg_split("/[\s,-]+/", $searchString);

        foreach ($resultArray as $field=>$value)
        {
            $this->_currentCount = 0;

            foreach ($searches as $search)
            {
                if (empty($search) || $this->isOperator($search))
                {
                    continue;
                }

                $pattern = '/\b' . str_replace('*', '.*?', $search) . '\b/i';
                $value = preg_replace_callback($pattern, array($this, 'highlightCallback'), $value, -1, $count);
                if ($count > 0)
                {
                    $this->_currentCount += $count;
                    $field = $this->translateFieldName($field);
                    $ret[$field] = $this->postProcessHighlights($value);
                }
            }
        }

        $GLOBALS['log']->debug('FTS highligh: ' . print_r($ret,true));
        return $ret;
    }

    public function translateFieldName($field)
    {
        if(empty($this->_module))
        {
            return $field;
        }
        else
        {
            $tmpBean = BeanFactory::getBean($this->_module, null);
            $field_defs = $tmpBean->field_defs;
            $field_def = isset($field_defs[$field]) ? $field_defs[$field] : FALSE;
            if($field_def === FALSE || !isset($field_def['vname']))
                return $field;

            $module_lang = return_module_language($GLOBALS['current_language'], $this->_module);
            if(isset($module_lang[$field_def['vname']]))
            {
                $label = $module_lang[$field_def['vname']];
                if( substr($label,-1) == ':')
                    return (substr($label, 0, -1));
                else
                    return $label;
            }
            else
            {
                return $field;
            }
        }
    }
}