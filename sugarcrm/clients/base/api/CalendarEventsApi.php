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
            'invitee_search' => array(
                'reqType' => 'GET',
                'path' => array($module, 'invitee_search'),
                'pathVars' => array('module', ''),
                'method' => 'inviteeSearch',
                'shortHelp' => 'This method searches for people to invite to an event',
                'longHelp' => 'include/api/help/calendar_events_invitee_search_get_help.html',
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
            } else {
                $GLOBALS['calendarEvents']->rebuildFreeBusyCache($GLOBALS['current_user']);
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
        $api->action = 'view';
        $bean = $this->loadBean($api, $args, 'view');

        if ($GLOBALS['calendarEvents']->isEventRecurring($bean)) {
            if (isset($args['all_recurrences']) && $args['all_recurrences'] === 'true') {
                $updateResult = $this->updateRecurringCalendarEvent($bean, $api, $args);
            } else {
                // when updating a single occurrence of a recurring meeting without the
                // `all_recurrences` flag, no updates to recurrence fields are allowed
                $updateResult = $this->updateRecord($api, $this->filterOutRecurrenceFields($args));
                $GLOBALS['calendarEvents']->rebuildFreeBusyCache($GLOBALS['current_user']);
            }
        } else {
            $updateResult = $this->updateRecord($api, $args);

            // check if it changed from a non-recurring to recurring & generate events if necessary
            $bean = $this->loadBean($api, $args, 'view', array('use_cache' => false));
            if ($GLOBALS['calendarEvents']->isEventRecurring($bean)) {
                $this->generateRecurringCalendarEvents($bean);
            } else {
                $GLOBALS['calendarEvents']->rebuildFreeBusyCache($GLOBALS['current_user']);
            }
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
        $GLOBALS['calendarEvents']->rebuildFreeBusyCache($GLOBALS['current_user']);
    }

    /**
     * Creates child events in recurring series
     * @param SugarBean $bean
     * @throws SugarApiException
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
     * @throws SugarApiException
     */
    public function updateRecurringCalendarEvent(SugarBean $bean, $api, $args)
    {
        if (!empty($bean->repeat_parent_id) && ($bean->repeat_parent_id !== $bean->id)) {
            throw new SugarApiException('ERR_CALENDAR_CANNOT_UPDATE_FROM_CHILD');
        }

        $api->action = 'save';
        $this->updateBean($bean, $api, $args);

        try {
            // if event is still recurring after update, save recurring events
            if ($GLOBALS['calendarEvents']->isEventRecurring($bean)) {
                $GLOBALS['calendarEvents']->saveRecurringEvents($bean, true);
            } else {
                // event is not recurring anymore, delete child instances
                $this->deleteRecurrences($bean);
            }
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

        $this->deleteRecurrences($bean);
        $bean->mark_deleted($bean->id);

        return array('id' => $bean->id);
    }

    /**
     * Deletes the child recurrences of the given bean
     *
     * @param $bean
     */
    public function deleteRecurrences($bean)
    {
        CalendarUtils::markRepeatDeleted($bean);
    }

    /**
     * Filter out recurrence fields from the API arguments
     *
     * @param array $args
     * @return array
     */
    protected function filterOutRecurrenceFields($args) {
        $recurrenceFieldBlacklist = array(
            'repeat_type',
            'repeat_interval',
            'repeat_dow',
            'repeat_until',
            'repeat_count',
        );
        foreach($recurrenceFieldBlacklist as $fieldName) {
            unset($args[$fieldName]);
        }
        return $args;
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

    /**
     * Run a search for possible invitees to invite to a calendar event.
     *
     * TODO: currently uses legacy code - replace with global search when
     *       it supports searching across linked fields like account_name
     * TODO: when replaced with global search - update api docs as more
     *       will be supported (like offset and search term highlighting)
     * TODO: allow for passing in event id that will be used to exclude
     *       invitees that are already invited to that event
     *
     * @param $api
     * @param $args
     * @return array
     * @throws SugarApiExceptionMissingParameter
     */
    public function inviteeSearch($api, $args)
    {
        $api->action = 'list';
        $this->requireArgs($args, array('module', 'q', 'module_list', 'search_fields', 'fields'));

        //make legacy search request
        $params = $this->buildSearchParams($args);
        $searchResults = $this->runInviteeQuery($params);

        return $this->transformInvitees($api, $args, $searchResults);
    }

    /**
     * Map from global search api arguments to search params expected by
     * legacy invitee search code
     *
     * @param $args
     * @return array
     */
    protected function buildSearchParams($args)
    {
        $modules = explode(',', $args['module_list']);
        $searchFields = explode(',', $args['search_fields']);
        $fieldList = explode(',', $args['fields']);
        $fieldList = array_merge($fieldList, $searchFields);

        $conditions = array();
        foreach ($searchFields as $searchField) {
            $conditions[] = array(
                'name' => $searchField,
                'op' => 'starts_with',
                'value' => $args['q'],
            );
        }

        return array(
            array(
                'modules' => $modules,
                'group' => 'or',
                'field_list' => $fieldList,
                'conditions' => $conditions,
            ),
        );
    }

    /**
     * Run the the legacy invitee query
     *
     * @param $params
     * @return array
     */
    protected function runInviteeQuery($params)
    {
        $requestId = '1'; //not really used
        $jsonServer = new LegacyJsonServer();
        return $jsonServer->query($requestId, $params, true);
    }

    /**
     * Map from legacy invitee search code's result format to a format
     * that is closer to what global search returns
     *
     * Pagination is not supported
     *
     * @param $api
     * @param $args
     * @param $searchResults
     * @return array
     */
    protected function transformInvitees($api, $args, $searchResults)
    {
        $resultList = $searchResults['result']['list'];
        $records = array();
        foreach ($resultList as $result) {
            $record = $this->formatBean($api, $args, $result['bean']);
            $highlighted = $this->getMatchedFields($args, $record, 1);
            $record['_search'] = array(
                'highlighted' => $highlighted,
            );
            $records[] = $record;
        }

        return array(
            'next_offset' => -1,
            'records' => $records,
        );
    }

    /**
     * Returns an array of fields that matched search query
     *
     * @param array $args Api arguments
     * @param array $record Search result formatted from bean into array form
     * @param int $maxFields Number of highlighted fields to return, 0 = all
     *
     * @return array matched fields key value pairs
     */
    protected function getMatchedFields($args, $record, $maxFields = 0)
    {
        $query = $args['q'];
        $searchFields = explode(',', $args['search_fields']);

        $matchedFields = array();
        foreach ($searchFields as $searchField) {
            if (!isset($record[$searchField])) {
                continue;
            }

            $fieldValues = array();
            if ($searchField === 'email') {
                //can be multiple email addresses
                foreach ($record[$searchField] as $email) {
                    $fieldValues[] = $email['email_address'];
                }
            } elseif (is_string($record[$searchField])) {
                $fieldValues = array($record[$searchField]);
            }

            foreach ($fieldValues as $fieldValue) {
                if (stripos($fieldValue, $query) !== false) {
                    $matchedFields[$searchField] = array($fieldValue);
                }
            }
        }

        $ret = array();
        if (!empty($matchedFields) && is_array($matchedFields)) {
            $highlighter = new SugarSearchEngineHighlighter();
            $highlighter->setModule($record['_module']);
            $ret = $highlighter->processHighlightText($matchedFields);
            if ($maxFields > 0) {
                $ret = array_slice($ret, 0, $maxFields);
            }
        }

        return $ret;
    }
}
