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

require_once 'data/SugarBeanApiHelper.php';

class EmailsApiHelper extends SugarBeanApiHelper
{
    /**
     * {@link Email::outbound_email_id} is only populated if the email is a draft. An email is a draft at this stage if
     * it is either being saved as a draft or will be sent as a part of the current request. This is done to avoid
     * persisting data on {@link Email::outbound_email_id} when SugarCRM was not used to send the email or mutating the
     * sender-of-record of an archived email.
     *
     * {@inheritdoc}
     */
    public function populateFromApi(SugarBean $bean, array $submittedData, array $options = array())
    {
        $isDraft = isset($submittedData['state']) ?
            $submittedData['state'] === Email::EMAIL_STATE_DRAFT :
            $bean->state === Email::EMAIL_STATE_DRAFT;

        if (isset($submittedData['outbound_email_id']) && !$isDraft) {
            unset($submittedData['outbound_email_id']);
        }

        return parent::populateFromApi($bean, $submittedData, $options);
    }
}
