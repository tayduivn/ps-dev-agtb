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

namespace Sugarcrm\Sugarcrm\Denormalization\TeamSecurity;

use BeanFactory;

final class State
{
    const ADMIN_CATEGORY = 'TeamSecurityDenorm';

    private $admin;

    private $isLoaded = false;

    public function __construct()
    {
        $this->admin = BeanFactory::newBean('Administration');
    }

    public function get($var)
    {
        if (!$this->isLoaded) {
            $this->admin->retrieveSettings(self::ADMIN_CATEGORY);
            $this->isLoaded = true;
        }

        $key = self::ADMIN_CATEGORY . '_' . $var;

        if (isset($this->admin->settings[$key])) {
            return $this->admin->settings[$key];
        }

        return null;
    }

    public function update($var, $value)
    {
        if (is_bool($value)) {
            $value = (int) $value;
        }

        // TODO: move to admin
        $this->admin->settings[self::ADMIN_CATEGORY . '_' . $var] = $value;
        $this->admin->saveSetting(self::ADMIN_CATEGORY, $var, $value);
    }
}
