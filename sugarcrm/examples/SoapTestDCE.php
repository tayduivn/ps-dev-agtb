<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
//FILE SUGARCRM flav=dce ONLY
chdir('../');
require_once('include/entryPoint.php');
$time = date($GLOBALS['timedate']->get_db_date_time_format());
$soap_client ='http://localhost/dce510RC/soap.php?wsdl';
$user_name ='';
$user_password = '';
$instance_name ='';
$account_id = "a83ae5bc-e45e-f942-fecb-48287cb2e226"; 
$dcecluster_id = '4470d368-c708-c7c8-1682-47d57b1661ca'; 
$dcetemplate_id = 'bad0d368-c708-c7c8-1682-47d57b16mnky';
$type = 'evaluation';
$license_start_date = $time;
$license_duration = 30;
$licensed_users = 50;
$license_key = 'aaaaa';
$wait_time = 600;
$sleep_interval = 20;
foreach($_POST as $name=>$value){
		$$name = $value;
}
echo <<<EOQ
<form name='test' method='POST'>
<table width ='800'>
<tr><th colspan='4' align='left'>
This function create an Instance and the "deploy" action related to this instance. It will wait until the instance is deployed (status="live") and return the admin user, admin password, URL and name of the new instance. <br><br>
</th></tr>

<tr><th colspan='4'>Enter  the URL to the soap client</th></tr>
<tr><td >SOAP CLIENT:</td><td colspan='4'><input type='text' name='soap_client' value='$soap_client' size="100"></td></tr>

<tr><th colspan='6'>Enter  SugarCRM  User Information - this is the same info entered when logging into sugarcrm</th></tr>
<tr><td >USER NAME:</td><td><input type='text' name='user_name' value='$user_name'></td><td>USER PASSWORD:</td><td><input type='password' name='user_password' value='$user_password'></td></tr>

<tr><th colspan='6'>Enter Name of the instance that you want to create</th></tr>
<tr><td >INSTANCE NAME:</td><td><input type='text' name='instance_name' value='$instance_name'></td><td >ACCOUNT ID:</td><td><input type='text' name='account_id' value='$account_id'></td></tr>
<tr><td >CLUSTER ID:</td><td><input type='text' name='dcecluster_id' value='$dcecluster_id'></td><td >TEMPLATE ID:</td><td><input type='text' name='dcetemplate_id' value='$dcetemplate_id'></td></tr>
<tr><td >INSTANCE TYPE:</td><td><input type='text' name='type' value='$type'></td><td >LICENSE START DATE:</td><td><input type='text' name='license_start_date' value='$license_start_date'></td></tr>
<tr><td >LICENSE DURATION:</td><td><input type='text' name='license_duration' value='$license_duration'></td><td >LICENSED USERS:</td><td><input type='text' name='licensed_users' value='$licensed_users'></td></tr>
<tr><td >LICENSE KEY:</td><td><input type='text' name='license_key' value='$license_key'></td></tr>

<tr><th colspan='4'>Options</th></tr>
<tr><td >WAIT TIME (wait of the "in_progress" status):</td><td><input type='text' name='wait_time' value='$wait_time'></td><td>SLEEP INTERVAL (interval between each check of the status):</td><td><input type='text' name='sleep_interval' value='$sleep_interval'></td></tr>

<tr><td><input type='submit' value='Submit'></td></tr>
</table>
</form>


