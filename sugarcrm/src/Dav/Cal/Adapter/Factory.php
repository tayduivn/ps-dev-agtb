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
namespace Sugarcrm\Sugarcrm\Dav\Cal\Adapter;

/**
 * Factory class to get supported modules and adapter for specified module if it's present
 * Class Factory
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Adapter
 */
class Factory
{
    /**
     * returns adapter for specified module
     * @param string $moduleName
     * @return bool|AdapterInterface
     */
    public function getAdapter($moduleName)
    {
        $adapter = false;

        if (!$adapter) {
            $adapterClass = \SugarAutoLoader::customClass($this->getCustomClassPath($moduleName));
            if (class_exists($adapterClass)) {
                if ($this->isImplementsCalDavInterface($adapterClass)) {
                    $adapter = new $adapterClass();
                }
            }
        }

        return $adapter;
    }

    /**
     * return modules and adapters that exists
     * @return string[] names of supported modules
     */
    public function getSupportedModules()
    {
        $modules = array();
        $modulesList = $this->getModulesList();
        foreach ($modulesList as $moduleName) {
            if ($this->getAdapter($moduleName)) {
                $modules[] = $moduleName;
            }
        }
        return $modules;
    }


    /**
     * get Adapter path using namespaces
     * @param $class
     * @return string
     */
    protected function getCustomClassPath($class)
    {
        return '\Sugarcrm\\Sugarcrm\\Dav\\Cal\\Adapter\\' . $class;
    }

    /**
     * check if object has implements of AdapterInterface
     * @param mixed $adapterClass object or class name
     * @return bool
     */
    protected function isImplementsCalDavInterface($adapterClass)
    {
        return in_array('Sugarcrm\Sugarcrm\Dav\Cal\Adapter\AdapterInterface', class_implements($adapterClass));
    }

    /**
     * return instance of adapter factory
     * @return Factory
     */
    public static function getInstance()
    {
        $class = \SugarAutoLoader::customClass('Sugarcrm\\Sugarcrm\\Dav\\Cal\\Adapter\\Factory');
        return new $class();
    }

    protected function getModulesList()
    {
        return $GLOBALS['moduleList'];
    }

}
