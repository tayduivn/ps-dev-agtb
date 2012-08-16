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
 * @api
 */
class SugarSearchEngineHighlighter
{
    protected $_module;

    public static $preTag = '<span class="highlight">';
    public static $postTag = '</span>';
    public static $fragmentSize = 20;
    public static $fragmentNumber = 2;

    public function __construct()
    {
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

    public function processHighlightText($resultArray)
    {
        $ret = array();
        foreach ($resultArray as $field=>$fragments)
        {
            $field = $this->translateFieldName($field);
            $first = true;
            foreach ($fragments as $frament)
            {
                if (!$first)
                {
                    $ret[$field] .= '...' . $frament;
                }
                else
                {
                    $ret[$field] = $frament;
                }
                $first = false;
            }
        }

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