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
 * Class SugarFieldVoter
 * using Symfony's Voter to make decision to access control modules' fields
 * @package Sugarcrm\Sugarcrm\AccessControl
 */
class SugarFieldVoter extends SugarVoter
{
    /**
     * supported keys in access_config.php
     * @var array
     */
    protected $supportedKeys = [
        AccessControlManager::FIELDS_KEY,
    ];

    /**
     *
     * $subject is in array format, i.e.
     * ["FIELDS" => ["MODULE_NAME1" => "field_name"]]
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

        $module = array_keys($subject[AccessControlManager::FIELDS_KEY])[0];
        if (!is_string($subject[AccessControlManager::FIELDS_KEY][$module])) {
            return false;
        }

        $field = $subject[AccessControlManager::FIELDS_KEY][$module];
        // check if it is defined
        if (!isset($this->getProtectedList(AccessControlManager::FIELDS_KEY)[$module][$field])) {
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

        $module = array_keys($subject[AccessControlManager::FIELDS_KEY])[0];
        $field = $subject[AccessControlManager::FIELDS_KEY][$module];

        if (isset($this->getProtectedList(AccessControlManager::FIELDS_KEY)[$module][$field])
            && array_intersect($entitled, $this->getProtectedList(AccessControlManager::FIELDS_KEY)[$module][$field])
        ) {
            return true;
        }

        return false;
    }
}