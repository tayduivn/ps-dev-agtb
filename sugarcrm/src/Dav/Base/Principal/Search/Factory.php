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

namespace Sugarcrm\Sugarcrm\Dav\Base\Principal\Search;

/**
 * Find search class by principal prefix string
 * Class Factory
 * @package Sugarcrm\Sugarcrm\Dav\Base\Principal\Search
 */
class Factory
{
    /**
     * List of modules for search
     * @var array
     */
    protected static $modulesForSearch = null;

    /**
     * Gets full list of "person" modules
     * @return array
     */
    public function getModulesForSearch()
    {
        if (!is_null(self::$modulesForSearch)) {
            return self::$modulesForSearch;
        }

        $beanList = $this->getBeanList();

        foreach ($beanList as $moduleName => $className) {
            if (class_exists($className)) {
                $classParents = class_parents($className);
                if (in_array('Person', $classParents)) {
                    self::$modulesForSearch[] = $moduleName;
                }
            }
        }

        return self::$modulesForSearch;
    }

    /**
     * Get search class name from principal prefix
     * @param string $prefixPath
     * @return string
     */
    protected function getSearchClassName($prefixPath)
    {
        if (!$prefixPath) {
            return '';
        }
        $aPrefix = explode('/', $prefixPath);
        $iCount = count($aPrefix);
        switch ($iCount) {
            case 2:
                return ucfirst($aPrefix[1]);
                break;
            default:
                return '';
        }
    }

    /**
     * Get search object for principal
     * @param $prefixPath
     * @return null| \Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Base
     */
    public function getSearchClass($prefixPath)
    {
        $searchClass = $this->getSearchClassName($prefixPath);

        if ($searchClass) {
            $class = \SugarAutoLoader::customClass('Sugarcrm\\Sugarcrm\\Dav\\Base\\Principal\\Search\\' . $searchClass);
            if ($class && class_exists($class)) {
                return new $class($prefixPath);
            }
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
