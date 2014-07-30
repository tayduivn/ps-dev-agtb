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
        return array(
            'delete' => array(
                'reqType' => 'DELETE',
                'path' => array('<module>', '?'),
                'pathVars' => array('module', 'record'),
                'method' => 'deleteCalendarEvent',
                'shortHelp' => 'This method deletes a single event record or a series of event records of the specified type',
                'longHelp' => 'include/api/help/calendar_events_record_delete_help.html',
            ),
        );
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
     * Deletes the parent and associated child events in a series.
     * @param $api
     * @param $args
     * @return array
     */
    public function deleteRecordAndRecurrences($api, $args)
    {
        $this->requireArgs($args,array('module','record'));

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
}
