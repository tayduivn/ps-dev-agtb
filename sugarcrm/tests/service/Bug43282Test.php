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

        $GLOBALS['db']->commit(); // Making sure we commit any changes before logging in
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