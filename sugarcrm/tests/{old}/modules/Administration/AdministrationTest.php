<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Administration
 */
class AdministrationTest extends TestCase
{
    protected $configs = array(
        array('name' => 'AdministrationTest', 'value' => 'Base', 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'AdministrationTest', 'value' => 'Portal', 'platform' => 'portal', 'category' => 'Forecasts'),
        array('name' => 'AdministrationTest', 'value' => '["Portal"]', 'platform' => 'json', 'category' => 'Forecasts'),
    );

    public static function setUpBeforeClass() : void
    {
        sugar_cache_clear('admin_settings_cache');
    }

    protected function setUp() : void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('moduleList');
        $db = DBManagerFactory::getInstance();
        $db->query("DELETE FROM config where name = 'AdministrationTest'");
        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');
        foreach($this->configs as $config){
            $admin->saveSetting($config['category'], $config['name'], $config['value'], $config['platform']);
        }
    }

    protected function tearDown() : void
    {
        $db = DBManagerFactory::getInstance();
        $db->query("DELETE FROM config where name = 'AdministrationTest'");
        $db->commit();
    }

    public function testRetrieveSettingsByInvalidModuleReturnsEmptyArray()
    {
        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');

        $results = $admin->getConfigForModule('InvalidModule', 'base');

        $this->assertEmpty($results);
    }

    public function testRetrieveSettingsByValidModuleWithPlatformReturnsOneRow()
    {
        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');

        $results = $admin->getConfigForModule('Forecasts', 'base');

        $this->assertTrue(count($results) > 0);
    }

    public function testRetrieveSettingsByValidModuleWithPlatformOverRidesBasePlatform()
    {
        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');

        $results = $admin->getConfigForModule('Forecasts', 'portal');

        $this->assertEquals('Portal', $results['AdministrationTest']);
    }

    public function testCacheExist()
    {
        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');

        $results = $admin->getConfigForModule('Forecasts', 'base');

        $this->assertNotEmpty(sugar_cache_retrieve("ModuleConfig-Forecasts"));
    }

    public function testCacheSameAsReturn()
    {
        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');

        $results = $admin->getConfigForModule('Forecasts', 'base');

        $this->assertSame($results, sugar_cache_retrieve("ModuleConfig-Forecasts"));
    }

    public function testCacheClearedAfterSave()
    {
        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');

        $results = $admin->getConfigForModule('Forecasts', 'base');

        $admin->saveSetting("Forecasts", "AdministrationTest", "testCacheClearedAfterSave", "base");

        $this->assertEmpty(sugar_cache_retrieve("ModuleConfig-Forecasts"));
    }

    public function testJsonValueIsArray()
    {
         /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');

        $results = $admin->getConfigForModule('Forecasts', 'json');

        $this->assertEquals(array("Portal"), $results['AdministrationTest']);
    }

    /**
     * @dataProvider configValueIntegrityProvider
     */
    public function testConfigValueIntegrity($value, $expected)
    {
        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');
        $admin->saveSetting('PHPUnit', 'Test', $value, 'base');
        $config = $admin->getConfigForModule('PHPUnit', 'base', true);
        $this->assertSame($expected, $config['Test']);
    }

    /**
     * @return array
     */
    public function configValueIntegrityProvider()
    {
        return array(
            array('A', 'A'), // simple string
            array('A\\B', 'A\\B'), // slashes
            array('Русский', 'Русский'), // unicode
            array('7.0', '7.0'), // simple number
            array('7.0.0', '7.0.0'),
            array(7, 7),      // integer
            array(array('portal'), array('portal')), // indexed array
            array(array('foo' => 'bar'), array('foo' => 'bar')), // associative array
            array('"value1"', '"value1"'), // quoted string
            array(array(2 => '"val"ue2'), array(2 => '"val"ue2')), // array with quoted string
        );
    }

    /**
     * @covers ::saveConfig
     */
    public function testSaveConfig()
    {
        // Don't allow the user to use the system configuration to guarantee that the true system configuration's name
        // and email address are retrieved from the database instead of being replaced by the user's name and primary
        // email address.
        OutboundEmailConfigurationTestHelper::setAllowDefaultOutbound(0);

        $_POST['mail_smtpserver'] = 'smtp.example.com';
        $_POST['mail_smtpport'] = 1025;
        $_POST['notify_fromname'] = 'Sugar';
        $_POST['notify_fromaddress'] = 'sugar@ex.com';
        // The following are ignored.
        $_POST['type'] = 'system-override';
        $_POST['email_address'] = 'foo@bar.com';
        $_POST['test'] = 'test';

        $admin = BeanFactory::newBean('Administration');
        $admin->saveConfig();

        unset($_POST['mail_smtpserver']);
        unset($_POST['mail_smtpport']);
        unset($_POST['notify_fromname']);
        unset($_POST['notify_fromaddress']);
        unset($_POST['type']);
        unset($_POST['email_address']);

        $this->assertSame('Sugar', $admin->settings['notify_fromname'], 'notify_fromname is incorrect');
        $this->assertSame('sugar@ex.com', $admin->settings['notify_fromaddress'], 'notify_fromaddress is incorrect');

        $oe = BeanFactory::newBean('OutboundEmail');
        $system = $oe->getSystemMailerSettings();
        $this->assertSame('smtp.example.com', $system->mail_smtpserver, 'The servers should match');
        $this->assertEquals(1025, $system->mail_smtpport, 'The ports should match');
        $this->assertSame('Sugar', $system->name, 'The names should match');
        $this->assertSame('sugar@ex.com', $system->email_address, 'The email addresses should match');

        $db = DBManagerFactory::getInstance();
        $db->query("UPDATE config SET value='do_not_reply@example.com' WHERE name='fromaddress' AND category='notify'");
        $db->query("UPDATE config SET value='SugarCRM' WHERE name='fromname' AND category='notify'");
        OutboundEmailConfigurationTestHelper::tearDown();
    }
}
