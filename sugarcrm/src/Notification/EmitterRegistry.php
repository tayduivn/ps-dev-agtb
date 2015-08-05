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

namespace Sugarcrm\Sugarcrm\Notification;

/**
 * Class EmitterRegistry.
 * Is a registry of all system and custom Notification emitters.
 * Use it to get various Emitters.
 * @package Sugarcrm\Sugarcrm\Notification
 */
class EmitterRegistry
{
    /**
     * Get object of EmitterRegistry, customized if it's present.
     * @return EmitterRegistry Registry instance.
     */
    public static function getInstance()
    {
        $class = \SugarAutoLoader::customClass('Sugarcrm\\Sugarcrm\\Notification\\EmitterRegistry');
        return new $class();
    }

    /**
     * Get an Application-level Emitter, customized if it's present.
     * @return EmitterInterface Application-level Emitter.
     */
    public function getApplicationEmitter()
    {
        $class = \SugarAutoLoader::customClass('Sugarcrm\\Sugarcrm\\Notification\\ApplicationEmitter\\Emitter');
        return new $class();
    }

    /**
     * Get a Bean-level Emitter.
     */
    public function getBeanEmitter()
    {
        // ToDo: add code.
    }

    /**
     * Get a Module-level Emitter.
     * @param string $moduleName
     */
    public function getModuleEmitter($moduleName)
    {
        // ToDo: add code.
    }

    /**
     * Get all Module-level Emitters.
     * @return array all Module-level Emitters.
     */
    public function getModuleEmitters()
    {
        // ToDo: add return EmitterInterface[].
        return array();
    }
}
