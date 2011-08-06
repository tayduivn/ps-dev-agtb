<?php
//FILE SUGARCRM flav=pro ONLY

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

require_once('include/database/MysqliManager.php');

/**
 * Bug44507Test
 * This test simulates the query that is run when a non-admin user makes a call to the get_bean_select_array method
 * in include/utils.php.  Bug 44507 is due to the problem
 *
 */
class Bug44507Test extends Sugar_PHPUnit_Framework_TestCase
{
	var $disableCountQuery;
	var $skipped = false;

    public function setUp()
    {
    	if($GLOBALS['db']->dbType != 'mysql')
    	{
    		$this->markTestSkipped('Skipping Test Bug44507');
    		$this->skipped = true;
    		return;
    	}

    	$GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    	$GLOBALS['current_user']->is_admin = false;

    	$randomTeam = SugarTestTeamUtilities::createAnonymousTeam();
        $randomTeam->add_user_to_team($GLOBALS['current_user']->id);

	    $this->useOutputBuffering = false;

	    global $sugar_config;
	    $this->disableCountQuery = isset($sugar_config['disable_count_query']) ? $sugar_config['disable_count_query'] : false;
	    $sugar_config['disable_count_query'] = true;
    }

    public function tearDown()
    {
    	if($this->skipped)
    	{
    		return;
    	}
        DBManagerFactory::disconnectAll();
        unset($GLOBALS['sugar_config']['dbconfig']['db_manager_class']);
        $GLOBALS['db'] = DBManagerFactory::getInstance();
    	global $sugar_config;
    	$sugar_config['disable_count_query'] = $this->disableCountQuery;

		SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
		SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        unset($GLOBALS['current_user']);
    }

    public function testGetBeanSelectArray()
    {
    	if($this->skipped)
    	{
    		return;
    	}

    	//From EmailMarketing/DetailView this covers most of the cases where EmailTemplate module is queries against
    	DBManagerFactory::disconnectAll();
    	$GLOBALS['sugar_config']['dbconfig']['db_manager_class'] = 'Bug44507SqlManager';
		$localDb = DBManagerFactory::getInstance();

		$this->assertInstanceOf("Bug44507SqlManager", $localDb);

    	get_bean_select_array('true', 'EmailTemplate', 'name');
    	$sql = $localDb->getExpectedSql();
		$this->assertRegExp('/email_templates\.id/', $sql, 'Assert that email_templates.id is not ambiguous');
    	$this->assertFalse($localDb->checkError(), "Assert we could run SQL:{$sql}");

		//From Emailmarketing/EditView
		get_bean_select_array(true, 'EmailTemplate','name','','name');
    	$sql = $localDb->getExpectedSql();
		$this->assertRegExp('/email_templates\.id/', $sql, 'Assert that email_templates.id is not ambiguous');
    	$this->assertFalse($localDb->checkError(), "Assert we could run SQL:{$sql}");

    	//From Expressions/Expressions.php
    	get_bean_select_array(true, 'ACLRole','name');
    	$sql = $localDb->getExpectedSql();
		$this->assertRegExp('/acl_roles\.id/', $sql, 'Assert that acl_roles.id is not ambiguous');
    	$this->assertFalse($localDb->checkError(), "Assert we could run SQL:{$sql}");

    	//From Contracts/Contract.php
    	get_bean_select_array(true, 'ContractType','name','deleted=0','list_order');
    	$sql = $localDb->getExpectedSql();
		$this->assertRegExp('/contract_types\.id/', $sql, 'Assert that contract_types.id is not ambiguous');
    	$this->assertFalse($localDb->checkError(), "Assert we could run SQL:{$sql}");
    }
}

class Bug44507SqlManager extends MysqliManager
{
	var $expectedSql;

    protected function addDistinctClause(&$sql)
    {
    	parent::addDistinctClause($sql);
    	$this->expectedSql = $sql;
    }

    public function getExpectedSql()
    {
    	return $this->expectedSql;
    }

    /**
     * @see DBManager::checkError()
     */
    public function checkError(
        $msg = '',
        $dieOnError = false
        )
    {
        if (DBManager::checkError($msg, $dieOnError))
        {
            return true;
        }

        if (mysqli_errno($this->getDatabase()))
        {
            return true;
        }

        return false;
    }
}

?>