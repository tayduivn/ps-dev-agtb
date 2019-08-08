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

namespace Sugarcrm\Sugarcrm\Visibility\Portal;

use Sugarcrm\Sugarcrm\Portal\Factory as PortalFactory;

class Dashboards extends Portal
{
    public function addVisibilityQuery(\SugarQuery $query, array $options = [])
    {
        if (PortalFactory::getInstance('Settings')->isServe()) {
            $dashboardId = '0ca2d773-0bb3-4bf3-ae43-68569968af57';
        } else {
            $dashboardId = '0ca2d773-3dc6-70d9-fa91-68569968af57';
        }
        $query->where()->equals($options['table_alias'] . '.id', $dashboardId);
    }
}
