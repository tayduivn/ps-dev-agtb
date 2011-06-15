<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
 
require_once 'include/database/DBManagerFactory.php';

class DBManagerFactoryTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_oldSugarConfig;

    public function setUp()
    {
        $this->_oldSugarConfig = $GLOBALS['sugar_config'];
    }

    public function tearDown()
    {
        $GLOBALS['sugar_config'] = $this->_oldSugarConfig;
    }

    public function testGetInstance()
    {
        $db = DBManagerFactory::getInstance();

        $this->assertTrue($db instanceOf DBManager,"Should return a DBManger object");
    }

    public function testGetInstanceCheckMysqlDriverChoosen()
    {
        if ( $GLOBALS['db']->dbType != 'mysql' )
            $this->markTestSkipped('Only applies to MySql');

        $db = DBManagerFactory::getInstance();

        if ( function_exists('mysqli_connect') )
            $this->assertTrue($db instanceOf MysqliManager,"Should return a MysqliManager object");
        else
            $this->assertTrue($db instanceOf MysqlManager,"Should return a MysqlManager object");
    }

    /**
     * @ticket 27781
     */
    public function testGetInstanceMssqlDefaultSelection()
    {
        if ( $GLOBALS['db']->dbType != 'mssql' )
            $this->markTestSkipped('Only applies to SQL Server');

        $GLOBALS['sugar_config']['db_mssql_force_driver'] = '';

        $db = &DBManagerFactory::getInstance();

        if ( function_exists('sqlsrv_connect') )
            $this->assertTrue($db instanceOf SqlsrvManager,"Should return a SqlsrvManager object");
        elseif ( is_freetds() )
            $this->assertTrue($db instanceOf FreeTDSManager,"Should return a FreeTDSManager object");
        else
            $this->assertTrue($db instanceOf MssqlManager,"Should return a MssqlManager object");
    }

    /**
     * @ticket 27781
     */
    public function testGetInstanceMssqlForceFreetdsSelection()
    {
        if ( $GLOBALS['db']->dbType != 'mssql' || !is_freetds() )
            $this->markTestSkipped('Only applies to SQL Server FreeTDS');

        $GLOBALS['sugar_config']['db_mssql_force_driver'] = 'freetds';

        $db = &DBManagerFactory::getInstance();

        $this->assertTrue($db instanceOf FreeTDSManager,"Should return a FreeTDSManager object");
    }

    /**
     * @ticket 27781
     */
    public function testGetInstanceMssqlForceMssqlSelection()
    {
        if ( $GLOBALS['db']->dbType != 'mssql' || !function_exists('mssql_connect') )
            $this->markTestSkipped('Only applies to SQL Server with the Native PHP mssql Driver');

        $GLOBALS['sugar_config']['db_mssql_force_driver'] = 'mssql';

        $db = &DBManagerFactory::getInstance();

        if ( is_freetds() )
            $this->assertTrue($db instanceOf MssqlManager,"Should return a MssqlManager object");
        elseif ( function_exists('mssql_connect') )
        $this->assertTrue($db instanceOf MssqlManager,"Should return a MssqlManager object");
        else
            $this->assertTrue($db instanceOf SqlsrvManager,"Should return a SqlsrvManager object");
    }

    /**
     * @ticket 27781
     */
    public function testGetInstanceMssqlForceSqlsrvSelection()
    {
        if ( $GLOBALS['db']->dbType != 'mssql' || !function_exists('sqlsrv_connect') )
            $this->markTestSkipped('Only applies to SQL Server');

        $GLOBALS['sugar_config']['db_mssql_force_driver'] = 'sqlsrv';

        $db = &DBManagerFactory::getInstance();

        if ( is_freetds() && !function_exists('sqlsrv_connect') )
            $this->assertTrue($db instanceOf FreeTDSManager,"Should return a FreeTDSManager object");
        elseif ( function_exists('mssql_connect') && !function_exists('sqlsrv_connect') )
            $this->assertTrue($db instanceOf MssqlManager,"Should return a MssqlManager object");
        else
        $this->assertTrue($db instanceOf SqlsrvManager,"Should return a SqlsrvManager object");
    }
}
