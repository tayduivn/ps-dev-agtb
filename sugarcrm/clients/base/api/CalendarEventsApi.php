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

class CalendarEventsApi extends ModuleApi
{
    /**
     * {@inheritdoc}
     */
    public function registerApiRest()
    {
        // Return any API definition that exists for this class
        return array();
    }

    /**
     * Tailor the specification (e.g. path) for the specified module and merge in the API specification passed in
     * @param string module
     * @param array child Api
     * @return array
     */
    protected function getRestApi($module, $childApi = array())
    {
        $calendarEventsApi = array(
            'create' => array(
                'reqType' => 'POST',
                'path' => array($module),
                'pathVars' => array('module'),
                'method' => 'createCalendarEvent',
                'shortHelp' => 'This method creates a single event record or a series of event records of the specified type',
                'longHelp' => 'include/api/help/calendar_events_record_create_help.html',
            ),
            'update' => array(
                'reqType' => 'PUT',
                'path' => array($module, '?'),
                'pathVars' => array('module', 'record'),
                'method' => 'updateCalendarEvent',
                'shortHelp' => 'This method updates a single event record or a series of event records of the specified type',
                'longHelp' => 'include/api/help/calendar_events_record_update_help.html',
            ),
            'delete' => array(
                'reqType' => 'DELETE',
                'path' => array($module, '?'),
                'pathVars' => array('module', 'record'),
                'method' => 'deleteCalendarEvent',
                'shortHelp' => 'This method deletes a single event record or a series of event records of the specified type',
                'longHelp' => 'include/api/help/calendar_events_record_delete_help.html',
            ),
            'send_invite_emails' => array(
                'reqType' => 'PUT',
                'path' => array($module, '?', 'send_invites'),
                'pathVars' => array('module', 'record', ''),
                'method' => 'sendInviteEmails',
                'shortHelp' => 'This method sends invite emails to all event participants',
                'longHelp' => 'include/api/help/calendar_events_send_invite_emails_put_help.html',
            ),
        );

        return array_merge($calendarEventsApi, $childApi);
    }

    /**
     * Create either a single event record or a set of recurring events if record is a recurring event
     * @param $api
     * @param $args
     * @return array
     */
    public function createCalendarEvent($api, $args)
    {
        $createResult = $this->createRecord($api, $args);

        if (!empty($createResult['id'])) {
            $loadArgs = array(
                'module' => $args['module'],
                'record' => $createResult['id'],
            );
            $bean = $this->loadBean($api, $loadArgs, 'view', array('use_cache' => false));
            if ($GLOBALS['calendarEvents']->isEventRecurring($bean)) {
                $this->generateRecurringCalendarEvents($bean);
            }
        }
        return $createResult;
    }

    /**
     * Updates either a single event record or a set of recurring events based on all_recurrences flag
     * @param $api
     * @param $args
     * @return array
     */
    public function updateCalendarEvent($api, $args)
    {
        $updateResult = array();
        if (isset($args['all_recurrences']) && $args['all_recurrences'] === 'true') {
            $api->action = 'view';
            $bean = $this->loadBean($api, $args, 'view');
            if ($GLOBALS['calendarEvents']->isEventRecurring($bean)) {
                $updateResult = $this->updateRecurringCalendarEvent($bean, $api, $args);
            } else {
                $updateResult = $this->updateRecord($api, $args);
            }
        } else {
            $updateResult = $this->updateRecord($api, $args);
        }
        return $updateResult;
    }

    /**
     * Deletes either a single event record or a set of recurring events based on all_recurrences flag
     * @param $api
     * @param $args
     * @return array
     */
    public function deleteCalendarEvent($api, $args)
    {
        if (isset($args['all_recurrences']) && $args['all_recurrences'] === 'true') {
            $this->deleteRecordAndRecurrences($api, $args);
        } else {
            $this->deleteRecord($api, $args);
        }
    }

    /**
     * Creates child events in recurring series
     * @param SugarBean $bean
     */
    public function generateRecurringCalendarEvents(SugarBean $bean)
    {
        try {
            $GLOBALS['calendarEvents']->saveRecurringEvents($bean, true);
        } catch (SugarApiException $e) {
            throw($e);
        } catch (Exception $e) {
            throw new SugarApiException($e->getMessage());
        }
    }

    /**
     * Re-generates child events in recurring series
     * @param SugarBean $bean
     * @param $api
     * @param $args
     * @return array
     */
    public function updateRecurringCalendarEvent(SugarBean $bean, $api, $args)
    {
        unset($args['id']);
        unset($args['repeat_parent_id']);

        if (!empty($bean->repeat_parent_id) && ($bean->repeat_parent_id !== $bean->id)) {
            $args['record'] = $bean->repeat_parent_id;
            $bean = $this->loadBean($api, $args, 'view');
            $bean->repeat_parent_id = null;
        }

        $api->action = 'save';
        $this->updateBean($bean, $api, $args);

        try {
            $GLOBALS['calendarEvents']->saveRecurringEvents($bean, true);
        } catch (SugarApiException $e) {
            throw($e);
        } catch (Exception $e) {
            throw new SugarApiException($e->getMessage());
        }

        return $this->getLoadedAndFormattedBean($api, $args, $bean);
    }

    /**
     * Deletes the parent and associated child events in a series.
     * @param $api
     * @param $args
     * @return array
     */
    public function deleteRecordAndRecurrences($api, $args)
    {
        $bean = $this->loadBean($api, $args, 'delete');

        if (!empty($bean->repeat_parent_id)) {
            $parentArgs = array_merge(
                $args,
                array('record' => $bean->repeat_parent_id)
            );

            $bean = $this->loadBean($api, $parentArgs, 'delete');
        }

        CalendarUtils::markRepeatDeleted($bean);
        $bean->mark_deleted($bean->id);

        return array('id' => $bean->id);
    }

    /**
     * Sends invite emails to all event participants.
     *
     * @param $api
     * @param $args
     * @return array
     */
    public function sendInviteEmails($api, $args)
    {
        $bean = $this->loadBean($api, $args, 'edit');
        // the dates need to be converted to their DB representation
        $bean->date_start = $GLOBALS['timedate']->to_db($bean->date_start);
        $bean->date_end = $GLOBALS['timedate']->to_db($bean->date_end);

        $admin = Administration::getSettings();

        foreach ($bean->get_notification_recipients() as $participant) {
            $bean->send_assignment_notifications($participant, $admin);
        }

        return $this->getLoadedAndFormattedBean($api, $args);
    }
}
