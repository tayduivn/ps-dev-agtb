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

$dictionary['email_addresses'] = array(
    'table' => 'email_addresses',
    'fields' => array(
        'id' => array(
            'name' => 'id',
            'type' => 'id',
            'vname' => 'LBL_EMAIL_ADDRESS_ID',
            'required' => true,
        ),
        'email_address' => array(
            'name' => 'email_address',
            'type' => 'varchar',
            'vname' => 'LBL_EMAIL_ADDRESS',
            'length' => 100,
            'required' => true,
        ),
        'email_address_caps' => array(
            'name' => 'email_address_caps',
            'type' => 'varchar',
            'vname' => 'LBL_EMAIL_ADDRESS_CAPS',
            'length' => 100,
            'required' => true,
            'reportable' => false,
        ),
        'invalid_email' => array(
            'name' => 'invalid_email',
            'type' => 'bool',
            'default' => 0,
            'vname' => 'LBL_INVALID_EMAIL',
        ),
        'opt_out' => array(
            'name' => 'opt_out',
            'type' => 'bool',
            'default' => 0,
            'vname' => 'LBL_OPT_OUT',
        ),
        'date_created' => array(
            'name' => 'date_created',
            'type' => 'datetime',
            'vname' => 'LBL_DATE_CREATE',
        ),
        'date_modified' => array(
            'name' => 'date_modified',
            'type' => 'datetime',
            'vname' => 'LBL_DATE_MODIFIED',
        ),
        'deleted' => array(
            'name' => 'deleted',
            'type' => 'bool',
            'default' => 0,
            'vname' => 'LBL_DELETED',
        ),
    ),
    'indices' => array(
        array(
            'name' => 'email_addressespk',
            'type' => 'primary',
            'fields' => array(
                'id',
            ),
        ),
        array(
            'name' => 'idx_ea_caps_opt_out_invalid',
            'type' => 'index',
            'fields' => array(
                'email_address_caps',
                'opt_out',
                'invalid_email',
            ),
        ),
        array(
            'name' => 'idx_ea_opt_out_invalid',
            'type' => 'index',
            'fields' => array(
                'email_address',
                'opt_out',
                'invalid_email',
            ),
        ),
    ),
);

$dictionary['EmailAddress'] = array(
    'table' => 'email_addresses',
    'fields' => array(
        'id' => array(
            'name' => 'id',
            'type' => 'id',
            'vname' => 'LBL_EMAIL_ADDRESS_ID',
            'required' => true,
        ),
        'email_address' => array(
            'name' => 'email_address',
            'type' => 'varchar',
            'vname' => 'LBL_EMAIL_ADDRESS',
            'length' => 100,
            'required' => true,
        ),
        'email_address_caps' => array(
            'name' => 'email_address_caps',
            'type' => 'varchar',
            'vname' => 'LBL_EMAIL_ADDRESS_CAPS',
            'length' => 100,
            'required' => true,
            'reportable' => false,
        ),
        'invalid_email' => array(
            'name' => 'invalid_email',
            'type' => 'bool',
            'default' => 0,
            'vname' => 'LBL_INVALID_EMAIL',
        ),
        'opt_out' => array(
            'name' => 'opt_out',
            'type' => 'bool',
            'default' => 0,
            'vname' => 'LBL_OPT_OUT',
        ),
        'date_created' => array(
            'name' => 'date_created',
            'type' => 'datetime',
            'vname' => 'LBL_DATE_CREATE',
        ),
        'date_modified' => array(
            'name' => 'date_modified',
            'type' => 'datetime',
            'vname' => 'LBL_DATE_MODIFIED',
        ),
        'deleted' => array(
            'name' => 'deleted',
            'type' => 'bool',
            'default' => 0,
            'vname' => 'LBL_DELETED',
        ),
        'emails_from' => array(
            'name' => 'emails_from',
            'relationship' => 'emails_email_addresses_from',
            'source' => 'non-db',
            'type' => 'link',
            'vname' => 'LBL_EMAILS_FROM',
        ),
        'emails_to' => array(
            'name' => 'emails_to',
            'relationship' => 'emails_email_addresses_to',
            'source' => 'non-db',
            'type' => 'link',
            'vname' => 'LBL_EMAILS_RECEIVED',
        ),
        'emails_cc' => array(
            'name' => 'emails_cc',
            'relationship' => 'emails_email_addresses_cc',
            'source' => 'non-db',
            'type' => 'link',
            'vname' => 'LBL_EMAILS_RECEIVED',
        ),
        'emails_bcc' => array(
            'name' => 'emails_bcc',
            'relationship' => 'emails_email_addresses_bcc',
            'source' => 'non-db',
            'type' => 'link',
            'vname' => 'LBL_EMAILS_RECEIVED',
        ),
        'email_addresses_used' => array(
            'name' => 'email_addresses_used',
            'relationship' => 'email_addresses_email_addresses_used',
            'source' => 'non-db',
            'type' => 'link',
            'vname' => 'LBL_EMAIL_ADDRESSES_USED',
        ),
        'email_address_used' => array(
            'name' => 'email_address_used',
            'link' => 'email_addresses_used',
            'rname' => 'email_address',
            'type' => 'varchar',
            'source' => 'non-db',
            'vname' => 'LBL_EMAIL_ADDRESS',
            'studio' => 'false',
            'massupdate' => false,
            'importable' => 'false',
        ),
    ),
    'indices' => array(
        array(
            'name' => 'email_addressespk',
            'type' => 'primary',
            'fields' => array(
                'id',
            ),
        ),
        array(
            'name' => 'idx_ea_caps_opt_out_invalid',
            'type' => 'index',
            'fields' => array(
                'email_address_caps',
                'opt_out',
                'invalid_email',
            ),
        ),
        array(
            'name' => 'idx_ea_opt_out_invalid',
            'type' => 'index',
            'fields' => array(
                'email_address',
                'opt_out',
                'invalid_email',
            ),
        ),
    ),
);

