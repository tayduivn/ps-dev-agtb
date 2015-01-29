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

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$module_name = 'pmse_Inbox';
$viewdefs[$module_name]['base']['view']['list'] = array(
    'panels' => array(
        array(
            'label' => 'LBL_PANEL_1',
            'fields' => array(
                array(
                    'name' => 'cas_id',
                    'label' => 'LBL_CAS_ID',
                    'default' => true,
                    'enabled' => true,
                    'link' => false,
                ),
                array(
                    'name' => 'pro_title',
                    'label' => 'LBL_PROCESS_DEFINITION_NAME',
                    'default' => true,
                    'enabled' => true,
                    'link' => false,
                ),
                array(
                    'name' => 'task_name',
                    'label' => 'LBL_PROCESS_NAME',
                    'default' => true,
                    'enabled' => true,
                    'link' => false,
                ),
                array(
                    'name' => 'cas_title',
                    'label' => 'LBL_RECORD_NAME',
                    'default' => true,
                    'enabled' => true,
                    'link' => false,
                ),
                /*array(
                    'name' => 'cas_status',
                    'label' => 'Status',
                    'default' => true,
                    'enabled' => true,
                    'link' => false,
                ),*/
                array(
                    'name' => 'case_init',
                    'label' => 'LBL_OWNER',
                    'width' => 9,
                    'default' => true,
                    'enabled' => true,
                    'link' => false,
                ),
                array(
                    'label' => 'LBL_DATE_ENTERED',
                    'enabled' => true,
                    'default' => true,
                    'name' => 'date_entered',
                    'readonly' => true,
                ),
            ),
        ),
    ),
    'orderBy' => array(
        'field' => 'date_modified',
        'direction' => 'desc',
    ),
);

/*$module_name = 'pmse_Inbox';
$viewdefs[$module_name]['base']['view']['list'] = array(
    'panels' => array(
        array(
            'label' => 'LBL_PANEL_1',
            'fields' => array(
                array(
                    'name' => 'cas_name',
                    'label' => 'Case Title',
                    'default' => true,
                    'enabled' => true,
                    'readonly' => true,
                    'link' => false,
                ),
                array(
                    'name' => 'pro_title',
                    'label' => 'Process',
                    'default' => true,
                    'readonly' => true,
                    'enabled' => true,
                    'link' => false
                ),
                array(
                    'name' => 'task_name',
                    'label' => 'Task Name',
                    'default' => true,
                    'enabled' => true,
                    'readonly' => true,
                    'link' => false,
                ),
                array(
                    'name' => 'cas_id',
                    'label' => 'Case ID',
                    'enabled' => true,
                    'default' => true,
                    'readonly' => true,
                ),
                array(
                    'name' => 'cas_delegate_date',
                    'label' => 'Delegated',
                    'enabled' => true,
                    'default' => true,
                    'readonly' => true,
                    'link' => false
                ),
                array(
                    'name' => 'cas_due_date',
                    'label' => 'Due Date',
                    'enabled' => true,
                    'default' => true,
                    'readonly' => true,
                    'link' => false
                ),
            ),
        ),
    ),
    'orderBy' => array(
        'field' => 'date_entered',
        'direction' => 'asc',
    ),
);*/