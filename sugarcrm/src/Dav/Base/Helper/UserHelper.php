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

namespace Sugarcrm\Sugarcrm\Dav\Base\Helper;

use Sugarcrm\Sugarcrm\Dav\Cal;

/**
 * Class UserHelper
 * @package Sugarcrm\Sugarcrm\Dav\Base\Helper
 */
class UserHelper
{
    /**
     * Dav principal prefix path
     * @var string
     */
    protected $prefixPath = '';

    /**
     * Set DAV prefix path
     * @param string $prefixPath DAV principal prefix
     */
    public function setPrincipalPrefix($prefixPath)
    {
        if (!empty($prefixPath)) {
            if (strpos($prefixPath, '/') === false) {
                $prefixPath .= '/';
            }
            $this->prefixPath = $prefixPath;
        }
    }

    /**
     * Retrieve DAV prefixPath
     * @return string
     */
    public function getPrincipalPrefix()
    {
        return $this->prefixPath;
    }

    /**
     * Retrive user bean by username
     * @param string $userName SugarCRM username
     * @return \SugarBean | null
     */
    public function getUserByUserName($userName)
    {
        $user = $this->getUserBean();
        $userID = $user->retrieve_user_id($userName);
        if (!$userID) {
            return null;
        }

        return $user->retrieve($userID);
    }

    /**
     * Converts SugarCRM user into DAV principals path format principalprefix/username
     * @param \User $user
     * @return string
     */
    public function getPrincipalStringByUser(\User $user)
    {
        $prefixPath = $this->getPrincipalPrefix();
        if (empty($prefixPath)) {
            return $user->user_name;
        }

        return $prefixPath . $user->user_name;
    }

    /**
     * Retrieve fields array for name formating
     * @param \User $user
     * @return array
     */
    public function getNameFormatFields(\User $user)
    {
        $localization = new \Localization();

        return $localization->getNameFormatFields($user);
    }

    /**
     * Converts SugarCRM user into DAV formatted array
     * @param \User $user
     * @return array
     */
    public function getPrincipalArrayByUser(\User $user)
    {
        return array(
            'id' => $user->id,
            'uri' => $this->getPrincipalStringByUser($user),
            '{DAV:}displayname' => $user->full_name,
            '{http://sabredav.org/ns}email-address' => $user->email1,
        );
    }

    /**
     * Retrieve user from DAV principal string
     * @param string $principal DAV principal path (principal/user)
     * @return \User | null
     */
    public function getUserByPrincipalString($principal)
    {
        $principalComponents = explode('/', $principal);
        if (isset($principalComponents[1])) {
            $userName = $principalComponents[1];
            $this->setPrincipalPrefix($principalComponents[0]);
        } else {
            $userName = $principalComponents[0];
            $this->setPrincipalPrefix('');
        }

        $user = $this->getUserByUserName($userName);

        if (!$user) {
            return null;
        }

        return $user;
    }

    /**
     * Get SugarCRM Users bean
     * @return null|\User
     */
    public function getUserBean()
    {
        return \BeanFactory::getBean('Users');
    }

    /**
     * Retrieve default calendar for user.
     * If calendar doesn't exists create it
     *
     * @param string $principalUri
     * @return array | null
     */
    public function getCalendars($principalUri)
    {
        $user = $this->getUserByPrincipalString($principalUri);
        if ($user->load_relationship('caldav_calendars')) {

            $calendarBeans = $user->caldav_calendars->getBeans();
            if ($calendarBeans) {
                return $calendarBeans;
            }

            $calendarBean = \BeanFactory::getBean('CalDavCalendars');
            $calendar = $calendarBean->createDefaultForUser($user);

            if ($calendar) {
                return array($calendar);
            }
        }

        return null;
    }
}