$dictionary['emails_email_addr_rel'] = array(
    'table' => 'emails_email_addr_rel',
    'comment' => 'Normalization of address fields FROM, TO, CC, and BCC',
    'fields' => array(
        'id' => array(
            'name' => 'id',
            'type' => 'id',
            'required' => true,
            'comment' => 'GUID',
        ),
        'email_id' => array(
            'name' => 'email_id',
            'type' => 'id',
            'required' => true,
            'comment' => 'Foreign key to emails table NOT unique',
        ),
        'address_type' => array(
            'name' => 'address_type',
            'type' => 'varchar',
            'len' => 4,
            'required' => true,
            'comment' => 'The role (from, to, cc, bcc) that the entry plays in the email',
        ),
        'email_address_id' => array(
            'name' => 'email_address_id',
            'type' => 'id',
            // Only required at send-time.
            'required' => false,
            'comment' => 'Foreign key to emails table NOT unique',
        ),
        'bean_type' => array(
            'name' => 'bean_type',
            'comment' => 'The module against which the bean can be resolved',
            'type' => 'varchar',
            'len' => 255,
            'required' => true,
        ),
        'bean_id' => array(
            'name' => 'bean_id',
            'comment' => "The bean's ID",
            'type' => 'id',
            'required' => true,
        ),
        'deleted' => array(
            'name' => 'deleted',
            'type' => 'bool',
            'default' => 0,
        ),
        'date_modified' => array(
            'name' => 'date_modified',
            'type' => 'datetime',
            'comment' => 'Last modified date for the entry',
        ),
    ),
    'indices' => array(
        array(
            'name' => 'emails_email_addr_relpk',
            'type' => 'primary',
            'fields' => array(
                'id',
            ),
        ),
        array(
            'name' => 'idx_eearl_email_id',
            'type' => 'index',
            'fields' => array(
                'email_id',
                'address_type',
            ),
        ),
        array(
            'name' => 'idx_eearl_address_id',
            'type' => 'index',
            'fields' => array(
                'email_address_id',
            ),
        ),
        array(
            'name' => 'idx_eearl_unique',
            'type' => 'unique',
            'fields' => array(
                'email_id',
                'address_type',
                'bean_type',
                'bean_id',
                'deleted',
            ),
        ),
        array(
            'name' => 'idx_eearl_email_address_deleted',
            'type' => 'index',
            'fields' => array(
                'email_address_id',
                'deleted',
            ),
        ),
        array(
            'name' => 'idx_eearl_email_address_role',
            'type' => 'index',
            'fields' => array(
                'email_address_id',
                'address_type',
                'deleted',
            ),
        ),
        array(
            'name' => 'idx_eearl_bean',
            'type' => 'index',
            'fields' => array(
                'bean_type',
                'bean_id',
                'deleted',
            ),
        ),
        array(
            'name' => 'idx_eearl_bean_role',
            'type' => 'index',
            'fields' => array(
                'bean_type',
                'bean_id',
                'address_type',
                'deleted',
            ),
        ),
    ),
    'relationships' => array(
        'email_addresses_email_addresses_used' => array(
            'lhs_module' => 'EmailAddresses',
            'lhs_table' => 'email_addresses',
            'lhs_key' => 'id',
            'rhs_module' => 'EmailAddresses',
            'rhs_table' => 'email_addresses',
            'rhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'join_table' => 'emails_email_addr_rel',
            'join_key_lhs' => 'bean_id',
            'join_key_rhs' => 'email_address_id',
            'relationship_role_column' => 'bean_type',
            'relationship_role_column_value' => 'EmailAddresses',
        ),
        'emails_email_addresses_from' => array(
            'lhs_module' => 'Emails',
            'lhs_table' => 'emails',
            'lhs_key' => 'id',
            'rhs_module' => 'EmailAddresses',
            'rhs_table' => 'email_addresses',
            'rhs_key' => 'id',
            'relationship_type' => 'one-to-many',
            'relationship_class' => 'EmailSenderRelationship',
            'relationship_file' => 'modules/Emails/EmailSenderRelationship.php',
            'join_table' => 'emails_email_addr_rel',
            'join_key_lhs' => 'email_id',
            'join_key_rhs' => 'bean_id',
            'relationship_role_columns' => array(
                'address_type' => 'from',
            ),
        ),
        'emails_email_addresses_to' => array(
            'lhs_module' => 'Emails',
            'lhs_table' => 'emails',
            'lhs_key' => 'id',
            'rhs_module' => 'EmailAddresses',
            'rhs_table' => 'email_addresses',
            'rhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'relationship_class' => 'EmailRecipientRelationship',
            'relationship_file' => 'modules/Emails/EmailRecipientRelationship.php',
            'join_table' => 'emails_email_addr_rel',
            'join_key_lhs' => 'email_id',
            'join_key_rhs' => 'bean_id',
            'relationship_role_columns' => array(
                'bean_type' => 'EmailAddresses',
                'address_type' => 'to',
            ),
        ),
        'emails_email_addresses_cc' => array(
            'lhs_module' => 'Emails',
            'lhs_table' => 'emails',
            'lhs_key' => 'id',
            'rhs_module' => 'EmailAddresses',
            'rhs_table' => 'email_addresses',
            'rhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'relationship_class' => 'EmailRecipientRelationship',
            'relationship_file' => 'modules/Emails/EmailRecipientRelationship.php',
            'join_table' => 'emails_email_addr_rel',
            'join_key_lhs' => 'email_id',
            'join_key_rhs' => 'bean_id',
            'relationship_role_columns' => array(
                'bean_type' => 'EmailAddresses',
                'address_type' => 'cc',
            ),
        ),
        'emails_email_addresses_bcc' => array(
            'lhs_module' => 'Emails',
            'lhs_table' => 'emails',
            'lhs_key' => 'id',
            'rhs_module' => 'EmailAddresses',
            'rhs_table' => 'email_addresses',
            'rhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'relationship_class' => 'EmailRecipientRelationship',
            'relationship_file' => 'modules/Emails/EmailRecipientRelationship.php',
            'join_table' => 'emails_email_addr_rel',
            'join_key_lhs' => 'email_id',
            'join_key_rhs' => 'bean_id',
            'relationship_role_columns' => array(
                'bean_type' => 'EmailAddresses',
                'address_type' => 'bcc',
            ),
        ),
    ),
);

