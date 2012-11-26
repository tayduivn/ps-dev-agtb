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

class RestPublicMetadataSugarViewsTest extends RestTestBase {
    public function setUp()
    {
        parent::setUp();
        $this->oldFiles = array();
        
        // Make sure the public test doesn't used a logged in client
        $this->authToken = 'LOGGING_IN';
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
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        parent::tearDown();
    }
    /**
     * @group rest
     */
    public function testMetadataSugarViews() {
        $restReply = $this->_restCall('metadata/public?type_filter=views');

        $this->assertTrue(isset($restReply['reply']['views']['_hash']),'SugarView hash is missing.');
    }
    /**
     * @group rest
     */
    public function testMetadataSugarViewsTemplates() {
        $filesToCheck = array(
            'clients/mobile/views/address/editView.hbt',
            'clients/mobile/views/address/detailView.hbt',
            'clients/portal/views/address/editView.hbt',
            'clients/portal/views/address/detailView.hbt',
            'clients/base/views/address/editView.hbt',
            'clients/base/views/address/detailView.hbt',
            'custom/clients/mobile/views/address/editView.hbt',
            'custom/clients/mobile/views/address/detailView.hbt',
            'custom/clients/portal/views/address/editView.hbt',
            'custom/clients/portal/views/address/detailView.hbt',
            'custom/clients/base/views/address/editView.hbt',
            'custom/clients/base/views/address/detailView.hbt',
        );

        foreach ( $filesToCheck as $filename ) {
            if ( file_exists($filename) ) {
                $this->oldFiles[$filename] = file_get_contents($filename);
            } else {
                $this->oldFiles[$filename] = '_NO_FILE';
            }
        }

        $dirsToMake = array('clients/mobile/views/address',
                            'clients/portal/views/address',
                            'clients/base/views/address',
                            'custom/clients/mobile/views/address',
                            'custom/clients/portal/views/address',
                            'custom/clients/base/views/address',
                            'clients/mobile/views/address',

        );

        foreach ($dirsToMake as $dir ) {
            SugarAutoLoader::ensureDir($dir);
        }

        // Make sure we get it when we ask for mobile
        SugarAutoLoader::put('clients/mobile/views/address/editView.hbt','MOBILE EDITVIEW', true);
        $restReply = $this->_restCall('metadata/public?type_filter=views&platform=mobile');
        $this->assertEquals('MOBILE EDITVIEW',$restReply['reply']['views']['address']['templates']['editView'],"Didn't get mobile code when that was the direct option");


        // Make sure we get it when we ask for mobile, even though there is base code there
        SugarAutoLoader::put('clients/base/views/address/editView.hbt','BASE EDITVIEW', true);
        $restReply = $this->_restCall('metadata/public?type_filter=views&platform=mobile');
        $this->assertEquals('MOBILE EDITVIEW',$restReply['reply']['views']['address']['templates']['editView'],"Didn't get mobile code when base code was there.");


        // Make sure we get the base code when we ask for it.
        $restReply = $this->_restCall('metadata/public?type_filter=views&platform=base');
        $this->assertEquals('BASE EDITVIEW',$restReply['reply']['views']['address']['templates']['editView'],"Didn't get base code when it was the direct option");
        $this->assertTrue(!isset($restReply['reply']['views']['mobile']), "Only should be base platform");

        // Delete the mobile address and make sure it falls back to base
        SugarAutoLoader::unlink('clients/mobile/views/address/editView.hbt', true);
        $restReply = $this->_restCall('metadata/public?type_filter=views&platform=mobile');
        $this->assertEquals('BASE EDITVIEW',$restReply['reply']['views']['address']['templates']['editView'],"Didn't fall back to base code when mobile code wasn't there.");


        // Make sure the mobile code is loaded before the non-custom base code
        SugarAutoLoader::put('custom/clients/mobile/views/address/editView.hbt','CUSTOM MOBILE EDITVIEW', true);
        $restReply = $this->_restCall('metadata/public?type_filter=views&platform=mobile');
        $this->assertEquals('CUSTOM MOBILE EDITVIEW',$restReply['reply']['views']['address']['templates']['editView'],"Didn't use the custom mobile code.");

        // Make sure custom base code works
        SugarAutoLoader::put('custom/clients/base/views/address/editView.hbt','CUSTOM BASE EDITVIEW', true);
        $restReply = $this->_restCall('metadata/public?type_filter=views&platform=base');
        $this->assertEquals('CUSTOM BASE EDITVIEW',$restReply['reply']['views']['address']['templates']['editView'],"Didn't use the custom base code.");
    }


}
