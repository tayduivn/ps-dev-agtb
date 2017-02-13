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

class OutboundEmailApiHelper extends SugarBeanApiHelper
{
    /**
     * {@inheritdoc}
     *
     * The user_id argument is unset and will be defaulted to the current user when the record is saved. This prevents a
     * user from attempting to steal ownership of a record.
     *
     * The name and type arguments are unset for system and system-override accounts as they cannot be changed. These
     * types of accounts cannot be created through the REST API; only user accounts can. The type field will always
     * become "user" when the record is created. The type field will remain unchanged when updating an existing record
     * of any type.
     */
    public function populateFromApi(\SugarBean $bean, array $submittedData, array $options = [])
    {
        // Avoid errors for attempting to change immutable fields by removing those fields.
        switch ($bean->type) {
            case OutboundEmail::TYPE_SYSTEM_OVERRIDE:
                // Only the username and password can be changed.
                unset($submittedData['mail_sendtype']);
                unset($submittedData['mail_smtptype']);
                unset($submittedData['mail_smtpserver']);
                unset($submittedData['mail_smtpport']);
                unset($submittedData['mail_smtpauth_req']);
                unset($submittedData['mail_smtpssl']);
                // Fall through to unset `name` and `type`.
            case OutboundEmail::TYPE_SYSTEM:
                unset($submittedData['name']);
                unset($submittedData['type']);
                // Fall through to unset `user_id`.
            default:
                unset($submittedData['user_id']);
                break;
        }

        return parent::populateFromApi($bean, $submittedData, $options);
    }

    /**
     * {@inheritdoc}
     *
     * The password field will return true if the field was requested and a password exists.
     */
    public function formatForApi(SugarBean $bean, array $fieldList = array(), array $options = array())
    {
        $record = parent::formatForApi($bean, $fieldList, $options);

        if (isset($record['mail_smtppass'])) {
            $record['mail_smtppass'] = empty($record['mail_smtppass']) ? null : true;
        }

        return $record;
    }
}
