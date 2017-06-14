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

class EmailsApiHelper extends SugarBeanApiHelper
{
    /**
     * {@inheritdoc}
     *
     * `outbound_email_id` is not included in the response if the user does not have access to the outbound email
     * account.
     */
    public function formatForApi(\SugarBean $bean, array $fieldList = array(), array $options = array())
    {
        $data = parent::formatForApi($bean, $fieldList, $options);
        $oe = null;

        if (!empty($data['outbound_email_id'])) {
            $oe = BeanFactory::retrieveBean('OutboundEmail', $data['outbound_email_id']);
        }

        if (empty($oe)) {
             unset($data['outbound_email_id']);
        }

        return $data;
    }

    /**
     * {@link Email::outbound_email_id} is only populated if the email is a draft. An email is a draft at this stage if
     * it is either being saved as a draft or will be sent as a part of the current request. This is done to avoid
     * persisting data on {@link Email::outbound_email_id} when SugarCRM was not used to send the email or mutating the
     * sender-of-record of an archived email.
     *
     * {@inheritdoc}
     *
     * @throws SugarApiExceptionNotFound Thrown if `outbound_email_id` is submitted and the record cannot be found or is
     * inaccessible to the user.
     */
    public function populateFromApi(SugarBean $bean, array $submittedData, array $options = array())
    {
        // Set the state before anything else because field-level ACL checks are dependent on an email's state and the
        // ACL checks are performed before the fields are populated.
        if (isset($submittedData['state'])) {
            $bean->state = $submittedData['state'];
        }

        if ($bean->state === Email::STATE_DRAFT) {
            if (!empty($submittedData['assigned_user_id']) &&
                $submittedData['assigned_user_id'] !== $GLOBALS['current_user']->id
            ) {
                throw new SugarApiExceptionInvalidParameter(
                    'assigned_user_id must be empty or specify the ID of the current user'
                );
            }

            unset($submittedData['assigned_user_id']);
        }

        $hasOutboundEmailId = isset($submittedData['outbound_email_id']) && !empty($submittedData['outbound_email_id']);

        if ($hasOutboundEmailId && $bean->state === Email::STATE_DRAFT) {
            $oe = BeanFactory::retrieveBean('OutboundEmail', $submittedData['outbound_email_id']);

            if (!$oe) {
                throw new SugarApiExceptionNotFound(
                    sprintf(
                        'Could not find record: %s in module: OutboundEmail for the submitted outbound_email_id',
                        $submittedData['outbound_email_id']
                    )
                );
            }
        }

        return parent::populateFromApi($bean, $submittedData, $options);
    }
}
