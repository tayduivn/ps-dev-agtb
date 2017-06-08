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

require_once 'modules/Emails/EmailRecipientRelationship.php';

/**
 * Class EmailSenderRelationship
 *
 * Represents the relationship between Emails and modules that can be senders of an email. In particular, each email can
 * have one sender. The EmailParticipants module is used to enable a three-way relationship, with EmailAddresses being
 * the third prong. Emails should be on the left side of the relationship, while EmailParticipants should be on the
 * right side.
 */
class EmailSenderRelationship extends EmailRecipientRelationship
{
    public $type = 'one-to-one';

    /**
     * If an email already has a sender, then the sender is removed before the new one is added.
     *
     * When an email address is being linked and the existing row has the same email address:
     *
     * - The existing row is replaced by the new row if the new row represents a record.
     * - The new row is not added if it represents an email address and the existing row represents a record.
     *
     * {@inheritdoc}
     */
    public function add($lhs, $rhs, $additionalFields = array())
    {
        if ($lhs->state === Email::STATE_DRAFT) {
            if (empty($rhs->parent_type) && empty($rhs->parent_id)) {
                // Default the parent to the current user.
                $rhs->parent_type = $GLOBALS['current_user']->getModuleName();
                $rhs->parent_id = $GLOBALS['current_user']->id;
            }

            $isRhsParentCurrentUser = $rhs->parent_type === $GLOBALS['current_user']->getModuleName() &&
                $rhs->parent_id === $GLOBALS['current_user']->id;

            if (!$isRhsParentCurrentUser) {
                throw new SugarApiExceptionNotAuthorized('Only the current user can be added as the sender of a draft');
            }
        }

        $this->setEmailAddress($lhs, $rhs);
        $currentRows = $lhs->{$this->lhsLink}->getBeans();

        // There can only be one. But just in case something weird happens, we'll iterate through the rows.
        foreach ($currentRows as $currentRow) {
            if ($currentRow->id === $rhs->id) {
                // They are the same. Let it be added again.
                continue;
            }

            // Equality is checked loosely because null and empty strings need to be considered the same.
            $doParentsMatch = $currentRow->parent_type == $rhs->parent_type &&
                $currentRow->parent_id == $rhs->parent_id;
            $doEmailAddressesMatch = $currentRow->email_address_id == $rhs->email_address_id;

            if ($doParentsMatch && empty($rhs->email_address_id) && !empty($currentRow->email_address_id)) {
                // The parents match, so keep the email address that the current row has.
                return false;
            }

            if (!$doEmailAddressesMatch) {
                // The email_address_id's do not collide. Consider it a new sender.
                if (!$this->remove($lhs, $currentRow)) {
                    return false;
                }

                continue;
            }

            if (empty($rhs->parent_type) || empty($rhs->parent_id)) {
                // We already have this email address stored, so keep the current row in case it includes a parent bean.
                return false;
            }

            if ($doParentsMatch) {
                // There is no reason to change the sender since the parents and email addresses are the same.
                return false;
            }

            // The sender has a different parent. Replace the sender.
            if (!$this->remove($lhs, $currentRow)) {
                return false;
            }
        }

        return parent::add($lhs, $rhs, $additionalFields);
    }

    /**
     * {@inheritdoc}
     */
    public function getType($side)
    {
        return REL_TYPE_ONE;
    }
}
