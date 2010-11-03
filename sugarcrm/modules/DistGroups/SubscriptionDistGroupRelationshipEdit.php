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

require_once('XTemplate/xtpl.php');
require_once('modules/DistGroups/SubscriptionDistGroupRelationship.php');
require_once('modules/DistGroups/Forms.php');
require_once('modules/Subscriptions/Subscription.php');
require_once('include/utils.php');

global $app_strings;
global $app_list_strings;
global $mod_strings;
global $sugar_version, $sugar_config;

$focus = new SubscriptionDistGroupRelationship();

if(isset($_REQUEST['record'])) {
    $focus->retrieve($_REQUEST['record']);
}

if(isset($_REQUEST['isDuplicate']) && $_REQUEST['isDuplicate'] == 'true') {
	$focus->id = "";
}

$subscription = new Subscription();
if(isset($focus->subscription_id) && !empty($focus->subscription_id)) {
    $subscription->retrieve($focus->subscription_id);
}

// Prepopulate either side of the relationship if passed in.
safe_map('distgroup_name', $focus);
safe_map('distgroup_id', $focus);
safe_map('subscription_id', $focus);
safe_map('subscription_name', $focus);
safe_map('quantity', $focus);


$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";

$GLOBALS['log']->info("Subscription DistGroup relationship");

$json = getJSONobj();
require_once('include/QuickSearchDefaults.php');
$qsd = new QuickSearchDefaults();
$distgroup_qs = array(
	'method' => 'query',
	'modules' => array('DistGroups'),
	'group' => 'or',
	'field_list' => array('name', 'id'),
	'populate_list' => array('distgroup_name', 'distgroup_id'),
	'conditions' => array(
		array(
			'name' => 'name',
			'op' => 'like_custom',
			'end' => '%',
			'value' => '',
		),
	),
	'order' => 'name',
	'limit' => '30',
	'no_match_text' => 'No Match',
);
//echo "<PRE>"; print_r($tmp); echo "</PRE>"; die();
$sqs_objects = array('distgroup_name' => $distgroup_qs);
$sqs_objects['distgroup_name']['populate_list'] = array('distgroup_name', 'distgroup_id');
$quicksearch_js = $qsd->getQSScripts();
$quicksearch_js .= '<script type="text/javascript" language="javascript">sqs_objects = ' . $json->encode($sqs_objects) . '</script>';
echo $quicksearch_js;

$xtpl=new XTemplate ('modules/DistGroups/SubscriptionDistGroupRelationshipEdit.html');
$xtpl->assign("MOD", $mod_strings);
$xtpl->assign("APP", $app_strings);

$xtpl->assign("RETURN_URL", "&return_module=$currentModule&return_action=DetailView&return_id=$focus->id");
$xtpl->assign("RETURN_MODULE", $_REQUEST['return_module']);
$xtpl->assign("RETURN_ACTION", $_REQUEST['return_action']);
$xtpl->assign("RETURN_ID", $_REQUEST['return_id']);
$xtpl->assign("THEME", $theme);
$xtpl->assign("IMAGE_PATH", $image_path);$xtpl->assign("PRINT_URL", "index.php?".$GLOBALS['request_string']);
$xtpl->assign("ID", $focus->id);
$xtpl->assign("SUBSCRIPTION",$subscriptionName = Array("NAME" => $subscription->subscription_id, "ID" => $focus->subscription_id));
$xtpl->assign("DISTGROUP",$distgroupName = Array("NAME" => $focus->distgroup_name, "ID" => $focus->distgroup_id));
$xtpl->assign("QUANTITY", isset($focus->quantity) ? $focus->quantity : '');

echo "\n<p>\n";
echo get_module_title($mod_strings['LBL_MODULE_NAME'], $mod_strings['LBL_SUBSCRIPTION_DIST_FORM_TITLE']." (".$subscriptionName['NAME']." - ".$distgroupName['NAME'].")", true);
echo "\n</p>\n";

$xtpl->parse("main");
$xtpl->out("main");

require_once('include/javascript/javascript.php');
$javascript = new javascript();
$javascript->setFormName('EditView');
$javascript->setSugarBean($focus);
$javascript->addToValidateBinaryDependency('distgroup_name', 'alpha', $app_strings['ERR_SQS_NO_MATCH_FIELD'] . $mod_strings['LBL_DISTGROUP_NAME'], 'false', '', 'distgroup_id');
echo $javascript->getScript();


?>
