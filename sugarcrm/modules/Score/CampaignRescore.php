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
global $db,$mod_strings,$app_strings;
require_once('include/ListView/ListViewSmarty.php');
require_once('include/SearchForm/SearchForm2.php');

$module = 'Campaigns';
$seed = loadBean($module);

// Set these up with hardcoded values for what we really want to see
$view = 'basic_search';
if (file_exists('custom/modules/'.$module.'/metadata/searchdefs.php')) {
	require_once('custom/modules/'.$module.'/metadata/searchdefs.php');
} else if (file_exists('modules/'.$module.'/metadata/searchdefs.php')) {
	require_once('modules/'.$module.'/metadata/searchdefs.php');
}
$searchdefs['Campaigns']['layout']['basic_search'][] = array('name'=>'start_date', 'type'=>'date', 'displayParams'=>array('showFormats'=>true));
$searchdefs['Campaigns']['layout']['basic_search'][] = array('name'=>'end_date', 'type'=>'date', 'displayParams'=>array('showFormats'=>true));
$searchdefs['Campaigns']['layout']['basic_search'][] = array('name'=>'date_entered', 'type'=>'date', 'displayParams'=>array('showFormats'=>true));

if (file_exists('custom/modules/'.$module.'/metadata/SearchFields.php')) {
	require_once('custom/modules/'.$module.'/metadata/SearchFields.php');
} else if (file_exists('modules/'.$module.'/metadata/SearchFields.php')) {
	require_once('modules/'.$module.'/metadata/SearchFields.php');
}
$listViewDefs[$module] = array(
	'NAME' => array(
		'width' => '20',
		'label' => 'LBL_LIST_CAMPAIGN_NAME',
        'link' => true,
        'default' => true),
    'DATE_ENTERED' => array(
        'width' => '10', 
        'label' => 'LBL_LIST_DATE_ENTERED',
        'default' => true), 
    'START_DATE' => array(
        'width' => '10', 
        'label' => 'LBL_LIST_START_DATE',
        'default' => true),        
    'END_DATE' => array(
        'width' => '10', 
        'label' => 'LBL_LIST_END_DATE',
        'default' => true),        
    'SCORE' => array(
        'width' => '10', 
        'label' => 'LBL_SCORE',
        'default' => true),  
    'MUL' => array(
        'width' => '10', 
        'label' => 'LBL_SCORE_MUL',
        'default' => true),
);
$params = array('massupdate' => false);
if(!empty($_REQUEST['orderBy'])) {
	$params['orderBy'] = $_REQUEST['orderBy'];
	$params['overrideOrder'] = true;
	if(!empty($_REQUEST['sortOrder'])) { $params['sortOrder'] = $_REQUEST['sortOrder']; }
}
$displayColumns = $listViewDefs[$module];
$headers = true;

$lv = new ListViewSmarty();
$lv->displayColumns = $displayColumns;
$lv->showMassUpdateFilds = true;
$lv->show_mass_update_from = true;
$lv->contextMenus = true;
$lv->multiSelect = false;
$lv->export = false;
$lv->delete = false;
$lv->select = false;
$searchForm = new SearchForm($seed, $module, 'ListView');
$searchForm->showAdvanced = false;
$searchForm->showCustom = false;
$searchForm->showSavedSearchesOptions = false;
$searchForm->setup($searchdefs, $searchFields, 'include/SearchForm/tpls/SearchFormGeneric.tpl', $view, $listViewDefs);
$searchForm->lv = $lv;
$where = '';
if ( isset($_REQUEST['query']) ) {
	$searchForm->populateFromRequest();
	$where_clauses = $searchForm->generateSearchWhere(true, $module);
	if (count($where_clauses) > 0 ) { 
        $where = '('. implode(' ) AND ( ', $where_clauses) . ')'; 
    }
}
$lv->searchColumns = $searchForm->searchColumns;
$lv->setup($seed, 'include/ListView/ListViewNoMassUpdate.tpl', $where, $params);

echo get_module_title($mod_strings['LBL_MODULE_TITLE'], $mod_strings['LBL_CS_TITLE'], true);

// Special headers so the search form doesn't attempt to submit to the standard campaign listview
echo <<<EOF
<form name='search_form' class='search_form' method='post' action='index.php?module=Score&action=CampaignRescore'>
<input type='hidden' name='searchFormTab' value='$view'/>
<input type='hidden' name='module' value='Score'/>
<input type='hidden' name='action' value='CampaignRescore'/> 
<input type='hidden' name='query' value='true'/>
<input type='hidden' id='saved_search_select' name='saved_search_select' value='_none'/>
EOF;

// Don't have the search form generate headers, we need to output some special ones
echo($searchForm->display(false));
// Special search form footers so the search form doesn't attempt to submit to the standard campaign listview
echo <<<EOF
<input tabindex='2' title='$app_strings[LBL_SEARCH_BUTTON_TITLE]' accessKey='$app_strings[LBL_SEARCH_BUTTON_KEY]' class='button' type='submit' name='button' value='$app_strings[LBL_SEARCH_BUTTON_LABEL]' id='search_form_submit'/>&nbsp;
<input tabindex='2' title='$app_strings[LBL_CLEAR_BUTTON_TITLE]' accessKey='$app_strings[LBL_CLEAR_BUTTON_KEY]' onclick='SUGAR.searchForm.clear_form(this.form)' class='button' type='button' name='clear' value=' $app_strings[LBL_CLEAR_BUTTON_LABEL] '/>&nbsp;
<input tabindex='2' title='$app_strings[LBL_BACK_BUTTON_TITLE]' accessKey='$app_strings[LBL_BACK_BUTTON_KEY]' onclick='document.location="index.php?module=Score&action=AdminSettings"' class='button' type='button' name='clear' value=' $app_strings[LBL_BACK_BUTTON_LABEL] '/>&nbsp;
</form>
EOF;

echo(get_form_header($mod_strings['LBL_CS_LIST_TITLE'], '', false));
echo($lv->display());
// Figure out the column numbers for the score and mul columns, so we don't have to hardcode them in some javascript somewhere
echo('<script type="text/javascript">');
$i = 0;
foreach ( $listViewDefs[$module] as $name => $col ) {
    if ( $name == 'SCORE' ) {
        echo('campaignScoreCol = '.$i.";\n");
    } else if ( $name == 'MUL' ) {
        echo('campaignMulCol = '.$i.";\n");
    }
    $i++;
}
echo('</script><script src="modules/Score/CampaignRescore.js"></script>');