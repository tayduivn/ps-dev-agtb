<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once("include/SugarSearchEngine/SugarSearchEngineHighlighter.php");


/**
 * ElasticHighlighter
 */
class SugarSearchEngineElasticHighlighter extends SugarSearchEngineHighlighter
{
    protected $module;

    /**
     * Setter for module name
     *
     * @param $module
     */
    public function setModule($module)
    {
        $this->module = $module;
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
        $searches = explode(' ', $searchString);

        foreach ($resultArray as $field=>$value)
        {
            // skip the summary_text as it's for auto complete searches
            if ($field == SugarSearchEngineElastic::SUMMARY_TEXT)
            {
                continue;
            }
            foreach ($searches as $search)
            {
                if (empty($search))
                {
                    continue;
                }

                $pattern = '/\b' . str_replace('*', '.*?', $search) . '\b/i';
                $value = preg_replace_callback($pattern, array($this, 'highlightCallback'), $value, -1, $count);
                if ($count > 0)
                {
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
        if(empty($this->module))
        {
            return $field;
        }
        else
        {
            $tmpBean = BeanFactory::getBean($this->module, null);
            $field_defs = $tmpBean->field_defs;
            $field_def = isset($field_defs[$field]) ? $field_defs[$field] : FALSE;
            if($field_def === FALSE || !isset($field_def['vname']))
                return $field;

            $module_lang = return_module_language($GLOBALS['current_language'], $this->module);
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
    /**
     * Return a string that matches the auto complete search.
     *
     * @param $resultArray array returned from search engine
     * @return string
     */
    public function getAutoCompleteText($resultArray)
    {
        $ret = '';
        foreach ($resultArray as $field=>$value) {
            // skip summary_text field for auto complete search
            if ($field != SugarSearchEngineElastic::SUMMARY_TEXT) {
                continue;
            }

            $ret = $value;
            break;
        }
        $GLOBALS['log']->debug('FTS auto complete: ' . $ret);
        return $ret;
    }
}