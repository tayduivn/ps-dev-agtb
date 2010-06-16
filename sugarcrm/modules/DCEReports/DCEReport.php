<?PHP
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
/**
 * THIS CLASS IS FOR DEVELOPERS TO MAKE CUSTOMIZATIONS IN
 */
require_once('modules/DCEReports/DCEReport_sugar.php');
class DCEReport extends DCEReport_sugar {
    var $start_date;
    var $end_date;
    var $where;
    var $licensingReportColumns;
    function DCEReport(){    
        parent::DCEReport_sugar();
    }
    function create_new_list_query($order_by, $where,$filter=array(),$params=array(), $show_deleted = 0,$join_type='', $return_array = false,$parentbean, $singleSelect = false){

// FOR LICENSING REPORT
        if($_REQUEST['action']=='run_lincensingReport'){
            require('modules/DCEReports/LicensingReportColumns.php');
            $this->licensingReportColumns=$licensingReportColumns;
            $return= array(
                'from'=>' FROM dceinstances inst ',
                'order_by'=>' ORDER BY inst.name ',
                'select'=>'SELECT inst.id, inst.name AS instance_name, inst.license_key, inst.licensed_users, inst.license_start_date, inst.license_duration, inst.update_key_user_id, inst.get_key_user_id ',
                'where'=><<<WHEREQUERY
LEFT OUTER JOIN dceinstances_contacts d_c
ON inst.id= d_c.instance_id and d_c.contact_role='Primary Decision Maker'
LEFT OUTER JOIN contacts ct
ON ct.id=d_c.contact_id
WHERE $this->where
WHEREQUERY
            );
            foreach($this->licensingReportColumns['first_query'] as $k=>$column){
                $return['select'].=", $column";
            }
            return $return;
            
//FOR RESOURCE USAGE BY INSTANCE
        }else if($_REQUEST['module']=='Home'){
            $no_SUM_fields=array('instance_name', 'instance_id');
            $MAX_fields=array('max_num_sessions', 'last_login_time');
            $return['from']=' FROM dceinstances inst ';
            if(!empty($where)){
                $return['where']=" LEFT OUTER JOIN dcereports ON dcereports.instance_id=inst.id AND $where AND dcereports.id IS NOT NULL WHERE inst.deleted=0 GROUP BY inst.name, inst.id ";
            }else{
                $return['where']=" LEFT OUTER JOIN dcereports ON dcereports.instance_id=inst.id AND dcereports.id IS NOT NULL WHERE inst.deleted=0 GROUP BY inst.name, inst.id ";
            }
            if(!empty($order_by)){
                $return['order_by']=" ORDER BY $order_by, inst.name, inst.id ";
            }else{
                $return['order_by']=" ORDER BY inst.name, inst.id ";
            }
            $return['select']='SELECT inst.name as instance_name, inst.id as id';
            foreach($filter as $k=>$v){
                if($v && $k!='instance_name'){
                    if(in_array($k, $no_SUM_fields)){
                        $return['select'].= ", dcereports.$k as $k";
                    }else if(in_array($k, $MAX_fields)){
                        $return['select'].= ", MAX(dcereports.$k) as $k";
                    }else if($k == 'memory'){
                        //convert bytes in MB
                        $return['select'].= ", SUM(dcereports.$k)/1048576 as $k";
                    }else{
                        $return['select'].= ", SUM(dcereports.$k) as $k";
                    }
                }
            }
            return $return;
        }
        
    }
    function fill_in_additional_list_fields() { 
        parent::fill_in_additional_list_fields();
        if($_REQUEST['action']=='run_lincensingReport'){
        //return_data is for create the export file
            $return_data=array();
            $query="SELECT MAX(rep.num_of_users) AS num_of_users FROM dcereports rep WHERE rep.date_entered>=$this->start_date AND rep.date_entered<=$this->end_date AND rep.instance_id='$this->id' ";
            $res = $this->db->query($query);
            while($row = $this->db->fetchByAssoc($res)){
                $this->num_of_users=$row['num_of_users'];
            }
            if(empty($this->num_of_users)){
                $this->num_of_users='N/A';
            }
            $return_data['num_of_users']=$this->num_of_users;
            
        //FOR SUGAR VERSION and SUGAR EDITION
            $query="SELECT sugar_version, sugar_edition FROM dcetemplates tpl INNER JOIN dceinstances inst ON inst.id='$this->id' WHERE tpl.id=inst.dcetemplate_id ";
            $res = $this->db->query($query);
            while($row = $this->db->fetchByAssoc($res)){
                if(isset($row['sugar_version']))
                    $this->sugar_version=$row['sugar_version'];
                if(isset($row['sugar_edition']))
                    $this->sugar_edition=$row['sugar_edition'];
                $return_data=array_merge($return_data,$row);
            }
        //FOR KEY_USER (username, last name, first name, email)
            $key_user_id=array();
            if(isset($this->get_key_user_id))
                $key_user_id["get_key"]=$this->get_key_user_id;
            if(isset($this->update_key_user_id))
                $key_user_id["update_key"]=$this->update_key_user_id;
            foreach($key_user_id as $name=>$id){
                $query="SELECT users.user_name AS {$name}_user_name, users.first_name AS {$name}_first_name, users.last_name AS {$name}_last_name, addr.email_address AS {$name}_email_address FROM users INNER JOIN email_addr_bean_rel bean ON bean.bean_id=users.id AND bean.primary_address=1 INNER JOIN email_addresses addr ON addr.id=bean.email_address_id WHERE users.id='$id'";
                $res = $this->db->query($query);
                while($row = $this->db->fetchByAssoc($res)){
                    $return_data=array_merge($return_data,$row);
                }
            }
         //FOR OTHER DATA
            if(!empty($this->licensingReportColumns['account_query'])){
                $select='SELECT ';
                $first=false;
                foreach($this->licensingReportColumns['account_query'] as $k=>$column){
                    if($first)
                        $select.=",";
                    $select.=" $column";
                    $first=true;
                }
                $query="$select FROM accounts act INNER JOIN dceinstances inst ON act.id=inst.account_id WHERE inst.id='$this->id' ";
                $res = $this->db->query($query);
                while($row = $this->db->fetchByAssoc($res)){
                    if(isset($row['account_name']))
                        $this->account_name=$row['account_name'];
                    $return_data=array_merge($return_data,$row);
                }
            }
            if(empty($this->last_name) && !empty($this->licensingReportColumns['first_query'])){
                $select='SELECT ';
                $first=false;
                foreach($this->licensingReportColumns['first_query'] as $k=>$column){
                    if($first)
                        $select.=",";
                    $first=true;
                    $select.=" $column";
                }
                $query="$select FROM contacts ct LEFT OUTER JOIN dceinstances_contacts d_c ON '$this->id'= d_c.instance_id WHERE ct.id=d_c.contact_id ";
                $res = $this->db->query($query);
                $row = $this->db->fetchByAssoc($res);
                if(isset($row['first_name']))
                    $this->first_name=$row['first_name'];
                if(isset($row['last_name']))
                    $this->last_name=$row['last_name'];
                if(isset($row['contact_role']))
                    $this->contact_role=$row['contact_role'];
                if(!empty($row))    
                    $return_data=array_merge($return_data,$row);
            }
            return $return_data;
        }
    }
}
?>