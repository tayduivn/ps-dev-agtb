<?php

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
$module_name = '<module_name>';
$viewdefs[$module_name]['mobile']['view']['detail'] = array(
	'templateMeta' => array('form' => array('buttons'=>array('EDIT', 'DUPLICATE', 'DELETE',)),
                        	'maxColumns' => '1',
                        	'widths' => array(
                                        array('label' => '10', 'field' => '30'),
                                        array('label' => '10', 'field' => '30')
                                        ),
                        	),

	'panels' => array (
    	array (
            'label' => 'LBL_PANEL_DEFAULT',
            'fields' => array(
                array (
                    'name' => 'document_name',
                    'label' => 'LBL_DOC_NAME',
                ),
                array (
                    'name' => 'uploadfile',
                    'displayParams' => array('link'=>'uploadfile', 'id'=>'id'),
                ),
                'active_date',
                'exp_date',
                'assigned_user_name',
                //BEGIN SUGARCRM flav=pro ONLY
	  	    	'team_name',
                //END SUGARCRM flav=pro ONLY
            ),
    	),
	),
);
