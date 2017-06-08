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

require_once 'data/SugarACLStrategy.php';

class SugarACLDraftEmails extends SugarACLStrategy
{
    /**
     * Don't allow write-access to some fields if the email is a draft.
     *
     * {@inheritdoc}
     */
    public function checkAccess($module, $view, $context)
    {
        if (!$this->isWriteOperation($view, $context)) {
            return true;
        }

        if ($view !== 'field') {
            return true;
        }

        if (!isset($context['bean'])) {
            return true;
        }

        $bean = $context['bean'];

        if ($bean->state !== Email::STATE_DRAFT) {
            return true;
        }

        $immutableFields = [
            'date_sent',
            'assigned_user_id',
            // The sender is always the current user for drafts. No one can submit a different sender. Use
            // outbound_email_id to choose the SMTP account used for sending the email.
            'from_link',
        ];

        if (in_array($context['field'], $immutableFields)) {
            return false;
        }

        return true;
    }
}
