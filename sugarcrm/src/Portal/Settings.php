<?php declare(strict_types=1);
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

// FILE SUGARCRM flav=ent ONLY

namespace Sugarcrm\Sugarcrm\Portal;

class Settings
{
    /**
     * @return bool
     */
    public function isPortalAllowed() : bool
    {
        // TODO check that the customer is licensed for the portal
        return true;
    }

    /**
     * @return bool
     */
    public function isDeflectionEnabled() : bool
    {
        if ($this->isPortalAllowed()) {
            // TODO some more checks here
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function allowCasesForContactsWithoutAccount() : bool
    {
        return false;
    }
}