$dictionary['email_addr_bean_rel'] = array(
    'table' => 'email_addr_bean_rel',
    'fields' => array(
        'id' => array(
            'name' => 'id',
            'type' => 'id',
            'required' => true,
        ),
        'email_address_id' => array(
            'name' => 'email_address_id',
            'type' => 'id',
            'required' => true,
        ),
        'bean_id' => array(
            'name' => 'bean_id',
            'type' => 'id',
            'required' => true,
        ),
        'bean_module' => array(
            'name' => 'bean_module',
            'type' => 'varchar',
            'len' => 100,
            'required' => true,
        ),
        'primary_address' => array(
            'name' => 'primary_address',
            'type' => 'bool',
            'default' => '0',
        ),
        'reply_to_address' => array(
            'name' => 'reply_to_address',
            'type' => 'bool',
            'default' => '0',
        ),
        'date_created' => array(
            'name' => 'date_created',
            'type' => 'datetime',
        ),
        'date_modified' => array(
            'name' => 'date_modified',
            'type' => 'datetime',
        ),
        'deleted' => array(
            'name' => 'deleted',
            'type' => 'bool',
            'default' => 0,
        ),
    ),
    'indices' => array(
        array(
            'name' => 'email_addresses_relpk',
            'type' => 'primary',
            'fields' => array(
                'id',
            ),
        ),
        array(
            'name' => 'idx_email_address_id',
            'type' => 'index',
            'fields' => array(
                'email_address_id',
            ),
        ),
        array(
            'name' => 'idx_bean_id',
            'type' => 'index',
            'fields' => array(
                'bean_id',
                'bean_module',
            ),
        ),
    ),
    'relationships' => array(
    ),
);
