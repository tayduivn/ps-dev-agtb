<?php
//FILE SUGARCRM flav=pro ONLY
require_once 'modules/Users/User.php';

/**
 * @ticket 31013
 */
class Bug31013Test extends Sugar_PHPUnit_Framework_TestCase
{
	public $_user = null;

	public function setUp() 
    {
    	$this->_user = SugarTestUserUtilities::createAnonymousUser(false);
    	$this->_user->portal_only = true;
    	$this->_user->save();
	}

	public function tearDown() 
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
	}

	public function testPrivateTeamForPortalUserNotCreated() 
    {
    	$result = $GLOBALS['db']->query("SELECT count(*) AS TOTAL FROM teams WHERE associated_user_id = '{$this->_user->id}'");
        $row = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertTrue(empty($row['TOTAL']), "Assert that the private team was not created for portal user");
    }

}

