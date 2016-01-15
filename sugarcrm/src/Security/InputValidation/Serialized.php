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

namespace Sugarcrm\Sugarcrm\Security\InputValidation;

class Serialized
{
    /**
     * Performs unserialization. Accepts all types except Objects
     *
     * @param string $value Serialized value of any type except Object
     * @return mixed False if Object, converted value for other cases
     */
    public static function unserialize($value)
    {
        preg_match('/[oc]:\d+:/i', $value, $matches);

        if (count($matches)) {
            \LoggerManager::getLogger()->warning('Objects unserialization is not allowed');
            return false;
        }

        return unserialize($value);
    }
}
