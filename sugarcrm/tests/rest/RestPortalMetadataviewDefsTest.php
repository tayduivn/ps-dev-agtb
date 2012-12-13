<?php
//FILE SUGARCRM flav=ent ONLY
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

require_once('tests/rest/RestTestPortalBase.php');

class RestMetadataViewDefsTest extends RestTestPortalBase {
    public $testMetaDataFiles = array(
        //BEGIN SUGARCRM flav=ent ONLY
        'contacts' => 'custom/modules/Contacts/clients/portal/layouts/banana/banana.php',
        'cases'     => 'modules/Cases/clients/portal/views/ghostrider/ghostrider.php'
        //END SUGARCRM flav=ent ONLY
    );

    public function tearDown()
    {
        foreach($this->testMetaDataFiles as $file ) {
            if (file_exists($file)) {
                // Ignore the warning on this, the file stat cache causes the file_exist to trigger even when it's not really there
                @SugarAutoLoader::unlink($file);

                // Remove the stray directory since metadata manager will pick it up
                $dirname = dirname($file);
                rmdir($dirname);
                SugarAutoLoader::delFromMap($dirname);
            }
        }
        SugarAutoLoader::saveMap();

        parent::tearDown();
    }

    //BEGIN SUGARCRM flav=ent ONLY
    /**
     * @group rest
     */
    public function testDefaultPortalLayoutMetaData() {
        $restReply = $this->_restCall('metadata?type_filter=modules&module_filter=Contacts');
        // Hash should always be set
        $this->assertTrue(isset($restReply['reply']['modules']['Contacts']['layouts']['_hash']), "Portal layouts missing hash empty");
        unset($restReply['reply']['modules']['Contacts']['layouts']['_hash']);

        // Now the layouts should be empty
        $this->assertTrue(empty($restReply['reply']['modules']['Contacts']['layouts']), "Portal layouts are not empty");
    }

    /**
     * @group rest
     */
    public function testDefaultPortalViewMetaData() {
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('metadata?type_filter=modules&module_filter=Cases');
        $this->assertTrue(empty($restReply['reply']['modules']['Cases']['views']['ghostrider']), "Test file found unexpectedly");
    }

    /**
     * @group rest
     */
    public function testAdditionalPortalLayoutMetaData() {
        SugarAutoLoader::ensureDir(dirname($this->testMetaDataFiles['contacts']));
        SugarAutoLoader::put($this->testMetaDataFiles['contacts'], "<?php\n\$viewdefs['Contacts']['portal']['layout']['banana'] = array('yummy' => 'Banana Split');", true);
        SugarAutoLoader::saveMap();

        $this->_clearMetadataCache();
        $restReply = $this->_restCall('metadata?type_filter=modules&module_filter=Contacts');
        $this->assertEquals('Banana Split',$restReply['reply']['modules']['Contacts']['layouts']['banana']['meta']['yummy'], "Failed to retrieve all layout metadata");
    }

    /**
     * @group rest
     */
    public function testAdditionalPortalViewMetaData() {
        SugarAutoLoader::ensureDir(dirname($this->testMetaDataFiles['cases']));
        SugarAutoLoader::put($this->testMetaDataFiles['cases'], "<?php\n\$viewdefs['Cases']['portal']['view']['ghostrider'] = array('pattern' => 'Full');", true);
        SugarAutoLoader::saveMap();

        $this->_clearMetadataCache();
        $restReply = $this->_restCall('metadata?type_filter=modules&module_filter=Cases');
        $this->assertEquals('Full',$restReply['reply']['modules']['Cases']['views']['ghostrider']['meta']['pattern'], "Failed to retrieve all view metadata");

    }

    /**
     * @group rest
     */
    public function testMetadataCacheBuild() {
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('metadata/public?type_filter=config&platform=portal');
        $this->assertArrayHasKey('_hash',$restReply['reply'],"Did not have a _hash on the first run");

        $restReply = $this->_restCall('metadata/public?type_filter=config&platform=portal');
        $this->assertArrayHasKey('_hash',$restReply['reply'],"Did not have a _hash on the second run");

    }

    //END SUGARCRM flav=ent ONLY

}
