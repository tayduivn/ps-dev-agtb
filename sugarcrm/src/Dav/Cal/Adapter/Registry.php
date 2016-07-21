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
 * Factory producer for modules adapters factory.
 *
 * Class Registry
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Adapter
 */
class Registry
{
    /**
     * Returns self.
     *
     * @return Registry
     */
    public static function getInstance()
    {
        return new static();
    }

    /**
     * Returns adapter factory for specified module.
     *
     * @param string $moduleName
     * @return null|FactoryInterface
     */
    public function getFactory($moduleName)
    {
        $factoryClass = $this->getClassName($moduleName);
        return $factoryClass ? new $factoryClass() : null;
    }

    /**
     * Returns an array of modules names which have adapter factory.
     *
     * @return string[] names of supported modules
     */
    public function getSupportedModules()
    {
        $modules = array();
        $modulesList = $this->getModulesList();
        foreach ($modulesList as $moduleName) {
            if ($this->getClassName($moduleName)) {
                $modules[] = $moduleName;
            }
        }
        return $modules;
    }

    /**
     * Gets Adapter factory class name or false if it does not exist.
     *
     * @param $moduleName
     * @return string
     */
    protected function getClassName($moduleName)
    {
        $className = \SugarAutoLoader::customClass(
            'Sugarcrm\Sugarcrm\Dav\Cal\Adapter\\' . $moduleName . 'Adapter\Factory'
        );
        if (class_exists($className)) {
            if (!in_array('Sugarcrm\Sugarcrm\Dav\Cal\Adapter\FactoryInterface', class_implements($className))) {
                \LoggerManager::getLogger()->warning("The factory class $className does not 
                implement Sugarcrm\\Sugarcrm\\Dav\\Cal\\Adapter\\FactoryInterface");
            }

            return $className;
        }
        return '';
    }

    /**
     * Gets modules list.
     *
     * @return array
     */
    protected function getModulesList()
    {
        return $GLOBALS['moduleList'];
    }
}
