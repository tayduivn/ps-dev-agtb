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

use Sugarcrm\Sugarcrm\Notification\Exception\LogicException;

/**
 * Class CarrierRegistry
 * @package Notification
 */
class CarrierRegistry
{

    /**
     * Path to file in which store cached dictionary array
     */
    const CACHE_FILE = 'src/Notification/carrierRegistry.php';

    /**
     *
     */
    const CACHE_VARIABLE = 'carrierRegistry';

    /**
     * Initializing carriers list
     *
     * @throws LogicException if cone of Carriers not implement CarrierInterface
     */
    public function __construct()
    {
        foreach ($GLOBALS['sugar_config']['notification']['carriers'] as $name => $class) {
            try {
                $interfaces = class_implements($class);
            } catch (\Exception $e) {
                $interfaces = array();
            }

            if (!in_array('Sugarcrm\Sugarcrm\Notification\Carrier\CarrierInterface', $interfaces)) {
                throw new LogicException('Carrier should implement CarrierInterface');
            }

            $this->registry[$name] = $class;
        }
    }

    /**
     * Function return Carrier modules(retrieve from cache)
     *
     * @return array
     */
    public static function getCarriers()
    {
        return array_keys(self::getDictionary());
    }

    /**
     * Get Carrier class name
     *
     * @param string $moduleName
     * @return string|null
     */
    public static function getCarrier($moduleName)
    {
        $carriers = self::getDictionary();
        @require_once $carriers[$moduleName]['path'];

        return $carriers[$moduleName]['class'];
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
    protected static function scan()
    {

        $dictionary = array();
        foreach (self::getCarrierModules() as $module) {
            $pathCarrier = \SugarAutoLoader::existingCustomOne('modules/' . $module . '/Carrier.php');
            require_once $pathCarrier;
            $dictionary[$module] = array(
                'path' => $pathCarrier,
                'class' => \SugarAutoLoader::customClass($module . 'Carrier')
            );
        }

        return $dictionary;
    }

    /**
     * Retrieving array(dictionary array with carrier class names and path to it) from cache file if it exists
     *
     * @return array|null
     */
    protected static function getCache()
    {
        $path = sugar_cached(self::CACHE_FILE);
        @include($path);
        if (isset(${self::CACHE_VARIABLE})) {
            return ${self::CACHE_VARIABLE};
        } else {
            return null;
        }
    }

    /**
     * Saving array(dictionary array with carrier class names and path to it) to cache file
     *
     * @param array data
     */
    protected static function setCache($data)
    {
        create_cache_directory(self::CACHE_FILE);
        write_array_to_file(self::CACHE_VARIABLE, $data, sugar_cached(self::CACHE_FILE));
    }

    /**
     * Function scan $moduleList and return only Carrier modules
     *
     * @return array
     */
    protected static function getCarrierModules()
    {
        return array_filter($GLOBALS['moduleList'], function ($module) {
            $file = 'modules/' . $module . '/Carrier.php';
            if (!\SugarAutoLoader::fileExists($file)) {
                return false;
            }
            $class = $module . 'Carrier';
            require_once $file;

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
    protected static function getDictionary()
    {
        $data = self::getCache();
        if (empty($data)) {
            $data = self::scan();
            self::setCache($data);
        }

        return $data;
    }

}


