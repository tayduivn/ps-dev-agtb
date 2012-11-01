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
require_once('tests/rest/RestTestBase.php');

class RestBug57835Test extends RestTestBase
{
    public function setUp()
    {
        // Start out with a fake auth token to prevent _restCall from auto logging in
        $this->authToken = 'LOGGING_IN';
        
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();

        $GLOBALS['db']->query("DELETE FROM oauth_consumer WHERE id LIKE 'UNIT%'");
        $GLOBALS['db']->query("DELETE FROM oauth_tokens WHERE consumer LIKE '_unit_%'");
        if ( isset($this->contact->id) ) {
            $GLOBALS['db']->query("DELETE FROM contacts WHERE id = '".$this->contact->id."'");
            if ($GLOBALS['db']->tableExists('contacts_cstm')) {
                $GLOBALS['db']->query("DELETE FROM contacts_cstm WHERE id_c = '".$this->contact->id."'");
            }
        }
        if ( isset($this->apiuser->id) ) {
            $GLOBALS['db']->query("DELETE FROM users WHERE id = '".$this->apiuser->id."'");
            if ($GLOBALS['db']->tableExists('users_cstm')) {
                $GLOBALS['db']->query("DELETE FROM users_cstm WHERE id_c = '".$this->apiuser->id."'");
            }
        }
        $GLOBALS ['system_config']->saveSetting('supportPortal', 'RegCreatedBy', '');
        $GLOBALS ['system_config']->saveSetting('portal', 'on', 0);
        $GLOBALS['db']->commit();
    }

    //BEGIN SUGARCRM flav=pro ONLY
    /**
     * @group rest
     */
    public function testBug57835()
    {
        // Create a portal API user
        $this->apiuser = BeanFactory::newBean('Users');
        $this->apiuser->id = "UNIT-TEST-apiuser";
        $this->apiuser->new_with_id = true;
        $this->apiuser->first_name = "Portal";
        $this->apiuser->last_name = "Apiuserson";
        $this->apiuser->username = "_unittest_apiuser";
        $this->apiuser->portal_only = true;
        $this->apiuser->status = 'Active';
        $this->apiuser->save();

        // Create a contact to log in as
        $this->contact = BeanFactory::newBean('Contacts');
        $this->contact->id = "UNIT-TEST-littleunittest";
        $this->contact->new_with_id = true;
        $this->contact->first_name = "Little";
        $this->contact->last_name = "Unittest";
        $this->contact->description = "Little Unittest";
        $this->contact->portal_name = "liltest@unit.com";
        $this->contact->portal_active = '1';
        $this->contact->portal_password = User::getPasswordHash("unittest");
        $this->contact->save();
        $GLOBALS ['system_config']->saveSetting('supportPortal', 'RegCreatedBy', '');
        $GLOBALS ['system_config']->saveSetting('portal', 'on', 1);
        $GLOBALS['db']->commit();
        
        $args = array(
            'grant_type' => 'password',
            'username' => $this->contact->portal_name,
            'password' => 'unittest',
            'client_id' => 'support_portal',
            'client_secret' => '',
        );
        
        $reply = $this->_restCall('oauth2/token',json_encode($args));
        $this->assertEquals('portal_not_configured',$reply['reply']['error']);
                                                          
    }
    //END SUGARCRM flav=pro ONLY
}