EOQ;
if(!empty($user_name) && !empty($instance_name) && !empty($wait_time) && !empty($sleep_interval)){
	function print_result($result){
    	global $soapclient;
    	if(!empty($soapclient->error_str)){
    		echo '<b>HERE IS ERRORS:</b><BR>';
    		echo $soapclient->error_str;
    	
    		echo '<BR><BR><b>HERE IS RESPONSE:</b><BR>';
    		echo $soapclient->response;
    	}
    	
    	echo '<BR><BR><b>HERE IS RESULT:</b><BR>';
    	print_r($result);
    	echo '<br>';
	}
	require_once('include/nusoap/nusoap.php');  //must also have the nusoap code on the ClientSide.
	
	$soapclient = new nusoapclient($soap_client, true, false, false, false, false, 0, $wait_time+60);  //define the SOAP Client an
	
	echo '<BR><b>Get Server Time: - get_server_time test</b><BR>';
	$result = $soapclient->call('get_server_time',array());
	print_result($result);
	
	$user_password = md5($user_password);
	echo '<BR><b>LOGIN: -login test</b><BR>';
	$result = $soapclient->call('login',array('user_auth'=>array('user_name'=>$user_name,'password'=>$user_password, 'version'=>'.01'), 'application_name'=>'SoapTest'));
	print_result($result);
	$session = $result['id'];
	
	echo '<BR><b>Verify duplicate name: - get_entries_count test</b><BR>';
	$result = $soapclient->call('get_entries_count',array('session'=>$session,'module_name'=>'DCEInstances', 'query'=>'name="'.$instance_name.'"', 'deleted'=>0));
    if($result['error']['number']== 0 && $result['result_count'] == 0){
	    echo '<BR><b>Create New Instance: - set_entry test</b><BR>';
	    $result = $soapclient->call('set_entry',array('session'=>$session,'module_name'=>'DCEInstances', 
	                                'name_value_list'=>array(
	                                    array('name'=>'name' , 'value'=>$instance_name),
	                                    array('name'=>'account_id' , 'value'=>$account_id), 
	                                    array('name'=>'dcecluster_id' , 'value'=>$dcecluster_id), 
	                                    array('name'=>'dcetemplate_id' , 'value'=>$dcetemplate_id),
	                                    array('name'=>'type' , 'value'=>$type),
	                                    array('name'=>'license_start_date' , 'value'=>$license_start_date),
	                                    array('name'=>'license_duration' , 'value'=>$license_duration),
	                                    array('name'=>'licensed_users' , 'value'=>$licensed_users),
	                                    array('name'=>'license_key' , 'value'=>$license_key),
	                                    )));
	    print_result($result);
	    if(isset($result['id']) && isset($result['error']['number']) && $result['error']['number'] == 0){
	        $record = $result['id'];
	        echo '<BR><b>Create New Action: - create_DCE_action test</b><BR>';
	        $result = $soapclient->call('create_DCE_action',array('session'=>$session, 
	                                                           'record'=>$record, 
	                                                           "actionType"=>"create", 
	                                                           "startDate"=>$license_start_date,
	                                                           "priority"=>1, 
	                                                           "upgradeVars"=>"", 
	                                                           "dbCloned"=>""
	                                                               ));
	        print_result($result);
	        echo "$record<br>";
	        if($result == 'in_progress'){
                ini_set("max_execution_time", $wait_time+60);
	            echo '<BR><b>Wait for status: - return_when_found test</b><BR>';
	            $result = $soapclient->call('return_when_found',array('session'=>$session,'module_name'=>'DCEInstances','id'=>$record,'field'=>'status', 'values'=>array('Live'), 'wait'=>$wait_time, 'sleep'=>$sleep_interval));
	            print_result($result);
	            if($result == 'Live'){
	                echo '<BR><b>Retrieve password, username and url: - get_entry test</b><BR>';
	                $result = $soapclient->call('get_entry',array('session'=>$session,'module_name'=>'DCEInstances','id'=>$record,'select_fields'=>array('name', 'admin_pass', 'admin_user', 'url')));
	                if($result['entry_list'][0]['error']['number']==0){
	                	foreach($result['entry_list'][0]['name_value_list'] as $v){
	                	    echo "<BR>{$v['name']} : " . $v['value'];
	                	}
	                }else{
	                    print_result($result);
	                }
	            }
	        }else{
	            echo "<BR>Instance creation has a problem<BR>";
	        }
	    }
    }else{
        echo "<BR>DUPLICATE NAME<BR>";
    	print_result($result);
    }
}
?>
