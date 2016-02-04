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

namespace Sugarcrm\Sugarcrm\Dav\Base\Principal;

use Sugarcrm\Sugarcrm\Dav\Base\Principal\Search;

/**
 * Provide methods for search
 * Class Manager
 * @package Sugarcrm\Sugarcrm\Dav\Base\Principal
 */
class Manager
{
    /**
     * List of modules for search
     * @var array
     */
    protected static $modulesForSearch = null;

    /**
     * @var Search\Format\StrategyInterface
     */
    protected $formatStrategy = null;

    /**
     * Set output format strategy for search methods
     * @param Search\Format\StrategyInterface $format
     * @return $this
     */
    public function setOutputFormat(Search\Format\StrategyInterface $format)
    {
        $this->formatStrategy = $format;
        return $this;
    }

    /**
     * Gets full list of "person" modules
     * @return array
     */
    public function getModulesForSearch()
    {
        if (!is_null(static::$modulesForSearch)) {
            return static::$modulesForSearch;
        }

        $beanList = $this->getBeanList();

        foreach ($beanList as $moduleName => $className) {
            if (class_exists($className)) {
                $classParents = class_parents($className);
                $searchClass = $this->getSearchClassName($moduleName);
                if ($searchClass && in_array('Person', $classParents)) {
                    static::$modulesForSearch[$searchClass::getOrder()] = $moduleName;
                }
            }
        }

        ksort(static::$modulesForSearch);

        return static::$modulesForSearch;
    }

    /**
     * @see Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\SearchInterface::getPrincipalsByPrefix
     * @param string $principalPrefix
     * @return array
     */
    public function getPrincipalsByPrefix($principalPrefix)
    {
        $searchObject = $this->getSearchObject($principalPrefix);

        if ($searchObject) {
            return $searchObject->getPrincipalsByPrefix();
        }

        return array();
    }

    /**
     * @see Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\SearchInterface::getPrincipalByIdentify
     * @param string $path
     * @return array
     */
    public function getPrincipalByIdentify($path)
    {
        $principalComponents = explode('/', $path);
        if (count($principalComponents) != 3) {
            return array();
        }
        $identify = array_pop($principalComponents);
        $prefixPath = implode('/', $principalComponents);
        $searchObject = $this->getSearchObject($prefixPath);

        if ($searchObject) {
            return $searchObject->getPrincipalByIdentify($identify);
        }

        return array();
    }

    /**
     * @see Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\SearchInterface::searchPrincipals
     * @param string $prefixPath
     * @param array $searchProperties
     * @param string $test
     * @return array
     */
    public function searchPrincipals($prefixPath, array $searchProperties, $test = 'allof')
    {
        $searchObject = $this->getSearchObject($prefixPath);
        if ($searchObject) {
            return $searchObject->searchPrincipals($searchProperties, $test);
        }

        return array();
    }

    /**
     * Finds a principal by its URI.
     *
     * This method may receive any type of uri, but mailto: addresses will be
     * the most common.
     * @param $uri
     * @param $principalPrefix
     * @return null
     */
    public function findByUri($uri, $principalPrefix)
    {
        $uri = strtolower($uri);
        if (strpos($uri, 'mailto:') !== 0) {
            return null;
        }
        $result = $this->searchPrincipals(
            $principalPrefix,
            array('{http://sabredav.org/ns}email-address' => substr($uri, 7))
        );

        if ($result) {
            return $result[0];
        }

        return null;
    }

    /**
     * Find sugar bean by principal mail
     * @param $email
     * @return array
     */
    public function findSugarLinkByEmail($email)
    {
        $modules = $this->getModulesForSearch();

        foreach ($modules as $module) {
            $searchObject = $this->getSearchObject($module);
            if (!$this->formatStrategy) {
                $searchObject->setFormat(new Search\Format\ArrayStrategy());
            }
            $result = $searchObject->searchPrincipals(array('{http://sabredav.org/ns}email-address' => $email));
            if ($result) {
                return array_shift($result);
            }
        }

        return array();
    }

    /**
     * Get search class name from principal prefix
     * @param string $prefixPath
     * @return string | null
     */
    protected function getSearchClassName($prefixPath)
    {
        if (!$prefixPath) {
            return null;
        }
        $aPrefix = explode('/', $prefixPath);
        $searchClass = \SugarAutoLoader::customClass('Sugarcrm\\Sugarcrm\\Dav\\Base\\Principal\\Search\\' .
            ucfirst(array_pop($aPrefix)));
        if (class_exists($searchClass)) {
            return $searchClass;
        }

        return null;
    }

    /**
     * Get search object for principal
     * @param $prefixPath
     * @return null| \Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Base
     */
    protected function getSearchObject($prefixPath)
    {
        $searchClass = $this->getSearchClassName($prefixPath);

        if ($searchClass) {
            return new $searchClass($prefixPath, $this->formatStrategy);
        }

        return null;
    }

    /**
     * Gets list of beans
     * @return array
     */
    protected function getBeanList()
    {
        return $GLOBALS['beanList'];
    }
}
