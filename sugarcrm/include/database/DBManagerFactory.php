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
    static $instances = array();

    /**
	 * Returns a reference to the DB object of specific type
     *
     * @param  string $type DB type
     * @return object DBManager instance
     */
    public static function getTypeInstance($type, $config = array(), $global_config = array())
    {
        global $sugar_config;
        $my_db_manager = 'MysqlManager';
        if( $type == "mysql" ) {
            if (empty($sugar_config['mysqli_disabled']) && function_exists('mysqli_connect')) {
                $my_db_manager = 'MysqliManager';
            }
        //BEGIN SUGARCRM flav=ent ONLY
        } elseif($type == "oci8" ) {
                $my_db_manager = 'OracleManager';
        } elseif($type == "ibm_db2" ) {
                $my_db_manager = 'IBMDB2Manager';
        //END SUGARCRM flav=ent ONLY
        } elseif( $type == "mssql" ){
          	if ( function_exists('sqlsrv_connect')
                        && (empty($config['db_mssql_force_driver']) || $config['db_mssql_force_driver'] == 'sqlsrv' )) {
                $my_db_manager = 'SqlsrvManager';
            } elseif (is_freetds()
                        && (empty($config['db_mssql_force_driver']) || $config['db_mssql_force_driver'] == 'freetds' )) {
                $my_db_manager = 'FreeTDSManager';
            } else {
                $my_db_manager = 'MssqlManager';
            }
        }
        if(!empty($config['db_manager'])){
            $my_db_manager = $config['db_manager'];
        }
        $GLOBALS['log']->info("using $my_db_manager DBManager backend");

        require_once("include/database/{$my_db_manager}.php");
        if(class_exists($my_db_manager)) {
            return new $my_db_manager();
        } else {
            return null;
        }
    }

    /**
	 * Returns a reference to the DB object for instance $instanceName, or the default
     * instance if one is not specified
     *
     * @param  string $instanceName optional, name of the instance
     * @return object DBManager instance
     */
	public static function getInstance($instanceName = '')
    {
        global $sugar_config;
        static $count = 0, $old_count = 0;

        //fall back to the default instance name
        if(empty($sugar_config['db'][$instanceName])){
        	$instanceName = '';
        }
        if(!isset(self::$instances[$instanceName])){
            $config = $sugar_config['dbconfig'];
            $count++;
//BEGIN SUGARCRM flav=ent ONLY
            if(!empty($instanceName)){
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


            if(!empty($parentInstanceName) && !empty(self::$instances[$parentInstanceName])){
                self::$instances[$instanceName] = self::$instances[$parentInstanceName];
                $old_count++;
                self::$instances[$parentInstanceName]->references = $old_count;
                self::$instances[$parentInstanceName]->children[] = $instanceName;
            }
            else{
//END SUGARCRM flav=ent ONLY
                self::$instances[$instanceName] = self::getTypeInstance($config['db_type'], $config);
                self::$instances[$instanceName]->connect($config, true);
                self::$instances[$instanceName]->count_id = $count;
                self::$instances[$instanceName]->references = 0;
//BEGIN SUGARCRM flav=ent ONLY
            }
//END SUGARCRM flav=ent ONLY
        } else {
            $old_count++;
            self::$instances[$instanceName]->references = $old_count;
        }
        return self::$instances[$instanceName];
    }

    /**
     * Disconnect all DB connections in the system
     */
    public static function disconnectAll()
    {
        foreach(self::$instances as $instance) {
            $instance->disconnect();
        }
        self::$instances = array();
    }

    /**
     * Get list of all available DB drivers
     * @return array List of Db drivers, key - variant (mysql, mysqli), value - driver type (mysql, mssql)
     */
    public static function getDbDrivers()
    {
        $drivers = array();
        $dir = opendir("include/database");
        while(($name = readdir($dir)) !== false) {
            if(substr($name, -11) != "Manager.php") continue;
            if($name == "DBManager.php") continue;
            require_once("include/database/$name");
            $classname = substr($name, 0, -4);
            if(!class_exists($classname)) continue;
            $driver = new $classname;
            if($driver->valid()) {
                $drivers[$driver->variant] = $driver->dbType;
            }
        }
        return $drivers;
    }
}