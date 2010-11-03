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
/*********************************************************************************

 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
if(!isset($_REQUEST['record'])){
	sugar_die("Error 2487: The ID was not passed when submitting the form. Please file an IT Request with this error message with a description of what you were doing");
}

require_once('modules/Touchpoints/Touchpoint.php');
$touchpoint = new Touchpoint();
$touchpoint->retrieve($_REQUEST['record']);
$touchpoint->assigned_user_id = 'bf6f1e6b-f6bf-01e5-69e3-4a833bf57cfd'; // Leads_escalation user
$touchpoint->scrubbed = 0;
$touchpoint->save(false, false);

require_once('include/MVC/SugarApplication.php');
if(!empty($_REQUEST['return_module']) && $_REQUEST['return_module'] != 'LeadQualScoredLead'){
	$module = (!empty($_REQUEST['return_module']) ? "module={$_REQUEST['return_module']}" : "");
	$action = (!empty($_REQUEST['return_action']) ? "&action={$_REQUEST['return_action']}" : "&action=index");
	$record = (!empty($_REQUEST['return_id']) ? "&record={$_REQUEST['return_id']}" : "");
	$user_queue = (!empty($_SESSION['lead_qual_bucket']) && !empty($_SESSION['lead_qual_bucket']['user']) ? "&user={$_SESSION['lead_qual_bucket']['user']}" : "");
	SugarApplication::redirect("index.php?{$module}{$action}{$record}{$user_queue}");
}
else if(!isset($_SESSION['lead_qual_bucket'])){
	SugarApplication::redirect('index.php?module=Touchpoints&action=LeadQualScoredLead&user=c15afb6d-a403-b92a-f388-4342a492003e');
}
else{
	$user = $_SESSION['lead_qual_bucket']['user'];
	SugarApplication::redirect("index.php?module=Touchpoints&action=LeadQualScoredLead&user=$user");
}
?>
