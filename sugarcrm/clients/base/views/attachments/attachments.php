<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */
$viewdefs['base']['view']['attachments'] = array(
	'dashlets' => array(
		array(
            'name' => 'Attachments',
            'description' => 'Attachments belongs to the record',
            'config' => array(
                'auto_refresh' => '0',
                'module' => 'Notes',
                'link' => 'notes',
            ),
            'preview' => array(
                'module' => 'Notes',
            ),
            'filter' => array(
                'module' => array(
                    'Accounts',
                    'Contacts',
                    'Opportunities',
                    'Leads',
                    'Bugs',
                    'Cases',
                ),
                'view' => 'record',
            )
        ),
 	),
    'buttons' => array(
        array(
            'type' => 'button',
            'icon' => 'icon-plus',
            'name' => 'create_button',
            'label' => ' ',
            'acl_action' => 'create',
        ),
        array(
            'type' => 'button',
            'name' => 'select_button',
            'label' => 'LBL_ASSOC_RELATED_RECORD',
            'icon' => 'icon-pencil',
            'acl_action' => 'view',
        ),
    ),
    'panels' => array(
        array(
            'name' => 'panel_body',
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => array(
                array(
                    'name' => 'limit',
                    'label' => 'Display Rows',
                    'type' => 'enum',
                    'options' => array(
                        5 => 5,
                        10 => 10,
                        15 => 15,
                        20 => 20
                    )
                ),
                array(
                    'name' => 'auto_refresh',
                    'label' => 'Auto Refresh',
                    'type' => 'enum',
                    'options' => 'sugar7_dashlet_auto_refresh_options',
                ),
            ),
        ),
    ),
	'supportedImageExtensions' => array(
		'jpg' => 'image/jpeg', 
		'jpeg' => 'image/jpeg',
		'png' => 'image/png',
	),
	'defaultType' => 'txt',
);
