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

class RestPublicMetadataViewTemplatesTest extends RestTestBase {
    public function setUp()
    {
        parent::setUp();

        $this->oldFiles = array();
    }

    public function tearDown()
    {
        foreach ( $this->oldFiles as $filename => $filecontents ) {
            if ( $filecontents == '_NO_FILE' ) {
                if ( file_exists($filename) ) {
                    SugarAutoLoader::unlink($filename);
                }
            } else {
                SugarAutoLoader::put($filename,$filecontents);
            }
        }
        SugarAutoLoader::saveMap();
        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testMetadataViewTemplates() {
        $restReply = $this->_restCall('metadata/public?type_filter=views');

        $this->assertTrue(isset($restReply['reply']['views']['_hash']),'Views hash is missing.');
    }

    /**
     * @group rest
     */
    public function testMetadataViewTemplatesHbt() {
        $filesToCheck = array(
            //BEGIN SUGARCRM flav=ent ONLY
            'portal' => array(
                'clients/portal/views/edit/edit.hbt',
                'custom/clients/portal/views/edit/edit.hbt',
            ),
            //END SUGARCRM flav=ent ONLY
            'base' => array(
                'clients/base/views/edit/edit.hbt',
                'custom/clients/base/views/edit/edit.hbt',
            ),
        );

        foreach ( $filesToCheck as $platformFiles) {
            foreach ($platformFiles as $filename){
                if ( file_exists($filename) ) {
                    $this->oldFiles[$filename] = file_get_contents($filename);
                } else {
                    $this->oldFiles[$filename] = '_NO_FILE';
                }
            }
        }

        $dirsToMake = array(
            //BEGIN SUGARCRM flav=ent ONLY
            'portal' => array(
                'clients/portal/views/edit',
                'custom/clients/portal/views/edit',
            ),
            //END SUGARCRM flav=ent ONLY
            'base' => array(
                'clients/base/views/edit',
                'custom/clients/base/views/edit',
            ),
        );

        foreach ($dirsToMake as $platformDirs) {
            foreach ($platformDirs as $dir) {
                SugarAutoLoader::ensureDir($dir);
            }
        }

        //BEGIN SUGARCRM flav=ent ONLY
        // Make sure we get it when we ask for portal
        SugarAutoLoader::put($filesToCheck['portal'][0],'PORTAL CODE', true);
        $this->_clearMetadataCache();
        // To test public API's, we need to not login.
        $this->authToken = 'LOGGING_IN';
        $restReply = $this->_restCall('metadata/public?type_filter=views&platform=portal');
        $this->assertEquals('PORTAL CODE',$restReply['reply']['views']['edit']['templates']['edit'],"Didn't get portal code when that was the direct option");


        // Make sure we get it when we ask for portal, even though there is base code there
        SugarAutoLoader::put($filesToCheck['base'][0],'BASE CODE', true);
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('metadata/public?type_filter=views&platform=portal');
        $this->assertEquals('PORTAL CODE',$restReply['reply']['views']['edit']['templates']['edit'],"Didn't get portal code when base code was there.");
        //END SUGARCRM flav=ent ONLY

        // Make sure we get the base code when we ask for it.
        //BEGIN SUGARCRM flav=com ONLY
        SugarAutoLoader::put($filesToCheck['base'][0],'BASE CODE', true);
        $this->_clearMetadataCache();
        //END SUGARCRM flav=com ONLY
        $restReply = $this->_restCall('metadata/public?type_filter=views&platform=base');
        $this->assertEquals('BASE CODE',$restReply['reply']['views']['edit']['templates']['edit'],"Didn't get base code when it was the direct option");

        //BEGIN SUGARCRM flav=ent ONLY
        // Delete the portal template and make sure it falls back to base
        SugarAutoLoader::unlink($filesToCheck['portal'][0], true);
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('metadata/public?type_filter=views&platform=portal');
        $this->assertEquals('BASE CODE',$restReply['reply']['views']['edit']['templates']['edit'],"Didn't fall back to base code when portal code wasn't there.");


        // Make sure the portal code is loaded before the non-custom base code
        SugarAutoLoader::put($filesToCheck['portal'][1],'CUSTOM PORTAL CODE', true);
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('metadata/public?type_filter=views&platform=portal');
        $this->assertEquals('CUSTOM PORTAL CODE',$restReply['reply']['views']['edit']['templates']['edit'],"Didn't use the custom portal code.");
        //END SUGARCRM flav=ent ONLY

        // Make sure custom base code works
        SugarAutoLoader::put($filesToCheck['base'][1],'CUSTOM BASE CODE', true);
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('metadata/public?type_filter=views');
        $this->assertEquals('CUSTOM BASE CODE',$restReply['reply']['views']['edit']['templates']['edit'],"Didn't use the custom base code.");

    }
}
