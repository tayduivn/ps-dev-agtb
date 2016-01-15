<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
//FILE SUGARCRM flav=int ONLY
$layout_defs['Queues'] = array(
	// list of what Subpanels to show in the DetailView 
	'subpanel_setup' => array( 
        'emails' => array(
			'order' => 20,
			'module' => 'Emails',
			'sort_by' => 'name',
			'sort_order' => 'asc',			
			'subpanel_name' => 'ForQueues',
			'get_subpanel_data' => 'queues_emails',
			'add_subpanel_data' => 'object_id',
			'title_key' => 'LBL_EMAILS_SUBPANEL_TITLE',
			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopSelectButton', 'mode'=>'MultiSelect'),
			),
		),
	),
);
 
?>