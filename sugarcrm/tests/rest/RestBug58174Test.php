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
require_once 'tests/rest/RestTestBase.php';
require_once 'include/MetaDataManager/MetaDataManager.php';

/**
 * Bug 58174 - Studio escapes labels
 */
class RestBug58174Test extends RestTestBase
{
    protected $_testLangFile;
    private $_customContents;
    
    public function setUp()
    {
        parent::setUp();

        /*
        if(file_exists('cache/modules/Notes/language/en_us.lang.php')) {
           unlink('cache/modules/Notes/language/en_us.lang.php');
        }
        */

        // Create a custom language file
        $this->_testLangFile = 'custom/modules/Notes/language/en_us.lang.php';

        if (file_exists($this->_testLangFile)) {
            $this->_customContents = file_get_contents($this->_testLangFile);
        } else {
            mkdir_recursive(dirname($this->_testLangFile));
        }
        
        // Write our test file
        $content = "<?php
         \$mod_strings = array (
           'LBL_ASSIGNED_TO_ID' => 'Assigned User&#039;s Id',
           'LBL_ACCOUNT_ID' => 'Account&#039;s ID:',
         );";

        file_put_contents($this->_testLangFile, $content);
        SugarAutoLoader::addToMap($this->_testLangFile);

        LanguageManager::refreshLanguage('Notes', 'en_us');

        // Clear the metadata cache to ensure a fresh load of data
        $this->_clearMetadataCache();
    }
    
    public function tearDown()
    {
        // Get rid of our test file and restore if there's a need
        unlink($this->_testLangFile);
        if (!empty($this->_customContents)) {
            file_put_contents($this->_testLangFile, $this->_customContents);
        } else {
            SugarAutoLoader::delFromMap($this->_testLangFile);
        }
        
        parent::tearDown();
    }

    /**
     * @group Bug58174
     * @group rest
     */
    public function testHtmlEntitiesAreConvertedInMetaDataManager()
    {
        $mm = new RestBug58174MetaDataManager($this->_user);
        $data = array(
            'TEST_LBL_1' => 'Test&#039;s Label',
            'TEST_LBL_GRP_1' => array(
                'TEST_LBL_GRP_A' => 'Nothing',
                'TEST_LBL_GRP_B' => 'Billy&#039;s'
            ),
            'TEST_LBL_2' => 'Nothing Else',
        );
        
        $values = $mm->getDecodedStrings($data);
        
        $this->assertTrue(isset($values['TEST_LBL_1']), "'TEST_LBL_1' was not set in the result");
        $this->assertEquals("Test's Label", $values['TEST_LBL_1'], "Test encoded value was not properly decoded for 'TEST_LBL_1'");
        
        $this->assertTrue(isset($values['TEST_LBL_GRP_1']['TEST_LBL_GRP_B']), "'TEST_LBL_GRP_B' was not set in the result");
        $this->assertEquals("Billy's", $values['TEST_LBL_GRP_1']['TEST_LBL_GRP_B'], "Test encoded value was not properly decoded for 'TEST_LBL_GRP_B'");
    }

    /**
     * @group Bug58174
     * @group rest
     */
    public function testHtmlEntitiesAreConvertedInMetadataRequest()
    {
        $this->_clearMetadataCache();
        $reply = $this->_restCall('metadata?module_filter=Notes&type_filter=labels');

        //$this->assertFileExists($reply['reply']['labels']['en_us'], "Language file does not exist");

        $json = file_get_contents($GLOBALS['sugar_config']['site_url'] . '/' . $reply['reply']['labels']['en_us']);

        $object = json_decode($json, true);

        $this->assertTrue(isset($object['mod_strings']['Notes']['LBL_ASSIGNED_TO_ID']), "'LBL_ASSIGNED_TO_ID' mod strings for the Notes module was not returned");
        $this->assertEquals("Assigned User's Id", $object['mod_strings']['Notes']['LBL_ASSIGNED_TO_ID'], "Returned value for 'LBL_ASSIGNED_TO_ID' was not decoded properly");
        
        $this->assertTrue(isset($object['mod_strings']['Notes']['LBL_ACCOUNT_ID']), "'LBL_ACCOUNT_ID' mod strings for the Notes module was not returned");
        $this->assertEquals("Account's ID:", $object['mod_strings']['Notes']['LBL_ACCOUNT_ID'], "Returned value for 'LBL_ACCOUNT_ID' was not decoded properly");
    }
}

class RestBug58174MetaDataManager extends MetaDataManager
{
    public function getDecodedStrings($source) {
        return $this->decodeStrings($source);
    }
}