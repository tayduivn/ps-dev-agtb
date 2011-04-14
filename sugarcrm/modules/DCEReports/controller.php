<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*
 * Created on Feb 20, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once('include/MVC/Controller/SugarController.php');

require_once('include/export_utils.php');
class DCEReportsController extends SugarController{
    function DCEReportsController(){
        parent::SugarController();
    }
    function action_LicensingReport()
    {
        $this->view = 'licensingreport';
    }
    function action_Run_LincensingReport()
    {
        global $timedate, $db;
        $seed = new DCEReport();
        $start_date=strtotime($_REQUEST['startDate_date']);
        $end_date = strtotime("+1 day -1 second", strtotime($_REQUEST['endDate_date']));
        $end_date=db_convert("'".$timedate->to_db($end_date)."'",'datetime'); 
        $start_date=db_convert("'".$timedate->to_db($start_date)."'",'datetime');
        $first=false;
        $where='(';
        foreach($_REQUEST['instances_types_opt'] as $v){
            if($first){
                $where.= " OR ";
            }
            $first=true;
            $where .= "inst.type='$v'";
        }
        $where .= ")";
        $seed->start_date=$start_date;
        $seed->end_date=$end_date;
        $seed->where=$where;
// Create Export File
        $query_array=$seed->create_new_list_query('','','','','','','','','');
        $query=$query_array['select'].$query_array['from'].$query_array['where'].$query_array['order_by'];
        $res = $seed->db->query($query);
        while($row = $seed->db->fetchByAssoc($res)){
            $seed->id="";
            $seed->update_key_user_id="";
            $seed->get_key_user_id="";
            $seed->last_name="";
            
            $seed->id=$row['id'];
            if(isset($row['update_key_user_id']))
                $seed->update_key_user_id=$row['update_key_user_id'];
            if(isset($row['get_key_user_id']))
                $seed->get_key_user_id=$row['get_key_user_id'];
            if(isset($row['last_name']))
                $seed->last_name=$row['last_name'];
            foreach($row as $k=>$va){
                //We don't show the ids in the CSV file
                if($k!='id' && $k!='update_key_user_id' && $k!='get_key_user_id')
                    $values[$row['id']][$k]=$va;
            }
            foreach($seed->fill_in_additional_list_fields() as $key=>$value){
                if(array_key_exists($key, $values[$row['id']]) && empty($values[$row['id']][$key])){
                        $values[$row['id']][$key]=$value;
                }else{
                    $values[$row['id']][$key]=$value;
                }
            }
        }
        $arr_value=array_values($values);
        $count=0;
        $max_field=0;
        //create the field list
        foreach($arr_value as $k=>$v){
            foreach($v as $field=>$value){
                $fieldList[$field]=$field;
            }
        }
        // For translate the name of the fields
        /*
        $modules = array(   "Account" => "Accounts", 
                            "Contact" => "Contacts", 
                            "User" => "Users", 
                            "DCEInstance" => "DCEInstances"
                        );
        foreach($fieldList as $field){
            foreach($modules as $bean=>$module){
                require_once('modules/'.$module.'/'.$bean.'.php');
                $focus = new $bean();
                if(isset($focus->field_name_map[$field]['vname'])){
                    $fieldList[$field]=translate($focus->field_name_map[$field]['vname'], $module);
                }
            }
        }*/
        $header = "{$_REQUEST['startDate_date']} To {$_REQUEST['endDate_date']}";
        $header .= "\"\r\n";
        $header.="\"Instance types : ".implode(", ",$_REQUEST['instances_types_opt']);
        $header .= "\"\r\n";
        $header .= implode("\"".getDelimiter()."\"", $fieldList);
        $header = "\"" .$header;
        $header .= "\"\r\n";
        $content = $header;
        
        foreach($values as $id=>$va){
            $new_arr = array();
            foreach($fieldList as $k=>$field){
                if(isset($va[$k])){
                    array_push($new_arr, preg_replace("/\"/","\"\"", $va[$k]));
                }else{
                    array_push($new_arr, "");
                }
            }
            $line = implode("\"".getDelimiter()."\"", $new_arr);
            $line = "\"" .$line;
            $line .= "\"\r\n";
    
            $content .= $line;
        }
        $TmpFile="{$GLOBALS['sugar_config']['tmp_dir']}LicensingReport.tmp";
        file_put_contents($TmpFile, $content);
// ListView
        $where='';
        
        require_once ('include/ListView/ListViewSmarty.php');
        $lv = new ListViewSmarty();
        $lv->lvd->additionalDetailsAjax=false;
        $lv->quickViewLinks = false;
        $listViewDefs = array (
            'DCEReports' =>
            array (
              'INSTANCE_NAME' => array ('width' => '15', 'label' => 'LBL_NAME', 'default' => true),
              'ACCOUNT_NAME' => array( 'width' => '10',  'label' => 'LBL_ACCOUNT'),
              'NUM_OF_USERS' => array( 'width' => '10', 'label' => 'LBL_NUM_OF_USERS'),
              'FIRST_NAME' => array( 'width' => '10', 'label' => 'LBL_FIRST_NAME'),
              'LAST_NAME' => array( 'width' => '10', 'label' => 'LBL_LAST_NAME'),
              'CONTACT_ROLE' => array( 'width' => '10', 'label' => 'LBL_CONTACT_ROLE'),
            )
        );
        $lv->displayColumns = $listViewDefs['DCEReports'];
        //disable some features.
        $lv->mergeduplicates = false;
        $lv->delete = false;
        $lv->select = false;
        $lv->multiSelect = false;
        $lv->export = false;
        $lv->contextMenus = true;
        $lv->show_mass_update_form = false;
        $lv->ss->assign('exportLink', '<input class="button" type="button" value="'.$GLOBALS['app_strings']['LBL_EXPORT'].
                '" onclick="document.location.href = \'index.php?module=DCEReports&action=Export_LincensingReport\'">');
        $lv->setup($seed, 'include/ListView/ListViewGeneric.tpl', $where);
        $contents = $lv->display(false);
        echo $contents;

    }
    function action_Export_LincensingReport()
    {
        $content=file_get_contents("{$GLOBALS['sugar_config']['tmp_dir']}LicensingReport.tmp");
        ob_clean();
        header("Pragma: cache");
        header("Content-type: application/octet-stream; charset=".$GLOBALS['locale']->getExportCharset());
        header("Content-Disposition: attachment; filename=licensingReport.csv");
        header("Content-transfer-encoding: binary");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
        header("Last-Modified: " . $timedate->httpTime() . " GMT" );
        header("Cache-Control: post-check=0, pre-check=0", false );
        header("Content-Length: ".strlen($content));
        
        print $GLOBALS['locale']->translateCharset($content, 'UTF-8', $GLOBALS['locale']->getExportCharset());
        
        sugar_cleanup(true);
    }
}
?>