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

namespace Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Format;

/**
 * Format sugar bean to user principal path
 * Class UserPrincipalStrategy
 * @package Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Format
 */
class UserPrincipalStrategy extends PrincipalStrategy
{
    /**
     * Format SugarBean info in needed format
     * @param \SugarBean $bean
     * @return string
     * @throws \InvalidArgumentException
     */
    public function formatUri(\SugarBean $bean)
    {
        if (!$bean instanceof \User) {
            throw new \InvalidArgumentException('Bean must be instance of \User');
        }

        return $this->prefixPath . $bean->user_name;
    }
}
