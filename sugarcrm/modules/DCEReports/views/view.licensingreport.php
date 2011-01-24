<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2007 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: view.licensingreport.php 31561 2008-02-04 18:41:10Z bsoufflet $
 * Description: view handler for step 2 of the import process
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 ********************************************************************************/
require_once('include/MVC/View/SugarView.php');

class DCEReportsViewLicensingReport extends SugarView 
{   
    /**
     * Constructor
     */
    public function LicensingReport(){
        parent::SugarView();
    }
    /** 
     * display the form
     */
    public function display(){
        global $mod_strings, $app_list_strings, $app_strings, $current_user;
        $this->ss->assign("MODULE_TITLE", 
            getClassicModuleTitle(
                $mod_strings['LBL_MODULE_NAME'], 
                array($mod_strings['LBL_MODULE_NAME'],$mod_strings['LBL_LICENSING_REPORT']), 
                false
                )
            );
        $this->ss->assign("MOD", $mod_strings);
        $this->ss->assign("APP", $app_strings);
        $this->ss->assign("JAVASCRIPT", $this->_getJS());
        //$this->ss->assign("RETURN_ACTION", $_REQUEST['return_action']);
        if(isset($_REQUEST['return_id']))$this->ss->assign("RETURN_ID", $_REQUEST['return_id']);
//For date
        global $timedate;
        $this->ss->assign('DEFAULT_START_DATE', $timedate->asUserDate($timedate->getNow()->get("-1 month")));
        $this->ss->assign('DEFAULT_END_DATE', $timedate->asUserDate($timedate->getNow()->get("-1 day")));
        $this->ss->assign('CALENDAR_DATEFORMAT', $timedate->get_cal_date_format());
        $this->ss->assign('USER_DATEFORMAT', $timedate->get_user_date_format());
        $time_format = $timedate->get_user_time_format();
        $date_format = $timedate->get_cal_date_format();
        $time_separator = ":";
        if(preg_match('/\d+([^\d])\d+([^\d]*)/s', $time_format, $match)) {
           $time_separator = $match[1];
        }

        // Create Smarty variables for the Calendar picker widget
        $t23 = strpos($time_format, '23') !== false ? '%H' : '%I';
        if(!isset($match[2]) || $match[2] == '') {
          $this->ss->assign('CALENDAR_FORMAT', $date_format . ' ' . $t23 . $time_separator . "%M");
        } else {
          $pm = $match[2] == "pm" ? "%P" : "%p";
          $this->ss->assign('CALENDAR_FORMAT', $date_format . ' ' . $t23 . $time_separator . "%M" . $pm);
        }
        $this->ss->assign('INSTANCES_TYPES_OPT', $app_list_strings['instance_type_list']);
        $this->ss->assign('INSTANCES_TYPES_SELECTED', array_keys($app_list_strings['instance_type_list']));
//display
        $this->ss->display('modules/DCEReports/tpls/LicensingReport.tpl');
    }
    /**
     * Returns JS used in this view
     */
    private function _getJS()
    {
        global $mod_strings;
        return <<<EOJAVASCRIPT
<script type="text/javascript">
<!--
addToValidate('licensingreport', 'endDate_date', 'date',true, "{$mod_strings['LBL_END_DATE']}");
addToValidateDateBefore('licensingreport', 'startDate_date', 'date', true, "{$mod_strings['LBL_START_DATE']}", 'endDate_date');
addToValidate('licensingreport', 'instances_types_opt', 'select',true, "{$mod_strings['LBL_INSTANCES_TYPES']}");
document.getElementById('btnrun').onclick = function(){
    if(check_form('licensingreport')){
        document.getElementById('licensingreport').action.value = 'run_lincensingReport';    
        runReport();
    }else{
        return false;
    }
}
function runReport(){
    var callback = {
        success:function(o){
            document.getElementById('listview').innerHTML=o.responseText;
            document.getElementById('listview').style.display="inline";
            document.getElementById('loading_img').style.display="none";
        },
        failure:function(o){
            alert(SUGAR.language.get('app_strings','LBL_AJAX_FAILURE'));
        }
    }
    document.getElementById('loading_img').style.display="inline";
    YAHOO.util.Connect.setForm('licensingreport');
    YAHOO.util.Connect.asyncRequest('POST', 'index.php?module=DCEReports&action=Run_LincensingReport&to_pdf=1', callback);

}
-->
</script>

EOJAVASCRIPT;
    }
}
