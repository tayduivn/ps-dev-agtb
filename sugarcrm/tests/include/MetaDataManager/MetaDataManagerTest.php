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

require_once 'include/MetaDataManager/MetaDataManager.php';
class MetaDataManagerTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $mm;
    protected $configBackup;

    public function setup()
    {
        // Backup current language settings so manipulation can be tested
        $this->configBackup['languages'] = $GLOBALS['sugar_config']['languages'];
        if (isset($GLOBALS['sugar_config']['disabled_languages'])) {
            $this->configBackup['disabled_languages'] = $GLOBALS['sugar_config']['disabled_languages'];
        }

        $this->setTestLanguageSettings();

        SugarTestHelper::setup('current_user', array(true, true));
        $this->mm = MetaDataManager::getManager();
    }

    public function tearDown()
    {
        // Restore changed config stuff
        $GLOBALS['sugar_config']['languages'] = $this->configBackup['languages'];
        if (isset($this->configBackup['disabled_languages'])) {
            $GLOBALS['sugar_config']['disabled_languages'] = $this->configBackup['disabled_languages'];
        }

        MetaDataFiles::clearModuleClientCache();
    }

    public function testGetAllLanguages()
    {

        $languages = $this->mm->getAllLanguages();

        $this->assertArrayHasKey('enabled', $languages, "Enabled languages is missing.");
        $this->assertArrayHasKey('disabled', $languages, "Disabled languages is missing.");
        $this->assertNotEmpty($languages['enabled'], "Enabled languages is empty.");
        $this->assertNotEmpty($languages['disabled'], "Disabled languages is empty");

        // Test content of each list
        $this->assertArrayHasKey(2, $languages['enabled'], "Missing element of enabled languages");
        $this->assertArrayHasKey(1, $languages['disabled'], "Missing element of disabled languages");
        $this->assertEquals('snazzy', $languages['enabled'][2], "Incorrect value for disabled language 2");
        $this->assertEquals('br_ikea', $languages['disabled'][1], "Incorrect value for disabled language 1");
    }

    /**
     * This is a functional test rather than a unit test.
     * This is due to MetaDataManager and MetaDataFiles not having
     * any tests.
     *
     * This test covers two scenarios,
     * the first one is if there exists no data, than we should expect
     * the metadata to not pick up any controllers.
     *
     * The second scenario covers metadata manager picking up on
     * provided collection / model controllers.
     */
    public function testFinalMetadataJSSource()
    {

        // Scenario 1
        // Create empty module with correct metadata structure.
        sugar_mkdir("modules/TestModule/clients/base/datas/model", 0700, true);

        $moduleMeta = $this->mm->getModuleDatas('TestModule');

        // We verify our assumptions that we should have an empty set of metadata.
        $this->assertArrayHasKey("_hash", $moduleMeta, "Metadata does not contain a hash");
        $this->assertEquals(count($moduleMeta), 1, "Metadata has incorrect amount of elements");

        // Clear our metadata cache.
        MetaDataFiles::clearModuleClientCache("TestModule");

        // Scenario 2
        // Add a model controller to our datas directory.
        SugarAutoLoader::touch("modules/TestModule/clients/base/datas/model/model.js");
        $moduleMeta = $this->mm->getModuleDatas('TestModule');

        // We now verify if we have additional controller metadata in our return.
        $this->assertArrayHasKey("model", $moduleMeta, "Metadata does not contain a controller");
        $this->assertEquals(count($moduleMeta), 2, "Metadata doesn't include the controller");

        // Clean up our test.
        MetaDataFiles::clearModuleClientCache("TestModule");
        SugarAutoLoader::unlink("modules/TestModule/clients/base/datas/model/model.js");
        rmdir_recursive("modules/TestModule/");
        SugarAutoLoader::buildCache();
    }

    protected function setTestLanguageSettings()
    {
        $GLOBALS['sugar_config']['languages'] = array (
            'br_test' => 'Test Language',
            'br_mine' => 'My Language',
            'snazzy'  => 'Snazzy Language',
            'whiskey' => 'Whiskey Language',
            'awesome' => 'Awesome Sauce',
            'br_ikea' => 'Ikead an idea',
        );

        $GLOBALS['sugar_config']['disabled_languages'] = "whiskey,br_ikea";
    }

    public function testGetConfigs()
    {
        $sugarConfig = array(
            'list_max_entries_per_page' => 1,
            'max_record_fetch_size' => 2,
            'mass_actions' => array(
                'mass_update_chunk_size' => 3,
                'not_on_white_list' => 4,
            ),
            'analytics' => array(
                'enabled' => true,
            )
        );

        $expectedConfigs = array(
            'maxQueryResult' => 1,
            'maxRecordFetchSize' => 2,
            'massActions' => array(
                'massUpdateChunkSize' => 3,
            ),
            'analytics' => array(
                'enabled' => true,
            )
        );

        $manager = $this->getMock('MetadataManagerMock', array('getSugarConfig'));
        $manager->expects($this->any())
            ->method('getSugarConfig')
            ->will($this->returnValue($sugarConfig));

        $this->assertEquals($expectedConfigs, $manager->getConfigs());
    }

    public function testNormalizeMetadata()
    {
        // Test data, to be used for testing both mobile and base
        $data = array(
            'modules' => array(
                'Accounts' => array(
                    'menu' => true,
                    'views' => array(
                        'record' => true,
                    ),
                    'layouts' => array(
                        'record' => true,
                    ),
                ),
            ),
        );

        // Test base first, which should be equality
        $mm = MetaDataManager::getManager();
        $test = $mm->normalizeMetadata($data);
        $this->assertEquals($test, $data, "Base data was manipulated and it should not have been");
        
        $mm = MetaDataManager::getManager('mobile');
        $test = $mm->normalizeMetadata($data);
        $this->assertNotEquals($test, $data, "Mobile metadata was not manipulated and it should have been");
        $this->assertFalse(isset($test['modules']['Accounts']['menu']));
        $this->assertEmpty($test['modules']['Accounts']['views']);
        $this->assertEmpty($test['modules']['Accounts']['layouts']);
    }

    public function testGetAppListStrings() {
        $mm = MetaDataManager::getManager();
        $normalList = $mm->getAppListStrings('en_us');
        $tupleList = $mm->getAppListStrings('en_us', true);

        //Would be nice to mock the app_list_strings, but this currently isn't possible with return_app_list_strings_language
        $this->assertEquals($normalList['checkbox_dom'], array(
                '' => '',
                '1' => 'Yes',
                '2' => 'No',
            )
        );

        $this->assertEquals($tupleList['checkbox_dom'], array(
                array('', ''),
                array('1', 'Yes'),
                array('2', 'No'),
            )
        );
    }
}

class MetadataManagerMock extends MetadataManager
{
    public function getConfigs()
    {
        return parent::getConfigs();
    }
}
