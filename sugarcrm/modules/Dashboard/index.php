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

global $current_user, $sugar_version, $sugar_config;


require_once('include/MySugar/MySugar.php');

// build dashlet cache file if not found
if(!is_file($GLOBALS['sugar_config']['cache_dir'].'dashlets/dashlets.php')) {
    require_once('include/Dashlets/DashletCacheBuilder.php');
    
    $dc = new DashletCacheBuilder();
    $dc->buildCache();
}
require_once($GLOBALS['sugar_config']['cache_dir'].'dashlets/dashlets.php');

require('modules/Dashboard/dashlets.php');

$pages = $current_user->getPreference('pages', 'Dashboard'); 
$dashlets = $current_user->getPreference('dashlets', 'Dashboard');

// BEGIN fill in with default homepage and dashlet selections
if(!isset($pages) || !isset($dashlets)) {
    $dashboardDashlets = array();

	//list of preferences to move over and to where
    $prefstomove = array(
        'mypbss_date_start' => 'MyPipelineBySalesStageDashlet',
        'mypbss_date_end' => 'MyPipelineBySalesStageDashlet',
        'mypbss_sales_stages' => 'MyPipelineBySalesStageDashlet',
        'mypbss_chart_type' => 'MyPipelineBySalesStageDashlet',
        'lsbo_lead_sources' => 'OpportunitiesByLeadSourceByOutcomeDashlet',
        'lsbo_ids' => 'OpportunitiesByLeadSourceByOutcomeDashlet',
        'pbls_lead_sources' => 'OpportunitiesByLeadSourceDashlet',
        'pbls_ids' => 'OpportunitiesByLeadSourceDashlet',
        'pbss_date_start' => 'PipelineBySalesStageDashlet',
        'pbss_date_end' => 'PipelineBySalesStageDashlet',
        'pbss_sales_stages' => 'PipelineBySalesStageDashlet',
        'pbss_chart_type' => 'PipelineBySalesStageDashlet',
        'obm_date_start' => 'OutcomeByMonthDashlet',
        'obm_date_end' => 'OutcomeByMonthDashlet',
        'obm_ids' => 'OutcomeByMonthDashlet');
    
	// upgrading from pre-5.0 dashboard
	// begin upgrade code
    

    $dashboard = new Dashboard();
    $old_dashboard = $dashboard->getUsersTopDashboard($current_user->id);
	$dashboard_def = unserialize(from_html(from_html($old_dashboard->content)));

	if (isset($dashboard_def)){
		
		
		foreach($dashboard_def as $def){
			if ($def['type'] == 'code'){
				$dashboardDashletName = $dashboard->getDashletName($def['id']);
				// clint - fixes bug #20398
				// only display dashlets that are from visibile modules and that the user has permission to list
				$myDashlet = new MySugar('Opportunities');
				$displayDashlet = $myDashlet->checkDashletDisplay();

				if (isset($dashletsFiles[$dashboardDashletName]) && $displayDashlet){
                   $options = array();
                   $prefsforthisdashlet = array_keys($prefstomove,$dashboardDashletName);
                   foreach ( $prefsforthisdashlet as $pref ) {
                       $options[$pref] = $current_user->getPreference($pref);
                   }
					$dashboardDashlets[create_guid()] = array('className' => $dashboardDashletName, 
												 'module' => 'Opportunities',					
		                                         'fileLocation' => $dashletsFiles[$dashboardDashletName]['file'],
                                                 'options' => $options);
				}
			}
			else if ($def['type'] == 'report'){
				$focus = new SavedReport();
				$dashboardReport = $focus->retrieve($def['id']);
				// clint - fixes bug #20398
				// only display dashlets that are from visibile modules and that the user has permission to list
				$myDashlet = new MySugar($dashboardReport->module);
				$displayDashlet = $myDashlet->checkDashletDisplay();

				if ($dashboardReport != null && $displayDashlet){
					$dashboardDashlets[create_guid()] = array('className' => 'ChartsDashlet',
															'module' => $dashboardReport->module,
		    												'fileLocation' => $dashletsFiles['ChartsDashlet']['file'],
		    												'reportId' => $def['id'], );
				}
			}
		}
	}
	// end upgrade code
	else{
	    foreach($defaultDashboardDashlets as $dashboardDashletName=>$module){
			// clint - fixes bug #20398
			// only display dashlets that are from visibile modules and that the user has permission to list
			$myDashlet = new MySugar($module);
			$displayDashlet = $myDashlet->checkDashletDisplay();
				
	    	if (isset($dashletsFiles[$dashboardDashletName]) && $displayDashlet){
                $options = array();
                $prefsforthisdashlet = array_keys($prefstomove,$dashboardDashletName);
                foreach ( $prefsforthisdashlet as $pref ) {
                    $options[$pref] = $current_user->getPreference($pref);
                }
                $dashboardDashlets[create_guid()] = array('className' => $dashboardDashletName, 
												 'module' => $module,
		                                         'fileLocation' => $dashletsFiles[$dashboardDashletName]['file'],
                                                'options' => $options,);
	    	}
	    }  
	}
    
    $count = 0;
    $dashboardColumns = array();
    $dashboardColumns[0] = array();
    $dashboardColumns[0]['width'] = '60%';
    $dashboardColumns[0]['dashlets'] = array();
    $dashboardColumns[1] = array();
    $dashboardColumns[1]['width'] = '40%';
    $dashboardColumns[1]['dashlets'] = array();

    foreach($dashboardDashlets as $guid=>$dashlet){
        if($count % 2 == 0) array_push($dashboardColumns[0]['dashlets'], $guid); 
        else array_push($dashboardColumns[1]['dashlets'], $guid);        
        $count++;
    }
    
    // BEGIN 'Sales Dashboard Page'
    
    
    $salesDashlets = array();
	foreach ($defaultSalesChartDashlets as $salesChartDashlet=>$module){
		$savedReport = new SavedReport();
		$reportId = $savedReport->retrieveReportIdByName($salesChartDashlet);
		// clint - fixes bug #20398
		// only display dashlets that are from visibile modules and that the user has permission to list
		$myDashlet = new MySugar($module);
		$displayDashlet = $myDashlet->checkDashletDisplay();
		if (isset($reportId) && $displayDashlet) {
    	$salesDashlets[create_guid()] = array('className' => 'ChartsDashlet',
													'module' => $module,
    												'fileLocation' => $dashletsFiles['ChartsDashlet']['file'],
    												'reportId' => $reportId, );
        }       								  
    }       								  
    foreach($defaultSalesDashlets as $salesDashletName=>$module){
 		// clint - fixes bug # 20398
		// only display dashlets that are from visibile modules and that the user has permission to list
		$myDashlet = new MySugar($module);
		$displayDashlet = $myDashlet->checkDashletDisplay();
    
	   	if (isset($dashletsFiles[$salesDashletName]) && $displayDashlet){
            $salesDashlets[create_guid()] = array('className' => $salesDashletName, 
											 'module' => $module,
	                                         'fileLocation' => $dashletsFiles[$salesDashletName]['file'],
                                             'reportId' => $reportId,);
    	}
    }  
    
    $count = 0;
    $salesColumns = array();
    $salesColumns[0] = array();
    $salesColumns[0]['width'] = '30%';
    $salesColumns[0]['dashlets'] = array();
    $salesColumns[1] = array();
    $salesColumns[1]['width'] = '30%';
    $salesColumns[1]['dashlets'] = array();
    $salesColumns[2] = array();
    $salesColumns[2]['width'] = '40%';
    $salesColumns[2]['dashlets'] = array();
    
    
    foreach($salesDashlets as $guid=>$dashlet){
        if($count % 3 == 0) array_push($salesColumns[0]['dashlets'], $guid); 
        else if($count % 3 == 1) array_push($salesColumns[1]['dashlets'], $guid);         
        else array_push($salesColumns[2]['dashlets'], $guid);        
        $count++;
    }
    // END 'Sales Page'
    $dashlets = array_merge($dashboardDashlets, $salesDashlets);    
    /*
    
	
    */
    $current_user->setPreference('dashlets', $dashlets, 0, 'Dashboard');
}

