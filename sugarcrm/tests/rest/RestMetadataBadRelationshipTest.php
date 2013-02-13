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
require_once 'data/Relationships/RelationshipFactory.php';
/**
 * Bug 57782 and 57780
 */

class RestMetadataBadRelationshipTest extends RestTestBase
{
    public function tearDown()
    {
        // delete file
        foreach($this->files AS $file) {
            unlink($file);
        }
        // re-run repair and rebuild
        $old_user = $GLOBALS['current_user'];
        $user = new User();

        $GLOBALS['current_user'] = $user->getSystemUser();

        // run repair and rebuild
        $_REQUEST['repair_silent']=1;
        $rc = new RepairAndClear();
        $rc->repairAndClearAll(array("clearAll"), array("Accounts"),  false, false);

        // switch back to the user
        $GLBOALS['current_user'] = $old_user;
        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testBadRelationship() {
        /**
         * For full suite runs immediately after installation (like for CI), the
         * relationship cache will have already been created. The cache needs to
         * be cleared prior to this run so that this gets picked up properly for
         * testing;
         */
        SugarRelationshipFactory::deleteCache();

        // write out a bad relationship vardef
        $metadata = '<?php
$dictionary[\'Account\'][\'fields\'][\'notes\'][\'relationship\'] = "accounts_notes_awesome";
';

        $metadata_dir = 'custom/Extension/modules/Accounts/Ext/Vardefs';
        $metadata_file = 'accounts_notes_field.php';
        if(!is_dir($metadata_dir)) {
            mkdir("{$metadata_dir}", 0777, true);
        }

        file_put_contents( $metadata_dir . '/' . $metadata_file, $metadata );

        $this->assertTrue(file_exists($metadata_dir . '/' . $metadata_file), "Did not write out the new cache file");

        $this->files[] = $metadata_dir . '/' . $metadata_file;

        // run repair and rebuild
        // save old user
        $old_user = $GLOBALS['current_user'];
        $user = new User();

        $GLOBALS['current_user'] = $user->getSystemUser();


        // run repair and rebuild
        $_REQUEST['repair_silent']=1;
        $rc = new RepairAndClear();
        $rc->repairAndClearAll(array("clearAll"), array("Accounts"),  false, false);

        // switch back to the user
        $GLBOALS['current_user'] = $old_user;

        // call module metadata
        $this->_restCall('metadata/flush', 'flush');
        $restReply = $this->_restCall('metadata?type_filter=modules');

        // verify no 500 and results for the module
        $this->assertNotEquals($restReply['info']['http_code'], 500,'HTTP Code is 500');

        $this->assertTrue(isset($restReply['reply']['modules']['Accounts']),'Account module is missing. Reply looked like: '.var_export($restReply['replyRaw'],true));

        $this->assertEquals($restReply['reply']['modules']['Accounts']['fields']['notes']['relationship'], 'accounts_notes_awesome', 'Did not rewrite relationship to accounts_notes_awesome, it is: ' . $restReply['reply']['modules']['Accounts']['fields']['notes']['relationship'] );

    }
}
