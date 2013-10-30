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
            'label' => 'LBL_DASHLET_ATTACHMENTS_NAME',
            'description' => 'LBL_DASHLET_ATTACHMENTS_DESCRIPTION',
            'config' => array(
                'auto_refresh' => '0',
                'module' => 'Notes',
                'link' => 'notes',
            ),
            'preview' => array(
                'module' => 'Notes',
                'link' => 'notes',
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
            ),
        ),
 	),
    'custom_toolbar' => array(
        'buttons' => array(
            array(
                'type' => 'actiondropdown',
                'icon' => 'icon-plus',
                'no_default_action' => true,
                'buttons' => array(
                    array(
                        'type' => 'dashletaction',
                        'css_class' => '',
                        'label' => 'LBL_CREATE_RELATED_RECORD',
                        'action' => 'openCreateDrawer',
                    ),
                    array(
                        'type' => 'dashletaction',
                        'css_class' => '',
                        'label' => 'LBL_ASSOC_RELATED_RECORD',
                        'action' => 'openSelectDrawer',
                    ),
                ),
            ),
            array(
                'dropdown_buttons' => array(
                    array(
                        'type' => 'dashletaction',
                        'action' => 'editClicked',
                        'label' => 'LBL_DASHLET_CONFIG_EDIT_LABEL',
                    ),
                    array(
                        'type' => 'dashletaction',
                        'action' => 'refreshClicked',
                        'label' => 'LBL_DASHLET_REFRESH_LABEL',
                    ),
                    array(
                        'type' => 'dashletaction',
                        'action' => 'toggleClicked',
                        'label' => 'LBL_DASHLET_MINIMIZE',
                        'event' => 'minimize',
                    ),
                    array(
                        'type' => 'dashletaction',
                        'action' => 'removeClicked',
                        'label' => 'LBL_DASHLET_REMOVE_LABEL',
                    ),
                )
            )
        )
    ),
    'rowactions' => array(
        array(
            'type' => 'rowaction',
            'icon' => 'icon-unlink',
            'css_class' => 'btn btn-mini',
            'event' => 'attachment:unlinkrow:fire',
            'target' => 'view',
            'tooltip' => 'LBL_UNLINK_BUTTON',
            'acl_action' => 'edit',
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
        'image/jpeg' => 'JPG',
        'image/gif' => 'GIF',
        'image/png' => 'PNG',
	),
	'defaultType' => 'txt',
);
