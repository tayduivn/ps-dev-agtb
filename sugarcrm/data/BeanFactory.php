<?php

require_once('data/SugarBean.php');

class BeanFactory {
    protected static $loadedBeans = array();
    protected static $maxLoaded = 10;
    protected static $total = 0;
    protected static $loadOrder = array();
    public static $hits = 0;

    static function getBean($module, $id = null)
    {
        if (!isset(self::$loadedBeans[$module]))
            self::$loadedBeans[$module] = array();

        $beanClass = self::getBeanName($module);

        if (empty($beanClass)) return false;

        if (!empty($id))
        {
            if (empty(self::$loadedBeans[$module][$id]))
            {
                $bean = new $beanClass();
                $bean->retrieve($id);
                self::registerBean($module, $bean);
            } else
            {
                self::$hits++;
                $bean = self::$loadedBeans[$module][$id];
            }
        } else {
            $bean = new $beanClass();
        }
        
        return $bean;
    }

    static function newBean($module)
    {
        return self::getBean($module);
    }

    static function getBeanName($module)
    {
        global $beanList;
        if (empty($beanList[$module]))  return false;

        return $beanList[$module];
    }

    static function registerBean($module, $bean)
    {
        global $beanList;
        if (empty($beanList[$module]))  return false;

        if (!isset(self::$loadedBeans[$module]))
            self::$loadedBeans[$module] = array();

        if (self::$total > self::$maxLoaded)
        {
            $index = self::$total - self::$maxLoaded;
            $info = self::$loadOrder[$index];
            unset(self::$loadedBeans[$info['module']][$info['id']]);
            unset(self::$loadOrder[$index]);
        }

        self::$loadedBeans[$module][$bean->id] = $bean;
        self::$total++;
        self::$loadOrder[self::$total] = array("module" => $module, "id" => $bean->id);


        return $beanList[$module];
    }
}

