<?php declare(strict_types=1);
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

use Sugarcrm\Sugarcrm\DependencyInjection\Container;

/**
 * Class Issue
 */
class Issue extends Basic
{
    public $object_name;

    public $date_entered;
    public $status;

    //BEGIN SUGARCRM flav=ent ONLY
    public $resolved_datetime;
    //END SUGARCRM flav=ent ONLY

    /**
     * @var array List of possible status values that indicate this issue is
     * "done" or "resolved". Should be a subset of case_status_dom.
     */
    protected $resolvedStati = ['Closed', 'Rejected', 'Duplicate'];

    /**
     * Check if the given status is a "resolved" status.
     *
     * @param string|null $status Status to check.
     * @return bool true if the given status is a resolved status and false otherwise.
     */
    protected function isResolvedStatus(?string $status): bool
    {
        if (!isset($status)) {
            return false;
        }

        return in_array($status, $this->resolvedStati);
    }

    /**
     * Check if this issue is resolved.
     *
     * @return bool true if this issue is resolved and false otherwise.
     */
    public function isResolved(): bool
    {
        return $this->isResolvedStatus($this->status);
    }

    //BEGIN SUGARCRM flav=ent ONLY
    /**
     * Check if this issue is newly resolved; i.e. it changed from an
     * unresolved to a resolved status.
     *
     * FIXME: We'll probably have to change this logic to support reopened issues
     * in the future...
     *
     * @return bool true if this issue is newly resolved and false otherwise.
     */
    public function isNewlyResolved(): bool
    {
        if (!$this->isResolved()) {
            return false;
        }

        // handle issues that were created with a resolved status
        if (empty($this->fetched_row) || !isset($this->fetched_row['status'])) {
            return true;
        }

        return !$this->isResolvedStatus($this->fetched_row['status']);
    }

    /**
     * Calculates the total hours between the two given datetimes, returns two different hours in an array,
     * one is based on calendar hours, the other one is based on the business hours (if business center
     * doesn't exist or not defined, calendar hours will be returned instead).
     *
     * @param SugarDateTime $startDateTime The start date
     * @param SugarDateTime $endDateTime The end date
     * @param string $bid The business center bean id
     * @return array containing the total calendar time and business time (in decimal)
     */
    public function getHoursBetween(
        \SugarDateTime $startDateTime,
        \SugarDateTime $endDateTime,
        string $bid = ''
    ) {
        $hours = [
            'calendarHours' => 0.00,
            'businessHours' => 0.00,
        ];

        $businessCenter = BeanFactory::getBean('BusinessCenters', $bid);

        $hours['calendarHours'] = $this->getCalendarTimeBetween($startDateTime, $endDateTime);

        if (!empty($businessCenter) && !empty($businessCenter->id) && $businessCenter->hasBusinessHours()) {
            $hours['businessHours'] = $businessCenter->getBusinessTimeBetween($startDateTime, $endDateTime);
        } else {
            $hours['businessHours'] = $hours['calendarHours'];
        }

        return $hours;
    }

    /**
     * Calculates the total calendar time (in decimal hours now) between the two given datetimes
     *
     * @param SugarDateTime $startDateTime The start date
     * @param SugarDateTime $endDateTime The end date
     * @return float The total calendar time (in decimal)
     */
    public function getCalendarTimeBetween(\SugarDateTime $startDateTime, \SugarDateTime $endDateTime)
    {
        if ($startDateTime > $endDateTime) {
            return 0.00;
        }

        return round(($endDateTime->getTimestamp() - $startDateTime->getTimestamp()) / 3600, 2);
    }

    /**
     * Returns the hours (calendar/business) it took to resolve this issue.
     */
    public function calculateResolutionHours()
    {
        $timeDate = Container::getInstance()->get(\TimeDate::class);
        $now = $timeDate->nowDb();

        // get the UNIX timestamps (seconds) for both resolved_datetime and date_entered,
        // substituting the current time if either does not exist.
        $resolvedDatetime = empty($this->resolved_datetime) ? $now : $this->resolved_datetime;
        $resolvedDatetime = $timeDate->fromDb($resolvedDatetime);
        $dateEntered = empty($this->date_entered) ? $now : $this->date_entered;
        $dateEntered = $timeDate->fromDb($dateEntered);

        $hours = $this->getHoursBetween($dateEntered, $resolvedDatetime, $this->business_center_id ?? '');
        $this->hours_to_resolution = $hours['calendarHours'];
        $this->business_hours_to_resolution = $hours['businessHours'];
    }

