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
$user_name ='';
$user_password = '';
$soap_client = 'http://localhost/sugar_trunk/soap.php?wsdl';

$account_name = 'Demo Account';
$account_phone_office = '123-456-7789';
$account_billing_address_street = '123 Fake St.';
$account_billing_address_city = 'Springfield';
$account_billing_address_state = 'QL';
$account_billing_address_postalcode = '34901';
$account_billing_address_country = 'USA';
$account_assigned_user_id = '1';

$contact_first_name = 'Max';
$contact_last_name = 'Dan';
$contact_email1 = 'example1@example.com';
$contact_phone_work = '123-456-7789';
$contact_assigned_user_id = '1';

$instance_name = 'apidemoinstance';
$instance_dcetemplate_id = '7e8d2c88-5766-ad33-94eb-486d20fbd7f8';
$instance_dcecluster_id = 'aea40684-655b-edae-a0e1-486d20807a44';
$instance_license_start_date = '2008-07-01';
$instance_license_duration = '365';
$instance_licensed_users = '25';
//$instance_license_key = 'KEY PENDING';
$instance_assigned_user_id = '1';

$deploy = 0;
$deploy_f = '';

foreach($_POST as $name=>$value){
        $$name = $value;
}
if($deploy) $deploy_f="CHECKED";
echo <<<EOQ
<form name='test' method='POST'>
<table width ='800'>
<tr><th colspan='4' align='left'>
This function create an account, a contact and an instance and create an input_array from the values of these records. action_dce_get_key is call with the array and a license key is created or updated. A "create" action is created for the instance.<br><br>
</th></tr>
<tr><th colspan='4'>Enter  the URL to the soap client</th></tr>
<tr><td >SOAP CLIENT:</td><td colspan='4'><input type='text' name='soap_client' value='$soap_client' size="100"></td></tr>
<tr><th colspan='4'>Enter  SugarCRM  User Information - this is the same info entered when logging into sugarcrm</th></tr>
<tr><td >USER NAME:</td><td><input type='text' name='user_name' value='$user_name'></td><td>USER PASSWORD:</td><td><input type='password' name='user_password' value='$user_password'></td></tr>

<tr><th colspan='4'>Account Informations</th></tr>
<tr><td >ACCOUNT NAME:</td><td><input type='text' name='account_name' value='$account_name'></td><td >OFFICE PHONE:</td><td><input type='text' name='account_phone_office' value='$account_phone_office'></td></tr>
<tr><td >BILLING ADDRESS STREET:</td><td><input type='text' name='account_billing_address_street' value='$account_billing_address_street'></td><td >BILLING ADDRESS CITY:</td><td><input type='text' name='account_billing_address_city' value='$account_billing_address_city'></td></tr>
<tr><td >BILLING ADDRESS STATE:</td><td><input type='text' name='account_billing_address_state' value='$account_billing_address_state'></td><td >BILLING ADDRESS POSTAL CODE:</td><td><input type='text' name='account_billing_address_postalcode' value='$account_billing_address_postalcode'></td></tr>
<tr><td >BILLING ADDRESS COUNTRY:</td><td><input type='text' name='account_billing_address_country' value='$account_billing_address_country'></td><td >ASSIGNED USER ID:</td><td><input type='text' name='account_assigned_user_id' value='$account_assigned_user_id'></td></tr>

<tr><th colspan='4'>Contact Informations</th></tr>
<tr><td >FIRST NAME:</td><td><input type='text' name='contact_first_name' value='$contact_first_name'></td><td >LAST NAME:</td><td><input type='text' name='contact_last_name' value='$contact_last_name'></td></tr>
<tr><td >EMAIL 1:</td><td><input type='text' name='contact_email1' value='$contact_email1'></td><td >OFFICE PHONE:</td><td><input type='text' name='contact_phone_work' value='$contact_phone_work'></td></tr>
<tr><td >ASSIGNED USER ID:</td><td><input type='text' name='contact_assigned_user_id' value='$contact_assigned_user_id'></td></tr>

<tr><th colspan='4'>Instance Informations</th></tr>
<tr><td >INSTANCE NAME:</td><td><input type='text' name='instance_name' value='$instance_name'></td><td >TEMPLATE ID:</td><td><input type='text' name='instance_dcetemplate_id' value='$instance_dcetemplate_id'></td></tr>
<tr><td >CLUSTER ID:</td><td><input type='text' name='instance_dcecluster_id' value='$instance_dcecluster_id'></td><td >LICENSE START DATE:</td><td><input type='text' name='instance_license_start_date' value='$instance_license_start_date'></td></tr>
<tr><td >LICENSE DURATION (in days):</td><td><input type='text' name='instance_license_duration' value='$instance_license_duration'></td><td >LICENSED USERS:</td><td><input type='text' name='instance_licensed_users' value='$instance_licensed_users'></td></tr>
<tr><td >ASSIGNED USER ID:</td><td><input type='text' name='instance_assigned_user_id' value='$instance_assigned_user_id'></td></tr>

<tr><th colspan='4'>Options</th></tr>
<tr><td colspan='4'><input type="checkbox" name="deploy" value="true" $deploy_f> deploy the instance if it's a "new" instance</td></tr>

<tr><td ><input type='submit' value='Submit'></td></tr>
</table>
</form>


