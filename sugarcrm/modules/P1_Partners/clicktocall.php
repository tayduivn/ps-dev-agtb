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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

$dial_first = isset($_REQUEST['dial_first']) ? $_REQUEST['dial_first'] : "";
$dial_second = isset($_REQUEST['dial_second']) ? $_REQUEST['dial_second'] : "";
echo '<p style="padding: 20px 0px 10px 15px;">';
if(isset($dial_first) && !empty($dial_first) && isset($dial_second) && !empty($dial_second)) {
	// call to click to call api
	// dial_first is the number from which the call is originated
	// dial_second is the number who the person is calling
	// dial_as is the number that will be displayed on the caller id for both callers
	// ITREQUEST 12202 - hostname phone.cup1.sugarcrm.net is not in the dns servers in the datacenters. using ip address instead.
        $url_to_api = "https://10.8.1.12/api?cmd=call&dial_first={$dial_first}&dial_second={$dial_second}&dial_as=175&admin=1&password=fe39aad73b846147a48a8446e495f76d";

	$ch = curl_init($url_to_api);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	$content = curl_exec($ch);
	curl_close($ch);
	echo "Calling ... <br /><br />Please pick up your phone. Calling number <b>{$dial_second}</b> from <b>{$dial_first}</b>.";
}
else {
	echo "The user's and/or the contact's phone number is not specified. Please update your information or the contact's information.";
}
echo "<br /><br /><a href='JavaScript:window.close();'>Close Window</a>";
echo "</p>";
?>
<link rel="stylesheet" type="text/css" href="cache/themes/Sugar/css/style.css?s=1a819c2bc4a31e24118a9a4e79410d96&c=1" />
