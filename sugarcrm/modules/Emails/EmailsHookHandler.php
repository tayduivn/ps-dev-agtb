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

class EmailsHookHandler
{
    /**
     * Anytime an attachment is added to an email, the attachment must be updated to guarantee that its teams fields
     * match those of the email.
     *
     * @param SugarBean $bean The email.
     * @param string $event
     * @param array $args
     */
    public function updateTeamsForAttachment(SugarBean $bean, $event, array $args)
    {
        if ($event === 'after_relationship_add' && $args['link'] === 'attachments') {
            $attachment = BeanFactory::retrieveBean(
                $args['related_module'],
                $args['related_id'],
                array('disable_row_level_security' => true)
            );

            if ($attachment) {
                $bean->updateTeamsForAttachment($attachment);
            }
        }
    }
}
