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
global $db;

$targetModule = $_REQUEST['score_module'];
$targetId = $_REQUEST['score_id'];

$targetBean = loadBean($targetModule);
$targetBean->retrieve($targetId);

$score = loadBean('Score');
$ruleClasses = $score->getRuleClassesForModule($targetModule);
$config = $score->getModuleConfigs($targetModule);
$multThis = $config['apply_mult']=='record';

$rawRows = $score->getScoreEntries($targetModule,$targetId,$targetBean,$config,$ruleClasses);

$totalScore = 0;
$totalMul = 0;
$scoreRows = array();
foreach ( $rawRows as $row ) {
	$curr = array();
	if ( isset($row['childScore']) && $row['childScore'] == true ) {
		// It's a child that bubbled it's score up to a parent
		$curr['name'] = isset($GLOBALS['app_list_strings']['moduleListSingular'][$row['source_module']])?$GLOBALS['app_list_strings']['moduleListSingular'][$row['source_module']]:$GLOBALS['app_list_strings']['moduleList'][$row['source_module']];
		$childBean = loadBean($row['source_module']);
		$childBean->retrieve($row['source_id']);
		$curr['val'] = $childBean->get_summary_text();
	} else if ( isset($config['rules'][$row['rule_id']]) ) {
		// It's a valid rule
		$rule = $config['rules'][$row['rule_id']];
		$curr = $ruleClasses[$rule['ruleClass']]->getScoreInfo($row,$rule,$targetModule);
	} else {
		// It's probably a score from an old rule that has not been eliminated yet
		$curr['name'] = $mod_strings['LBL_SB_INVALIDRULE'];
		$curr['val'] = '';
	}

	$totalScore += (int)$row['score_add'];
	$totalMul += (float)$row['score_mul'];

	$curr['score'] = (int)$row['score_add'];
	$curr['mul'] = sprintf("%0.1f",$row['score_mul']);

	$scoreRows[] = $curr;
}

function scoreSort( $a, $b ) {
	return(strcmp($a['name'],$b['name']));
}
uasort($scoreRows,'scoreSort');

// Now time to display the score, for real
$sugar_smarty	= new Sugar_Smarty();
ob_start();
insert_popup_header($GLOBALS['theme']);
$sugar_smarty->assign('themeHTML',ob_get_contents());
ob_end_clean();
$sugar_smarty->assign('mod', $mod_strings);
$sugar_smarty->assign('app', $app_strings);
$sugar_smarty->assign('scoreRows', $scoreRows);
$sugar_smarty->assign('totalScore', $totalScore);
$sugar_smarty->assign('totalMul', $totalMul);
$sugar_smarty->assign('multThis', $multThis);
$sugar_smarty->display('modules/Score/Board.tpl');
