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

require_once('tests/rest/RestTestBase.php');

class RestMetadataModuleViewLayoutTest extends RestTestBase {
    public function setUp()
    {
        parent::setUp();

        $this->oldFiles = array();

//BEGIN SUGARCRM flav=pro ONLY
        $this->_restLogin('','','mobile');
        $this->mobileAuthToken = $this->authToken;
//END SUGARCRM flav=pro ONLY
        $this->_restLogin('','','base');
        $this->baseAuthToken = $this->authToken;

    }

    /**
     * @group rest
     */
    public function testMetadataSugarFields() {
        $this->_clearMetadataCache();
        $this->authToken = $this->mobileAuthToken;
        $restReply = $this->_restCall('metadata?type_filter=modules');

        $this->assertTrue(isset($restReply['reply']['modules']['Cases']['views']),'No views for the cases module');
    }

    /**
     * @group rest
     */
    public function testMetadataModuleLayout() {
        $filesToCheck = array('modules/Cases/clients/mobile/layouts/edit/edit.php',
                              'custom/modules/Cases/clients/mobile/layouts/edit/edit.php',
        );
        SugarTestHelper::saveFile($filesToCheck);

        $dirsToMake = array('modules/Cases/clients/mobile/layouts/edit',
                            'custom/modules/Cases/clients/mobile/layouts/edit',
        );

        foreach ($dirsToMake as $dir ) {
            SugarAutoLoader::ensureDir($dir);
        }

        // Make sure we get it when we ask for mobile
        SugarAutoLoader::put($filesToCheck[0],'<'."?php\n\$viewdefs['Cases']['mobile']['layout']['edit'] = array('unit_test'=>'Standard Dir');\n", true);
        $this->_clearMetadataCache();
        $this->authToken = $this->mobileAuthToken;
        $restReply = $this->_restCall('metadata/?type_filter=modules&module_filter=Cases');
        $this->assertEquals('Standard Dir',$restReply['reply']['modules']['Cases']['layouts']['edit']['meta']['unit_test'],"Didn't get the mobile layout");

        // Make sure we get the custom file
        SugarAutoLoader::put($filesToCheck[1],'<'."?php\n\$viewdefs['Cases']['mobile']['layout']['edit'] = array('unit_test'=>'Custom Dir');\n", true);
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('metadata/?type_filter=modules&module_filter=Cases');
        $this->assertEquals('Custom Dir',$restReply['reply']['modules']['Cases']['layouts']['edit']['meta']['unit_test'],"Didn't get the custom mobile layout");

        // Make sure it flops back to the standard file
        SugarAutoLoader::unlink($filesToCheck[1], true);
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('metadata/?type_filter=modules&module_filter=Cases');
        $this->assertEquals('Standard Dir',$restReply['reply']['modules']['Cases']['layouts']['edit']['meta']['unit_test'],"Didn't get the mobile layout");
    }

    /**
     * @group rest
     */
    public function testMetadataSubPanels()
    {
        $this->_clearMetadataCache();
        $this->authToken = $this->mobileAuthToken;
        $restReply = $this->_restCall('metadata?type_filter=modules');
        $this->assertTrue(isset($restReply['reply']['modules']['Cases']['subpanels']),'No subpanels for the cases module');
    }

    /**
     * @group rest
     */
    public function testMetadataFTS()
    {
        $this->_clearMetadataCache();
        $this->authToken = $this->mobileAuthToken;
        $restReply = $this->_restCall('metadata?typeFilter=modules');
        $this->assertTrue(isset($restReply['reply']['modules']['Cases']['ftsEnabled']),'No ftsEnabled for the cases module');
    }

    /**
     * @group rest
     */
    public function testMetadataFavorites()
    {
        $this->_clearMetadataCache();
        $this->authToken = $this->mobileAuthToken;
        $restReply = $this->_restCall('metadata?typeFilter=modules');
        $this->assertTrue(isset($restReply['reply']['modules']['Cases']['favoritesEnabled']),'No favoritesEnabled for the cases module');
    }

    /**
    * @group rest
    */
    public function testMetadataModuleViews() {
        $filesToCheck = array('modules/Cases/clients/mobile/views/edit/edit.php',
                              'custom/modules/Cases/clients/mobile/views/edit/edit.php',
        );
        SugarTestHelper::saveFile($filesToCheck);

        $dirsToMake = array('modules/Cases/clients/mobile/views/edit',
                            'custom/modules/Cases/clients/mobile/views/edit',
        );

        foreach ($dirsToMake as $dir ) {
            SugarAutoLoader::ensureDir($dir);
        }

        // Make sure we get it when we ask for mobile
        SugarAutoLoader::put($filesToCheck[0],'<'."?php\n\$viewdefs['Cases']['mobile']['view']['edit'] = array('unit_test'=>'Standard Dir');\n", true);
        $this->_clearMetadataCache();
        $this->authToken = $this->mobileAuthToken;
        $restReply = $this->_restCall('metadata/?type_filter=modules&module_filter=Cases');
        $this->assertEquals('Standard Dir',$restReply['reply']['modules']['Cases']['views']['edit']['meta']['unit_test'],"Didn't get the mobile view");

        // Make sure we get the custom file
        SugarAutoLoader::put($filesToCheck[1],'<'."?php\n\$viewdefs['Cases']['mobile']['view']['edit'] = array('unit_test'=>'Custom Dir');\n", true);
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('metadata/?type_filter=modules&module_filter=Cases');
        $this->assertEquals('Custom Dir',$restReply['reply']['modules']['Cases']['views']['edit']['meta']['unit_test'],"Didn't get the custom mobile view");

        // Make sure it flops back to the standard file
        SugarAutoLoader::unlink($filesToCheck[1], true);
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('metadata/?type_filter=modules&module_filter=Cases');
        $this->assertEquals('Standard Dir',$restReply['reply']['modules']['Cases']['views']['edit']['meta']['unit_test'],"Didn't get the mobile view");
    }

    /**
     * Test addresses a case related to the metadata location move that caused
     * metadatamanager to not roll up to sugar objects properly
     *
     * @group rest
     */
    public function testMobileMetaDataRollsUp()
    {
        $this->authToken = $this->mobileAuthToken;
        $reply = $this->_restCall('metadata?typeFilter=modules&moduleFilter=Contacts');
        $this->assertNotEmpty($reply['reply']['modules']['Contacts']['views']['list']['meta'], 'Contacts list view metadata was not fetched from SugarObjects');
    }
}
