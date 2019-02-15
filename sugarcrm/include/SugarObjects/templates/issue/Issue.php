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
    public $time_to_resolution;
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
    private function isResolvedStatus(?string $status): bool
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
     * Returns the time it took to resolve this issue, calculating if needed.
     *
     * @return int The time, in minutes, it took to resolve this issue.
     * @throws Exception If $resolved_datetime is earlier than $date_entered.
     */
    public function calculateResolutionTime(): int
    {
        if (empty($this->time_to_resolution) && !is_numeric($this->time_to_resolution)) {
            $timeDate = Container::getInstance()->get(\TimeDate::class);
            $now = $timeDate->nowDb();

            // get the UNIX timestamps (seconds) for both resolved_datetime and date_entered,
            // substituting the current time if either does not exist.
            $resolvedDatetime = empty($this->resolved_datetime) ? $now : $this->resolved_datetime;
            $resolvedDatetime = $timeDate->fromDb($resolvedDatetime)->getTimestamp();
            $dateEntered = empty($this->date_entered) ? $now : $this->date_entered;
            $dateEntered = $timeDate->fromDb($dateEntered)->getTimestamp();

            $resolutionInterval = $resolvedDatetime - $dateEntered;
            $this->time_to_resolution = ((int) $resolutionInterval) / 60;

            if ($this->time_to_resolution < 0) {
                $msg = "$this->object_name cannot have a resolution time earlier than its creation time";
                throw new \Exception($msg);
            }
        }

        return (int) ceil($this->time_to_resolution);
    }

    /**
     * {@inheritDoc}
     */
    public function save($check_notify = false)
    {
        if ($this->isNewlyResolved()) {
            $this->calculateResolutionTime();
        }

        return parent::save($check_notify);
    }
    //END SUGARCRM flav=ent ONLY
}
