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
     * {@inheritdoc}
     */
    public function vote(string $key, string $subject, ?string $value = null) : bool
    {
        if (empty($value) || !$this->supports($key)) {
            return true;
        }

        if (!isset($this->getProtectedList($key)[$subject][$value])) {
            return true;
        }

        $entitled = $this->getCurrentUserSubscriptions();

        if (array_intersect($entitled, $this->getProtectedList(AccessControlManager::RECORDS_KEY)[$subject][$value])) {
            return true;
        }

        return false;
    }
}
//END REQUIRED CODE DO NOT MODIFY
