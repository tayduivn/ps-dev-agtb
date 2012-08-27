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

/**
 * Factory to create SugarBeans
 * @api
 */
class BeanFactory {
    protected static $loadedBeans = array();
    protected static $maxLoaded = 10;
    protected static $total = 0;
    protected static $loadOrder = array();
    protected static $touched = array();
    public static $hits = 0;

    /**
     * Returns a SugarBean object by id. The Last 10 loaded beans are cached in memory to prevent multiple retrieves per request.
     * If no id is passed, a new bean is created.
     * @static
     * @param String $module
     * @param String $id
     * @param Array $params A name/value array of parameters. Names: encode, deleted, 
     * //BEGIN SUGARCRM flav=pro ONLY
     *        disable_row_level_security
     * //END SUGARCRM flav=pro ONLY
     *        If $params is boolean we revert to the old arguments (encode, deleted), and use $params as $encode.
     *        This will be changed to using only $params in later versions.
     * @param Bool $deleted @see SugarBean::retrieve
     * @return SugarBean
     */
    public static function getBean($module, $id = null, $params = array(), $deleted = true)
    {
    	
    	// Check if params is an array, if not use old arguments
    	if (isset($params) && !is_array($params)) {
    		$params = array('encode' => $params);
    	}
    	
    	// Pull values from $params array
    	$encode = isset($params['encode']) ? $params['encode'] : true;
    	$deleted = isset($params['deleted']) ? $params['deleted'] : $deleted;
    	
        if (!isset(self::$loadedBeans[$module])) {
            self::$loadedBeans[$module] = array();
            self::$touched[$module] = array();
        }

        $beanClass = self::getBeanName($module);

        if (empty($beanClass) || !class_exists($beanClass)) return false;

        if (!empty($id))
        {
            if (empty(self::$loadedBeans[$module][$id]))
            {
                $bean = new $beanClass();
                //BEGIN SUGARCRM flav=pro ONLY
                // Pro+ versions, to disable team check if we have rights
                // to change the parent bean, but not the related (e.g. change Account Name of Opportunity) 
                if (!empty($params['disable_row_level_security'])) {
                    $bean->disable_row_level_security = true;	
                }
                //END SUGARCRM flav=pro ONLY
                $result = $bean->retrieve($id, $encode, $deleted);
                if($result == null)
                    return FALSE;
                else
                    self::registerBean($module, $bean, $id);
            } else
            {
                self::$hits++;
                self::$touched[$module][$id]++;
                $bean = self::$loadedBeans[$module][$id];
            }
        } else {
            $bean = new $beanClass();
        }

        return $bean;
    }

    public static function newBean($module)
    {
        return self::getBean($module);
    }

    public static function getBeanName($module)
    {
        global $beanList;
        if (empty($beanList[$module]))  {
            return false;
        }

        return $beanList[$module];
    }

    /**
     * Returns the object name / dictionary key for a given module. This should normally
     * be the same as the bean name, but may not for special case modules (ex. Case vs aCase)
     * @static
     * @param String $module
     * @return bool
     */
    public static function getObjectName($module)
    {
        global $objectList;
        if (empty($objectList[$module]))
            return self::getBeanName($module);

        return $objectList[$module];
    }


    /**
     * @static
     * This function registers a bean with the bean factory so that it can be access from accross the code without doing
     * multiple retrieves. Beans should be registered as soon as they have an id.
     * @param String $module
     * @param SugarBean $bean
     * @param bool|String $id
     * @return bool true if the bean registered successfully.
     */
    public static function registerBean($module, $bean, $id=false)
    {
        global $beanList;
        if (empty($beanList[$module]))  return false;

        if (!isset(self::$loadedBeans[$module]))
            self::$loadedBeans[$module] = array();

        //Do not double register a bean
        if (!empty($id) && isset(self::$loadedBeans[$module][$id]))
            return true;

        $index = "i" . (self::$total % self::$maxLoaded);
        //We should only hold a limited number of beans in memory at a time.
        //Once we have the max, unload the oldest bean.
        if (count(self::$loadOrder) >= self::$maxLoaded - 1)
        {
            for($i = 0; $i < self::$maxLoaded; $i++)
            {
                if (isset(self::$loadOrder[$index]))
                {
                    $info = self::$loadOrder[$index];
                    //If a bean isn't in the database yet, we need to hold onto it.
                    if (!empty(self::$loadedBeans[$info['module']][$info['id']]->in_save))
                    {
                        self::$total++;
                    }
                    //Beans that have been used recently should be held in memory if possible
                    else if (!empty(self::$touched[$info['module']][$info['id']]) && self::$touched[$info['module']][$info['id']] > 0)
                    {
                        self::$touched[$info['module']][$info['id']]--;
                        self::$total++;
                    }
                    else
                        break;
                } else {
                    break;
                }
                $index = "i" . (self::$total % self::$maxLoaded);
            }
            if (isset(self::$loadOrder[$index]))
            {
                unset(self::$loadedBeans[$info['module']][$info['id']]);
                unset(self::$touched[$info['module']][$info['id']]);
                unset(self::$loadOrder[$index]);
            }
        }

        if(!empty($bean->id))
           $id = $bean->id;

        if ($id)
        {
            self::$loadedBeans[$module][$id] = $bean;
            self::$total++;
            self::$loadOrder[$index] = array("module" => $module, "id" => $id);
            self::$touched[$module][$id] = 0;
        } else{
            return false;
        }
        return true;
    }
}

