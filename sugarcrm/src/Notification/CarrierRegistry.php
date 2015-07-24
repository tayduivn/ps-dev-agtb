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
 * Class CarrierRegistry
 * @package Notification
 */
class CarrierRegistry
{

    /**
     * Returns object of CarrierRegistry, customized if it's present
     *
     * @return CarrierRegistry
     */
    public static function getInstance()
    {
        $class = \SugarAutoLoader::customClass('Sugarcrm\Sugarcrm\Notification\CarrierRegistry');

        return new $class();
    }

    /**
     * Function return Carrier modules(retrieve from cache)
     *
     * @return array
     */
    public function getCarriers()
    {
        return array_keys(self::getDictionary());
    }

    /**
     * Get Carrier class name
     *
     * @param string $moduleName
     * @return string|null
     */
    public function getCarrier($moduleName)
    {
        $carriers = $this->getDictionary();

        if (isset($carriers[$moduleName])) {
            \SugarAutoLoader::load($carriers[$moduleName]['path']);

            return $carriers[$moduleName]['class'];
        } else {
            return null;
        }
    }

    /**
     * Build dictionary array with carrier class names and paths
     *
     *  array(
     *      'moduleName' => array(
     *          'class' => 'className',
     *          'path' => 'pathToClass'
     *      )
     *  );
     *
     * @return array
     */
    protected function scan()
    {

        $dictionary = array();
        foreach ($this->getCarrierModules() as $module) {
            $pathCarrier = \SugarAutoLoader::existingCustomOne('modules/' . $module . '/Carrier.php');
            \SugarAutoLoader::load($pathCarrier);
            $dictionary[$module] = array(
                'path' => $pathCarrier,
                'class' => \SugarAutoLoader::customClass($module . 'Carrier')
            );
        }

        return $dictionary;
    }

    /**
     * Function scan $moduleList and return only Carrier modules
     *
     * @return array
     */
    protected function getCarrierModules()
    {
        return array_filter($GLOBALS['moduleList'], function ($module) {
            $file = 'modules/' . $module . '/Carrier.php';
            if (!\SugarAutoLoader::fileExists($file)) {
                return false;
            }
            $class = $module . 'Carrier';
            \SugarAutoLoader::load($file);

            return class_exists($class)
            && in_array('Sugarcrm\\Sugarcrm\\Notification\\Carrier\\CarrierInterface', class_implements($class));
        });
    }

    /**
     * Retrieving array(dictionary array with carrier class names and path to it)
     *
     * Retrieving array(dictionary array with carrier class names and path to it)
     * from cache file if it not exists rebuild cache
     *
     * @return array
     */
    protected function getDictionary()
    {

        $data = \SugarCache::instance()->carrierRegistry;
        if (empty($data)) {
            $data = $this->scan();
            \SugarCache::instance()->carrierRegistry = $data;
        }

        return $data;
    }

}
