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

/**
 * @var CalendarEvents
 */

use Sugarcrm\Sugarcrm\Dav\Cal\Hook\Handler as CalDavHandler;

class CalendarEvents
{
    public static $old_assigned_user_id = '';

    /**
     * Schedulable calendar events (modules) supported
     * @var array
     */
    public $calendarEventModules = array(
        'Meetings',
        'Calls',
        'Tasks',
    );
    /**
     * @param SugarBean $bean
     * @return bool
     * @throws SugarException
     */
    public function isEventRecurring(SugarBean $bean)
    {
        if (!in_array($bean->module_name, $this->calendarEventModules)) {
            $logmsg = 'Recurring Calendar Event - Module Unexpected: ' . $bean->module_name;
            $GLOBALS['log']->error($logmsg);
            throw new SugarException('LBL_CALENDAR_EVENT_RECURRENCE_MODULE_NOT_SUPPORTED', array($bean->module_name));
        }

        $isRecurring = !empty($bean->repeat_type) && !empty($bean->date_start);

        if ($isRecurring) {
            $GLOBALS['log']->debug(sprintf('%s/%s is recurring', $bean->module_name, $bean->id));
        } else {
            $GLOBALS['log']->debug(sprintf('%s/%s is not recurring', $bean->module_name, $bean->id));
        }

        return $isRecurring;
    }

    /**
     * Return Configured recurrence limit.
     * @return int
     */
    public function getRecurringLimit()
    {
        return SugarConfig::getInstance()->get('calendar.max_repeat_count', 1000);
    }

    /**
     * Rebuild the FreeBusy Vcal Cache for specified user
     */
    public function rebuildFreeBusyCache(User $user)
    {
        vCal::cache_sugar_vcal($user);
    }

    /**
     * @param SugarBean $parentBean
     * @return array events saved
     * @throws SugarException
     */
    public function saveRecurringEvents(SugarBean $parentBean)
    {
        if (!$this->isEventRecurring($parentBean)) {
            $logmsg = 'SaveRecurringEvents() : Event is not a Recurring Event';
            $GLOBALS['log']->error($logmsg);
            throw new SugarException('LBL_CALENDAR_EVENT_NOT_A_RECURRING_EVENT', array($parentBean->object_name));
        }

        if (!empty($parentBean->repeat_parent_id)) {
            $logmsg = 'SaveRecurringEvents() : Event received is not the Parent Occcurrence';
            $GLOBALS['log']->error($logmsg);
            throw new SugarException('LBL_CALENDAR_EVENT_IS_NOT_A_PARENT_OCCURRENCE', array($parentBean->object_name));
        }

        $dateStart = $this->formatDateTime('datetime', $parentBean->date_start, 'user');

        $params = array();
        $params['type'] = $parentBean->repeat_type;
        $params['interval'] = $parentBean->repeat_interval;
        $params['count'] = $parentBean->repeat_count;
        $params['until'] = $this->formatDateTime('date', $parentBean->repeat_until, 'user');
        $params['dow'] = $parentBean->repeat_dow;

        $repeatDateTimeArray = $this->buildRecurringSequence($dateStart, $params);

        $limit = $this->getRecurringLimit();
        if (count($repeatDateTimeArray) > ($limit - 1)) {
            $logMessage = sprintf(
                'Calendar Events (%d) exceed Event Limit: (%d)',
                count($repeatDateTimeArray),
                $limit
            );
            $GLOBALS['log']->warning($logMessage);
        }

        // Turn off The Cache Updates while deleting the multiple recurrences.
        // The current Cache Enabled status is returned so it can be appropriately
        // restored when all the recurrences have been deleted.
        $cacheEnabled = vCal::setCacheUpdateEnabled(false);
        $this->markRepeatDeleted($parentBean);
        // Restore the Cache Enabled status to its previous state
        vCal::setCacheUpdateEnabled($cacheEnabled);

        return $this->saveRecurring($parentBean, $repeatDateTimeArray);
    }

    /**
     * Reconcile Tags on Child Bean to Match Parent
     * @param array Tag Beans on the Parent Calendar Event
     * @param SugarBean Child Calendar Event Bean
     * @param array Tag Beans currently existing on Child (optional - defaults to empty array)
     */
    public function reconcileTags(array $parentTagBeans, SugarBean $childBean, $childTagBeans = array())
    {
        $sf = SugarFieldHandler::getSugarField('tag');
        $parentTags = $sf->getOriginalTags($parentTagBeans);
        $childTags = $sf->getOriginalTags($childTagBeans);
        list($addTags, $removeTags) = $sf->getChangedValues($childTags, $parentTags);

        // Handle removal of tags
        $sf->removeTagsFromBean($childBean, $childTagBeans, 'tag_link', $removeTags);

        // Handle addition of new tags
        $sf->addTagsToBean($childBean, $parentTagBeans, 'tag_link', $addTags);
    }

    /**
     * Generate the Start and End Dates for each event occurrence.
     * @param string Start Date
     * @param array  Repeat Occurrence Fields: 'type', 'interval', 'count' 'until' 'dow'
     * @return array Start DateTimes
     */
    protected function buildRecurringSequence($dateStart, array $params)
    {
        return CalendarUtils::buildRecurringSequence($dateStart, $params);
    }

