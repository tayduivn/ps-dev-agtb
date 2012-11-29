<?php
//FILE SUGARCRM flav=ent ONLY
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
require_once('tests/rest/RestTestPortalBase.php');

class RestBug57835Test extends RestTestPortalBase
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

        if ( isset($this->testapiuser->id) ) {
            $GLOBALS['db']->query("DELETE FROM users WHERE id = '".$this->testapiuser->id."'");
            if ($GLOBALS['db']->tableExists('users_cstm')) {
                $GLOBALS['db']->query("DELETE FROM users_cstm WHERE id_c = '".$this->testapiuser->id."'");
            }
        }
        $GLOBALS['db']->commit();
    }

    /**
     * @group rest
     */
    public function testBug57835()
    {
        // Create a portal API user
        $this->testapiuser = BeanFactory::newBean('Users');
        $this->testapiuser->id = "UNIT-TEST-apiuser";
        $this->testapiuser->new_with_id = true;
        $this->testapiuser->first_name = "Portal";
        $this->testapiuser->last_name = "Apiuserson";
        $this->testapiuser->username = "_unittest_apiuser";
        $this->testapiuser->portal_only = true;
        $this->testapiuser->status = 'Active';
        $this->testapiuser->save();
        // unset the default configsetting for the portal user
        $GLOBALS ['system_config']->saveSetting('supportPortal', 'RegCreatedBy', '');
        $GLOBALS['db']->commit();

        $args = array(
            'grant_type' => 'password',
            'username' => 'unittestportal',
            'password' => 'unittest',
            'client_id' => 'support_portal',
            'client_secret' => '',
            'platform' => 'portal',
        );
        $reply = $this->_restCall('oauth2/token',json_encode($args));
        $this->assertEquals('portal_not_configured',$reply['reply']['error']);
                                                          
    }
}