if (empty($pages)){
	$pages = array();
	$pages[0]['columns'] = $dashboardColumns;
	$pages[0]['numColumns'] = '2';
	$pages[0]['pageTitle'] = $mod_strings['LBL_DASHBOARD_PAGE_1'];
	$pages[1]['columns'] = $salesColumns;
	$pages[1]['numColumns'] = '3';
	$pages[1]['pageTitle'] = $mod_strings['LBL_DASHBOARD_PAGE_2'];
	$current_user->setPreference('pages', $pages, 0, 'Dashboard');
	$activePage = 0;
}

if(isset($_COOKIE[$current_user->id . '_activeDashboardPage']) && $_COOKIE[$current_user->id . '_activeDashboardPage'] != '')
    $activePage = $_COOKIE[$current_user->id . '_activeDashboardPage'];
else{
    $_COOKIE[$current_user->id . '_activeDashboardPage'] = '0';
    setcookie($current_user->id . '_activeDashboardPage','0',3000);
    $activePage = 0;
}

$divPages[] = $activePage;
    
$numCols = $pages[$activePage]['numColumns'];

foreach($pages as $pageNum => $page){
    //grab the now viewed pages to render the <div> foreach
    if($pageNum != $activePage)
        $divPages[] = $pageNum;

    $pageData[$pageNum]['pageTitle'] = $page['pageTitle'];

    if($pageNum == $activePage){
        $pageData[$pageNum]['tabClass'] = 'current';
        $pageData[$pageNum]['visibility'] = 'inline';
    }
    else{
        $pageData[$pageNum]['tabClass'] = '';
        $pageData[$pageNum]['visibility'] = 'none';
    }
}    

