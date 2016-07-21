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

namespace Sugarcrm\Sugarcrm\Dav\Cal\Adapter\MeetingsAdapter;

use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\CustomPropertiesAdapterInterface;

/**
 * Class for processing Meetings module properties by iCal protocol.
 *
 * Class CustomPropertiesAdapter
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Adapter\MeetingsAdapter
 */
class CustomPropertiesAdapter implements CustomPropertiesAdapterInterface
{
    /**
     * @inheritdoc
     */
    public function setCollectionProperties(\CalDavEventCollection $collection, \User $user = null)
    {

    }

    /**
     * @inheritdoc
     */
    public function setBeanProperties($bean, \CalDavEventCollection $collection = null, \User $user = null)
    {

    }
}
