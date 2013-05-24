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

class RestMetadataTest extends RestTestBase {
    public $createdFiles = array();

    public function tearDown()
    {
        // Cleanup
        foreach($this->createdFiles as $file)
        {
        	if (is_file($file)) {
        		SugarAutoLoader::unlink($file, true);
            }
        }

        parent::tearDown();
    }
    /**
     * @group rest
     */
    public function testFullMetadata() {
        $restReply = $this->_restCall('metadata');
        $this->assertTrue(isset($restReply['reply']['_hash']),'Primary hash is missing.');
        $this->assertTrue(isset($restReply['reply']['modules']),'Modules are missing.');

        $this->assertTrue(isset($restReply['reply']['fields']),'SugarFields are missing.');
        $this->assertTrue(isset($restReply['reply']['views']),'Views are missing.');
        $this->assertTrue(isset($restReply['reply']['currencies']),'Currencies are missing.');
        $this->assertTrue(isset($restReply['reply']['jssource']),'JSSource is missing.');
        // SIDECAR-14 - Move server info into the metadata api
        $this->assertTrue(isset($restReply['reply']['server_info']), 'ServerInfo is missing');
    }

    //BEGIN SUGARCRM flav=ent ONLY
    /**
     * @group rest
     */
    public function testFullMetadaNoAuth() {
        $restReply = $this->_restCall('metadata/public?app_name=superAwesome&platform=portal');
        $this->assertTrue(isset($restReply['reply']['_hash']),'Primary hash is missing.');
        $this->assertTrue(isset($restReply['reply']['config']), 'Portal Configs are missing.');
        $this->assertTrue(isset($restReply['reply']['fields']),'SugarFields are missing.');
        $this->assertTrue(isset($restReply['reply']['views']),'Views are missing.');
        $this->assertTrue(isset($restReply['reply']['jssource']),'JSSource is missing.');
        // SIDECAR-14 - Move server info into the metadata api
        $this->assertFalse(isset($restReply['reply']['server_info']), 'ServerInfo should not be in public metadata');
    }

    /**
     * @group rest
     */
    public function testMetadataLanguage() {
        if ( get_class(SugarCache::instance()) == 'SugarCacheAPC' ) {
            $this->markTestSkipped('This test will not pass with APC enabled.');
            return;
        }
        $langContent = "<?php\n\$app_strings['LBL_KEYBOARD_SHORTCUTS_HELP_TITLE'] = 'UnitTest';\n";

        $fileLoc = "custom/include/language/en_us.lang.php";
        $this->createdFiles[] = $fileLoc;
        SugarAutoLoader::ensureDir('custom/include/language');
        SugarAutoLoader::put($fileLoc, $langContent, true);
        sugar_cache_clear('app_strings.en_us');
        $langStrings = return_application_language('en_us');
        $this->assertEquals($langStrings['LBL_KEYBOARD_SHORTCUTS_HELP_TITLE'], "UnitTest",'The override is not taking effect in the same instance, there is no hope for the rest of this test.');
        // No current user
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('metadata/public?platform=portal&type_filter=labels');

        $this->assertArrayHasKey('en_us',$restReply['reply']['labels']);
        $fileLoc = ltrim($GLOBALS['sugar_config']['site_url'],$restReply['reply']['labels']['en_us']);
        $en_us = json_decode(file_get_contents($GLOBALS['sugar_config']['site_url'] .'/'. $restReply['reply']['labels']['en_us']),true);
        $this->assertEquals($en_us['app_strings']['LBL_KEYBOARD_SHORTCUTS_HELP_TITLE'], "UnitTest");

        // Current user is logged in & submit language
        $restReply = $this->_restCall('metadata');
        $this->assertArrayHasKey('en_us',$restReply['reply']['labels']);
        $en_us = json_decode(file_get_contents($GLOBALS['sugar_config']['site_url'].'/'.$restReply['reply']['labels']['en_us']),true);
        $this->assertEquals($en_us['app_strings']['LBL_KEYBOARD_SHORTCUTS_HELP_TITLE'], "UnitTest");

        // TODO add test for user pref when that field gets added

    }
    //END SUGARCRM flav=ent ONLY
}