$count = 0;
$dashletIds = array(); // collect ids to pass to javascript
$display = array();

foreach($pages[$activePage]['columns'] as $colNum => $column) {
	if ($colNum == $numCols){
		break;
	}	
    $display[$colNum]['width'] = $column['width'];
    $display[$colNum]['dashlets'] = array(); 
    foreach($column['dashlets'] as $num => $id) {
        if(!empty($id) && isset($dashlets[$id]) && is_file($dashlets[$id]['fileLocation'])) {
			$module = 'Home';
			if ( isset($dashletsFiles[$dashlets[$id]['className']]['module']) )
        		$module = $dashletsFiles[$dashlets[$id]['className']]['module'];

			$myDashlet = new MySugar($module);

			if($myDashlet->checkDashletDisplay()) {
        		require_once($dashlets[$id]['fileLocation']);
        		if ($dashlets[$id]['className'] == 'ChartsDashlet'){
        			$dashlet = new $dashlets[$id]['className']($id, $dashlets[$id]['reportId'], (isset($dashlets[$id]['options']) ? $dashlets[$id]['options'] : array()));
        		}
          		else{
	          		$dashlet = new $dashlets[$id]['className']($id, (isset($dashlets[$id]['options']) ? $dashlets[$id]['options'] : array()));
            	}
            	array_push($dashletIds, $id);

            	$dashlet->process();
            	$display[$colNum]['dashlets'][$id]['display'] = $dashlet->display();
            	if($dashlet->hasScript) {
             	   $display[$colNum]['dashlets'][$id]['script'] = $dashlet->displayScript();
            	}
        	}
    	}
    }
}

$sugar_smarty = new Sugar_Smarty();
if(!empty($sugar_config['lock_homepage']) && $sugar_config['lock_homepage'] == true) $sugar_smarty->assign('lock_homepage', true);  

$sugar_smarty->assign('pages', $pageData);
$sugar_smarty->assign('numPages', sizeof($pages));
$sugar_smarty->assign('loadedPage', 'pageNum_' . $activePage .'_div');

$sugar_smarty->assign('sugarVersion', $sugar_version);
$sugar_smarty->assign('sugarFlavor', $sugar_flavor);
$sugar_smarty->assign('currentLanguage', $GLOBALS['current_language']);
$sugar_smarty->assign('serverUniqueKey', $GLOBALS['server_unique_key']);
$sugar_smarty->assign('imagePath', $GLOBALS['image_path']);

$sugar_smarty->assign('jsCustomVersion', $sugar_config['js_custom_version']);
$sugar_smarty->assign('maxCount', empty($sugar_config['max_dashlets_homepage']) ? 15 : $sugar_config['max_dashlets_homepage']);
$sugar_smarty->assign('dashletCount', $count);
$sugar_smarty->assign('dashletIds', '["' . implode('","', $dashletIds) . '"]');
$sugar_smarty->assign('columns', $display);

global $theme;
$sugar_smarty->assign('theme', $theme);

$sugar_smarty->assign('divPages', $divPages);
$sugar_smarty->assign('activePage', $activePage);
$sugar_smarty->assign('numCols', $pages[$activePage]['numColumns']);

$sugar_smarty->assign('current_user', $current_user->id);

$local_mod_strings = return_module_language($sugar_config['default_language'], 'Home');
$sugar_smarty->assign('lblAddDashlets', $GLOBALS['app_strings']['LBL_ADD_DASHLETS']);
$sugar_smarty->assign('lblLnkHelp', $GLOBALS['app_strings']['LNK_HELP']);
$sugar_smarty->assign('lblAddPage', $GLOBALS['app_strings']['LBL_ADD_PAGE']);
$sugar_smarty->assign('lblPageName', $GLOBALS['app_strings']['LBL_PAGE_NAME']);
$sugar_smarty->assign('lblChangeLayout', $GLOBALS['app_strings']['LBL_CHANGE_LAYOUT']);
$sugar_smarty->assign('lblNumberOfColumns', $GLOBALS['app_strings']['LBL_NUMBER_OF_COLUMNS']);
$sugar_smarty->assign('lbl1Column', $GLOBALS['app_strings']['LBL_1_COLUMN']);
$sugar_smarty->assign('lbl2Column', $GLOBALS['app_strings']['LBL_2_COLUMN']);
$sugar_smarty->assign('lbl3Column', $GLOBALS['app_strings']['LBL_3_COLUMN']);

$sugar_smarty->assign('module', 'Dashboard');

echo $sugar_smarty->fetch('include/MySugar/tpls/MySugar.tpl');

?>
