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
$password = md5('a');
$user_name = 'admin';
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
chdir('../');


require_once('soap/SoapError.php');
require_once('soap/SoapHelperFunctions.php');


require_once('include/nusoap/nusoap.php');
require_once('include/entryPoint.php');
$soapclient = new nusoapclient('http://localhost/sugar_trunk/soap.php?wsdl', true);
$err = $soapclient->getError();
if ($err) {
    var_dump($err);
    die();
}
echo '<BR><b>LOGIN: - login test</b><BR>';
$result = $soapclient->call('login',array('user_auth'=>array('user_name'=>$user_name,'password'=>$password, 'version'=>'.01'), 'application_name'=>'SoapTest'));
print_result($result);
$session = $result['id'];
echo '<BR><b>LOGIN: - dce_update_license test</b><BR>';
$result = $soapclient->call('dce_update_license',array('session'=>"$session", 'instance_id'=>'a550baf5-f52c-ab81-6778-4862c1341266', 'contact_id'=>'910321d2-65bd-9416-67ab-4862c185f7fd', 'deploy'=>false));
print_result($result);
?>