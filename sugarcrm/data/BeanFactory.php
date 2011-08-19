<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
*The contents of this file are subject to the SugarCRM Professional End User License Agreement
*("License") which can be viewed at http://www.sugarcrm.com/EULA.
*By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
*not use this file except in compliance with the License. Under the terms of the license, You
*shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
*otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
*service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
*of a third party.  Use of the Software may be subject to applicable fees and any use of the
*Software without first paying applicable fees is strictly prohibited.  You do not have the
*right to remove SugarCRM copyrights from the source code or user interface.
* All copies of the Covered Code must include on each user interface screen:
* (i) the "Powered by SugarCRM" logo and
* (ii) the SugarCRM copyright notice
* in the same form as they appear in the distribution.  See full license for requirements.
*Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
*to the License for the specific language governing these rights and limitations under the License.
*Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
********************************************************************************/

require_once('data/SugarBean.php');

class BeanFactory {
    protected static $loadedBeans = array();
    protected static $maxLoaded = 10;
    protected static $total = 0;
    protected static $loadOrder = array();
    public static $hits = 0;

    /**
     * Returns a SugarBean object by id. The Last 10 loaded beans are cached in memory to prevent multiple retrieves per request.
     * If no id is passed, a new bean is created.
     * @static
     * @param  String $module
     * @param String $id
     * @return SugarBean
     */
    static function getBean($module, $id = null)
    {
        if (!isset(self::$loadedBeans[$module]))
            self::$loadedBeans[$module] = array();

        $beanClass = self::getBeanName($module);

        if (empty($beanClass) || !class_exists($beanClass)) return false;

        if (!empty($id))
        {
            if (empty(self::$loadedBeans[$module][$id]))
            {
                $bean = new $beanClass();
                $bean->retrieve($id);
                self::registerBean($module, $bean, $id);
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

    static function registerBean($module, $bean, $id=false)
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

        if(!empty($bean->id))
           $id = $bean->id;
        
        if ($id)
        {
            self::$loadedBeans[$module][$id] = $bean;
            self::$total++;
            self::$loadOrder[self::$total] = array("module" => $module, "id" => $id);
        }

        return $beanList[$module];
    }
}

