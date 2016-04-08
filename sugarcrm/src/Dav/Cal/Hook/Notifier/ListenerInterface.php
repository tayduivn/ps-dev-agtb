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

namespace Sugarcrm\Sugarcrm\Dav\Cal\Hook\Notifier;

/**
 * Interface ListenerInterface
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Hook\Notifier
 */
interface ListenerInterface
{
    /**
     * Update listener on notify.
     *
     * @param string $beanModule Name of the module.
     * @param string $beanId record id.
     * @param array $data Prepared data for export/import.
     * @return boolean Result of the update operation.
     */
    public function update($beanModule, $beanId, $data);
}
