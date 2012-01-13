<?php
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
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


class FormulaHelper
{

    /**
     * Takes an array of field defs and returns a formated list of fields that are valid for use in expressions.
     *
     * @param array $fieldDef
     * @return array
     */
    public static function cleanFields($fieldDef, $includeLinks = true, $forRelatedField = false)
    {
        $fieldArray = array();
        foreach ($fieldDef as $fieldName => $def) {
            if ($fieldName == 'deleted' || $fieldName == 'email1' || empty($def['type']))
                continue;
            //Check the studio property of the field def.
            if (isset($def['studio']) && (self::isFalse($def['studio']) || (is_array($def['studio']) && (
                (isset($def['studio']['formula']) && self::isFalse($def['studio']['formula'])) ||
                ($forRelatedField && isset($def['studio']['related']) && self::isFalse($def['studio']['related']))
            ))))
            {
                continue;
            }
            switch ($def['type']) {
                case "int":
                case "float":
                case "decimal":
                case "currency":
                    $fieldArray[] = array($fieldName, 'number');
                    break;
                case "bool":
                    $fieldArray[] = array($fieldName, 'boolean');
                    break;
                case "varchar":
                case "name":
                case "phone":
                case "text":
                case "url":
                case "encrypt":
                case "enum":
                    $fieldArray[] = array($fieldName, 'string');
                    break;
                case "date":
                case "datetime":
                case "datetimecombo":
                    $fieldArray[] = array($fieldName, 'date');
                    break;
                case "link":
                    if ($includeLinks)
                        $fieldArray[] = array($fieldName, 'relate');
                    break;
                default:
                    //Do Nothing
                    break;
            }
        }
        return $fieldArray;
    }

    protected static function isFalse($v){
        if (is_string($v)){
            return strToLower($v) == "false";
        }
        if (is_array($v))
            return false;

        return $v == false;
    }

}