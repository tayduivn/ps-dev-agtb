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
namespace Sugarcrm\Sugarcrm\Notification\MessageBuilder;

use Sugarcrm\Sugarcrm\Notification\EventInterface;

/**
 * Registry of MessageBuilders. Gets list of files form "include/nmb.php" and forms "custom/include/mnb.php".
 *
 * To add a custom MessageBuilder one need to override a base class
 * or create his own(based on MessageBuilder interface) and add it to custom registry ("custom/include/mnb.php").
 *
 * Class MessageBuilderRegistry
 * @package Sugarcrm\Sugarcrm\Notification\MessageBuilder
 */
class MessageBuilderRegistry
{
    /**
     * Path to file in which dictionary array of Message Builders is stored, supports customization.
     */
    const REGISTRY_FILE = 'include/nmb.php';

    /**
     * Initial path to file in which cached dictionary array is stored.
     * Often is prepended by 'cache/' or whatever cache location is configured in the system.
     */
    const CACHE_FILE = 'Notification/MessageBuilder/nmb.php';

    /**
     * Full path to MessageBuilderInterface.
     */
    const MESSAGE_BUILDER_INTERFACE = 'Sugarcrm\\Sugarcrm\\Notification\\MessageBuilder\\MessageBuilderInterface';

    /**
     * Variable name in which dictionary array is stored.
     */
    const VARIABLE = 'nmb';

    /**
     * Return object of MessageBuilderRegistry, customized if it's present.
     *
     * @return MessageBuilderRegistry
     */
    public static function getInstance()
    {
        $path = 'Sugarcrm\Sugarcrm\Notification\MessageBuilder\MessageBuilderRegistry';
        $class = \SugarAutoLoader::customClass($path);
        return new $class();
    }

    /**
     * Get MessageBuilder by event.
     * Looks for a registered Message Builders and forms a list of builders that support a given event,
     * then the last found builder with the greatest level is returned.
     *
     * @param EventInterface $event Event to get MessageBuilder for.
     * @return MessageBuilderInterface|null Message Builder instance. Null if no builders found.
     */
    public function getBuilder(EventInterface $event)
    {
        $supportedBuilders = array();
        $buildersList = $this->getDictionary();

        foreach ($buildersList as $builderClass) {
            $builder = new $builderClass;
            if ($builder->supports($event)) {
                $supportedBuilders[$builder->getLevel()] = $builder;
            }
        }

        if (!empty($supportedBuilders)) {
            ksort($supportedBuilders);
            return end($supportedBuilders);
        } else {
            return null;
        }
    }

    /**
     * Retrieve array (dictionary array with Message Builder classes' paths) from cache file;
     * if it does not exist, rebuild cache.
     *
     * @return array class names and path to it
     */
    protected function getDictionary()
    {
        $data = $this->getCache();
        if (is_null($data)) {
            $data = $this->scan();
            $this->setCache($data);
        }
        return $data;
    }

    /**
     * Retrieve dictionary array from cache file if it exists.
     *
     * Retrieve array (dictionary array with message builder full class names) from cache file if it exists.
     *
     * @return array|null dictionary array from cache
     */
    protected function getCache()
    {
        $path = sugar_cached(static::CACHE_FILE);
        if (\SugarAutoLoader::fileExists($path)) {
            return $this->getDataFromFile($path);
        } else {
            return null;
        }
    }

    /**
     * Retrieve dictionary array with message builder full class names from file if it exists.
     *
     * @param string $path to file.
     * @return array dictionary array from file.
     */
    protected function getDataFromFile($path)
    {
        include($path);
        if (isset(${static::VARIABLE})) {
            return ${static::VARIABLE};
        } else {
            return array();
        }
    }

    /**
     * Build dictionary array with Message Builders full class names.
     * 1. Get base (out of the box) list of builders, add it to the resulting list;
     * 2. Check the existence of custom list of builders.
     * If custom list is found, check if classes, listed in it, follow MessageBuilder interface
     * and if so, add them to the resulting list.
     *
     * @return array
     *  array(
     *      'BaseBuilder1FullClassPath',
     *      'BaseBuilder2FullClassPath',
     *      'CustomBuilder3FullClassPath',
     *      ...
     *  );
     */
    protected function scan()
    {
        $registry = array();

        foreach ($this->getDataFromFile(self::REGISTRY_FILE) as $class) {
            if ($this->isBuilderClass($class)) {
                $registry[] = $class;
            }
        }

        $customRegistryFile = 'custom/' . self::REGISTRY_FILE;
        if (\SugarAutoLoader::fileExists($customRegistryFile)) {
            foreach ($this->getDataFromFile($customRegistryFile) as $class) {
                if (!array_key_exists($class, $registry) && $this->isBuilderClass($class)) {
                    $registry[] = $class;
                }
            }
        }
        return $registry;
    }

    /**
     * Save dictionary array with Message Builder classes' paths to a cache file.
     *
     * @param array $data classes' paths.
     */
    protected function setCache($data)
    {
        create_cache_directory(static::CACHE_FILE);
        write_array_to_file(static::VARIABLE, $data, sugar_cached(static::CACHE_FILE));
    }

    /**
     * Does class implement BuilderInterface.
     *
     * @param string $class
     * @return bool
     */
    protected function isBuilderClass($class)
    {
        return class_exists($class) && in_array(static::MESSAGE_BUILDER_INTERFACE, class_implements($class));
    }
}
