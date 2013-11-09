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
        $this->assertEquals('snazzy', $languages['enabled'][2], "Incorret value for disabled language 2");
        $this->assertEquals('br_ikea', $languages['disabled'][1], "Incorret value for disabled language 1");
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

        $GLOBALS['sugar_config']['disabled_languages'] = array (
            'whiskey',
            'br_ikea',
        );
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
        );

        $expectedConfigs = array(
            'maxQueryResult' => 1,
            'maxRecordFetchSize' => 2,
            'massActions' => array(
                'massUpdateChunkSize' => 3,
            )
        );

        $manager = $this->getMock('MetadataManagerMock', array('getSugarConfig'));
        $manager->expects($this->any())
            ->method('getSugarConfig')
            ->will($this->returnValue($sugarConfig));

        $this->assertEquals($expectedConfigs, $manager->getConfigs());
    }
}

class MetadataManagerMock extends MetadataManager
{
    public function getConfigs()
    {
        return parent::getConfigs();
    }
}