    /**
     * Mark recurring event deleted
     * @param SugarBean parent Bean
     */
    protected function markRepeatDeleted(SugarBean $parentBean)
    {
        CalendarUtils::markRepeatDeleted($parentBean);
    }

    /**
     * @param SugarBean $parentBean
     * @param array $repeatDateTimeArray
     * @return array events saved
     */
    protected function saveRecurring(SugarBean $parentBean, array $repeatDateTimeArray)
    {
        // Load the user relationship so the child events that are created will
        // have the users added via bean->save (which has special auto-accept
        // logic)
        if (empty($parentBean->users_arr) && $parentBean->load_relationship('users')) {
            $parentBean->users->load();
            $parentBean->users_arr = $parentBean->users->get();
        }
        return CalendarUtils::saveRecurring($parentBean, $repeatDateTimeArray);
    }

    /**
     * Convert A Date, Time  or DateTime String from one format to Another
     * @param string type of the second argument : one of 'date', 'time', 'datetime', 'datetimecombo'
     * @param string formatted date, time or datetime field in DB, ISO, or User Format
     * @param string output format - one of: 'db', 'iso' or 'user'
     * @return string formatted result
     */
    public function formatDateTime($type, $dtm, $toFormat)
    {
        $result = '';
        $sugarDateTime = $this->getSugarDateTime($type, $dtm);
        if (!empty($sugarDateTime)) {
            $result = $sugarDateTime->formatDateTime($type, $toFormat, $GLOBALS['current_user']);
        }
        return $result;
    }

    /**
     * Return a SugarDateTime Object given any Date to Time Format
     * @param string type of the second argument : one of 'date', 'time', 'datetime', 'datetimecombo'
     * @param string  formatted date, time or datetime field in DB, ISO, or User Format
     * @return SugarDateTime
     */
    public function getSugarDateTime($type, $dtm)
    {
        global $timedate;
        $sugarDateTime = null;
        if (!empty($dtm)) {
            $sugarDateTime = $timedate->fromUserType($dtm, $type);
            if (empty($sugarDateTime)) {
                $sugarDateTime = $timedate->fromDBType($dtm, $type);
            }
            if (empty($sugarDateTime)) {
                switch($type) {
                    case 'time':
                        $sugarDateTime = $timedate->fromIsoTime($dtm);
                        break;
                    case 'date':
                    case 'datetime':
                    case 'datetimecombo':
                    default:
                        $sugarDateTime = $timedate->fromIso($dtm);
                        break;
                }
            }
        }
        return $sugarDateTime;
    }

    /**
     * Store Current Assignee Id or blank if New Bean (Create)
     */
    public function setOldAssignedUser($module, $id = null)
    {
        static::$old_assigned_user_id = '';
        if (!empty($module) && !empty($id)) {
            $old_record = BeanFactory::getBean($module, $id);
            if (!empty($old_record->assigned_user_id)) {
                static::$old_assigned_user_id = $old_record->assigned_user_id;
            }
        }
    }

    /**
     * Add record defined by parent field as an invitee if it is a Contact or Lead record
     *
     * @param $bean
     * @param $parentType
     * @param $parentId
     */
    public function inviteParent($bean, $parentType, $parentId)
    {
        $inviteeRelationships = array(
            'Contacts' => 'contacts',
            'Leads' => 'leads',
        );

        foreach($inviteeRelationships as $module => $relationship) {
            if ($parentType == $module) {
                $bean->load_relationship($relationship);
                if (!$bean->$relationship->relationship_exists($relationship, array('id' => $parentId))) {
                    $bean->$relationship->add($parentId);
                }
            }
        }
    }

    /**
     * Set Start Datetime and End Datetime for a Meeting or Call
     *
     * @param SugarBean $bean - Schedulable Event - i.e Meeting, Call
     * @param SugarDateTime $userDateTime in Database Format (UTC)
     */
    public function setStartAndEndDateTime(SugarBean $bean, SugarDateTime $dateStart)
    {
        global $current_user;

        $dtm = clone $dateStart;
        $bean->duration_hours = empty($bean->duration_hours) ? 0 : intval($bean->duration_hours);
        $bean->duration_minutes =  empty($bean->duration_minutes) ? 0 : intval($bean->duration_minutes);

        if ($bean->repeat_type === 'Weekly' && !empty($bean->repeat_dow)) {
            // This calculation Must occur in the User's TimeZone
            $timezone = $current_user->getTimeZone();
            $dtm->setTimeZone($timezone);

            // Start Date must be one of the weekdays specified
            $dow = $dtm->format('w');
            $j = 6;
            while ($j > 0 && strpos($bean->repeat_dow, $dow) === false) {
                $dtm->modify('+1 Days');
                $dow = $dtm->format('w');
                $j--;
            }
        }

        $bean->date_start = $dtm->asDb();
        if ($bean->duration_hours > 0) {
            $dtm->modify("+{$bean->duration_hours} hours");
        }
        if ($bean->duration_minutes > 0) {
            $dtm->modify("+{$bean->duration_minutes} mins");
        }
        $bean->date_end = $dtm->asDb();
    }

