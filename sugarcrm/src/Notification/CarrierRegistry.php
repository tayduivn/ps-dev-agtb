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

namespace Sugarcrm\Sugarcrm\Notification;

use Sugarcrm\Sugarcrm\Notification\Carrier\CarrierInterface;
use Sugarcrm\Sugarcrm\Logger\LoggerTransition;

/**
 * Class CarrierRegistry
 * @package Notification
 */
class CarrierRegistry
{

    /**
     * Path to file in which store cached dictionary array
     */
    const CACHE_FILE = 'Notification/carrierRegistry.php';

    /**
     * Variable name in which store cached dictionary array
     */
    const CACHE_VARIABLE = 'carrierRegistry';

    /**
     * Full path to CarrierInterface with nameSpace
     */
    const CARRIER_INTERFACE = 'Sugarcrm\\Sugarcrm\\Notification\\Carrier\\CarrierInterface';

    /**
     * @var LoggerTransition
     */
    protected $logger;

    /**
     * Set up logger.
     */
    public function __construct()
    {
        $this->logger = new LoggerTransition(\LoggerManager::getLogger());
    }

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
     * @return string[]
     */
    public function getCarriers()
    {
        $carriers = array_keys($this->getDictionary());
        $this->logger->debug('NC: All found Carrier names are: ' . var_export($carriers, true));
        return $carriers;
    }

    /**
     * Get Carrier by module name
     *
     * @param string $moduleName
     * @return CarrierInterface|null
     */
    public function getCarrier($moduleName)
    {
        $carriers = $this->getDictionary();

        if (isset($carriers[$moduleName])) {
            \SugarAutoLoader::load($carriers[$moduleName]['path']);
            $class = $carriers[$moduleName]['class'];

            $this->logger->debug("NC: $class Carrier was found for $moduleName");

            return new $class();
        } else {
            $this->logger->notice("NC: no Carrier found for $moduleName");
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
        $this->logger->debug("NC: Carrier registry builds dictionary array with carrier class names and paths");

        $dictionary = array();
        foreach (array_merge($GLOBALS['moduleList'], $GLOBALS['modInvisList']) as $module) {
            $path = 'modules/' . $module . '/Carrier.php';
            if (!\SugarAutoLoader::fileExists($path)) {
                $this->logger->notice("NC: There is no carrier file found in $path");
                continue;
            }
            \SugarAutoLoader::load($path);
            $class = $module . 'Carrier';

            if (!$this->isCarrierClass($class)) {
                $this->logger->notice("NC: Carrier $class is not of a carrier class");
                continue;
            }

            $customPath = \SugarAutoLoader::existingCustomOne($path);
            \SugarAutoLoader::load($customPath);
            $customClass = \SugarAutoLoader::customClass($class);
            if ($this->isCarrierClass($customClass)) {
                $this->logger->debug("NC: Custom Carrier $customClass was found");
                $class = $customClass;
                $path = $customPath;
            }

            $this->logger->debug("NC: Carrier $class with path = '$path' will be used");

            $dictionary[$module] = array(
                'path' => $path,
                'class' => $class
            );
        }

        return $dictionary;
    }

    /**
     * Does class implement CarrierInterface
     *
     * @param string $class
     * @return bool
     */
    protected function isCarrierClass($class)
    {
        return class_exists($class) && in_array(static::CARRIER_INTERFACE, class_implements($class));
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
        $data = $this->getCache();
        if (is_null($data)) {
            $this->logger->debug("NC: Carrier registry: no cache found, proceed to scanning files");
            $data = $this->scan();
            $this->setCache($data);
        }

        return $data;
    }

    /**
     * Retrieving array(dictionary array with carrier class names and path to it) from cache file if it exists
     *
     * @return array|null
     */
    protected function getCache()
    {
        $path = sugar_cached(static::CACHE_FILE);
        $this->logger->debug("NC: Carrier registry tries to get carriers data from cache $path file");

        if (\SugarAutoLoader::fileExists($path)) {
            include($path);
        }

        if (isset(${static::CACHE_VARIABLE})) {
            return ${static::CACHE_VARIABLE};
        } else {
            return null;
        }
    }

    /**
     * Saving array(dictionary array with carrier class names and path to it) to cache file
     *
     * @param array $data
     */
    protected function setCache($data)
    {
        $this->logger->info(
            'NC: Carrier registry sets cache file ' . static::CACHE_FILE . ' with data: '
            . var_export($data, true)
        );
        create_cache_directory(static::CACHE_FILE);
        write_array_to_file(static::CACHE_VARIABLE, $data, sugar_cached(static::CACHE_FILE));
    }
}
