<?php
require_once 'tests/upgrade/UpgradeTestCase.php';

class UpgradeRenameModuleTest extends UpgradeTestCase
{
    protected $globalFilename = 'custom/include/language/en_us.lang.php';
    protected $moduleFilename = 'custom/modules/Contacts/Ext/Language/en_us.lang.ext.php';
    protected $alsBackup;
    protected $modBackup;

    public function setUp() {
        parent::setUp();
        if (file_exists($this->globalFilename)) {
            copy($this->globalFilename, $this->globalFilename . '.bak');
        }
        if (file_exists($this->moduleFilename)) {
            copy($this->moduleFilename, $this->moduleFilename . '.bak');
        }

        LanguageManager::clearLanguageCache('Contacts');

        sugar_mkdir(dirname($this->globalFilename), null, true);

        $GLOBALS['current_language'] = 'en_us';
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', array('Contacts'));

        $this->alsBackup = $GLOBALS['app_list_strings'];
        $this->modBackup = $GLOBALS['mod_strings'];
    }

    public function tearDown() {
        parent::tearDown();
        SugarAutoLoader::delFromMap($this->globalFilename);
        SugarAutoLoader::delFromMap($this->moduleFilename);
        if (file_exists($this->globalFilename . '.bak')) {
            copy($this->globalFilename . '.bak', $this->globalFilename);
        }
        if (file_exists($this->moduleFilename . '.bak')) {
            copy($this->moduleFilename . '.bak', $this->moduleFilename);
        }
        $GLOBALS['current_language'] = $GLOBALS['sugar_config']['default_language'];
        $GLOBALS['app_list_strings'] = $this->alsBackup;
        $GLOBALS['mod_strings'] = $this->modBackup;
    }

    public function testUpgradeRename() {
        $toWrite = "<?php
\$app_list_strings['moduleListSingular']['Contacts']='Property Contact';
\$app_list_strings['moduleList']['Contacts']='Property Contacts';";
        sugar_file_put_contents($this->globalFilename, $toWrite);
        $GLOBALS['app_list_strings']['moduleListSingular']['Contacts'] = 'Property Contact';
        $GLOBALS['app_list_strings']['moduleList']['Contacts'] = 'Property Contacts';

        $this->upgrader->setVersions('6.7.3', 'ent', '7.1.5', 'ent');
        $script = $this->upgrader->getScript('post', '7_RenameModules');
        $script->run();
        /*
         * Ensure that even on the second run it still stays as "Property Contacts"
         * instead of "Property Property Contacts"
         */
        $script->run();

        include($this->globalFilename);
        $this->assertEquals($app_list_strings['moduleListSingular']['Contacts'], 'Property Contact');
        $this->assertEquals($app_list_strings['moduleList']['Contacts'], 'Property Contacts');
    }

    public function testUpgradeRenameWithIntendedDouble() {
        $toWrite = "<?php
\$app_list_strings['moduleListSingular']['Contacts']='New Contact';
\$app_list_strings['moduleList']['Contacts']='New Contacts';";
        sugar_file_put_contents($this->globalFilename, $toWrite);
        $GLOBALS['app_list_strings']['moduleListSingular']['Contacts'] = 'New Contact';
        $GLOBALS['app_list_strings']['moduleList']['Contacts'] = 'New Contacts';

        $this->upgrader->setVersions('6.7.3', 'ent', '7.1.5', 'ent');
        $script = $this->upgrader->getScript('post', '7_RenameModules');
        $script->run();

        include($this->globalFilename);
        $mod_strings = return_module_language('en_us', 'Contacts');
        $this->assertEquals($app_list_strings['moduleListSingular']['Contacts'], 'New Contact');
        $this->assertEquals($app_list_strings['moduleList']['Contacts'], 'New Contacts');
        $this->assertEquals($mod_strings['LBL_NEW_FORM_TITLE'], 'New New Contact');
    }
}
