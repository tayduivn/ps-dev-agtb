<?php
/*
* Your installation or use of this SugarCRM file is subject to the applicable
* terms available at
* http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
* If you do not agree to all of the applicable terms or do not have the
* authority to bind the entity as an authorized representative, then do not
* install or use this SugarCRM file.
*
* Copyright (C) SugarCRM Inc. All rights reserved.
*/

namespace Sugarcrm\Sugarcrm\Notification\Emitter\Bean;

use Sugarcrm\Sugarcrm\Notification\EmitterRegistry;

/**
 * Class Hook.
 * Is used to perform Notifications Event Trigger logic after some event has occurred in Sugar Bean.
 * @package Sugarcrm\Sugarcrm\Notification\Emitter\Bean
 */
class Hook
{
    /**
     * Trigger a new Notification event after SugarBean is saved.
     * Method is called via Sugar logic-hooks mechanism.
     *
     * @param \SugarBean $bean Bean object.
     * @param string $event Logic hook event name.
     * @param array $arguments Arguments about event from logic-hook call.
     * @return bool Result of method execution
     */
    public function hook(\SugarBean $bean, $event, $arguments)
    {
        $result = false;
        $emitterRegistry = $this->getEmitterRegistry();
        $moduleEmitter = $emitterRegistry->getModuleEmitter($bean->module_name);
        if ($moduleEmitter && $moduleEmitter instanceof BeanEmitterInterface) {
            $result = $moduleEmitter->exec($bean, $event, $arguments);
        }
        return $result;
    }

    /**
     * Get EmitterRegistry.
     * @return EmitterRegistry Registry instance.
     */
    protected function getEmitterRegistry()
    {
        return EmitterRegistry::getInstance();
    }
}
