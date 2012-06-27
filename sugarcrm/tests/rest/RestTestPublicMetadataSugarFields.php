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

class RestTestPublicMetadataSugarFields extends RestTestBase {
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
                    unlink($filename);
                }
            } else {
                file_put_contents($filename,$filecontents);
            }
        }
        
        parent::tearDown();
    }

    public function testMetadataSugarFields() {
        $restReply = $this->_restCall('metadata/public?typeFilter=fields');

        $this->assertTrue(isset($restReply['reply']['fields']['_hash']),'SugarField hash is missing.');
    }
    
    public function testMetadataSugarFieldsController() {
        $filesToCheck = array('clients/mobile/fields/address/address.js',
                              'clients/portal/fields/address/address.js',
                              'clients/base/fields/address/address.js',
                              'custom/clients/mobile/fields/address/address.js',
                              'custom/clients/portal/fields/address/address.js',
                              'custom/clients/base/fields/address/address.js',
        );
        
        foreach ( $filesToCheck as $filename ) {
            if ( file_exists($filename) ) {
                $this->oldFiles[$filename] = file_get_contents($filename);
            } else {
                $this->oldFiles[$filename] = '_NO_FILE';
            }
        }

        $dirsToMake = array('clients/mobile/fields/address',
                            'clients/portal/fields/address',
                            'clients/base/fields/address',
                            'custom/clients/mobile/fields/address',
                            'custom/clients/portal/fields/address',
                            'custom/clients/base/fields/address',
        );

        foreach ($dirsToMake as $dir ) {
            if (!is_dir($dir) ) {
                mkdir($dir,0777,true);
            }
        }
        
        // Make sure we get it when we ask for mobile
        file_put_contents('clients/mobile/fields/address/address.js','MOBILE CODE');
        $restReply = $this->_restCall('metadata/public?typeFilter=fields&platform=mobile');
        $this->assertEquals('MOBILE CODE',$restReply['reply']['fields']['address']['controller'],"Didn't get mobile code when that was the direct option");


        // Make sure we get it when we ask for mobile, even though there is base code there
        file_put_contents('clients/base/fields/address/address.js','BASE CODE');
        $restReply = $this->_restCall('metadata/public?typeFilter=fields&platform=mobile');
        $this->assertEquals('MOBILE CODE',$restReply['reply']['fields']['address']['controller'],"Didn't get mobile code when base code was there.");


        // Make sure we get the base code when we ask for it.
        $restReply = $this->_restCall('metadata/public?typeFilter=fields&platform=base');
        $this->assertEquals('BASE CODE',$restReply['reply']['fields']['address']['controller'],"Didn't get base code when it was the direct option");


        // Delete the mobile address and make sure it falls back to base
        unlink('clients/mobile/fields/address/address.js');
        $restReply = $this->_restCall('metadata/public?typeFilter=fields&platform=mobile');
        $this->assertEquals('BASE CODE',$restReply['reply']['fields']['address']['controller'],"Didn't fall back to base code when mobile code wasn't there.");


        // Make sure the mobile code is loaded before the non-custom base code
        file_put_contents('custom/clients/mobile/fields/address/address.js','CUSTOM MOBILE CODE');
        $restReply = $this->_restCall('metadata/public?typeFilter=fields&platform=mobile');
        $this->assertEquals('CUSTOM MOBILE CODE',$restReply['reply']['fields']['address']['controller'],"Didn't use the custom mobile code.");

        // Make sure custom portal code works
        file_put_contents('custom/clients/portal/fields/address/address.js','CUSTOM PORTAL CODE');
        $restReply = $this->_restCall('metadata/public?typeFilter=fields&platform=portal');
        $this->assertEquals('CUSTOM PORTAL CODE',$restReply['reply']['fields']['address']['controller'],"Didn't use the custom portal code.");

    }

    public function testMetadataSugarFieldsTemplates() {
        $filesToCheck = array(
            'clients/mobile/fields/address/editView.hbt',
            'clients/mobile/fields/address/detailView.hbt',
            'clients/portal/fields/address/editView.hbt',
            'clients/portal/fields/address/detailView.hbt',
            'clients/base/fields/address/editView.hbt',
            'clients/base/fields/address/detailView.hbt',
            'custom/clients/mobile/fields/address/editView.hbt',
            'custom/clients/mobile/fields/address/detailView.hbt',
            'custom/clients/portal/fields/address/editView.hbt',
            'custom/clients/portal/fields/address/detailView.hbt',
            'custom/clients/base/fields/address/editView.hbt',
            'custom/clients/base/fields/address/detailView.hbt',
        );

        foreach ( $filesToCheck as $filename ) {
            if ( file_exists($filename) ) {
                $this->oldFiles[$filename] = file_get_contents($filename);
            } else {
                $this->oldFiles[$filename] = '_NO_FILE';
            }
        }

        $dirsToMake = array('clients/mobile/fields/address',
                            'clients/portal/fields/address',
                            'clients/base/fields/address',
                            'custom/clients/mobile/fields/address',
                            'custom/clients/portal/fields/address',
                            'custom/clients/base/fields/address',
        );

        foreach ($dirsToMake as $dir ) {
            if (!is_dir($dir) ) {
                mkdir($dir,0777,true);
            }
        }

        // Make sure we get it when we ask for mobile
        file_put_contents('clients/mobile/fields/address/editView.hbt','MOBILE EDITVIEW');
        $restReply = $this->_restCall('metadata/public?typeFilter=fields&platform=mobile');
        $this->assertEquals('MOBILE EDITVIEW',$restReply['reply']['fields']['address']['templates']['editView'],"Didn't get mobile code when that was the direct option");


        // Make sure we get it when we ask for mobile, even though there is base code there
        file_put_contents('clients/base/fields/address/editView.hbt','BASE EDITVIEW');
        $restReply = $this->_restCall('metadata/public?typeFilter=fields&platform=mobile');
        $this->assertEquals('MOBILE EDITVIEW',$restReply['reply']['fields']['address']['templates']['editView'],"Didn't get mobile code when base code was there.");


        // Make sure we get the base code when we ask for it.
        $restReply = $this->_restCall('metadata/public?typeFilter=fields&platform=base');
        $this->assertEquals('BASE EDITVIEW',$restReply['reply']['fields']['address']['templates']['editView'],"Didn't get base code when it was the direct option");


        // Delete the mobile address and make sure it falls back to base
        unlink('clients/mobile/fields/address/editView.hbt');
        $restReply = $this->_restCall('metadata/public?typeFilter=fields&platform=mobile');
        $this->assertEquals('BASE EDITVIEW',$restReply['reply']['fields']['address']['templates']['editView'],"Didn't fall back to base code when mobile code wasn't there.");


        // Make sure the mobile code is loaded before the non-custom base code
        file_put_contents('custom/clients/mobile/fields/address/editView.hbt','CUSTOM MOBILE EDITVIEW');
        $restReply = $this->_restCall('metadata/public?typeFilter=fields&platform=mobile');
        $this->assertEquals('CUSTOM MOBILE EDITVIEW',$restReply['reply']['fields']['address']['templates']['editView'],"Didn't use the custom mobile code.");

        // Make sure custom base code works
        file_put_contents('custom/clients/base/fields/address/editView.hbt','CUSTOM BASE EDITVIEW');
        $restReply = $this->_restCall('metadata/public?typeFilter=fields&platform=base');
        $this->assertEquals('CUSTOM BASE EDITVIEW',$restReply['reply']['fields']['address']['templates']['editView'],"Didn't use the custom base code.");
    }


}
