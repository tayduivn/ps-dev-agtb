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

namespace Sugarcrm\Sugarcrm\Dav\Cal\Adapter;

use \Sugarcrm\Sugarcrm\Dav\Base\Helper\ParticipantsHelper as ParticipantsHelper;

/**
 * Abstract class for iCal adapters common functionality
 *
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Adapter
 */
abstract class AdapterAbstract
{
    /**
     * @param \Call|\Meeting|\SugarBean $bean
     * @param array $changedFields
     * @param array $invitesBefore
     * @param array $invitesAfter
     * @param bool|false $forceInsert
     * @return mixed
     */
    public function prepareForExport(
        \SugarBean $bean,
        $changedFields = array(),
        $invitesBefore = array(),
        $invitesAfter = array(),
        $forceInsert = false
    ) {
        $participantsHelper = $this->getParticipantHelper();
        $parentBean = null;
        $childEvents = null;
        $repeatParentId = $bean->repeat_parent_id;
        $isUpdated = $bean->isUpdate() && !$forceInsert;
        /**
         * null means nothing changed, otherwise child was changed
         */
        $childEventsId = null;

        if (!$repeatParentId) {
            if ((!$isUpdated && $bean->repeat_type)
                ||
                ($isUpdated && $this->isRecurringChanged($changedFields))
            ) {
                $childEventsId = array();
                $calendarEvents = $this->getCalendarEvents();
                $childEvents = $calendarEvents->getChildrenQuery($bean)->execute();
                foreach ($childEvents as $event) {
                    $childEventsId[] = $event->id;
                }
            }
        }

        if ($isUpdated) {
            $changedFields = $this->getFieldsDiff($changedFields);
        } else {
            $changedFields = $this->getBeanFetchedRow($bean);
        }

        $beanData = array(
            $bean->module_name,
            $bean->id,
            $repeatParentId,
            $childEventsId,
            $isUpdated,
        );

        return array($beanData, $changedFields, $participantsHelper->getInvitesDiff($invitesBefore, $invitesAfter));
    }

    /**
     * return true if one of rucurrig rules was changed
     * @param array $changedFields
     * @return bool
     */
    protected function isRecurringChanged($changedFields)
    {
        $fieldList = array(
            'repeat_type',
            'repeat_interval',
            'repeat_count',
            'repeat_until',
            'repeat_dow',
        );

        if (count(array_intersect(array_keys($changedFields), $fieldList))) {
            return true;
        }
        return false;
    }

    /**
     * get fields list with before (if exists) and after values of field
     * @param array $changedFields
     * @return mixed
     */
    protected function getFieldsDiff($changedFields)
    {
        $dataDiff = array();
        foreach ($changedFields as $field => $fieldValues) {
            $dataDiff[$field] = array (
                0 => $fieldValues['after'],
            );
            if ($fieldValues['before']) {
                $dataDiff[$field][1] = $fieldValues['before'];
            }
        }
        return $dataDiff;
    }

    /**
     * Retrieve bean fetched row
     * @param \SugarBean $bean
     * @return array
     */
    protected function getBeanFetchedRow(\SugarBean $bean)
    {
        $dataDiff = array();
        if (!$bean->fetched_row) {
            $bean->retrieve($bean->id);
        }

        foreach ($bean->fetched_row as $name => $value) {
            $dataDiff[$name] = array(
                0 => $value
            );
        }
        return $dataDiff;
    }

    /**
     * @return \Sugarcrm\Sugarcrm\Dav\Base\Helper\ParticipantsHelper
     */
    protected function getParticipantHelper()
    {
        return new ParticipantsHelper();
    }

    /**
     * @return \CalendarEvents
     */
    protected function getCalendarEvents()
    {
        return new \CalendarEvents();
    }
}
