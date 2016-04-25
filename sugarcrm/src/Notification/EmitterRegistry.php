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

use Sugarcrm\Sugarcrm\Notification\Emitter\Bean\BeanEmitterInterface;
use Sugarcrm\Sugarcrm\Logger\LoggerTransition;

/**
 * Class EmitterRegistry.
 * Is a registry of all system and custom Notification emitters.
 * Use it to get various Emitters.
 * @package Sugarcrm\Sugarcrm\Notification
 */
class EmitterRegistry
{
    /**
     * Path to file in which store cached dictionary array
     */
    const CACHE_FILE = 'Notification/emitterRegistry.php';

    /**
     * Variable name in which store cached dictionary array
     */
    const CACHE_VARIABLE = 'emitterRegistry';

    /**
     * Full path to BeanEmitterInterface with nameSpace
     */
    const BEAN_EMITTER_INTERFACE = 'Sugarcrm\\Sugarcrm\\Notification\\Emitter\\Bean\\BeanEmitterInterface';

    /**
     * Full path to EmitterInterface with nameSpace
     */
    const EMITTER_INTERFACE = 'Sugarcrm\\Sugarcrm\\Notification\\EmitterInterface';

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
        $class = \SugarAutoLoader::customClass('Sugarcrm\\Sugarcrm\\Notification\\Emitter\\Application\\Emitter');
        return new $class();
    }

    /**
     * Get a Bean-level Emitter, customized if it's present.
     * @return EmitterInterface Bean-level Emitter.
     */
    public function getBeanEmitter()
    {
        $class = \SugarAutoLoader::customClass('Sugarcrm\\Sugarcrm\\Notification\\Emitter\\Bean\\Emitter');
        return new $class();
    }

    /**
     * Get a Module-level Emitter.
     * @param string $moduleName
     * @return BeanEmitterInterface|null Emitter instance
     */
    public function getModuleEmitter($moduleName)
    {
        $emitters = $this->getDictionary();

        if (isset($emitters[$moduleName])) {
            \SugarAutoLoader::load($emitters[$moduleName]['path']);
            $class = $emitters[$moduleName]['class'];

            $this->logger->debug("NC: Emitter registry instantiates '$class' emitter");

            if (in_array(static::BEAN_EMITTER_INTERFACE, class_implements($class))) {
                return new $class($this->getBeanEmitter());
            } else {
                return new $class();
            }

        } else {
            $this->logger->notice("NC: No emitter found for $moduleName");
            return null;
        }
    }

    /**
     * Get all Module-level Emitters.
     * @return array all Module-level Emitters.
     */
    public function getModuleEmitters()
    {
        $emitters = array_keys($this->getDictionary());
        $this->logger->debug('NC: All found Emitters are: ' . var_export($emitters, true));
        return $emitters;
    }

    /**
     * Build dictionary array(array with module-level emitters class names and path to it)
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
        $this->logger->debug(
            "NC: Emitter registry builds dictionary with module-level emitters class names and path to it"
        );

        $dictionary = array();
        foreach ($GLOBALS['moduleList'] as $module) {
            if (!array_key_exists($module, $GLOBALS['beanList'])) {
                continue;
            }

            $moduleEmitters = array();

            $path = 'modules/' . $module . '/Emitter.php';
            $class = $GLOBALS['beanList'][$module] . 'Emitter';

            $this->logger->debug("NC: Emitter registry scan looks for $module module's Emitter");

            \SugarAutoLoader::load($path);
            if ($this->isEmitterClass($class)) {
                $this->logger->debug("NC: Emitter $class was found in '$path' path");

                $moduleEmitters[] = array(
                    'path' => $path,
                    'class' => $class,
                );
            }

            $customPath = $this->customPath($path);
            if (!empty($customPath)) {
                \SugarAutoLoader::load($customPath);
                $customClass = $this->customClass($class);
                if ($this->isEmitterClass($customClass)) {
                    $this->logger->debug("NC: Custom Emitter $customClass was found in '$customPath' path");
                    $moduleEmitters[] = array(
                        'path' => $customPath,
                        'class' => $customClass,
                    );
                }
            }

            if (count($moduleEmitters) > 0) {
                $dictionary[$module] = array_pop($moduleEmitters);
            }
        }

        return $dictionary;
    }

    /**
     * Does class implement EmitterInterface
     *
     * @param string $class name for checking
     * @return bool is class implements EmitterInterface
     */
    protected function isEmitterClass($class)
    {
        return class_exists($class) && in_array(static::EMITTER_INTERFACE, class_implements($class));
    }

    /**
     * Retrieving array(dictionary array with module-level emitters class names and path to it)
     *
     * Retrieving array(dictionary array with module-level emitters class names and path to it)
     * from cache file if it not exists rebuild cache
     *
     * @return array
     */
    protected function getDictionary()
    {
        $data = $this->getCache();
        if (is_null($data)) {
            $this->logger->debug("NC: Emitter registry: no cache found, proceed to scanning files");
            $data = $this->scan();
            $this->setCache($data);
        }

        return $data;
    }

    /**
     * Retrieving array from cache file if it exists
     * Retrieving array(dictionary array with module-level emitters class names and path to it)
     * from cache file if it exists
     *
     * @return array|null
     */
    protected function getCache()
    {
        $path = sugar_cached(static::CACHE_FILE);
        $this->logger->debug("NC: Emitter registry tries to get emitters data from cache $path file");

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
     * Saving array(dictionary array with module-level emitter class names and path to it) to cache file
     *
     * @param array $data
     */
    protected function setCache($data)
    {
        $this->logger->info(
            'NC: Emitter registry sets cache file ' . static::CACHE_FILE . ' with data: '
            . var_export($data, true)
        );
        create_cache_directory(static::CACHE_FILE);
        write_array_to_file(static::CACHE_VARIABLE, $data, sugar_cached(static::CACHE_FILE));
    }

    /**
     * @see \SugarAutoLoader::existingCustomOne
     */
    protected function customPath($path)
    {
        return \SugarAutoLoader::existingCustomOne($path);
    }

    /**
     * @see \SugarAutoLoader::customClass
     */
    protected function customClass($class)
    {
        return \SugarAutoLoader::customClass($class);
    }
}
