<?php
if (!defined('sugarEntry')) define('sugarEntry', true);
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

require_once("include/rest/internalObjects/RestError.php");

/**
 * This is a class factory class for all rest api entry points.  This class works much like
 * the sugar bean factory does in that it reads information from the "RestData.php" file which
 * is also much like "modules.php" where it finds classes by name and then returns the proper
 * php source file containing the class code to implement.
 */
class RestFactory {

    /**
     * Create a new object by based on the $objName.
     *
     * @static
     * @param $objName, the name of the object to create a new class for.
     * @return RestSugarObject, the newly created class instance.
     */
    public static function newRestObject($objName) {
        global $restObjectList;
        $obj = null;

        require_once("include/rest/RestData.php");

        try {
            if (array_key_exists($objName, $restObjectList)) {
                require_once($restObjectList[$objName]);
                return new $objName();
            }

            if (RestFactory::isValidSugarModule($objName)) {
                $objName = ucfirst($objName);
                require_once("include/rest/internalObjects/restsugarobject.php");
                return new RestSugarObject($objName);
            } else {
                $err = new RestError();
                $err->ReportError(404, "\nUnknown Object: '{$objName}'\n\n");
                exit;
            }
        } catch (Exception $e) {
            $err = new RestError();
            $err->ReportError(404, "\nUnknown request!\n\n");
            exit;
        }
    }

    /**
     * Just a simple method for changing the name of the module to a sugar brean name,
     * really this just means making the first char in the string an uppercase.
     *
     * @static
     * @param $modName, the name of the object or sugar bean.
     * @return string, the modified name.
     */
    public static function uriToBeanName($modName) {
        $result = $modName;
        $result = ucfirst($result);
        return $result;
    }

    /**
     * Checks to see if the requested module/object exists in the sugar modules.php, and
     * also checks for a custom modules.php file as well.
     *
     * @return bool
     */
    public static function isValidSugarModule($modName) {
        global $moduleList;
        global $beanList;
        $valid = 0;

        require_once("include/modules.php");

        if (file_exists("custom/include/modules.php")) {
            require_once("custom/include/modules.php");
        }

        $modName = ucfirst($modName);
        if (in_array($modName, $moduleList)) {
            $valid = 1;
        } else if ($valid != 1 && array_key_exists($modName, $beanList)) {
            $valid = 1;
        }

        return $valid;
    }
}
