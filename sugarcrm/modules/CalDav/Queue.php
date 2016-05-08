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

class CalDavQueue extends SugarBean
{
    const ACTION_IMPORT = 'import';
    const ACTION_EXPORT = 'export';

    const STATUS_QUEUED = 'queued';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CONFLICT = 'conflict';

    public $new_schema = true;
    public $module_dir = 'CalDav';
    public $module_name = 'CalDavQueues';
    public $object_name = 'CalDavQueue';
    public $table_name = 'caldav_queue';
    public $disable_custom_fields = true;

    /**
     * CalDav bean id
     *
     * @var string
     */
    public $event_id;

    /**
     * Action for handler. Possible values: import, export.
     *
     * @var string
     */
    public $action;

    /**
     * Module queue counter
     * @var int
     */
    public $save_counter = 0;

    /**
     * Status for handler. Possible values: queued, completed.
     *
     * @var string
     */
    public $status;

    /**
     * @var string
     */
    public $data;

    /**
     * Add to queue for export.
     *
     * @param $data
     * @param $saveCounter
     * @return CalDavQueue
     * @throws Exception
     */
    public function export($data, $saveCounter)
    {
        $GLOBALS['log']->debug(
            "CalDav: Queue item for export action with save_counter = $saveCounter and data = " .
            var_export($data, true)
        );
        $bean = new static;
        $bean->event_id = $this->event_id;
        $bean->action = static::ACTION_EXPORT;
        $bean->save_counter = $saveCounter;
        $bean->status = static::STATUS_QUEUED;
        $bean->data = json_encode($data);
        if (!$this->set_created_by) {
            $bean->created_by = $this->created_by;
            $bean->set_created_by = $this->set_created_by;
        }
        $bean->save();
        return $bean;
    }

    /**
     * Add to queue for export.
     *
     * @param $data
     * @param $saveCounter
     * @return CalDavQueue
     * @throws Exception
     */
    public function import($data, $saveCounter)
    {
        $GLOBALS['log']->debug(
            "CalDav: Queue item for import action with save_counter = $saveCounter and data = " .
            var_export($data, true)
        );
        $bean = new static;
        $bean->event_id = $this->event_id;
        $bean->action = static::ACTION_IMPORT;
        $bean->save_counter = $saveCounter;
        $bean->status = static::STATUS_QUEUED;
        $bean->data = json_encode($data);
        $bean->save();
        return $bean;
    }

    /**
     * Get a first element queued.
     *
     * @var string $eventId
     * @return mixed|null
     * @throws SugarQueryException
     */
    public function findFirstQueued($eventId)
    {
        $query = new SugarQuery();
        $query->from($this);
        $query->where()->equals('event_id', $eventId)->equals('status', static::STATUS_QUEUED);
        $query->orderBy('save_counter', 'ASC');
        $query->limit(1);
        $result = $this->fetchFromQuery($query);

        if ($result) {
            return array_shift($result);
        }

        return null;
    }
}
