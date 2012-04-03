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

class RestTestMetadataSugarFields extends RestTestBase {
    public function setUp()
    {
        //Create an anonymous user for login purposes/
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->_user;
        $this->_restLogin($this->_user->user_name,$this->_user->user_name);
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
        
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function testMetadataSugarFields() {
        $restReply = $this->_restCall('metadata?metadataTypes=sugarFields');

        $this->assertTrue(isset($restReply['reply']['sugarFields']['_hash']),'SugarField hash is missing.');
    }
    
    public function testMetadataSugarFieldsController() {
        $filesToCheck = array('include/SugarFields/Fields/Address/mobile/Address.js',
                              'include/SugarFields/Fields/Address/portal/Address.js',
                              'include/SugarFields/Fields/Address/base/Address.js',
                              'custom/include/SugarFields/Fields/Address/mobile/Address.js',
                              'custom/include/SugarFields/Fields/Address/portal/Address.js',
                              'custom/include/SugarFields/Fields/Address/base/Address.js',
        );
        
        foreach ( $filesToCheck as $filename ) {
            if ( file_exists($filename) ) {
                $this->oldFiles[$filename] = file_get_contents($filename);
            } else {
                $this->oldFiles[$filename] = '_NO_FILE';
            }
        }

        $dirsToMake = array('include/SugarFields/Fields/Address/mobile',
                            'include/SugarFields/Fields/Address/portal',
                            'include/SugarFields/Fields/Address/base',
                            'custom/include/SugarFields/Fields/Address/mobile',
                            'custom/include/SugarFields/Fields/Address/portal',
                            'custom/include/SugarFields/Fields/Address/base',
        );

        foreach ($dirsToMake as $dir ) {
            if (!is_dir($dir) ) {
                mkdir($dir,0777,true);
            }
        }
        $basePath = 'include/SugarFields/Fields/Address/';
        
        // Make sure we get it when we ask for mobile
        file_put_contents($basePath.'mobile/Address.js','MOBILE CODE');
        $restReply = $this->_restCall('metadata/?metadataType=sugarFields&platform=mobile');
        $this->assertEquals('MOBILE CODE',$restReply['reply']['sugarFields']['Address']['controller'],"Didn't get mobile code when that was the direct option");


        // Make sure we get it when we ask for mobile, even though there is portal code there
        file_put_contents($basePath.'portal/Address.js','PORTAL CODE');
        $restReply = $this->_restCall('metadata/?metadataType=sugarFields&platform=mobile');
        $this->assertEquals('MOBILE CODE',$restReply['reply']['sugarFields']['Address']['controller'],"Didn't get mobile code when portal code was there.");


        // Make sure we get the portal code when we ask for it.
        $restReply = $this->_restCall('metadata/?metadataType=sugarFields&platform=portal');
        $this->assertEquals('PORTAL CODE',$restReply['reply']['sugarFields']['Address']['controller'],"Didn't get portal code when it was the direct option");


        // Delete the mobile address and make sure it falls back to portal
        unlink($basePath.'mobile/Address.js');
        $restReply = $this->_restCall('metadata/?metadataType=sugarFields&platform=mobile');
        $this->assertEquals('PORTAL CODE',$restReply['reply']['sugarFields']['Address']['controller'],"Didn't fall back to portal code when mobile code wasn't there.");


        // Make sure the mobile code is loaded before the non-custom portal code
        file_put_contents('custom/'.$basePath.'mobile/Address.js','CUSTOM MOBILE CODE');
        $restReply = $this->_restCall('metadata/?metadataType=sugarFields&platform=mobile');
        $this->assertEquals('CUSTOM MOBILE CODE',$restReply['reply']['sugarFields']['Address']['controller'],"Didn't use the custom mobile code.");

        // Make sure custom portal code works
        file_put_contents('custom/'.$basePath.'portal/Address.js','CUSTOM PORTAL CODE');
        $restReply = $this->_restCall('metadata/?metadataType=sugarFields&platform=portal');
        $this->assertEquals('CUSTOM PORTAL CODE',$restReply['reply']['sugarFields']['Address']['controller'],"Didn't use the custom portal code.");

        // Delete the custom mobile code, this should then fallback to the custom portal code, which should override the default portal code for the fallback
        unlink('custom/'.$basePath.'mobile/Address.js');
        $restReply = $this->_restCall('metadata/?metadataType=sugarFields&platform=mobile');
        $this->assertEquals('CUSTOM PORTAL CODE',$restReply['reply']['sugarFields']['Address']['controller'],"Didn't use the custom portal code when the custom mobile code was deleted.");

    }

    public function testMetadataSugarFieldsTemplates() {
        $filesToCheck = array(
            'include/SugarFields/Fields/Address/mobile/editView.hbt',
            'include/SugarFields/Fields/Address/mobile/deatilView.hbt',
            'include/SugarFields/Fields/Address/portal/editView.hbt',
            'include/SugarFields/Fields/Address/portal/deatilView.hbt',
            'include/SugarFields/Fields/Address/base/editView.hbt',
            'include/SugarFields/Fields/Address/base/deatilView.hbt',
            'custom/include/SugarFields/Fields/Address/mobile/editView.hbt',
            'custom/include/SugarFields/Fields/Address/mobile/deatilView.hbt',
            'custom/include/SugarFields/Fields/Address/portal/editView.hbt',
            'custom/include/SugarFields/Fields/Address/portal/deatilView.hbt',
            'custom/include/SugarFields/Fields/Address/base/editView.hbt',
            'custom/include/SugarFields/Fields/Address/base/deatilView.hbt',
        );
        
        foreach ( $filesToCheck as $filename ) {
            if ( file_exists($filename) ) {
                $this->oldFiles[$filename] = file_get_contents($filename);
            } else {
                $this->oldFiles[$filename] = '_NO_FILE';
            }
        }

        $dirsToMake = array('include/SugarFields/Fields/Address/mobile',
                            'include/SugarFields/Fields/Address/portal',
                            'include/SugarFields/Fields/Address/base',
                            'custom/include/SugarFields/Fields/Address/mobile',
                            'custom/include/SugarFields/Fields/Address/portal',
                            'custom/include/SugarFields/Fields/Address/base',
        );

        foreach ($dirsToMake as $dir ) {
            if (!is_dir($dir) ) {
                mkdir($dir,0777,true);
            }
        }
        $basePath = 'include/SugarFields/Fields/Address/';
        
        // Make sure we get it when we ask for mobile
        file_put_contents($basePath.'mobile/editView.hbt','MOBILE EDITVIEW');
        $restReply = $this->_restCall('metadata/?metadataType=sugarFields&platform=mobile');
        $this->assertEquals('MOBILE EDITVIEW',$restReply['reply']['sugarFields']['Address']['templates']['editView'],"Didn't get mobile code when that was the direct option");


        // Make sure we get it when we ask for mobile, even though there is portal code there
        file_put_contents($basePath.'portal/editView.hbt','PORTAL EDITVIEW');
        $restReply = $this->_restCall('metadata/?metadataType=sugarFields&platform=mobile');
        $this->assertEquals('MOBILE EDITVIEW',$restReply['reply']['sugarFields']['Address']['templates']['editView'],"Didn't get mobile code when portal code was there.");


        // Make sure we get the portal code when we ask for it.
        $restReply = $this->_restCall('metadata/?metadataType=sugarFields&platform=portal');
        $this->assertEquals('PORTAL EDITVIEW',$restReply['reply']['sugarFields']['Address']['templates']['editView'],"Didn't get portal code when it was the direct option");


        // Delete the mobile address and make sure it falls back to portal
        unlink($basePath.'mobile/editView.hbt');
        $restReply = $this->_restCall('metadata/?metadataType=sugarFields&platform=mobile');
        $this->assertEquals('PORTAL EDITVIEW',$restReply['reply']['sugarFields']['Address']['templates']['editView'],"Didn't fall back to portal code when mobile code wasn't there.");


        // Make sure the mobile code is loaded before the non-custom portal code
        file_put_contents('custom/'.$basePath.'mobile/editView.hbt','CUSTOM MOBILE EDITVIEW');
        $restReply = $this->_restCall('metadata/?metadataType=sugarFields&platform=mobile');
        $this->assertEquals('CUSTOM MOBILE EDITVIEW',$restReply['reply']['sugarFields']['Address']['templates']['editView'],"Didn't use the custom mobile code.");

        // Make sure custom portal code works
        file_put_contents('custom/'.$basePath.'portal/editView.hbt','CUSTOM PORTAL EDITVIEW');
        $restReply = $this->_restCall('metadata/?metadataType=sugarFields&platform=portal');
        $this->assertEquals('CUSTOM PORTAL EDITVIEW',$restReply['reply']['sugarFields']['Address']['templates']['editView'],"Didn't use the custom portal code.");

        // Delete the custom mobile code, this should then fallback to the custom portal code, which should override the default portal code for the fallback
        unlink('custom/'.$basePath.'mobile/editView.hbt');
        $restReply = $this->_restCall('metadata/?metadataType=sugarFields&platform=mobile');
        $this->assertEquals('CUSTOM PORTAL EDITVIEW',$restReply['reply']['sugarFields']['Address']['templates']['editView'],"Didn't use the custom portal code when the custom mobile code was deleted.");

    }


}