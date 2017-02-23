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

namespace Sugarcrm\Sugarcrm\IdentityProvider\Authentication;

use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;

class Lockout
{
    const ENABLED = 2;

    /**
     * Is enabled lockout.
     * @return bool
     */
    public function isEnabled()
    {
        $config = $this->getPasswordSettings();
        return array_key_exists('lockoutexpiration', $config) && $config['lockoutexpiration'] == self::ENABLED;
    }

    /**
     * Calculation expire time of user.
     * @param User $user
     * @return bool|string
     */
    protected function calculateExpireTime(User $user)
    {
        $config = $this->getPasswordSettings();
        $logoutTime = $user->getSugarUser()->getPreference('logout_time');
        if (empty($logoutTime)) {
            return false;
        }
        $lockoutDurationMins = $config['lockoutexpirationtime'] * $config['lockoutexpirationtype'];
        $expireTime = $this->getTimeDate()
            ->fromDb($logoutTime)
            ->modify("+$lockoutDurationMins minutes")
            ->asDb();
        return $expireTime;
    }

    /**
     * Generation message of error.
     * @param User $user
     * @return string
     */
    public function getLockedMessage(User $user)
    {
        $expireTime = $this->calculateExpireTime($user);
        if (!$expireTime) {
            return $GLOBALS['app_strings']['EXCEPTION_UNKNOWN_EXCEPTION'];
        }
        $timeLeft = strtotime($expireTime) - strtotime($this->getTimeDate()->nowDb());

        $message = trim($GLOBALS['app_strings']['LBL_LOGIN_ATTEMPTS_OVERRUN'])
            . ' ' . trim($GLOBALS['app_strings']['LBL_LOGIN_LOGIN_TIME_ALLOWED'])
            . ' ';
        switch (true) {
            case (floor($timeLeft / 86400) != 0):
                $message .= floor($timeLeft / 86400) . $GLOBALS['app_strings']['LBL_LOGIN_LOGIN_TIME_DAYS'];
                break;
            case (floor($timeLeft / 3600) != 0):
                $message .= floor($timeLeft / 3600) . $GLOBALS['app_strings']['LBL_LOGIN_LOGIN_TIME_HOURS'];
                break;
            case (floor($timeLeft / 60) != 0):
                $message .= floor($timeLeft / 60) . $GLOBALS['app_strings']['LBL_LOGIN_LOGIN_TIME_MINUTES'];
                break;
            case (floor($timeLeft) != 0):
                $message .= floor($timeLeft) . $GLOBALS['app_strings']['LBL_LOGIN_LOGIN_TIME_SECONDS'];
                break;
        }
        return $message;
    }

    /**
     * Is user still locked.
     * @param User $user
     * @return bool
     */
    public function isUserStillLocked(User $user)
    {
        $expireTime = $this->calculateExpireTime($user);
        if (!$expireTime) {
            return false;
        }
        $nowTime = $this->getTimeDate()->nowDb();
        return $nowTime < $expireTime;
    }

    /**
     * Return Lockout Expiration Login from config.
     * @return int
     */
    public function getFailedLoginsCount()
    {
        $config = $this->getPasswordSettings();
        if (array_key_exists('lockoutexpirationlogin', $config)) {
            return intval($config['lockoutexpirationlogin']);
        } else {
            return 0;
        }
    }

    /**
     * @return \TimeDate
     */
    protected function getTimeDate()
    {
        return \TimeDate::getInstance();
    }

    /**
     * Return password's settings from sugar config.
     * @return mixed
     */
    protected function getPasswordSettings()
    {
        return \SugarConfig::getInstance()->get('passwordsetting');
    }
}
