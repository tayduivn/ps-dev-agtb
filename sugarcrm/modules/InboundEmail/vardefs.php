<?php
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
$dictionary['InboundEmail'] = array('table' => 'inbound_email', 'comment' => 'Inbound email parameters',
	'fields' => array (
		'id' => array (
			'name' => 'id',
			'vname' => 'LBL_ID',
			'type' => 'id',
			'dbType' => 'varchar',
			'required' => true,
			'reportable'=>false,
			'comment' => 'Unique identifier'
		),
        'eapm_id' => [
            'name' => 'eapm_id',
            'vname' => 'LBL_EAPM_ID',
            'type' => 'id',
            'readonly' => true,
        ],
        'authorized_account' => [
            'name' => 'authorized_account',
            'vname' => 'LBL_AUTHORIZED_ACCOUNT',
            'type' => 'varchar',
            'readonly' => true,
        ],
        'auth_type' => [
            'name' => 'auth_type',
            'vname' => 'LBL_MAIL_AUTHTYPE',
            'type' => 'varchar',
            'len' => '10',
            'readonly' => true,
        ],
        'email_provider' => [
            'name' => 'email_provider',
            'vname' => 'LBL_EMAIL_PROVIDER',
            'type' => 'enum',
            'options' => 'mail_imaptype_options',
            'len' => 20,
            'default' => 'other',
            'required' => true,
        ],
		'deleted' => array (
			'name' => 'deleted',
			'vname' => 'LBL_DELETED',
			'type' => 'bool',
			'required' => false,
			'default' => '0',
			'reportable'=>false,
			'comment' => 'Record deltion indicator'
		),
		'date_entered' => array (
			'name' => 'date_entered',
			'vname' => 'LBL_DATE_ENTERED',
			'type' => 'datetime',
			'required' => true,
			'comment' => 'Date record created'
		),
		'date_modified' => array (
			'name' => 'date_modified',
			'vname' => 'LBL_DATE_MODIFIED',
			'type' => 'datetime',
			'required' => true,
			'comment' => 'Date record last modified'
		),
		'modified_user_id' => array (
			'name' => 'modified_user_id',
			'rname' => 'user_name',
			'id_name' => 'modified_user_id',
			'vname' => 'LBL_MODIFIED_BY',
			'type' => 'modified_user_name',
			'table' => 'users',
			'isnull' => false,
			'dbType' => 'id',
			'reportable'=>true,
			'comment' => 'User who last modified record'
		),
		'modified_user_id_link' => array (
			'name' => 'modified_user_id_link',
			'type' => 'link',
			'relationship' => 'inbound_email_modified_user_id',
			'vname' => 'LBL_MODIFIED_BY_USER',
			'link_type' => 'one',
			'module' => 'Users',
			'bean_name' => 'User',
			'source' => 'non-db',
		),
		'created_by' => array (
			'name' => 'created_by',
			'rname' => 'user_name',
			'id_name' => 'modified_user_id',
			'vname' => 'LBL_ASSIGNED_TO',
			'type' => 'assigned_user_name',
			'table' => 'users',
			'isnull' => false,
			'dbType' => 'id',
			'comment' => 'User who created record'
		),
		'created_by_link' => array (
			'name' => 'created_by_link',
			'type' => 'link',
			'relationship' => 'inbound_email_created_by',
			'vname' => 'LBL_CREATED_BY_USER',
			'link_type' => 'one',
			'module' => 'Users',
			'bean_name' => 'User',
			'source' => 'non-db',
		),
		'name' => array (
			'name' => 'name',
			'vname' => 'LBL_NAME',
			'type' => 'varchar',
			'len' => '255',
			'required' => false,
			'reportable' => false,
			'comment' => 'Name given to the inbound email mailbox'
		),
		'status' => array (
			'name' => 'status',
			'vname' => 'LBL_STATUS',
			'type' => 'varchar',
			'len' => 100,
			'default' => 'Active',
			'required' => true,
			'reportable' => false,
			'comment' => 'Status of the inbound email mailbox (ex: Active or Inactive)'
		),
		'server_url' => array (
			'name' => 'server_url',
			'vname' => 'LBL_SERVER_URL',
			'type' => 'varchar',
			'len' => '100',
			'required' => true,
			'reportable' => false,
			'comment' => 'Mail server URL',
			'importable' => 'required',
		),
		'email_user' => array (
			'name' => 'email_user',
			'vname' => 'LBL_LOGIN',
			'type' => 'varchar',
			'len' => '100',
			'required' => true,
			'reportable' => false,
			'comment' => 'User name allowed access to mail server'
		),
		'email_password' => array (
			'name' => 'email_password',
			'vname' => 'LBL_PASSWORD',
			'type' => 'encrypt',
			'len' => '100',
			'required' => true,
			'reportable' => false,
            'write_only' => true,
			'comment' => 'Password of user identified by email_user'
		),
		'port' => array (
			'name' => 'port',
			'vname' => 'LBL_SERVER_TYPE',
			'type' => 'int',
			'len' => '5',
			'required' => true,
			'reportable' => false,
			'validation' => array ('type' => 'range', 'min' => '110', 'max' => '65535'),
			'comment' => 'Port used to access mail server'
		),
		'service' => array (
			'name' => 'service',
			'vname' => 'LBL_SERVICE',
			'type' => 'varchar',
			'len' => '50',
			'required' => true,
			'reportable' => false,
			'comment' => '',
			'importable' => 'required',
		),
		'mailbox' => array (
			'name' => 'mailbox',
			'vname' => 'LBL_MAILBOX',
			'type' => 'text',
			'required' => true,
			'reportable' => false,
			'comment' => ''
		),
		'delete_seen' => array (
			'name' => 'delete_seen',
			'vname' => 'LBL_DELETE_SEEN',
			'type' => 'bool',
			'default' => '0',
			'reportable' => false,
			'massupdate' => '',
			'comment' => 'Delete email from server once read (seen)'
		),
		'mailbox_type' => array (
			'name' => 'mailbox_type',
			'vname' => 'LBL_MAILBOX_TYPE',
			'type' => 'varchar',
			'len' => '10',
			'reportable' => false,
			'comment' => ''
		),
		'template_id' => array (
			'name' => 'template_id',
			'vname' => 'LBL_AUTOREPLY',
			'type' => 'id',
			'reportable' => false,
			'comment' => 'Template used for auto-reply'
		),
		'stored_options' => array (
			'name' => 'stored_options',
			'vname' => 'LBL_STORED_OPTIONS',
			'type' => 'text',
			'reportable' => false,
			'comment' => ''
		),
		'group_id' => array (
			'name' => 'group_id',
			'vname' => 'LBL_GROUP_ID',
			'type' => 'id',
			'reportable' => false,
			'comment' => 'Group ID (unused)'
		),
		'is_personal' => array (
			'name' => 'is_personal',
			'vname' => 'LBL_IS_PERSONAL',
			'type' => 'bool',
			'required' => true,
			'default' => '0',
			'reportable'=>false,
			'massupdate' => '',
			'comment' => 'Personal account flag'
		),
		'groupfolder_id' => array (
			'name' => 'groupfolder_id',
			'vname' => 'LBL_GROUPFOLDER_ID',
			'type' => 'id',
			'required' => false,
			'reportable'=>false,
			'comment' => 'Unique identifier'
		),
        'emails' => array(
            'name' => 'emails',
            'type' => 'link',
            'relationship' => 'inbound_email_emails',
            'source' => 'non-db',
            'vname' => 'LBL_EMAILS',
        ),
	), /* end fields() */
	'indices' => array (
		array(
			'name' =>'inbound_emailpk',
			'type' =>'primary',
			'fields' => array(
				'id'
			)
		),
        array(
            'name' => 'idx_deleted',
            'type' => 'index',
            'fields' => array('deleted'),
        ),
	), /* end indices */
	'relationships' => array (
		'inbound_email_created_by' => array(
			'lhs_module'=> 'Users',
			'lhs_table' => 'users',
			'lhs_key' => 'id',
			'rhs_module'=> 'InboundEmail',
			'rhs_table'=> 'inbound_email',
			'rhs_key' => 'created_by',
			'relationship_type' => 'one-to-one'
		),
		'inbound_email_modified_user_id' => array (
			'lhs_module' => 'Users',
			'lhs_table' => 'users',
			'lhs_key' => 'id',
			'rhs_module'=> 'InboundEmail',
			'rhs_table'=> 'inbound_email',
			'rhs_key' => 'modified_user_id',
			'relationship_type' => 'one-to-one'
		),
        'inbound_email_emails' => array(
            'lhs_module' => 'InboundEmail',
            'lhs_table' => 'inbound_email',
            'lhs_key' => 'id',
            'rhs_module' => 'Emails',
            'rhs_table' => 'emails',
            'rhs_key' => 'mailbox_id',
            'relationship_type' => 'one-to-many',
        ),
	), /* end relationships */
    'acls' => ['SugarACLAdminOnly' => true],
);


VardefManager::createVardef('InboundEmail','InboundEmail', array(
'team_security',
));

?>
