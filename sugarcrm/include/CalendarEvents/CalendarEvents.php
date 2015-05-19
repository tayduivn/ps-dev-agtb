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
     * Recurring record fields that require a full re-construction of a recurring event if their values change
     * NOTE: Fields tied to meeting duration do NOT require a full reconstruction of the series
     *        'duration_hours'
     *        'duration_minutes'
     *        'date_end'
     * @TODO
     *      There would be little effort needed to remove 'repeat_until' and 'repeat_count' from
     *      this list, since they only change the total number of meetings. A method to lengthen
     *      or shorten the series using the existing recurrence rules should be reasonably
     *      straight forward to implement.
     *
     * @var array
     *
     */
    public $fieldChangesRequiringRebuild = array(
        'date_start' => true,
        'repeat_type' => true,
        'repeat_interval' => true,
        'repeat_dow' => true,
        'repeat_until' => true,
        'repeat_count' => true,
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

        return (!empty($bean->repeat_type) && !empty($bean->date_start));
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
     * Determine whether the recurring series needs to be fully rebuilt
     * @param SugarBean $parentBean recurring event parent
     * @param array $inviteeChanges
     * @return bool true if full reconstruction of the event series is required
     */
    public function isFullReconstructionOfRecurringSeriesRequired(SugarBean $parentBean, $inviteeChanges = array())
    {
        // If we don't recognize the bean as a valid Recurring Event, we will return true
        try {
            if (!$this->isEventRecurring($parentBean)) {
                return true;
            }
        } catch(Exception $e) {
            return true;
        }

        // If there are any changes to the invitee list, a full reconstruction of the series is required
        if (!empty($inviteeChanges) &&
            ((isset($inviteeChanges['add']) && !empty($inviteeChanges['add'])) ||
                (isset($inviteeChanges['delete']) && !empty($inviteeChanges['delete'])))
        ) {
            return true;
        }

        // Assume that a full reconstruction is required unless the data Changes array is present and
        // none of the field changes it holds require reconstruction
        if (!empty($parentBean->dataChanges) && is_array($parentBean->dataChanges)) {
            foreach ($parentBean->dataChanges AS $field => $changeInfo) {
                if (!empty($this->fieldChangesRequiringRebuild[$field])) {
                    return true;
                }
            }
        }

        // There are changes - but none that require full reconstruction of the recurring series
        return false;
    }

    /**
     * Update all children of the recurring series by overwriting all
     * children with the changes made to the parent
     * @param SugarBean $parentBean
     * @return bool true if successfully completed all updates
     */
    public function applyChangesToRecurringEvents(SugarBean $parentBean)
    {
        $moduleName = $parentBean->module_name;
        $parentBean->load_relationship('tag_link');
        $parentTagBeans = $parentBean->tag_link->getBeans();
        $success = false;

         try {
            Activity::disable();
            $clone = clone $parentBean;

            $limit = 200;
            $offset = 0;
            $q = new SugarQuery();
            $q->select(array('id'));
            $q->from($parentBean);
            $q->where()->equals('repeat_parent_id', $parentBean->id);
            $q->limit($limit);

            while (true) {
                $q->offset($offset);
                $rows = $q->execute();
                $rowCount = count($rows);
                foreach ($rows as $row) {
                    $childBean = BeanFactory::getBean($moduleName, $row['id']);
                    if (empty($childBean) || $childBean->id !== $row['id']) {
                        throw new SugarException('Unable to Load Child Occurrence: ' . $moduleName);
                    }
                    $clone->id = $childBean->id;
                    $clone->date_start = $childBean->date_start;
                    $clone->recurring_source = $childBean->recurring_source;
                    $clone->repeat_parent_id = $parentBean->id;
                    $clone->update_vcal = false;
                    $clone->save(false);

                    $childBean->load_relationship('tag_link');
                    $childTagBeans = $childBean->tag_link->getBeans();
                    $this->reconcileTags($parentTagBeans, $childBean, $childTagBeans);
                }
                if ($rowCount < $limit) {
                    break;
                }
                $offset += $rowCount;
            }
            $success = true;
            Activity::enable();
            vCal::cache_sugar_vcal($GLOBALS['current_user']);
        } catch (Exception $e) {
            Activity::enable();
        }

        return $success;
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
        $sf = new SugarFieldTag('tag');
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
        if ($parentBean->load_relationship('users')) {
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


}
