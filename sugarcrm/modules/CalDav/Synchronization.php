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

class CalDavSynchronization extends SugarBean
{
    public $new_schema = true;
    public $module_dir = 'CalDav';
    public $module_name = 'CalDavSynchronizations';
    public $object_name = 'CalDavSynchronization';
    public $table_name = 'caldav_synchronization';
    public $disable_custom_fields = true;

    /**
     * Module synchronization counter
     * @var int
     */
    public $save_counter;

    /**
     * CalDav event synchronization counter
     * @var int
     */
    public $job_counter;

    /**
     * CalDav bean id
     * @var string
     */
    public $event_id;

    /**
     * Number of save_counter which solves conflict.
     * @var bool
     */
    public $conflict_counter = 0;

    /**
     * Set save counter of caldav event or module
     * @return int
     */
    public function setSaveCounter()
    {
        $syncCounter = ++ $this->save_counter;
        $this->save();

        return $syncCounter;
    }

    /**
     * Set completed jobs counter
     * @return int
     */
    public function setJobCounter()
    {
        $syncCounter = ++ $this->job_counter;
        $this->save();

        return $syncCounter;
    }

    /**
     * Sets conflict counter which will be based on $flag and current save counter.
     *
     * @param bool $flag
     * @return bool
     */
    public function setConflictCounter($flag = false)
    {
        if ($flag) {
            $this->conflict_counter = $this->save_counter;
        } else {
            $this->conflict_counter = 0;
        }
        $this->save();

        return $this->conflict_counter;
    }

    /**
     * Get save counter of caldav event or module
     * @return int
     */
    public function getSaveCounter()
    {
        return $this->save_counter;
    }

    /**
     * Get completed jobs counter
     * @return int
     */
    public function getJobCounter()
    {
        return $this->job_counter;
    }

    /**
     * Returns conflict counter.
     *
     * @return int
     */
    public function getConflictCounter()
    {
        return $this->conflict_counter;
    }
}