    /**
     * Update an invitee's accept status for a particular event. Update all future events in the series if the event is
     * recurring.
     *
     * Future events are those that have a status that is neither "Held" nor "Not Held".
     *
     * @param SugarBean $event
     * @param SugarBean $invitee
     * @param string $status
     * @param array $options See {@link BeanFactory::retrieveBean}.
     * @return bool True if at least one accept status was updated.
     * @throws SugarException
     */
    public function updateAcceptStatusForInvitee(
        SugarBean $event,
        SugarBean $invitee,
        $status = 'accept',
        $options = array()
    ) {
        $changeWasMade = false;
        $invitesBefore = CalendarUtils::getInvites($event);
        if (in_array($event->status, array('Held', 'Not Held'))) {
            $GLOBALS['log']->debug(
                sprintf(
                    'Do not update the %s/%s accept status for the parent event %s/%s when the event status is %s',
                    $invitee->module_name,
                    $invitee->id,
                    $event->module_name,
                    $event->id,
                    $event->status
                )
            );
        } else {
            $GLOBALS['log']->debug(
                sprintf(
                    'Set %s/%s accept status to %s for %s/%s',
                    $invitee->module_name,
                    $invitee->id,
                    $status,
                    $event->module_name,
                    $event->id
                )
            );
            $event->update_vcal = false;
            $event->set_accept_status($invitee, $status);
            $changeWasMade = true;
        }

        if ($this->isEventRecurring($event)) {
            /**
             * Updates the invitee's accept status for one occurrence in the series.
             *
             * @param array $row The child record to update. Only the ID is used.
             */
            $callback = function(array $row) use ($event, $invitee, $status, $options, &$changeWasMade) {
                $child = BeanFactory::retrieveBean($event->module_name, $row['id'], $options);

                if ($child) {
                    $GLOBALS['log']->debug(sprintf(
                        'Set %s/%s accept status to %s for %s/%s',
                        $invitee->module_name,
                        $invitee->id,
                        $status,
                        $child->module_name,
                        $child->id
                    ));
                    $child->update_vcal = false;
                    $child->set_accept_status($invitee, $status);
                    $changeWasMade = true;
                } else {
                    $GLOBALS['log']->error("Could not set acceptance status for {$event->module_name}/{$row['id']}");
                }
            };

            $query = $this->getChildrenQuery($event);
            $GLOBALS['log']->debug('Only update occurrences that have not been held or canceled');
            $query->where()
                ->notEquals('status', 'Held')
                ->notEquals('status', 'Not Held');
            $this->repeatAction($query, $callback);
        }

        if ($changeWasMade) {
            $invitesAfter = CalendarUtils::getInvites($event);
            $calDavHandler = new CalDavHandler();
            $calDavHandler->export($event, array(array(), $invitesBefore, $invitesAfter));
            if ($invitee instanceof User) {
                $GLOBALS['log']->debug(sprintf('Update vCal cache for %s/%s', $invitee->module_name, $invitee->id));
                vCal::cache_sugar_vcal($invitee);
            }
        }


        return $changeWasMade;
    }

    /**
     * Returns a SugarQuery object that can be used to fetch all of the child events in a recurring series.
     *
     * @param SugarBean $parent
     * @return SugarQuery Modify the object to restrict the result set based on additional conditions.
     * @throws SugarQueryException
     */
    public function getChildrenQuery(SugarBean $parent)
    {
        $GLOBALS['log']->debug(sprintf(
            'Building a query to retrieve the IDs for %s records where the repeat_parent_id is %s',
            $parent->module_name,
            $parent->id
        ));
        $query = new SugarQuery();
        $query->select(array('id'));
        $query->from($parent);
        $query->where()->equals('repeat_parent_id', $parent->id);
        $query->orderBy('date_start', 'ASC');
        return $query;
    }

    /**
     * Repeat the same action for each record returned by a query. This is useful for repeating an action for each child
     * record in a series.
     *
     * Retrieves, from the database, a max of 200 records at a time upon which to perform the action. This is done to
     * reduce the memory footprint in the event that too many records would be loaded into memory.
     *
     * @param SugarQuery $query The SugarQuery object to use to retrieve the records.
     * @param Closure $callback The function to call for each child record. The database row -- as an array -- is
     * passed to the callback.
     */
    protected function repeatAction(SugarQuery $query, Closure $callback)
    {
        $limit = 200;
        $offset = 0;

        do {
            $GLOBALS['log']->debug(sprintf('Retrieving the next %d records beginning at %d', $limit, $offset));
            $query->limit($limit)->offset($offset);
            $rows = $query->execute();
            $rowCount = count($rows);
            $GLOBALS['log']->debug(sprintf('Repeating the action on %d events', $rowCount));
            array_walk($rows, $callback);
            $offset += $rowCount;
        } while ($rowCount === $limit);

        $GLOBALS['log']->debug(sprintf(
            'Finished repeating because the row count %d does not equal the limit %d',
            $rowCount,
            $limit
        ));
    }
}
