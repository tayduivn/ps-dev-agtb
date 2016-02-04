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

/**
 * Class CalDavScheduling
 * Represents implementation of Sugar Bean for CalDAV backend operations with scheduling objects
 */
class CalDavScheduling extends SugarBean
{
    public $new_schema = true;
    public $module_dir = 'CalDav';
    public $module_name = 'CalDavSchedulings';
    public $object_name = 'CalDavScheduling';
    public $table_name = 'caldav_scheduling';

    /**
     * Scheduling object ID
     * @var string
     */
    public $id;

    /**
     * Scheduling object name
     * @var string
     */
    public $name;

    /**
     * Scheduling object creation date
     * @var string
     */
    public $date_entered;

    /**
     * Scheduling object modified date
     * @var string
     */
    public $date_modified;

    /**
     * User who modified the object
     * @var string
     */
    public $modified_user_id;

    /**
     * User who created the object
     * @var string
     */
    public $created_by;

    /**
     * Scheduling object description
     * @var string
     */
    public $description;

    /**
     * Is object deleted or not
     * @var string
     */
    public $deleted;

    /**
     * Calendar event data in VOBJECT format
     * @var string
     */
    public $calendar_data;

    /**
     * Scheduling object uri
     * @var string
     */
    public $uri;

    /**
     * Object ETag. MD5 hash from $calendardata
     * @var string
     */
    public $etag;

    /**
     * $calendardata size in bytes
     * @var string
     */
    public $data_size;

    /**
     * @var string
     */
    public $assigned_user_id;

    /**
     * Calculate and set the size of the event data in bytes
     * @param string $data Calendar event text data
     * @return string Size of $data
     */
    protected function calculateSize($data)
    {
        return strlen($data);
    }

    /**
     * Calculate and set calendar event ETag hash
     * @param string $data Calendar event text data
     * @return string
     */
    protected function calculateETag($data)
    {
        return md5($data);
    }

    /**
     * Set scheduling event info
     * @param User $user
     * @param string $objectUri
     * @param string $eventData
     *
     * @return bool
     */
    public function setSchedulingEventData(\User $user, $objectUri, $eventData)
    {
        if (!$eventData) {
            return false;
        }

        if (!$user) {
            return false;
        }
        $this->assigned_user_id = $user->id;
        $this->uri = $objectUri;

        $this->calendar_data = $eventData;

        $this->data_size = $this->calculateSize($eventData);
        $this->etag = $this->calculateETag($eventData);

        return true;
    }

    /**
     * @param $objectUri
     * @param $userId
     * @return array
     */
    public function getByUri($objectUri, $userId)
    {
        $query = new \SugarQuery();

        $query->from($this);
        $query->where()->equals('uri', $objectUri);
        $query->where()->equals('assigned_user_id', $userId);
        $query->limit(1);

        $result = $this->fetchFromQuery($query);

        if (!$result) {
            return null;
        }

        return array_shift($result);
    }

    /**
     * Retrieve all scheduling objects by user
     * @param string $userId
     * @return \SugarBean
     */
    public function getByAssigned($userId)
    {
        $query = new \SugarQuery();

        $query->from($this);
        $query->where()->equals('assigned_user_id', $userId);

        return $this->fetchFromQuery($query);
    }

}
