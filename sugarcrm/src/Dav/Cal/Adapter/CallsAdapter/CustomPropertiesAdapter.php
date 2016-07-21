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

namespace Sugarcrm\Sugarcrm\Dav\Cal\Adapter\CallsAdapter;

use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\CustomPropertiesAdapterInterface;

/**
 * Class for processing Calls module properties by iCal protocol.
 *
 * Class CustomPropertiesAdapter
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Adapter\CallsAdapter
 */
class CustomPropertiesAdapter implements CustomPropertiesAdapterInterface
{
    const CALL_DIRECTION_EVENT_PROPERTY_NAME = 'CALL-DIRECTION';

    /**
     * @inheritdoc
     */
    public function setCollectionProperties(\CalDavEventCollection $collection, \User $user = null)
    {
        $event = $collection->getParent();
        $callDirection = $event->getCustomProperty(static::CALL_DIRECTION_EVENT_PROPERTY_NAME);
        $supportedCallDirections = $this->getCallDirections();

        if (!$callDirection || !in_array($callDirection, $supportedCallDirections)) {
            $callDirection = $user ? $user->getPreference('caldav_call_direction') :
                $this->getSugarConfig()->get('default_caldav_call_direction');

            $event->setCustomProperty(static::CALL_DIRECTION_EVENT_PROPERTY_NAME, $callDirection);
        }
    }

    /**
     * @param \Call $bean
     * @param \CalDavEventCollection|null $collection
     * @param \User|null $user
     */
    public function setBeanProperties($bean, \CalDavEventCollection $collection = null, \User $user = null)
    {
        if (!$bean->direction && $collection) {
            $event = $collection->getParent();
            $callDirection = $event->getCustomProperty(static::CALL_DIRECTION_EVENT_PROPERTY_NAME);
            $supportedCallDirections = $this->getCallDirections();

            if ($callDirection && in_array($callDirection, $supportedCallDirections)) {
                $bean->direction = $callDirection;
            } else {
                $bean->direction = $user ? $user->getPreference('caldav_call_direction') :
                    $this->getSugarConfig()->get('default_caldav_call_direction');
            }
        }
    }

    /**
     * Return Call Directions array.
     *
     * @return array
     */
    protected function getCallDirections()
    {
        return array_keys(translate('call_direction_dom'));
    }

    /**
     * Factory method for SugarConfig
     *
     * @return \SugarConfig
     */
    protected function getSugarConfig()
    {
        return \SugarConfig::getInstance();
    }
}
