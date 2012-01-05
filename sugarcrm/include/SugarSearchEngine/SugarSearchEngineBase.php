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
 *Portions created by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights
 *Reserved.
 ********************************************************************************/
require_once('include/SugarSearchEngine/Interface.php');

abstract class SugarSearchEngineBase implements SugarSearchEngineInterface
{

    /**
     * @param $module
     *
     * TODO: We may want to move this to a helper class so that our implementations will 'has-a' rather than 'is-a'.
     */
    protected function retrieveFtsEnabledFieldsPerModule($module)
    {
        $results = array();
        if( is_string($module))
        {
            $obj = BeanFactory::getBean($module, null);

        }
        else if( is_a($module, 'SugarBean') )
        {
            $obj = $module;
        }
        else
        {
            return $results;
        }

        foreach($obj->field_defs as $field => $def)
        {
            if( isset($def['unified_search']) && ( $def['unified_search'] === TRUE ||
                //Support the case where we can store additional metdata in the unifed_search entry
                (is_array($def['unified_search']) && !empty($def['unified_search']['enabled']))) )
                $results[$field] = $def;
        }

        return $results;

    }

    /**
     * Retrieve all FTS fields for all FTS enabled modules.
     *
     * @return array
     */
    protected function retrieveFtsEnabledFieldsForAllModules()
    {
        $results = array();
        foreach( $GLOBALS['moduleList'] as $moduleName )
        {
            if( $this->isModuleFtsEnabled($moduleName) )
            {
                $results[$moduleName] = $this->retrieveFtsEnabledFieldsPerModule($moduleName);
            }

        }
        return $results;
    }

    /**
     * Determine if a module is FTS enabled.
     *
     * @param $module
     * @return bool
     */
    protected function isModuleFtsEnabled($module)
    {
        $obj = BeanFactory::getBean($module, null);
        if( $obj !== FALSE && isset( $GLOBALS['dictionary'][$obj->object_name]) && !empty($GLOBALS['dictionary'][$obj->object_name]['unified_search']) )
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

}