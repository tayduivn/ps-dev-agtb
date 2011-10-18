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
require_once('soap/SoapHelperFunctions.php');
class MailMergeController extends SugarController{
	function MailMergeController(){
		parent::SugarController();
	}

    public function action_search(){
        //set ajax view
        $this->view = 'ajax';
        global $beanList, $beanFiles, $current_user;
        $module = $_REQUEST['qModule'];
        $term = $_REQUEST['term'];
        $lmodule = strtolower($module);
        $relModule = !empty($_REQUEST['rel_module']) ? $_REQUEST['rel_module'] : null;
        $where = '';
        $order_by = strtolower($module).".name";
        $using_cp = false;
        if($term != '')
        {
            if($module == 'Contacts' || $module == 'Leads')
            {
                $where = $lmodule.".first_name like '%".$term."%' OR ".$lmodule.".last_name like '%".$term."%'";
                $order_by = $lmodule.".last_name";
            }
            else if($module == 'CampaignProspects'){
                $using_cp = true;
                $lmodule = strtolower($relModule);
                $campign_where = $_SESSION['MAILMERGE_WHERE'];
                $where = $lmodule.".first_name like '%".$term."%' OR ".$lmodule.".last_name like '%".$term."%'";
                if($campign_where)
                    $where .= " AND ".$campign_where ;
                $where .= " AND related_type = #".$lmodule."#";
                $module = 'Prospects';
            }
            else
            {
                $where = $lmodule.".name like '".$term."%'";
            }
	    }

        $offset = 0;
        $max = 10;

        $class_name = $beanList[$module];
        require_once($beanFiles[$class_name]);
        $seed = new $class_name();
        
        $deleted = '0';
        if($using_cp){
            $fields = array('id', 'first_name', 'last_name');
           $response = $seed->retrieveTargetList($where, $fields, $offset,-1,$max,$deleted);
        }else{
          $response = $seed->get_list($order_by, $where, $offset,-1,$max,$deleted);
        }

        $list = $response['list'];
        $row_count = $response['row_count'];

        $output_list = array();
        foreach($list as $value)
        {
            $output_list[] = get_return_value($value, $module);
        }
        $response = array();
        //$response['id'] = $request_id;

        $response['result'] = array('result_count'=>$row_count,'entry_list'=>$output_list);
        $json = getJSONobj();
        $json_response = $json->encode($response, true);
        print $json_response;
    }
}
?>