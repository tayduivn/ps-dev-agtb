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
 
require_once('include/SugarOAuth2Storage.php');
require_once('tests/rest/RestTestPortalBase.php');

class SugarOAuth2StorageTest extends RestTestPortalBase
{
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('app_list_strings');
        
        parent::setUp();

        $admin = new Administration();

        if(!isset($admin->settings['license_num_portal_users'])) {
            $admin->settings['license_num_portal_users'] = 50;
            $admin->saveSetting('license', 'num_portal_users', '50');
        }


        $admin->retrieveSettings('system');
        if(!isset($admin->settings['system_session_timeout'])) {
           $session_timeout = abs(ini_get('session.gc_maxlifetime'));
           $admin->saveSetting('system', 'session_timeout', $session_timeout);
        }

        $admin->retrieveSettings('license');
        $admin->settings['license_enforce_portal_user_limit'] = '1';
        $admin->saveSetting('license', 'enforce_portal_user_limit', '1');

        $admin->retrieveSettings(false, true);
        sugar_cache_clear('admin_settings_cache');

        // We need to disable the cache headers, otherwise the session_start() complains=
        session_cache_limiter('');
        
    }

    public function tearDown()
    {
        // Reset the portal login license to previous numbers, if we have it
        if ( isset($this->previousPortalLicense) ) {
            $GLOBALS['db']->query("UPDATE config SET value = '".$this->previousPortalLicense."' WHERE name = 'num_portal_users'");
            sugar_cache_clear('admin_settings_cache');
        }

        $GLOBALS['db']->query("DELETE FROM session_active");

        SugarTestHelper::tearDown();

        parent::tearDown();
    }

    public function testTooManyUsers()
    {
        $ret = $GLOBALS['db']->query("SELECT value FROM config WHERE name = 'num_portal_users'");
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        $this->previousPortalLicense = $row['value'];

        $contact1 = BeanFactory::newBean('Contacts');
        $contact1->first_name = 'UNIT';
        $contact1->last_name = 'TEST1';
        $contact1->portal_active = true;
        $contact1->portal_name = "unittestportal1";
        $contact1->portal_password = User::getPasswordHash("unittestportal1");
        $contact1->save();
        $this->contacts[] = $contact1;

        $contact2 = BeanFactory::newBean('Contacts');
        $contact2->first_name = 'UNIT';
        $contact2->last_name = 'TEST2';
        $contact2->portal_active = true;
        $contact2->portal_name = "unittestportal2";
        $contact2->portal_password = User::getPasswordHash("unittestportal2");
        $contact2->save();
        $this->contacts[] = $contact2;

        $contact3 = BeanFactory::newBean('Contacts');
        $contact3->first_name = 'UNIT';
        $contact3->last_name = 'TEST3';
        $contact3->portal_active = true;
        $contact3->portal_name = "unittestportal3";
        $contact3->portal_password = User::getPasswordHash("unittestportal3");
        $contact3->save();
        $this->contacts[] = $contact3;

        $storage = new SugarOAuth2Storage();

        $GLOBALS['db']->query("UPDATE config SET value = '1' WHERE name = 'num_portal_users'");
        $admin = new Administration();

        if(!isset($admin->settings['license_num_portal_users'])) {
           $admin->settings['license_num_portal_users'] = 1;
           $admin->saveSetting('license', 'num_portal_users', '1');
        }

        sugar_cache_clear('admin_settings_cache');
        $admin->retrieveSettings(false, true);
        sugar_cache_clear('admin_settings_cache');
        
        // While we can be clever about this, for a unit test we're just going to act dumb, clear out all portal sessions to make sure
        // that we have an accurate test.
        $GLOBALS['db']->query("DELETE FROM session_active");

        // For some reason this really wants a remote address set
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        // First login should work.
        $firstCheck = $storage->checkUserCredentials('support_portal','unittestportal1','unittestportal1');
        $storage->setAccessToken('unittestportal1','support_portal',$contact1->id,time()+30,NULL);

        // Second login is borderline, but we let them pass because we are nice.
        $firstCheck = $storage->checkUserCredentials('support_portal','unittestportal2','unittestportal2');
        $storage->setAccessToken('unittestportal2','support_portal',$contact2->id,time()+30,NULL);

        try {
            // Third login is time to fail
            $firstCheck = $storage->checkUserCredentials('support_portal','unittestportal3','unittestportal3');
            $storage->setAccessToken('unittestportal3','support_portal',$contact3->id,time()+30,NULL);

            
            $errorLabel = 'no_error';
        } catch ( Exception $e ) {
            $errorLabel = $e->errorLabel;
        }

        // We need to make sure this errored out here
        $this->assertEquals('too_many_concurrent_connections',$errorLabel);

    }
}
