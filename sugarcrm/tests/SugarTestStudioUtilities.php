<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
 
require_once('include/MVC/Controller/SugarController.php');
require_once('modules/ModuleBuilder/controller.php');
require_once('modules/ModuleBuilder/parsers/ParserFactory.php');

class SugarTestStudioUtilities
{
    private static $_fieldsAdded = array();

    private function __construct() {}
    
    /*
     * $module_name should be the module name (Contacts, Leads, etc)
     * $view should be the layout (editview, detailview, etc)
     * $field_name should be the name of the field being added
     */
    public static function addFieldToLayout($module_name, $view, $field_name) 
    {
        $parser = ParserFactory::getParser($view, $module_name);
        $parser->addField(array('name' => $field_name));
        //$parser->writeWorkingFile();
        $parser->handleSave(false);
        unset($parser);
        
        self::$_fieldsAdded[$module_name][$view][$field_name] = $field_name;
    }
    
    public static function removeAllCreatedFields()
    {
        foreach(self::$_fieldsAdded as $module_name => $views)
        {
            foreach($views as $view => $fields)
            {
                $parser = ParserFactory::getParser($view, $module_name);
                foreach($fields as $field_name)
                {
                    $parser->removeField($field_name);
                }
                $parser->handleSave(false);
                unset($parser);
            }
        }
    }

}
?>