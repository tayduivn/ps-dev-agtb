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

$dictionary['emails_participants'] = array(
    'table' => 'emails_participants',
    'fields' => array(
        'id' => array(
            'name' => 'id',
            'comment' => 'Unique identifier',
            'type' => 'id',
            'required' => true,
        ),
        'email_id' => array(
            'name' => 'email_id',
            'comment' => 'A foreign key to the emails table',
            'type' => 'id',
            'required' => true,
        ),
        'role' => array(
            'name' => 'role',
            'comment' => 'The role (FROM, TO, CC, BCC) that this participant plays in the email',
            'type' => 'enum',
            'options' => 'dom_emails_participants_roles',
            'required' => true,
        ),
        'email_address_id' => array(
            'name' => 'email_address_id',
            'comment' => 'A foreign key to the email_addresses table',
            'type' => 'id',
            // Only required at send-time.
            'required' => false,
        ),
        'participant_module' => array(
            'name' => 'participant_module',
            'comment' => 'The module where the participant can be resolved',
            'type' => 'enum',
            'options' => 'dom_emails_participants_participant_modules',
            'required' => true,
        ),
        'participant_id' => array(
            'name' => 'participant_id',
            'comment' => "The participant's ID",
            'type' => 'id',
            'required' => true,
        ),
        // Need deleted for M2MRelationship to work when fetching and manipulating data. However, rows are physically
        // deleted, so this column is not useful.
        'deleted' => array(
            'name' => 'deleted',
            'type' => 'bool',
            'default' => 0,
        ),
    ),
    'indices' => array(
        array(
            'name' => 'idx_emails_participants_pk',
            'type' => 'primary',
            'fields' => array(
                'id',
            ),
        ),
        array(
            'name' => 'idx_emails_participants_unique',
            'type' => 'unique',
            'fields' => array(
                'email_id',
                'role',
                'participant_module',
                'participant_id',
            ),
        ),
        array(
            'name' => 'idx_email_address',
            'type' => 'index',
            'fields' => array(
                'email_address_id',
                'deleted',
            ),
        ),
        array(
            'name' => 'idx_email_address_role',
            'type' => 'index',
            'fields' => array(
                'email_address_id',
                'role',
                'deleted',
            ),
        ),
        array(
            'name' => 'idx_participant',
            'type' => 'index',
            'fields' => array(
                'participant_module',
                'participant_id',
                'deleted',
            ),
        ),
        array(
            'name' => 'idx_participant_role',
            'type' => 'index',
            'fields' => array(
                'participant_module',
                'participant_id',
                'role',
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
            'join_table' => 'emails_participants',
            'join_key_lhs' => 'participant_id',
            'join_key_rhs' => 'email_address_id',
            'relationship_role_column' => 'participant_module',
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
            'join_table' => 'emails_participants',
            'join_key_lhs' => 'email_id',
            'join_key_rhs' => 'participant_id',
            'relationship_role_columns' => array(
                'role' => 'from',
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
            'join_table' => 'emails_participants',
            'join_key_lhs' => 'email_id',
            'join_key_rhs' => 'participant_id',
            'relationship_role_columns' => array(
                'participant_module' => 'EmailAddresses',
                'role' => 'to',
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
            'join_table' => 'emails_participants',
            'join_key_lhs' => 'email_id',
            'join_key_rhs' => 'participant_id',
            'relationship_role_columns' => array(
                'participant_module' => 'EmailAddresses',
                'role' => 'cc',
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
            'join_table' => 'emails_participants',
            'join_key_lhs' => 'email_id',
            'join_key_rhs' => 'participant_id',
            'relationship_role_columns' => array(
                'participant_module' => 'EmailAddresses',
                'role' => 'bcc',
            ),
        ),
    ),
);
