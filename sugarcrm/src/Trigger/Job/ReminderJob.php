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

namespace Sugarcrm\Sugarcrm\Trigger\Job;

use Sugarcrm\Sugarcrm\Trigger\Reminder;

/**
 * Class ReminderJob extracts data and puts it to @see Sugarcrm\Sugarcrm\Trigger\Reminder class.
 * @package Sugarcrm\Sugarcrm\Trigger\Job
 */
class ReminderJob implements \RunnableSchedulerJob
{
    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    public function setJob(\SchedulersJob $job)
    {

    }

    /**
     * Loads call or meeting bean and user beans.
     * Then passes it to Reminder::remind.
     *
     * @param string $data JSON-encoded data
     * @return boolean
     */
    public function run($data)
    {
        $data = json_decode($data, true);
        $this->getReminder()->remind($data['module'], $data['beanId'], $data['userId']);
        return true;
    }

    /**
     * Factory method for Reminder class.
     *
     * @return Reminder
     * @codeCoverageIgnore
     */
    protected function getReminder()
    {
        return new Reminder();
    }
}
