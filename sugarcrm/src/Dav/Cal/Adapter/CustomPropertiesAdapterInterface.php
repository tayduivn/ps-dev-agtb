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
 * Interface for import and export module-specific params.
 *
 * Interface CustomPropertiesAdapterInterface
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Adapter
 */
interface CustomPropertiesAdapterInterface
{
    /**
     * Sets module-specific properties to vCal Event using user preferences or default values.
     *
     * @param \CalDavEventCollection $collection
     * @param \User|null $user
     */
    public function setCollectionProperties(\CalDavEventCollection $collection, \User $user = null);

    /**
     * Sets bean properties from vCal Event.
     *
     * @param \SugarBean $bean
     * @param \CalDavEventCollection|null $collection
     * @param \User|null $user
     */
    public function setBeanProperties($bean, \CalDavEventCollection $collection = null, \User $user = null);
}
