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

namespace Sugarcrm\Sugarcrm\Notification\Carrier;

/**
 * Implementation of interface provide configuration for carrier.
 *
 * Interface Carrier\ConfigurableInterface
 * @package Sugarcrm\Sugarcrm\Notification
 */
interface ConfigurableInterface extends CarrierInterface
{
    /**
     * Return layout which configure current carrier.
     *
     * @return string layout name
     */
    public function getConfigLayout();

    /**
     *  Is configured current carrier.
     *
     * @return boolean is configured
     */
    public function isConfigured();
}
