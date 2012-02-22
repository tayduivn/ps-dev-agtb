<?php

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

include_once("internalObjects/RestError.php");

class RestFactory {

    public static function newRestObject($objName) {
        global $restObjectList;

        include_once("RestData.php");

        try {
            if (array_key_exists($objName, $restObjectList)) {
                include_once($restObjectList[$objName]);
                return new $objName();
            }

            if (RestFactory::isValidSugarModule($objName)) {
                $objName = ucfirst($objName);
                include_once("internalObjects/restsugarobject.php");
                return new RestSugarObject($objName);
            } else {
                $err = new RestError();
                $err->ReportError(404, "\nUnknown Object: '{$objName}'\n\n");
            }
        } catch (Exception $e) {
            $err = new RestError();
            $err->ReportError(404, "\nUnknown request!\n\n");
        }
    }

    public static function uriToBeanName($modName) {
        $result = $modName;
        $result = ucfirst($result);
        return $result;
    }

    /**
     * Checks to see if the requested module/object exists in the sugar modules.php
     *
     * @return bool
     */
    public static function isValidSugarModule($modName) {
        global $moduleList;
        $valid = false;

        include_once("include/modules.php");

        $modName = ucfirst($modName);
        if (in_array($modName, $moduleList)) {
            $valid = true;
        }

        return $valid;
    }

}
