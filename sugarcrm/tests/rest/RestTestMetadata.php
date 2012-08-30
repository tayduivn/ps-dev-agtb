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

class RestTestMetadata extends RestTestBase {
    public function testFullMetadata() {
        $restReply = $this->_restCall('metadata');

        $this->assertTrue(isset($restReply['reply']['_hash']),'Primary hash is missing.');
        $this->assertTrue(isset($restReply['reply']['modules']),'Modules are missing.');
    
        $this->assertTrue(isset($restReply['reply']['fields']),'SugarFields are missing.');
        $this->assertTrue(isset($restReply['reply']['viewTemplates']),'ViewTemplates are missing.');
        $this->assertTrue(isset($restReply['reply']['currencies']),'Currencies are missing.');
    }

    public function testFullMetadaNoAuth() {
        $restReply = $this->_restCall('metadata/public?app_name=superAwesome&platform=portal');
        $this->assertTrue(isset($restReply['reply']['_hash']),'Primary hash is missing.');
        $this->assertTrue(isset($restReply['reply']['config']), 'Portal Configs are missing.');
        $this->assertTrue(isset($restReply['reply']['fields']),'SugarFields are missing.');
        $this->assertTrue(isset($restReply['reply']['viewTemplates']),'ViewTemplates are missing.');
    }

    public function testMetadataLanguage() {
        $langContent = <<<EOQ
        <?php
        \$app_strings = array (
            'LBL_KEYBOARD_SHORTCUTS_HELP_TITLE' => 'UnitTest',
            );
EOQ;

        $fileLoc = "include/language/ua_UA.lang.php";
        $this->createdFiles[] = $fileLoc;
        file_put_contents($fileLoc, $langContent);
        // No current user
        $restReply = $this->_restCall('metadata/public?lang=ua_UA&app_name=superAwesome&platform=portal');
        $this->assertEquals($restReply['reply']['appStrings']['LBL_KEYBOARD_SHORTCUTS_HELP_TITLE'], "UnitTest");

        // Current user is logged in & submit language
        $restReply = $this->_restCall('metadata?lang=ua_UA&app_name=superAwesome&platform=portal');

        $this->assertEquals($restReply['reply']['appStrings']['LBL_KEYBOARD_SHORTCUTS_HELP_TITLE'], "UnitTest");

        // TODO add test for user pref when that field gets added

        // Cleanup
        foreach($this->createdFiles as $file)
        {
            if (is_file($file))
                unlink($file);
        }
    }

}
