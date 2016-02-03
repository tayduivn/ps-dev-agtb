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

namespace Sugarcrm\Sugarcrm\Trigger\ReminderManager;

use Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper;

/**
 * Class Helper
 * @package Sugarcrm\Sugarcrm\Trigger\ReminderManager
 */
class Helper
{

    /**
     * Is date in future
     *
     * @param \DateTime $dateTime for checking
     * @return bool is $dateTime in future
     */
    public static function isInFuture(\DateTime $dateTime)
    {
        $now = new \DateTime();
        return $dateTime->getTimestamp() > $now->getTimestamp();
    }

    /**
     * Calculates reminder date and time.
     *
     * @param \Call|\Meeting|\SugarBean $bean event for which calculate reminder time.
     * @param \User $user user for which calculate reminder time.
     * @return \DateTime|null calculated reminder time.
     */
    public static function calculateReminderDateTime(\SugarBean $bean, \User $user)
    {
        if ($bean->assigned_user_id == $user->id) {
            $reminderTime = (int)$bean->reminder_time;
        } else {
            $reminderTime = (int)$user->getPreference('reminder_time');
        }
        if ($reminderTime < 0) {
            return null;
        }

        $dateTimeHelper = new DateTimeHelper();
        $reminderDateTime = $dateTimeHelper->sugarDateToUTC($bean->date_start);
        $reminderDateTime->modify('- ' . $reminderTime . ' seconds');
        return $reminderDateTime;
    }
}