EOQ;
if(!empty($user_name) && !empty($instance_name) && !empty($contact_last_name) && !empty($account_name) && !empty($instance_dcetemplate_id) && !empty($instance_dcecluster_id) && !empty($instance_license_start_date) && !empty($instance_license_duration) && !empty($instance_licensed_users) && !empty($soap_client)){
    function print_result($result){
        global $soapclient;
        if(!empty($soapclient->error_str)){
            echo '<b>HERE IS ERRORS:</b><BR>';
            echo $soapclient->error_str;
        
            echo '<BR><BR><b>HERE IS RESPONSE:</b><BR>';
            echo $soapclient->response;
        
        }
        
        echo '<BR><BR><b>HERE IS RESULT:</b><BR>';
        echo "<pre>";
        print_r($result);
        echo "</pre>";
        echo '<br/>';
    }
    
    chdir('../');
    $user_password = md5($user_password);
    require_once('include/nusoap/nusoap.php');
    
    $soapclient = new nusoapclient($soap_client, true);
    $err = $soapclient->getError();
    if ($err) {
        var_dump($err);
        die();
    }
    
    
    echo '<BR><b>LOGIN: - login test</b><br/>--------------------<br/>';
    $result = $soapclient->call('login',array('user_auth'=>array('user_name'=>$user_name,'password'=>$user_password, 'version'=>'.01'), 'application_name'=>'SoapTest'));
    //print_result($result);
    $session = $result['id'];
    
    //this is needed to set the get_key_user_id
    $user_id = $soapclient->call('get_user_id',$session);
    
    echo "Got the authenticated user id:".$user_id."<br/><br/>";
    
    //add the account with its details.., keep id around for further use
    echo '<br/>-----------------<br/><b>CREATE ACCOUNT</b><br/>--------------------<br/>';
    
    $set_entry_params = array(
        'session' => $session,
        'module_name' => 'Accounts',
        'name_value_list'=>array(
            array('name'=>'name','value'=>$account_name),
            array('name'=>'phone_office','value'=>$account_phone_office),
            array('name'=>'billing_address_street','value'=>$account_billing_address_street),
            array('name'=>'billing_address_city', 'value'=>$account_billing_address_city),
            array('name'=>'billing_address_state', 'value'=>$account_billing_address_state),
            array('name'=>'billing_address_postalcode', 'value'=>$account_billing_address_postalcode),
            array('name'=>'billing_address_country', 'value'=>$account_billing_address_country),
            array('name'=>'assigned_user_id', 'value'=>$account_assigned_user_id)
        )
    );
    
    $result = $soapclient->call('set_entry',$set_entry_params);
    
    $account_id = $result['id'];
    echo "Success..., with Account GUID: ".$account_id."<br/>";
    
    //add the contact with its details.., keep id around for further use
    echo '<br/>-----------------<br/><b>CREATE CONTACT</b><br/>--------------------<br/>';
    
    $set_entry_params = array(
        'session' => $session,
        'module_name' => 'Contacts',
        'name_value_list'=>array(
            array('name'=>'first_name','value'=>$contact_first_name),
            array('name'=>'last_name','value'=>$contact_last_name),
            array('name'=>'email1','value'=>$contact_email1),
            array('name'=>'phone_work','value'=>$contact_phone_work),
            array('name'=>'account_id','value'=>$account_id),
            array('name'=>'assigned_user_id', 'value'=>$contact_assigned_user_id)
        )
    );
    
    $result = $soapclient->call('set_entry',$set_entry_params);
    
    $contact_id = $result['id'];
    echo "Success..., with Contact GUID: ".$contact_id."<br/>";

    //add the contact with its details.., keep id around for further use
    echo '<br/>-----------------<br/><b>CREATE INSTANCE</b><br/>--------------------<br/>';
    
    $set_entry_params = array(
        'session' => $session,
        'module_name' => 'DCEInstances',
        'name_value_list'=>array(
            array('name'=>'name','value'=>$instance_name),
            array('name'=>'account_id','value'=>$account_id),
            array('name'=>'dcetemplate_id','value'=>$instance_dcetemplate_id),
            array('name'=>'dcecluster_id','value'=>$instance_dcecluster_id),
            array('name'=>'license_start_date', 'value'=>$instance_license_start_date),
            array('name'=>'license_duration', 'value'=>$instance_license_duration),
            array('name'=>'licensed_users', 'value'=>$instance_licensed_users),
            //array('name'=>'license_key', 'value'=>$instance_license_key),
            array('name'=>'get_key_user_id', 'value'=>$user_id),
            array('name'=>'assigned_user_id', 'value'=>$instance_assigned_user_id)
        )
    );
    
    $result = $soapclient->call('set_entry',$set_entry_params);
    
    $instance_id = $result['id'];
    echo "Success..., with Instance GUID: ".$instance_id."<br/>";
    
    //this call will create a new license key.., or update it if it already exists
    echo '<br/>-----------------<br/><b>GET KEY AND DEPLOY</b><br/>--------------------<br/>';
    $result = $soapclient->call('dce_update_license',array('session'=>"$session", 'instance_id'=>$instance_id, 'contact_id'=>$contact_id, 'deploy'=>$deploy));
    print_result($result);
}

?>