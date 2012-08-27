<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *(i) the "Powered by SugarCRM" logo and
 *(ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright(C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

class SugarWidgetFieldEncrypt extends SugarWidgetReportField
{
    public function SugarWidgetFieldEncrypt($layout_manager) {
        parent::SugarWidgetReportField($layout_manager);
    }

    function queryFilterEquals(&$layout_def)
    {
        require_once("include/utils/encryption_utils.php");
        $search_value = blowfishEncode(blowfishGetKey('encrypt_field'),$layout_def['input_name0']);
        return $this->_get_column_select($layout_def)."='".$GLOBALS['db']->quote($search_value)."'\n";
    }

    function queryFilterNot_Equals_Str(&$layout_def)
    {
        require_once("include/utils/encryption_utils.php");
        $search_value = blowfishEncode(blowfishGetKey('encrypt_field'),$layout_def['input_name0']);
        return $this->_get_column_select($layout_def)."!='".$GLOBALS['db']->quote($search_value)."'\n";
    }

    function displayList($layout_def) {
            return $this->displayListPlain($layout_def);
    }

    function displayListPlain($layout_def) {
            $value= $this->_get_list_value($layout_def);

            require_once("include/utils/encryption_utils.php");

            $value = blowfishDecode(blowfishGetKey('encrypt_field'), $value);
            return $value;
    }
       
}