    /**
     * @param array $fields change timer enabled fields
     * @param bool $isUpdate, return changed fields if true, non empty fields if false
     * @return array
     */
    protected function getCTFieldsToProcess(array $fields, bool $isUpdate) : array
    {
        $fieldsToProcess = [];
        if ($isUpdate) {
            $changedFields = [];
            foreach ($this->db->getDataChanges($this, ['field_filter' => $fields]) as $field) {
                if (!empty($field['field_name'])) {
                    $changedFields[] = $field['field_name'];
                }
            }
        }
        foreach ($fields as $field) {
            if ($isUpdate) {
                if (in_array($field, $changedFields)) {
                    $fieldsToProcess[] = $field;
                }
            } else {
                if (!empty($this->$field)) {
                    $fieldsToProcess[] = $field;
                }
            }
        }
        return $fieldsToProcess;
    }

    /**
     * @param string $field
     * @return string
     * @throws SugarQueryException
     */
    protected function getLastId(string $field) : string
    {
        $query = new SugarQuery();
        $query->select(['id']);
        $bean = BeanFactory::newBean('ChangeTimers');

        $query->from($bean);

        $query->where()->queryAnd()
            ->equals('field_name', $field)
            ->equals('parent_type', $this->getModuleName())
            ->equals('parent_id', $this->id)
            ->isNull('to_datetime');
        $query->limit(1);
        $query->orderBy('date_modified');

        $rows = $query->execute();

        return $rows[0]['id'] ?? '';
    }

    /**
     * @param string $field
     * @return string
     */
    protected function createNewCTRecord(string $field) : string
    {
        $newBean = BeanFactory::newBean('ChangeTimers');
        $newBean->parent_type = $this->getModuleName();
        $newBean->parent_id = $this->id;
        $newBean->field_name = $field;
        $newBean->value_string = $this->$field;
        $newBean->from_datetime = $this->date_modified;
        return $newBean->save();
    }

    /**
     * @param string $lastId
     * @return bool
     * @throws Exception
     */
    protected function updateLastCTRecord(string $lastId) : bool
    {
        $bean = BeanFactory::retrieveBean('ChangeTimers', $lastId);
        if ($bean) {
            $bean->to_datetime = $this->date_modified;

            // hours and business hours between from_datetime to to_datetime
            $hours = $this->getHoursBetween(
                new \SugarDateTime($bean->from_datetime, new DateTimeZone('UTC')),
                new \SugarDateTime($bean->to_datetime, new DateTimeZone('UTC')),
                $this->business_center_id ?? ''
            );
            $bean->hours = $hours['calendarHours'];
            $bean->business_hours = $hours['businessHours'];

            return (bool) $bean->save();
        }

        return false;
    }

    /**
     * @param string $field
     * @throws SugarQueryException
     */
    protected function updateChangeTimerRecord(string $field)
    {
        // update the last record
        $lastId = $this->getLastId($field);
        if (!empty($lastId)) {
            $this->updateLastCTRecord($lastId);
        }

        // add a new record
        $this->createNewCTRecord($field);
    }

    /**
     * @return array
     */
    protected function getChangeTimerFields() : array
    {
        $bean_name = get_valid_bean_name($this->getModuleName());
        return VardefManager::getModuleProperty($bean_name, 'change_timer_fields', []);
    }

    /**
     * Update the fields in the related ChangeTimers module
     * @param array $changeTimerFields
     * @param array $fieldsToProcess
     * @throws SugarQueryException
     */
    protected function processChangeTimers(array $changeTimerFields, array $fieldsToProcess)
    {
        if (empty($changeTimerFields) || !is_array($changeTimerFields)) {
            return;
        }

        foreach ($fieldsToProcess as $field) {
            $this->updateChangeTimerRecord($field);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function save($check_notify = false)
    {
        if ($this->isNewlyResolved()) {
            $this->calculateResolutionHours();
        }

        $isUpdate = $this->isUpdate();
        $changeTimerFields = $this->getChangeTimerFields();
        if (!empty($changeTimerFields)) {
            // need to get the changed fields before calling parent::save()
            // because during parent::save(), BPM may affect $this->fetched_row
            $fieldsToProcess = $this->getCTFieldsToProcess($changeTimerFields, $isUpdate);
        }

        $id = parent::save($check_notify);

        if (!empty($changeTimerFields) && !empty($fieldsToProcess)) {
            $this->processChangeTimers($changeTimerFields, $fieldsToProcess);
        }

        return $id;
    }
    //END SUGARCRM flav=ent ONLY
}
