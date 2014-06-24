<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

// $Id: default.php 13782 2006-06-06 17:58:55Z majed $

//BEGIN SUGARCRM flav=pro ONLY
global $current_user, $app;
// check if $app present - if in Studio/MB then loading a subpanel definition through the SubpanelDefinitions class 'requires' this file without an $app
if (isset($app) && isset($app->controller)){
	$projectId = $app->controller->record;
	
	$focus = BeanFactory::getBean('Project', $projectId);
	
	if (!$focus->isTemplate()){
		$subpanel_layout = array(
			'top_buttons' => array(
		        array('widget_class' => 'SubPanelTopCreateButton'),
					array('widget_class' => 'SubPanelTopSelectButton', 'popup_module' => 'ProjectTask'),
			),
		
			'where' => '',
		
		
			'list_fields' => array(
		        'name'=>array(
				 	'vname' => 'LBL_LIST_NAME',
					'widget_class' => 'SubPanelDetailViewLink',
					'width' => '20%',
				),
				'percent_complete'=>array(
				 	'vname' => 'LBL_LIST_PERCENT_COMPLETE',
					'width' => '20%',
				),
				'status'=>array(
				 	'vname' => 'LBL_LIST_STATUS',
					'width' => '20%',
				),
				'assigned_user_name'=>array(
				 	'vname' => 'LBL_LIST_ASSIGNED_USER_ID',
				 	'module' => 'Users',
					'width' => '20%',
				),
				'date_finish'=>array(
				 	'vname' => 'LBL_LIST_DATE_DUE',
					'width' => '20%',
				),
			),
		);
	}
	else{
//END SUGARCRM flav=pro ONLY
		$subpanel_layout = array(
	
		'top_buttons' => array(
		),
	
		'where' => '',
	
	
		'list_fields' => array(
	        'name'=>array(
			 	'vname' => 'LBL_LIST_NAME',
				'widget_class' => 'SubPanelDetailViewLink',
				'width' => '70%',
			),
			'date_start'=>array(
			 	'vname' => 'LBL_DATE_START',
				'width' => '15%',
			),
	        'date_finish'=>array(
	            'vname' => 'LBL_DATE_FINISH',
	            'width' => '15%',
	        ),
		),
	);
//BEGIN SUGARCRM flav=pro ONLY
	}
}
//END SUGARCRM flav=pro ONLY
?>