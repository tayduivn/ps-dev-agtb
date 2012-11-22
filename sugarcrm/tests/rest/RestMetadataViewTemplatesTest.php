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

class RestMetadataViewTemplatesTest extends RestTestBase {
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
        $restReply = $this->_restCall('metadata?type_filter=views');

        $this->assertTrue(isset($restReply['reply']['views']['_hash']),'Views hash is missing.');
    }
    /**
     * @group rest
     */
    public function testMetadataViewTemplatesHbt() {
        $filesToCheck = array(
            'base' => array(
                'clients/base/views/edit/edit.hbt',
                'custom/clients/base/views/edit/edit.hbt',
            ),
            //BEGIN SUGARCRM flav=pro ONLY
            'mobile' => array(
                'clients/mobile/views/edit/edit.hbt',
                'custom/clients/mobile/views/edit/edit.hbt',
            ),
            //END SUGARCRM flav=pro ONLY
        );

        foreach ( $filesToCheck as $client => $files) {
            foreach ($files as $filename ) {
                if ( file_exists($filename) ) {
                    $this->oldFiles[$filename] = file_get_contents($filename);
                } else {
                    $this->oldFiles[$filename] = '_NO_FILE';
                }
            }
        }

        $dirsToMake = array(
            'base' => array(
                'clients/base/views/edit',
                'custom/clients/base/views/edit',
            ),
            //BEGIN SUGARCRM flav=ent ONLY
            'mobile' => array(
                'clients/mobile/views/edit',
                'custom/clients/mobile/views/edit',
            ),
            //END SUGARCRM flav=ent ONLY
        );

        foreach ($dirsToMake as $client => $dirs ) {
            foreach ($dirs as $dir) {
                SugarAutoLoader::ensureDir($dir);
            }
        }

        //BEGIN SUGARCRM flav=pro ONLY
        // Make sure we get it when we ask for mobile
        SugarAutoLoader::put($filesToCheck['mobile'][0],'MOBILE CODE', true);
        $this->_clearMetadataCache();
        $this->authToken = $this->mobileAuthToken;
        $restReply = $this->_restCall('metadata/?type_filter=views');
        $this->assertEquals('MOBILE CODE',$restReply['reply']['views']['edit']['templates']['edit'],"Didn't get mobile code when that was the direct option");


        // Make sure we get it when we ask for mobile, even though there is base code there
        SugarAutoLoader::put($filesToCheck['base'][0],'BASE CODE', true);
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('metadata/?type_filter=views');
        $this->assertEquals('MOBILE CODE',$restReply['reply']['views']['edit']['templates']['edit'],"Didn't get mobile code when base code was there.");
        //END SUGARCRM flav=ent ONLY

        // Make sure we get the base code when we ask for it.
        //BEGIN SUGARCRM flav=com ONLY
        SugarAutoLoader::put($filesToCheck['base'][0],'BASE CODE', true);
        $this->_clearMetadataCache();
        //END SUGARCRM flav=com ONLY
        $this->authToken = $this->baseAuthToken;
        $restReply = $this->_restCall('metadata/?type_filter=views');
        $this->assertEquals('BASE CODE',$restReply['reply']['views']['edit']['templates']['edit'],"Didn't get base code when it was the direct option");

        //BEGIN SUGARCRM flav=ent ONLY
        // Delete the mobile template and make sure it falls back to base
        SugarAutoLoader::unlink($filesToCheck['mobile'][0], true);
        $this->_clearMetadataCache();
        $this->authToken = $this->mobileAuthToken;
        $restReply = $this->_restCall('metadata/?type_filter=views');
        $this->assertEquals('BASE CODE',$restReply['reply']['views']['edit']['templates']['edit'],"Didn't fall back to base code when mobile code wasn't there.");


        // Make sure the mobile code is loaded before the non-custom base code
        SugarAutoLoader::put($filesToCheck['mobile'][1],'CUSTOM MOBILE CODE', true);
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('metadata/?type_filter=views');
        $this->assertEquals('CUSTOM MOBILE CODE',$restReply['reply']['views']['edit']['templates']['edit'],"Didn't use the custom mobile code.");
        //END SUGARCRM flav=ent ONLY

        // Make sure custom base code works
        SugarAutoLoader::put($filesToCheck['base'][1],'CUSTOM BASE CODE', true);
        $this->_clearMetadataCache();
        $this->authToken = $this->baseAuthToken;
        $restReply = $this->_restCall('metadata/?type_filter=views');
        $this->assertEquals('CUSTOM BASE CODE',$restReply['reply']['views']['edit']['templates']['edit'],"Didn't use the custom base code.");

    }
}
