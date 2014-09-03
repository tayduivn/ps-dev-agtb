<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once('data/SugarBeanApiHelper.php');

class CalendarEventsApiHelper extends SugarBeanApiHelper
{
    /**
     * {@inheritdoc}
     *
     * The bean must have values for `date_start`, `duration_hours`, and `duration_minutes` after it has been populated.
     * These values can either already exist on the bean or have been populated from the submitted data.
     *
     * Adds the calendar event specific saves for leads, contacts, and users.
     *
     * The vCal cache is not updated for the current user as it is handled in the endpoints to guarantee that it happens
     * after all recurrences of an event are saved.
     *
     * @param SugarBean $bean
     * @param array $submittedData
     * @param array $options
     * @return array
     * @throws SugarApiExceptionMissingParameter
     */
    public function populateFromApi(SugarBean $bean, array $submittedData, array $options = array())
    {
        /**
         * The duration_hours and duration_minutes fields must be positive integers; either actual integers or strings
         * that are integers.
         *
         * @param mixed $time
         * @return bool
         */
        $isPositiveInteger = function ($time) {
            return preg_match('/^\d+$/', (string)$time) === 1;
        };

        unset($submittedData['repeat_parent_id']); // never allow this to be updated via the api

        $data = parent::populateFromApi($bean, $submittedData, $options);

        if (empty($bean->date_start)) {
            throw new SugarApiExceptionMissingParameter('Missing parameter: date_start');
        }

        if (!isset($bean->duration_hours) || strlen((string)$bean->duration_hours) === 0) {
            throw new SugarApiExceptionMissingParameter('Missing parameter: duration_hours');
        }

        if (!isset($bean->duration_minutes) || strlen((string)$bean->duration_minutes) === 0) {
            throw new SugarApiExceptionMissingParameter('Missing parameter: duration_minutes');
        }

        if (!$isPositiveInteger($bean->duration_hours)) {
            throw new SugarApiExceptionInvalidParameter('Invalid parameter: duration_hours');
        }

        if (!$isPositiveInteger($bean->duration_minutes)) {
            throw new SugarApiExceptionInvalidParameter('Invalid parameter: duration_minutes');
        }

        $bean->update_vcal = false;

        // add existing invitees to the lists so they don't get removed
        $bean->users_arr = $this->getUserInvitees($bean);
        $bean->leads_arr = $this->getInvitees($bean, 'leads');
        $bean->contacts_arr = $this->getInvitees($bean, 'contacts');

        return $data;
    }

    /**
     * {@inheritdoc}
     *
     * Adds the contact's name if one is related.
     *
     * `send_invites` is an internal processing flag and should never be returned as a field.
     *
     * @param SugarBean $bean
     * @param array $fieldList
     * @param array $options
     * @return array
     */
    public function formatForApi(SugarBean $bean, array $fieldList = array(), array $options = array())
    {
        $data = parent::formatForApi($bean, $fieldList, $options);

        if (!empty($bean->contact_id)) {
            $contact = BeanFactory::getBean('Contacts', $bean->contact_id);
            if ($contact instanceof Contact) {
                $data['contact_name'] = $contact->full_name;
            }
        }

        unset($data['send_invites']);

        return $data;
    }

    /**
     * Returns an array of IDs for records associated via the specified link.
     *
     * @param SugarBean $bean
     * @param string $link The name of the link from which to load related records.
     * @return array
     */
    protected function getInvitees(SugarBean $bean, $link)
    {
        if ($bean->load_relationship($link)) {
            return $bean->$link->get();
        }

        return array();
    }

    /**
     * Returns an array of IDs for associated users.
     *
     * The assigned user is included if not already invited. The current user is included if the event is new and the
     * current user is not the assigned user.
     *
     * @param SugarBean $bean
     * @return array
     */
    protected function getUserInvitees(SugarBean $bean)
    {
        $userInvitees = $this->getInvitees($bean, 'users');
        $userInvitees[] = $bean->assigned_user_id;

        if ($bean->assigned_user_id != $GLOBALS['current_user']->id && (empty($bean->id) || $bean->new_with_id)) {
            $userInvitees[] = $GLOBALS['current_user']->id;
        }

        return array_unique($userInvitees);
    }
}
