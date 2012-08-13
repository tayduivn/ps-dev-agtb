<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
$dictionary['User'] = array(
    'table' => 'users',
    'fields' => array(
        'id' => array(
            'name' => 'id',
            'vname' => 'LBL_ID',
            //BEGIN SUGARCRM flav=pro ONLY
            'type' => 'user_name',
            'dbType' => 'id',
            //END SUGARCRM flav=pro ONLY
            //BEGIN SUGARCRM flav!=pro ONLY
            'type' => 'id',
            //END SUGARCRM flav!=pro ONLY
            'required' => true,
        ) ,
        'user_name' => array(
            'name' => 'user_name',
            'vname' => 'LBL_USER_NAME',
            'type' => 'user_name',
            'dbType' => 'varchar',
            'len' => '60',
            'importable' => 'required',
            'required' => true,
            'studio' => array(
               'no_duplicate' => true,
               'editview' => false,
               'detailview' => true,
               'quickcreate' => false,
               'basic_search' => false,
               'advanced_search' => false,
               //BEGIN SUGARCRM flav=pro
               'wirelesseditview' => false,
               'wirelessdetailview' => true,
               'wirelesslistview' => false,
               'wireless_basic_search' => false,
               'wireless_advanced_search' => false,
               'rollup' => false,
               //END SUGARCRM flav=pro
               ),
        ) ,
        'user_hash' => array(
            'name' => 'user_hash',
            'vname' => 'LBL_USER_HASH',
            'type' => 'varchar',
            'len' => '255',
            'reportable' => false,
            'importable' => 'false',
            'studio' => array(
                'no_duplicate'=>true,
                'listview' => false,
                'searchview'=>false,
                //BEGIN SUGARCRM flav=pro ONLY
                'related' => false,
                'formula' => false,
                'rollup' => false,
                //END SUGARCRM flav=pro ONLY
            ),
        ) ,
        'system_generated_password' => array(
            'name' => 'system_generated_password',
            'vname' => 'LBL_SYSTEM_GENERATED_PASSWORD',
            'type' => 'bool',
            'required' => true,
            'reportable' => false,
            'massupdate' => false,
            'studio' => array(
                'listview' => false,
                'searchview'=>false,
                'editview'=>false,
                'quickcreate'=>false,
                'wirelesseditview' => false,
                //BEGIN SUGARCRM flav=pro ONLY
                'related' => false,
                'formula' => false,
                'rollup' => false,
                //END SUGARCRM flav=pro ONLY
            ),
        ) ,

        'pwd_last_changed' => array(
            'name' => 'pwd_last_changed',
            'vname' => 'LBL_PSW_MODIFIED',
            'type' => 'datetime',
            'required' => false,
            'massupdate' => false,
            'studio' => array('formula' => false),
        ) ,
        /**
         * authenticate_id is used by authentication plugins so they may place a quick lookup key for looking up a given user after authenticating through the plugin
         */
        'authenticate_id' => array(
            'name' => 'authenticate_id',
            'vname' => 'LBL_AUTHENTICATE_ID',
            'type' => 'varchar',
            'len' => '100',
            'reportable' => false,
            'importable' => 'false',
            'studio' => array('listview' => false, 'searchview'=>false, 'related' => false),
        ) ,
        /**
         * sugar_login will force the user to use sugar authentication
         * regardless of what authentication the system is configured to use
         */
        'sugar_login' => array(
            'name' => 'sugar_login',
            'vname' => 'LBL_SUGAR_LOGIN',
            'type' => 'bool',
            'default' => '1',
            'reportable' => false,
            'massupdate' => false,
            'importable' => false,
            'studio' => array('listview' => false, 'searchview'=>false, 'formula' => false),
        ) ,
        //BEGIN SUGARCRM flav!=com ONLY
        'picture' => array(
            'name' => 'picture',
            'vname' => 'LBL_PICTURE_FILE',
            'type' => 'image',
            'dbType' => 'varchar',
            'len' => '255',
            'width' => '',
            'height' => '',
            'border' => '',
        ) ,
        //END SUGARCRM flav!=com ONLY
        'first_name' => array(
            'name' => 'first_name',
            'vname' => 'LBL_FIRST_NAME',
            'dbType' => 'varchar',
            'type' => 'name',
            'len' => '30',
        ) ,
        'last_name' => array(
            'name' => 'last_name',
            'vname' => 'LBL_LAST_NAME',
            'dbType' => 'varchar',
            'type' => 'name',
            'len' => '30',
            'importable' => 'required',
        	'required' => true,
        ) ,
        'full_name' => array(
            'name' => 'full_name',
            'rname' => 'full_name',
            'vname' => 'LBL_NAME',
            'type' => 'name',
            'fields' => array(
                'first_name',
                'last_name'
            ) ,
            'source' => 'non-db',
            'sort_on' => 'last_name',
            'sort_on2' => 'first_name',
            'db_concat_fields' => array(
                0 => 'first_name',
                1 => 'last_name'
            ) ,
            'len' => '510',
            'studio' => array('formula' => false),
        ) ,
        'name' => array(
            'name' => 'name',
            'rname' => 'name',
            'vname' => 'LBL_NAME',
            'type' => 'varchar',
            'source' => 'non-db',
            'len' => '510',
            'db_concat_fields' => array(
                0 => 'first_name',
                1 => 'last_name'
            ) ,
            'importable' => 'false',
        ) ,
        'is_admin' => array(
            'name' => 'is_admin',
            'vname' => 'LBL_IS_ADMIN',
            'type' => 'bool',
            'default' => '0',
            'studio' => array('listview' => false, 'searchview'=>false, 'related' => false),
        ) ,
        'external_auth_only' => array(
            'name' => 'external_auth_only',
            'vname' => 'LBL_EXT_AUTHENTICATE',
            'type' => 'bool',
            'reportable' => false,
            'massupdate' => false,
            'default' => '0',
            'studio' => array('listview' => false, 'searchview'=>false, 'related' => false),
        ) ,
        'receive_notifications' => array(
            'name' => 'receive_notifications',
            'vname' => 'LBL_RECEIVE_NOTIFICATIONS',
            'type' => 'bool',
            'default' => '1',
            'massupdate' => false,
            'studio' => false,
        ) ,
        'description' => array(
            'name' => 'description',
            'vname' => 'LBL_DESCRIPTION',
            'type' => 'text',
        ) ,
        'date_entered' => array(
            'name' => 'date_entered',
            'vname' => 'LBL_DATE_ENTERED',
            'type' => 'datetime',
            'required' => true,
            'studio' => array('editview' => false, 'quickcreate' => false, 'wirelesseditview' => false),
        ) ,
        'date_modified' => array(
            'name' => 'date_modified',
            'vname' => 'LBL_DATE_MODIFIED',
            'type' => 'datetime',
            'required' => true,
            'studio' => array('editview' => false, 'quickcreate' => false, 'wirelesseditview' => false),
        ) ,
        'modified_user_id' => array(
            'name' => 'modified_user_id',
            'rname' => 'user_name',
            'id_name' => 'modified_user_id',
            'vname' => 'LBL_MODIFIED_BY_ID',
            'type' => 'assigned_user_name',
            'table' => 'users',
            'isnull' => 'false',
            'dbType' => 'id',
        ) ,
        'modified_by_name' => array(
            'name' => 'modified_by_name',
            'vname' => 'LBL_MODIFIED_BY',
            'type' => 'varchar',
            'source' => 'non-db',
            'studio' => false,
        ) ,
        'created_by' => array(
            'name' => 'created_by',
            'rname' => 'user_name',
            'id_name' => 'modified_user_id',
            'vname' => 'LBL_ASSIGNED_TO',
            'type' => 'assigned_user_name',
            'table' => 'users',
            'isnull' => 'false',
            'dbType' => 'id',
            'studio' => false,
        ) ,
        'created_by_name' => array(
            'name' => 'created_by_name',
	        'vname' => 'LBL_CREATED_BY_NAME', //bug 48978
            'type' => 'varchar',
            'source' => 'non-db',
            'importable' => 'false',
            //BEGIN SUGARCRM flav=pro ONLY
            'studio' => array(
                'related' => false,
                'formula' => false,
                'rollup' => false,
            ),
            //END SUGARCRM flav=pro ONLY
        ) ,
        'title' => array(
            'name' => 'title',
            'vname' => 'LBL_TITLE',
            'type' => 'varchar',
            'len' => '50',
        ) ,
        'department' => array(
            'name' => 'department',
            'vname' => 'LBL_DEPARTMENT',
            'type' => 'varchar',
            'len' => '50',
        ) ,
        'phone_home' => array(
            'name' => 'phone_home',
            'vname' => 'LBL_HOME_PHONE',
            'type' => 'phone',
			'dbType' => 'varchar',
            'len' => '50',
        ) ,
        'phone_mobile' => array(
            'name' => 'phone_mobile',
            'vname' => 'LBL_MOBILE_PHONE',
            'type' => 'phone',
			'dbType' => 'varchar',
            'len' => '50',
        ) ,
        'phone_work' => array(
            'name' => 'phone_work',
            'vname' => 'LBL_WORK_PHONE',
            'type' => 'phone',
			'dbType' => 'varchar',
            'len' => '50',
        ) ,
        'phone_other' => array(
            'name' => 'phone_other',
            'vname' => 'LBL_OTHER_PHONE',
            'type' => 'phone',
			'dbType' => 'varchar',
            'len' => '50',
        ) ,
        'phone_fax' => array(
            'name' => 'phone_fax',
            'vname' => 'LBL_FAX_PHONE',
            'type' => 'phone',
			'dbType' => 'varchar',
            'len' => '50',
        ) ,
        'status' => array(
            'name' => 'status',
            'vname' => 'LBL_STATUS',
            'type' => 'enum',
            'len' => 100,
            'options' => 'user_status_dom',
            'importable' => 'required',
            'required' => true,
        ) ,
        'address_street' => array(
            'name' => 'address_street',
            'vname' => 'LBL_ADDRESS_STREET',
            'type' => 'varchar',
            'len' => '150',
        ) ,
        'address_city' => array(
            'name' => 'address_city',
            'vname' => 'LBL_ADDRESS_CITY',
            'type' => 'varchar',
            'len' => '100',
        ) ,
        'address_state' => array(
            'name' => 'address_state',
            'vname' => 'LBL_ADDRESS_STATE',
            'type' => 'varchar',
            'len' => '100',
        ) ,
        'address_country' => array(
            'name' => 'address_country',
            'vname' => 'LBL_ADDRESS_COUNTRY',
            'type' => 'varchar',
            'len' => 100,
        ) ,
        'address_postalcode' => array(
            'name' => 'address_postalcode',
            'vname' => 'LBL_ADDRESS_POSTALCODE',
            'type' => 'varchar',
            'len' => '20',
        ) ,
        // This is a fake field for the edit view
        'UserType' => array(
            'name' => 'UserType',
            'vname' => 'LBL_USER_TYPE',
            'type' => 'enum',
            'len' => 50,
            'options' => 'user_type_dom',
            'source' => 'non-db',
            'import' => false,
            'reportable' => false,
            'studio' => array('formula' => false),
        ),
        //BEGIN SUGARCRM flav=sales ONLY
        'user_type' => array(
            'name' => 'user_type',
            'vname' => 'LBL_USER_TYPE',
            'type' => 'enum',
            'len' => 50,
            'options' => 'user_type_dom',
        ),
        //END SUGARCRM flav=sales ONLY
        //BEGIN SUGARCRM flav=pro ONLY
        'default_team' => array(
            'name' => 'default_team',
            'vname' => 'LBL_DEFAULT_TEAM',
            'reportable' => false,
            'type' => 'varchar',
            'len' => '36',
            'studio' => array('listview' => false, 'searchview'=>false, 'formula' => false),
        ) ,
        'team_id' => array(
            'name' => 'team_id',
            'vname' => 'LBL_DEFAULT_TEAM',
            'reportable' => false,
        	'source' => 'non-db',
            'type' => 'varchar',
            'len' => '36',
            'studio' => array('listview' => false, 'searchview'=>false, 'formula' => false),
        ) ,
			'team_set_id' =>
			array (
				'name' => 'team_set_id',
				'rname' => 'id',
				'id_name' => 'team_set_id',
				'vname' => 'LBL_TEAM_SET_ID',
				'type' => 'id',
			    'audited' => true,
			    'studio' => 'false',
			),
			'team_count' =>
			array (
				'name' => 'team_count',
				'rname' => 'team_count',
				'id_name' => 'team_id',
				'vname' => 'LBL_TEAMS',
				'join_name'=>'ts1',
				'table' => 'team_sets',
				'type' => 'relate',
	            'required' => 'true',
				'table' => 'teams',
				'isnull' => 'true',
				'module' => 'Teams',
				'link' => 'team_count_link',
				'massupdate' => false,
				'dbType' => 'int',
				'source' => 'non-db',
				'importable' => 'false',
				'reportable'=>false,
			    'duplicate_merge' => 'disabled',
				'studio' => 'false',
			),
			'team_name' =>
			array (
				'name' => 'team_name',
				'db_concat_fields'=> array(0=>'name', 1=>'name_2'),
				'rname' => 'name',
				'id_name' => 'team_id',
				'vname' => 'LBL_TEAMS',
				'type' => 'relate',
	            'required' => true,
				'table' => 'teams',
				'isnull' => 'true',
				'module' => 'Teams',
				'link' => 'team_link',
				'massupdate' => false,
				'dbType' => 'varchar',
				'source' => 'non-db',
				'len' => 36,
				'custom_type' => 'teamset',
                'studio' => array('listview' => false, 'searchview'=>false, 'editview'=>false, 'quickcreate'=>false, 'wirelesseditview' => false),
			),
			'team_link' =>
		    array (
		      'name' => 'team_link',
		      'type' => 'link',
		      'relationship' => 'users_team',
		      'vname' => 'LBL_TEAMS_LINK',
		      'link_type' => 'one',
		      'module' => 'Teams',
		      'bean_name' => 'Team',
		      'source' => 'non-db',
		      'duplicate_merge' => 'disabled',
		      'studio' => 'false',
                'reportable'=>false,
		    ),
            'default_primary_team' => array (
                'name' => 'default_primary_team',
                'type' => 'link',
                'relationship' => 'users_team',
                'vname' => 'LBL_DEFAULT_PRIMARY_TEAM',
                'link_type' => 'one',
                'module' => 'Teams',
                'bean_name' => 'Team',
                'source' => 'non-db',
                'duplicate_merge' => 'disabled',
                'studio' => 'false',
            ),
		    'team_count_link' =>
	  			array (
	  			'name' => 'team_count_link',
	    		'type' => 'link',
	    		'relationship' => 'users_team_count_relationship',
	            'link_type' => 'one',
			    'module' => 'Teams',
			    'bean_name' => 'TeamSet',
			    'source' => 'non-db',
			    'duplicate_merge' => 'disabled',
	  			'reportable'=>false,
	  			'studio' => 'false',
	  		),
	  		'teams' =>
			array (
				'name' => 'teams',
		        'type' => 'link',
				'relationship' => 'users_teams',
				'bean_filter_field' => 'team_set_id',
				'rhs_key_override' => true,
		        'source' => 'non-db',
				'vname' => 'LBL_TEAMS',
				'link_class' => 'TeamSetLink',
				'link_file' => 'modules/Teams/TeamSetLink.php',
				'studio' => 'false',
				'reportable'=>false,
			),
			'team_memberships' => array(
	            'name' => 'team_memberships',
	            'type' => 'link',
	            'relationship' => 'team_memberships',
	            'source' => 'non-db',
	            'vname' => 'LBL_TEAM_MEMBERSHIP'
        	) ,
            'team_sets' => array(
                'name' => 'team_sets',
                'type' => 'link',
                'relationship' => 'users_team_sets',
                'source' => 'non-db',
                'vname' => 'LBL_TEAM_SET'
            ),
			'users_signatures' => array(
			    'name' => 'users_signatures',
			    'type' => 'link',
			    'relationship' => 'users_users_signatures',
			    'source' => 'non-db',
			    'studio' => 'false',
			    'reportable'=>false,
			    ),

        //END SUGARCRM flav=pro ONLY
        'deleted' => array(
            'name' => 'deleted',
            'vname' => 'LBL_DELETED',
            'type' => 'bool',
            'required' => false,
            'reportable' => false,
        ) ,
        'portal_only' => array(
            'name' => 'portal_only',
            'vname' => 'LBL_PORTAL_ONLY_USER',
            'type' => 'bool',
            'massupdate' => false,
            //BEGIN SUGARCRM flav=sales ONLY
            'importable' => false,
            //END SUGARCRM flav=sales ONLY
            'default' => '0',
            'studio' => array('listview' => false, 'searchview'=>false, 'formula' => false),
        ) ,
        'show_on_employees' => array(
            'name' => 'show_on_employees',
            'vname' => 'LBL_SHOW_ON_EMPLOYEES',
            'type' => 'bool',
            'massupdate' => true,
            'importable' => true,
            'default' => true,
            'studio' => array('formula' => false),
        ) ,
        'employee_status' => array(
            'name' => 'employee_status',
            'vname' => 'LBL_EMPLOYEE_STATUS',
            'type' => 'varchar',
            'function' => array(
                'name' => 'getEmployeeStatusOptions',
                'returns' => 'html',
                'include' => 'modules/Employees/EmployeeStatus.php'
            ) ,
            'len' => 100,
        ) ,
        'messenger_id' => array(
            'name' => 'messenger_id',
            'vname' => 'LBL_MESSENGER_ID',
            'type' => 'varchar',
            'len' => 100,
        ) ,
        'messenger_type' => array(
            'name' => 'messenger_type',
            'vname' => 'LBL_MESSENGER_TYPE',
            'type' => 'enum',
            'options' => 'messenger_type_dom',
            'len' => 100,
        ) ,
        'calls' => array(
            'name' => 'calls',
            'type' => 'link',
            'relationship' => 'calls_users',
            'source' => 'non-db',
            'vname' => 'LBL_CALLS'
        ) ,
        'meetings' => array(
            'name' => 'meetings',
            'type' => 'link',
            'relationship' => 'meetings_users',
            'source' => 'non-db',
            'vname' => 'LBL_MEETINGS'
        ) ,
        'contacts_sync' => array(
            'name' => 'contacts_sync',
            'type' => 'link',
            'relationship' => 'contacts_users',
            'source' => 'non-db',
            'vname' => 'LBL_CONTACTS_SYNC',
            'reportable' => false,
        ) ,
        'reports_to_id' => array(
            'name' => 'reports_to_id',
            'vname' => 'LBL_REPORTS_TO_ID',
            'type' => 'id',
            'required' => false,
        ) ,
        'reports_to_name' => array(
            'name' => 'reports_to_name',
            'rname' => 'last_name',
            'id_name' => 'reports_to_id',
            'vname' => 'LBL_REPORTS_TO_NAME',
            'type' => 'relate',
            'isnull' => 'true',
            'module' => 'Users',
            'table' => 'users',
            'link' => 'reports_to_link',
            'reportable' => false,
            'source' => 'non-db',
            'duplicate_merge' => 'disabled',
            'side' => 'right',
        ) ,
        'reports_to_link' => array(
            'name' => 'reports_to_link',
            'type' => 'link',
            'relationship' => 'user_direct_reports',
            'link_type' => 'one',
            'side' => 'right',
            'source' => 'non-db',
            'vname' => 'LBL_REPORTS_TO',
        ) ,
        'reportees' => array(
            'name' => 'reportees',
            'type' => 'link',
            'relationship' => 'user_direct_reports',
            'link_type' => 'many',
            'side' => 'left',
            'source' => 'non-db',
            'vname' => 'LBL_REPORTS_TO',
            'reportable' => false,
        ) ,
        'email1' => array(
            'name' => 'email1',
            'vname' => 'LBL_EMAIL',
            'type' => 'varchar',
            'function' => array(
                'name' => 'getEmailAddressWidget',
                'returns' => 'html'
            ) ,
            'source' => 'non-db',
            'group' => 'email1',
            'merge_filter' => 'enabled',
            'required' => true,
        ) ,
        'email_addresses' => array(
            'name' => 'email_addresses',
            'type' => 'link',
            'relationship' => 'users_email_addresses',
            'module' => 'EmailAddress',
            'bean_name' => 'EmailAddress',
            'source' => 'non-db',
            'vname' => 'LBL_EMAIL_ADDRESSES',
            'reportable' => false,
            'required' => true,
        ) ,
        'email_addresses_primary' => array(
            'name' => 'email_addresses_primary',
            'type' => 'link',
            'relationship' => 'users_email_addresses_primary',
            'source' => 'non-db',
            'vname' => 'LBL_EMAIL_ADDRESS_PRIMARY',
            'duplicate_merge' => 'disabled',
            'required' => true,
        ),
        /* Virtual email fields so they will display on the main user page */
        'email_link_type' => array(
            'name' => 'email_link_type',
            'vname' => 'LBL_EMAIL_LINK_TYPE',
            'type' => 'enum',
            'options' => 'dom_email_link_type',
            'importable' => false,
            'reportable' => false,
            'source' => 'non-db',
            'studio' => false,
        ),
        
        'aclroles' => array(
            'name' => 'aclroles',
            'type' => 'link',
            'relationship' => 'acl_roles_users',
            'source' => 'non-db',
            'side' => 'right',
            'vname' => 'LBL_ROLES',
        ) ,
        'is_group' => array(
            'name' => 'is_group',
            'vname' => 'LBL_GROUP_USER',
            'type' => 'bool',
            'massupdate' => false,
            'studio' => array('listview' => false, 'searchview'=>false, 'formula' => false),
            //BEGIN SUGARCRM flav=sales ONLY
            'importable' => false,
            //END SUGARCRM flav=sales ONLY
        ) ,
        /* to support Meetings SubPanels */
        'c_accept_status_fields' => array(
            'name' => 'c_accept_status_fields',
            'rname' => 'id',
            'relationship_fields' => array(
                'id' => 'accept_status_id',
                'accept_status' => 'accept_status_name'
            ) ,
            'vname' => 'LBL_LIST_ACCEPT_STATUS',
            'type' => 'relate',
            'link' => 'calls',
            'link_type' => 'relationship_info',
            'source' => 'non-db',
            'importable' => 'false',
            'studio' => array('listview' => false, 'searchview'=>false, 'formula' => false),
        ) ,
        'm_accept_status_fields' => array(
            'name' => 'm_accept_status_fields',
            'rname' => 'id',
            'relationship_fields' => array(
                'id' => 'accept_status_id',
                'accept_status' => 'accept_status_name'
            ) ,
            'vname' => 'LBL_LIST_ACCEPT_STATUS',
            'type' => 'relate',
            'link' => 'meetings',
            'link_type' => 'relationship_info',
            'source' => 'non-db',
            'importable' => 'false',
            'studio' => array('listview' => false, 'searchview'=>false, 'formula' => false),
        ) ,
        'accept_status_id' => array(
            'name' => 'accept_status_id',
            'type' => 'varchar',
            'source' => 'non-db',
            'vname' => 'LBL_LIST_ACCEPT_STATUS',
            'importable' => 'false',
        	'studio' => array('listview' => false, 'searchview'=>false, 'formula' => false),
        ) ,
        'accept_status_name' => array(
            'name' => 'accept_status_name',
            'type' => 'enum',
            'source' => 'non-db',
            'vname' => 'LBL_LIST_ACCEPT_STATUS',
            'options' => 'dom_meeting_accept_status',
            'massupdate' => false,
            'studio' => array('listview' => false, 'searchview'=>false, 'formula' => false),
        ) ,
        //BEGIN SUGARCRM flav!=sales ONLY
        'prospect_lists' => array(
            'name' => 'prospect_lists',
            'type' => 'link',
            'relationship' => 'prospect_list_users',
            'module' => 'ProspectLists',
            'source' => 'non-db',
            'vname' => 'LBL_PROSPECT_LIST',
        ) ,
        //END SUGARCRM flav!=sales ONLY
        'emails_users' => array(
            'name' => 'emails_users',
            'type' => 'link',
            'relationship' => 'emails_users_rel',
            'module' => 'Emails',
            'source' => 'non-db',
            'vname' => 'LBL_EMAILS'
        ),
        'holidays' => array(
            'name' => 'holidays',
            'type' => 'link',
            'relationship' => 'users_holidays',
            'source' => 'non-db',
            'side' => 'right',
            'vname' => 'LBL_HOLIDAYS',
        ) ,
       'eapm' =>
		  array (
		    'name' => 'eapm',
		    'type' => 'link',
		    'relationship' => 'eapm_assigned_user',
		    'vname' => 'LBL_ASSIGNED_TO_USER',
		    'source'=>'non-db',
		  ),
        //BEGIN SUGARCRM flav=dce ONLY
        'dceinstance_role_fields' => array(
            'name' => 'dceinstance_role_fields',
            'rname' => 'id',
            'relationship_fields' => array(
                'id' => 'dceinstance_role_id',
                'user_role' => 'dceinstance_role'
            ) ,
            'vname' => 'LBL_ACCOUNT_NAME',
            'type' => 'relate',
            'link' => 'dceinstances',
            'link_type' => 'relationship_info',
            'join_link_name' => 'dceinstances_users',
            'join_primary' => false,
            'source' => 'non-db',
            'importable' => 'false',
            'duplicate_merge' => 'disabled',
            'studio' => false,
        ) ,
        'dceinstance_role_id' => array(
            'name' => 'dceinstance_role_id',
            'type' => 'varchar',
            'source' => 'non-db',
            'vname' => 'LBL_DCEINSTANCE_ROLE_ID',
            'importable' => 'false',
        ) ,
        'dceinstance_role' => array(
            'name' => 'dceinstance_role',
            'type' => 'enum',
            'source' => 'non-db',
            'vname' => 'LBL_DCEINSTANCE_ROLE',
            'options' => 'dceinstance_user_relationship_type_dom',
            'importable' => 'false',
            'massupdate' => false,
        ) ,
        'dceinstances' => array(
            'name' => 'dceinstances',
            'type' => 'link',
            'relationship' => 'dceinstances_users',
            'source' => 'non-db',
            'module' => 'DCEInstances',
            'bean_name' => 'DCEInstance',
            'vname' => 'LBL_DCEINSTANCES',
            'importable' => 'false',
        ) ,
        //END SUGARCRM flav=dce ONLY
	 'oauth_tokens' =>
      array (
        'name' => 'oauth_tokens',
        'type' => 'link',
        'relationship' => 'oauthtokens_assigned_user',
        'vname' => 'LBL_OAUTH_TOKENS',
        'link_type' => 'one',
        'module'=>'OAuthTokens',
        'bean_name'=>'OAuthToken',
        'source'=>'non-db',
        'side' => 'left',
      ),
        'project_resource'=>
		array (
			'name' => 'project_resource',
			'type' => 'link',
			'relationship' => 'projects_users_resources',
			'source' => 'non-db',
			'vname' => 'LBL_PROJECTS',
		),
        'quotas' =>
        array (
            'name' => 'quotas',
            'type' => 'link',
            'relationship' => 'users_quotas',
            'source'=>'non-db',
            'link_type'=>'one',
            'vname'=>'LBL_QUOTAS',
        ),
        'forecasts' =>
        array (
            'name' => 'forecasts',
            'type' => 'link',
            'relationship' => 'users_forecasts',
            'source'=>'non-db',
            'link_type'=>'one',
            'vname'=>'LBL_FORECASTS',
        ),
        'worksheets' =>
        array (
            'name' => 'worksheets',
            'type' => 'link',
            'relationship' => 'users_worksheets',
            'source'=>'non-db',
            'link_type'=>'one',
            'vname'=>'LBL_WORKSHEETS',
        ),
    ) ,
    'indices' => array(
        array(
            'name' => 'userspk',
            'type' => 'primary',
            'fields' => array(
                'id'
            )
        ) ,
        array(
            'name' => 'idx_user_name',
            'type' => 'index',
            'fields' => array(
                'user_name',
                //BEGIN SUGARCRM flav!=sales ONLY
                'is_group',
                //END SUGARCRM flav!=sales ONLY
                'status',
                'last_name',
                'first_name',
                'id'
            )
        ) ,
     //BEGIN SUGARCRM flav=pro ONLY
		array(
			'name' => 'idx_users_tmst_id',
			'type' => 'index',
			'fields' => array('team_set_id')
		),
	//END SUGARCRM flav=pro ONLY
    ) ,
	'relationships' => array (
  		'user_direct_reports' => array('lhs_module'=> 'Users', 'lhs_table'=> 'users', 'lhs_key' => 'id', 'rhs_module'=> 'Users', 'rhs_table'=> 'users', 'rhs_key' => 'reports_to_id', 'relationship_type'=>'one-to-many'),
  		'users_users_signatures' =>
  		   array(
  		       'lhs_module'=> 'Users',
  		       'lhs_table'=> 'users',
  		       'lhs_key' => 'id',
  		       'rhs_module'=> 'UserSignature',
  		       'rhs_table'=> 'users_signatures',
  		       'rhs_key' => 'user_id',
  		       'relationship_type'=>'one-to-many'
  		       ),
    	'users_email_addresses' =>
		    array(
		        'lhs_module'=> "Users", 'lhs_table'=> 'users', 'lhs_key' => 'id',
		        'rhs_module'=> 'EmailAddresses', 'rhs_table'=> 'email_addresses', 'rhs_key' => 'id',
		        'relationship_type'=>'many-to-many',
		        'join_table'=> 'email_addr_bean_rel', 'join_key_lhs'=>'bean_id', 'join_key_rhs'=>'email_address_id',
		        'relationship_role_column'=>'bean_module',
		        'relationship_role_column_value'=>"Users"
		    ),
		'users_email_addresses_primary' =>
		    array('lhs_module'=> "Users", 'lhs_table'=> 'users', 'lhs_key' => 'id',
		        'rhs_module'=> 'EmailAddresses', 'rhs_table'=> 'email_addresses', 'rhs_key' => 'id',
		        'relationship_type'=>'many-to-many',
		        'join_table'=> 'email_addr_bean_rel', 'join_key_lhs'=>'bean_id', 'join_key_rhs'=>'email_address_id',
		        'relationship_role_column'=>'primary_address',
		        'relationship_role_column_value'=>'1'
		    ),
		//BEGIN SUGARCRM flav=pro ONLY
		'users_team_count_relationship' =>
			 array(
			 	'lhs_module'=> 'Teams',
			 	'lhs_table'=> 'team_sets',
			 	'lhs_key' => 'id',
	    		'rhs_module'=> 'Users',
	    		'rhs_table'=> 'users',
	    		'rhs_key' => 'team_set_id',
	   			'relationship_type'=>'one-to-many'
			 ),
		'users_teams' =>
			array (
				'lhs_module'        => 'Users',
	            'lhs_table'         => 'users',
	            'lhs_key'           => 'team_set_id',
	            'rhs_module'        => 'Teams',
	            'rhs_table'         => 'teams',
	            'rhs_key'           => 'id',
	            'relationship_type' => 'many-to-many',
	            'join_table'        => 'team_sets_teams',
	            'join_key_lhs'      => 'team_set_id',
	            'join_key_rhs'      => 'team_id',
			),
        'users_forecasts' => array(
            'rhs_module'		=> 'Forecasts',
            'rhs_table'			=> 'forecasts',
            'rhs_key'			=> 'user_id',
            'lhs_module'		=> 'Users',
            'lhs_table'			=> 'users',
            'lhs_key'			=> 'id',
            'relationship_type'	=> 'one-to-many',
            'relationship_role_column'=>'forecast_type',
            'relationship_role_column_value'=>'Rollup'
        ),

        'users_quotas' => array(
            'rhs_module'		=> 'Quotas',
            'rhs_table'			=> 'quotas',
            'rhs_key'			=> 'user_id',
            'lhs_module'		=> 'Users',
            'lhs_table'			=> 'users',
            'lhs_key'			=> 'id',
            'relationship_type'	=> 'one-to-many',
            'relationship_role_column'=>'quota_type',
            'relationship_role_column_value'=>'Direct'
        ),

        'users_worksheets' => array(
            'rhs_module'		=> 'Worksheet',
            'rhs_table'			=> 'worksheet',
            'rhs_key'			=> 'related_id',
            'lhs_module'		=> 'Users',
            'lhs_table'			=> 'users',
            'lhs_key'			=> 'id',
            'relationship_type'	=> 'one-to-many',
            'relationship_role_column'=>'related_forecast_type',
            'relationship_role_column_value'=>'Direct'
        ),
        'users_team_sets' => array (
            'lhs_module'        => 'Teams',
            'lhs_table'         => 'teams',
            'lhs_key'           => 'id',
            'rhs_module'        => 'Users',
            'rhs_table'         => 'users',
            'rhs_key'           => 'team_set_id',
            'relationship_type' => 'many-to-many',
            'join_table'        => 'team_sets_teams',
            'join_key_lhs'      => 'team_id',
            'join_key_rhs'      => 'team_set_id',
        ),
        'users_team' => array(
            'lhs_module'=> 'Teams',
            'lhs_table'=> 'teams',
            'lhs_key' => 'id',
            'rhs_module'=> 'Users',
            'rhs_table'=> 'users',
            'rhs_key' => 'default_team',
            'relationship_type'=>'one-to-many'
        ),
	   //END SUGARCRM flav=pro ONLY
    ),



);
