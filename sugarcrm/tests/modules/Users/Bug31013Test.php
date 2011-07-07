<?php
//FILE SUGARCRM flav=pro ONLY
require_once 'modules/Users/User.php';

/**
 * @group bug31013
 */
class Bug31013Test extends Sugar_PHPUnit_Framework_TestCase
{
	public $_user = null;

	public function setUp() 
    {
    	$this->markTestSkipped(
              'We do not need this test for now'
            );
    	$time = mt_rand();
    	$this->_user = new User();
        $this->_user->user_name = 'portal' . $time;
        $this->_user->user_hash = md5($userId.$time);
        $this->_user->first_name = 'portal' . $time;
        $this->_user->last_name = 'portal' . $time;
        $this->_user->portal_only = true;
        $this->_user->save();
	}

	public function tearDown() 
    {
    	 //Remove the created test user
         $GLOBALS['db']->query("DELETE FROM users WHERE id = '{$this->_user->id}'");
	}

	public function testPrivateTeamForPortalUserNotCreated() 
    {
    	$result = $GLOBALS['db']->query("SELECT count(*) AS TOTAL FROM teams WHERE associated_user_id = '{$this->_user->id}'");
        $row = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertTrue(empty($row['TOTAL']), "Assert that the private team was not created for portal user");
    }

}

