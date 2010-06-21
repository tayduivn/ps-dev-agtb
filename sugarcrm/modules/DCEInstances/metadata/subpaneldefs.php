<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * Layout definition for Quotes
 *
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
 * by SugarCRM are Copyright (C) 2005 SugarCRM, Inc.; All Rights Reserved.
 */
 
// $Id: layout_defs.php 15742 2006-08-09 21:49:45Z awu $

$layout_defs['DCEInstances'] = array(
    'subpanel_setup' => array(
        'dceactions' => array(
            'order' => 10,
            'sort_order' => 'desc',
            'sort_by' => 'date_entered',
            'module' => 'DCEActions',
            'subpanel_name' => 'default',
            'get_subpanel_data' => 'DCEActions',
            'title_key' => 'LBL_DCEACTIONS_SUBPANEL_TITLE',
        ),
        'cases' => array(
            'order' => 40,
            'sort_order' => 'desc',
            'sort_by' => 'name',
            'module' => 'Cases',
            'subpanel_name' => 'default',
            'get_subpanel_data' => 'Cases',
            'title_key' => 'LBL_CASES_SUBPANEL_TITLE',
        ),
        'activities' => array(
            'order' => 50,
            'sort_order' => 'desc',
            'sort_by' => 'date_start',
            'title_key' => 'LBL_ACTIVITIES_SUBPANEL_TITLE',
            'type' => 'collection',
            'subpanel_name' => 'activities',   //this values is not associated with a physical file.
            'module'=>'Activities',

            'top_buttons' => array(
                array('widget_class' => 'SubPanelTopCreateTaskButton'),
                array('widget_class' => 'SubPanelTopComposeEmailButton'),
            ),

            'collection_list' => array( 
                'tasks' => array(
                    'module' => 'Tasks',
                    'subpanel_name' => 'ForActivities',
                    'get_subpanel_data' => 'tasks',
                ),
            )           
        ),
        'history' => array(
            'order' => 60,
            'sort_order' => 'desc',
            'sort_by' => 'date_entered',
            'title_key' => 'LBL_HISTORY_SUBPANEL_TITLE',
            'type' => 'collection',
            'subpanel_name' => 'history',   //this values is not associated with a physical file.
            'module'=>'History',

            'top_buttons' => array(
            array('widget_class' => 'SubPanelTopCreateNoteButton'),
			array('widget_class' => 'SubPanelTopArchiveEmailButton'),
            array('widget_class' => 'SubPanelTopSummaryButton'),
            ),

            'collection_list' => array( 
                'tasks' => array(
                    'module' => 'Tasks',
                    'subpanel_name' => 'ForHistory',
                    'get_subpanel_data' => 'tasks',
                ),
                'notes' => array(
                    'module' => 'Notes',
                    'subpanel_name' => 'ForHistory',
                    'get_subpanel_data' => 'notes',
                ),  
                'emails' => array(
                    'module' => 'Emails',
                    'subpanel_name' => 'ForHistory',
                    'get_subpanel_data' => 'emails',
                ),  
            )           
        ),
        'contacts' => array(
            'order' => 70,
            'module' => 'Contacts',
            'sort_order' => 'asc',
            'sort_by' => 'last_name, first_name',
            'subpanel_name' => 'ForDCEInstances',
            'get_subpanel_data' => 'contacts',
            'add_subpanel_data' => 'contact_id',
            'title_key' => 'LBL_CONTACTS_SUBPANEL_TITLE',
            'top_buttons' => array(
                array('widget_class' => 'SubPanelTopCreateAccountNameButton'),
                array('widget_class' => 'SubPanelTopSelectButton',
                    'popup_module' => 'DCEInstances',
                    'mode' => 'MultiSelect', 
                    'initial_filter_fields' => array('account_id' => 'account_id', 'account_name' => 'account_name'),
                ),
            ),
        ),
        'users' => array(
            'order' => 80,
            'module' => 'Users',
            'sort_order' => 'asc',
            'sort_by' => 'user_name',
            'subpanel_name' => 'ForDCEInstances',
            'get_subpanel_data' => 'users',
            'add_subpanel_data' => 'user_id',
            'title_key' => 'LBL_USERS_SUBPANEL_TITLE',
        ),
        'dceinstances' => array(
            'order' => 90,
            'sort_order' => 'desc',
            'sort_by' => 'name',
            'module' => 'DCEInstances',
            'subpanel_name' => 'ForDCEInstances',
            'get_subpanel_data' => 'parent_dceinstance',
            'add_subpanel_data' => 'parent_dceinstance_id',
            'title_key' => 'LBL_DCEINSTANCES_SUBPANEL_TITLE',
        ),
    ),
);
?>