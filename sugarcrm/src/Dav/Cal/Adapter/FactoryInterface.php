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

namespace Sugarcrm\Sugarcrm\Dav\Cal\Adapter;

/**
 * Interface for getting modules Data and CustomProperties adapters.
 *
 * Interface FactoryInterface
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Adapter
 */
interface FactoryInterface
{
    /**
     * Returns data adapter for its module.
     *
     * @return DataAdapterInterface
     */
    public function getAdapter();

    /**
     * Returns custom properties adapter for its module.
     *
     * @return CustomPropertiesAdapterInterface
     */
    public function getPropertiesAdapter();
}
