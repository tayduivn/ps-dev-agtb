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
 * $Id: view.step2.php 31561 2008-02-04 18:41:10Z jmertic $
 * Description: view handler for step 2 of the import process
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 ********************************************************************************/
require_once('include/MVC/View/SugarView.php');
  
 
require_once('include/SugarFields/Fields/Datetimecombo/SugarFieldDatetimecombo.php');
class DCEInstancesViewDCEUpgradestep2 extends SugarView 
{	
 	/**
     * Constructor
     */
 	public function DCEUpgradeStep2(){
        parent::SugarView();
 	}
 	/** 
     * display the form
     */
 	public function display(){
 	    global $mod_strings, $app_list_strings, $app_strings, $current_user;
        if(empty($_REQUEST['uid']) && empty($_REQUEST['record'])){
            header("Location: index.php?module=DCEInstances&action=DCEUpgradeStep1");
            die();
        }
        $uids='';
        if(!empty($_REQUEST['uid'])){
            $uids = explode(",", $_REQUEST['uid']);
            $this->ss->assign("UIDS", $_REQUEST['uid']);
        }
        else{
            $uids[0]=$_REQUEST['record'];
            $this->ss->assign("UIDS", $_REQUEST['record']);
        }
        $dcetpl = new DCETemplate();
        $dceinst = new DCEInstance();
        $dceinst->retrieve($uids[0]);
        $dcetpl->retrieve($dceinst->dcetemplate_id);
        $query = "SELECT name ,id 
                    FROM dcetemplates 
                    WHERE sugar_edition='{$dcetpl->sugar_edition}'
                    AND upgrade_acceptable_version like '%{$dcetpl->sugar_version}%'
                    AND deleted=0";
        
        $result = $dcetpl->db->query($query,true," Error: ");
        $obj_arr = array();
        $ToTemplateDD='';
        while ($row = $dcetpl->db->fetchByAssoc($result,-1,FALSE) ) {
            if($row['id']!=$dceinst->dcetemplate_id)
                $ToTemplateDD[$row['id']]=$row['name'];
        }

        $this->ss->assign("MODULE_TITLE", 
            get_module_title(
                $mod_strings['LBL_MODULE_NAME'], 
                $mod_strings['LBL_MODULE_NAME']." ".$mod_strings['LBL_DCEUPGRADE_STEP_2_TITLE'], 
                false
                )
            );
        $this->ss->assign("MOD", $mod_strings);
        $this->ss->assign("APP", $app_strings);
        $this->ss->assign("JAVASCRIPT", $this->_getJS());
        $this->ss->assign("FROMTEMPLATE", $dcetpl->name);
        $this->ss->assign("TOTEMPLATEDD", $ToTemplateDD);
 	    if(isset($_REQUEST['return_action'])){
            $this->ss->assign("RETURN_ACTION", $_REQUEST['return_action']);
        }else{
            $this->ss->assign("RETURN_ACTION", 'index');
        }
        if(isset($_REQUEST['return_id']))$this->ss->assign("RETURN_ID", $_REQUEST['return_id']);
//For date Time Combo
        global $timedate;
 	    $this->ss->assign('DEFAULT_DATE', $timedate->to_display_date_time(gmdate($timedate->get_date_time_format())));
        $this->ss->assign('CALENDAR_DATEFORMAT', $timedate->get_cal_date_format());
        $this->ss->assign('USER_DATEFORMAT', $timedate->get_user_date_format());
        $time_format = $timedate->get_user_time_format();
        $this->ss->assign('TIME_FORMAT', $time_format);

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
        $this->ss->assign('TIME_SEPARATOR', $time_separator);
        
// ListView
        $where=NULL;
        foreach($uids as $k=>$v){
            if($k!='0')
                $where.=" OR ";
            $where.="dceinstances.id='$v'";
        }
        
        $seed = new DCEInstance();
        require_once ('include/ListView/ListViewSmarty.php');
        $lv = new ListViewSmarty();
        $lv->lvd->additionalDetailsAjax=false;
        include ('modules/DCEInstances/metadata/listviewdefs.php');
        $lv->displayColumns = $listViewDefs['DCEInstances'];
        //disable some features.
        $lv->mergeduplicates = false;
        $lv->delete = false;
        $lv->select = false;
        $lv->multiSelect = false;
        $lv->export = false;
        $lv->show_mass_update_form = false;
        
        $lv->setup($seed, 'include/ListView/ListViewGeneric.tpl', $where);
        $contents = $lv->display(false);
        $this->ss->assign('LISTVIEW', $contents);
//display
        $this->ss->display('modules/DCEInstances/tpls/DCEUpgradeStep2.tpl');
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
function update_options(){
    if(document.getElementById('upgrade_live').checked){
        document.getElementById('save_clone_on_error_div').style.visibility = '';
        document.getElementById('save_clone_on_error').disabled = false;
    }else{
        document.getElementById('save_clone_on_error_div').style.visibility = 'hidden';
        document.getElementById('save_clone_on_error').disabled = true;
    }
}
document.getElementById('goback').onclick = function(){
    document.getElementById('dceupgradestep2').action.value = document.getElementById('dceupgradestep2').return_action.value;
    document.getElementById('dceupgradestep2').record.value = document.getElementById('dceupgradestep2').return_id.value;
    return true;
}

document.getElementById('gonext').onclick = function(){
    if(document.getElementById('upgrade_live').checked){
        document.getElementById('dceupgradestep2').actionType.value = 'upgrade_live';
        if(document.getElementById('save_clone_on_error').checked){
            document.getElementById('dceupgradestep2').delete_clone.value = false;
        }else{
            document.getElementById('dceupgradestep2').delete_clone.value = true;
        }
    }else{
        document.getElementById('dceupgradestep2').actionType.value = 'upgrade_test';
        document.getElementById('dceupgradestep2').delete_clone.disabled = true;
    }
    document.getElementById('dceupgradestep2').action.value = 'CreateAction';
    return true;
}
-->
</script>

EOJAVASCRIPT;
    }
}
