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
/*********************************************************************************
* $Id: DBManagerFactory.php 53116 2009-12-10 01:24:37Z mitani $
* Description: This file generates the appropriate manager for the database
* 
* Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
* All Rights Reserved.
* Contributor(s): ______________________________________..
********************************************************************************/

require_once('include/database/DBManager.php');

class DBManagerFactory
{
    /** 
	 * Returns a reference to the DB object for instance $instanceName, or the default 
     * instance if one is not specified
     *
     * @param  string $instanceName optional, name of the instance
     * @return object DBManager instance 
     */
	public static function getInstance(
        $instanceName = ''
        )
    {
        global $sugar_config, $dbinstances;
        static $count, $old_count;

        //BEGIN SUGARCRM flav=ent ONLY
        /*
        //END SUGARCRM flav=ent ONLY
        $instanceName = 'db';
        $config = $sugar_config['dbconfig'];
        //BEGIN SUGARCRM flav=ent ONLY
        */
        //END SUGARCRM flav=ent ONLY
        if(!isset($dbinstances)){
            $dbinstances = array();
        }
        //fall back to the default instance name
        if(empty($sugar_config['db'][$instanceName])){
        	$instanceName = '';
        }
        if(!isset($dbinstances[$instanceName])){
            //BEGIN SUGARCRM flav=ent ONLY
            $count++;
            if(empty($instanceName)){
                $config = $sugar_config['dbconfig'];
            }
            else{
                $config = $sugar_config['db'][$instanceName];
                //trace the parent dbs until we get a real db
                $parentInstanceName = '';
                while(!empty($config['parent_db'])){
                    if(empty($sugar_config['db'][$config['parent_db']])){
                        $config = $sugar_config['dbconfig'];
                        $parentInstanceName = '';
                        break;
                    }
                    else{
                        $parentInstanceName = $config['parent_db'];
                        $config = $sugar_config['db'][$config['parent_db']];
                    }
                }
            }

            //END SUGARCRM flav=ent ONLY
            $my_db_manager = 'MysqlManager';
            if( $config['db_type'] == "mysql" ) {
                if ((!isset($sugar_config['mysqli_disabled'])
                            || $sugar_config['mysqli_disabled'] == false) 
                    && function_exists('mysqli_connect')) {
                    $my_db_manager = 'MysqliManager';
                }
            }
            if( $config['db_type'] == "oci8" ){
                //BEGIN SUGARCRM flav=ent ONLY
                $my_db_manager = 'OracleManager';
                //END SUGARCRM flav=ent ONLY
            }
            elseif( $config['db_type'] == "mssql" ){
            	if ( function_exists('sqlsrv_connect')
                        && (empty($config['db_mssql_force_driver']) || $config['db_mssql_force_driver'] == 'sqlsrv' ))
                	$my_db_manager = 'SqlsrvManager';
            	elseif (is_freetds() 
                        && (empty($config['db_mssql_force_driver']) || $config['db_mssql_force_driver'] == 'freetds' ))
                    $my_db_manager = 'FreeTDSManager';
                else
                    $my_db_manager = 'MssqlManager';
            }
            $GLOBALS['log']->info("using $my_db_manager DBManager backend");
            if(!empty($config['db_manager'])){
                $my_db_manager = $config['db_manager'];
            }

            //BEGIN SUGARCRM flav=ent ONLY
            if(!empty($parentInstanceName) && !empty($dbinstances[$parentInstanceName])){
                $dbinstances[$instanceName] = $dbinstances[$parentInstanceName];
                $old_count++;
                $dbinstances[$parentInstanceName]->references = $old_count;
                $dbinstances[$parentInstanceName]->children[] = $instanceName;
            }
            else{
                //END SUGARCRM flav=ent ONLY
                require_once("include/database/{$my_db_manager}.php");
                $dbinstances[$instanceName] = new $my_db_manager();
                $dbinstances[$instanceName]->getHelper();
                $dbinstances[$instanceName]->connect($config, true);
                $dbinstances[$instanceName]->count_id = $count;
                $dbinstances[$instanceName]->references = 0;
                $dbinstances[$instanceName]->getHelper()->db = $dbinstances[$instanceName];           
                //BEGIN SUGARCRM flav=ent ONLY
            }
            //END SUGARCRM flav=ent ONLY
        }
        else {
            $old_count++;
            $dbinstances[$instanceName]->references = $old_count;
        }
        return $dbinstances[$instanceName];
    }
    
    /**
     * Returns an instance of the helper class
     *
     * @deprecated
     * @return object DBHelper instance
     */
    public static function getHelperInstance()
    {
        $GLOBALS['log']->info('call to DBManagerFactory::getHelperInstance() is deprecated');
        return self::getInstance()->getHelper();
    }
    
    /**
     * Loads the DBManager and DBHelper instance class files
     *
     * @deprecated
     * @param string $class_name
     */
    public static function load_db_manager_class(
        $class_name
        )
    {
        $GLOBALS['log']->info('call to DBManagerFactory::load_db_manager_class() is deprecated');
        if( is_file("include/database/{$class_name}.php") && !class_exists($class_name))
            require_once("include/database/{$class_name}.php");
        
        $class_name = str_ireplace('Manager','Helper',$class_name);
        
        if( is_file("include/database/{$class_name}.php") && !class_exists($class_name))
            require_once("include/database/{$class_name}.php");
    }
                
}

?>
