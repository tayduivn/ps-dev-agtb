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
require_once('modules/Notifications/views/view.systemquicklist.php');
require_once('modules/Administration/Administration.php');

class SystemQuickListFTSClearTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setup()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user');
    }
    
    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    public function testFTSFlagRemoval() {
        $this->markTestIncomplete('Needs to be fixed by FRM team.');
        $GLOBALS['current_user']->is_admin = 1;
        $admin = BeanFactory::newBean('Administration');
        $admin->saveSetting('info', 'fts_index_done', 1);

        $cfg = new Configurator();
        $cfg->config['fts_disable_notification'] = true;
        $cfg->handleOverride();
        

        $vsql = new ViewSystemQuicklistMock();
        $vsql->clear();

        $cfg->loadConfig();
        $this->assertFalse($cfg->config['fts_disable_notification'], "FTS Disabled Notification is not false, it was: " . var_export($cfg->config['fts_disable_notification'], true));
        $settings = $admin->retrieveSettings();
        $this->assertEmpty($settings->settings['info_fts_index_done'], "FTS Index Done Flag not cleared, it was: " . var_export($settings->settings['info_fts_index_done'], true));

    }
}

class ViewSystemQuicklistMock extends ViewSystemQuicklist {
    public function clear() {
        return $this->clearFTSFlags();
    }
}
