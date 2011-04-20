<?php
require_once('include/nusoap/nusoap.php');

/**
 * @group bug43282
 */
class Bug43282Test extends Sugar_PHPUnit_Framework_TestCase
{
	public $_soapURL = null;
    public $_soapClient = null;
    private $_tsk = null;

	public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/service/v4/soap.php';
        $this->_soapClient = new nusoapclient($this->_soapURL,false,false,false,false,false,600,600);
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->_tsk = new Task();
        $this->_tsk->name = "Unit Test";
        $this->_tsk->save();
    }

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM tasks WHERE id = '{$this->_tsk->id}'");

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);

    }

    /**
     * Ensure that when updating the team_id value for a bean that the team_set_id is not
     * populated into the team_id field if the team_id value is already set.
     *
     * @return void
     */
    public function testUpdateRecordsTeamID()
    {
        $privateTeamID = $GLOBALS['current_user']->getPrivateTeamID();

        $this->_login();
        $result = $this->_soapClient->call('set_entry',
            array(
                'session' => $this->_sessionId,
                'module' => 'Tasks',
                'name_value_list' => array(
                    array('name' => 'id', 'value' => $this->_tsk->id),
                    array('name' => 'team_id', 'value' => $privateTeamID),
                    ),
                )
            );

        $modifiedTask = new Task();
        $modifiedTask->retrieve($this->_tsk->id);
        $this->assertEquals($privateTeamID, $modifiedTask->team_id);

    }

    /**
     * Attempt to login to the soap server
     *
     * @return $set_entry_result - this should contain an id and error.  The id corresponds
     * to the session_id.
     */
    public function _login()
    {
		global $current_user;

		$result = $this->_soapClient->call(
		    'login',
            array('user_auth' =>
                array('user_name' => $current_user->user_name,
                    'password' => $current_user->user_hash,
                    'version' => '.01'),
                'application_name' => 'SoapTest')
            );
        $this->_sessionId = $result['id'];

        return $result;
    }
}