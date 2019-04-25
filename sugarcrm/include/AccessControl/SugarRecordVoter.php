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
 * Class SugarRecordVoter
 * using Symfony's Voter to make decision to access control modules' fields
 * @package Sugarcrm\Sugarcrm\AccessControl
 */
class SugarRecordVoter extends SugarVoter
{
    /**
     * supported keys in access_config.php
     * @var array
     */
    protected $supportedKeys = [
        AccessControlManager::RECORDS_KEY,
    ];

    /**
     *
     * $subject is in array format, i.e.
     * ["RECORDS" => ["MODULE_NAME1" => "field_name"]]
     *
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        if (!is_array($subject) || count($subject) != 1 || empty(array_keys($subject)[0])) {
            return false;
        }

        if (!in_array(array_keys($subject)[0], $this->supportedKeys)) {
            return false;
        }

        $module = array_keys($subject[AccessControlManager::RECORDS_KEY])[0];
        if (!is_string($subject[AccessControlManager::RECORDS_KEY][$module])) {
            return false;
        }

        $record = $subject[AccessControlManager::RECORDS_KEY][$module];
        // check if it is defined
        if (!isset($this->getProtectedList(AccessControlManager::RECORDS_KEY)[$module][$record])) {
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

        $module = array_keys($subject[AccessControlManager::RECORDS_KEY])[0];
        $record = $subject[AccessControlManager::RECORDS_KEY][$module];

        if (isset($this->getProtectedList(AccessControlManager::RECORDS_KEY)[$module][$record])
            && array_intersect($entitled, $this->getProtectedList(AccessControlManager::RECORDS_KEY)[$module][$record])
        ) {
            return true;
        }

        return false;
    }
}
