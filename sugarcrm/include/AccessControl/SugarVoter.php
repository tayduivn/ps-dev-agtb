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

namespace Sugarcrm\Sugarcrm\AccessControl;

use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

// This section of code is a portion of the code referred
// to as Critical Control Software under the End User
// License Agreement.  Neither the Company nor the Users
// may modify any portion of the Critical Control Software.

/**
 * Class SugarVoter, this class does access control to module and dashlet
 * using Symfony's Voter to make decision to access control modules, dashlets and reports
 * @package Sugarcrm\Sugarcrm\AccessControl
 */
class SugarVoter extends Voter
{
    /**
     * access control configuration file.
     */
    const ACCESS_CONFIG_FILE = 'access_config.json';
    /**
     * list of valid subscriptions
     * @var array
     */
    protected $subscriptions = [];

    /**
     * access configuration
     * @var array
     */
    protected $access_config = [];

    /**
     * supported keys in access_config.php
     * @var array
     */
    protected $supportedKeys = [
        AccessControlManager::MODULES_KEY,
        AccessControlManager::DASHLETS_KEY,
    ];

    /**
     * get valid subscriptions for current user,
     * a valid subscription is:
     * 1. current product has subbscription
     * 2. current user has the subscription type
     *
     * @return array
     * @throws \Exception
     */
    protected function getCurrentUserSubscriptions()
    {
        if (!empty($this->subscriptions)) {
            return $this->subscriptions;
        }

        global $current_user;

        if (empty($current_user)) {
            throw new \Exception('User is not logged in');
        }

        // check subscriptions, TBD
        $subscribed = ['SUGAR_SERVE', 'CURRENT'];

        $userLicenseTypes = $current_user->getLicenseType();
        $this->subscriptions = array_intersect($subscribed, $userLicenseTypes);
        return $this->subscriptions;
    }

    /**
     * get protected list, let children classes to override
     *
     * @param string $key
     * @return array
     */
    protected function getProtectedList(string $key)
    {
        if (empty($this->access_config)) {
            $this->loadAccessConfig();
        }

        if (isset($this->access_config[$key])) {
            return $this->access_config[$key];
        }

        return [];
    }

    /**
     * load access config from disk
     */
    protected function loadAccessConfig()
    {
        if (file_exists(self::ACCESS_CONFIG_FILE)) {
            $accConfig = file_get_contents(self::ACCESS_CONFIG_FILE);
            $this->access_config = json_decode($accConfig, true);
            return;
        }

        throw new \Exception("access config file doesn't exist: " . self::ACCESS_CONFIG_FILE);
    }

    /**
     *
     * $subject is in array format, i.e.
     * ['MODULE' => 'SECURED_MODULE_NAME']
     * or
     * ['DASHLET' => 'SECURED_DASHLET_NAME']
     *
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        if (!is_array($subject) || count($subject) != 1 || empty(array_keys($subject)[0])) {
            return false;
        }

        $key = array_keys($subject)[0];

        if (!in_array($key, $this->supportedKeys)) {
            return false;
        }

        if (!isset($this->getProtectedList($key)[array_values($subject)[0]])) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $entitled = $this->getCurrentUserSubscriptions();
        if (isset($this->getProtectedList(array_keys($subject)[0])[array_values($subject)[0]])
            && array_intersect($entitled, $this->getProtectedList(array_keys($subject)[0])[array_values($subject)[0]])
        ) {
            return true;
        }

        return false;
    }
}
