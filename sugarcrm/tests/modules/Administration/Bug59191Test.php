<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once ('modules/Administration/views/view.globalsearchsettings.php');

class Bug59191Test extends Sugar_PHPUnit_Framework_TestCase {
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

    public function testFTSFlagStatus() {
        $GLOBALS['current_user']->is_admin = 1;
        $admin = BeanFactory::newBean('Administration');
        $admin->saveSetting('info', 'fts_down', 1);

        $cfg = new Configurator();
        $cfg->config['fts_disable_notification'] = true;
        $cfg->handleOverride();
        

        $vsql = new AdministrationViewGlobalsearchsettingsMock();
        $vsql->checkFTSSettingUp();

        $cfg->loadConfig();
        $this->assertFalse($cfg->config['fts_disable_notification'], "FTS Disabled Notification is not false, it was: " . var_export($cfg->config['fts_disable_notification'], true));
        $settings = $admin->retrieveSettings();
        $this->assertEmpty($settings->settings['info_fts_down'], "FTS Index Done Flag not cleared, it was: " . var_export($settings->settings['info_fts_down'], true));

    }
}

class AdministrationViewGlobalsearchsettingsMock extends AdministrationViewGlobalsearchsettings {
	public function checkFTSSettingUp() {
		$this->setFTSUp();
	}
}
