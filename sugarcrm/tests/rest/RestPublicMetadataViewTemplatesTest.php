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
                    unlink($filename);
                }
            } else {
                file_put_contents($filename,$filecontents);
            }
        }

        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testMetadataViewTemplates() {
        $restReply = $this->_restCall('metadata/public?type_filter=view_templates');

        $this->assertTrue(isset($restReply['reply']['view_templates']['_hash']),'ViewTemplate hash is missing.');
    }
    
    /**
     * @group rest
     */
    public function testMetadataViewTemplatesHbt() {
        $filesToCheck = array(
            //BEGIN SUGARCRM flav=ent ONLY
            'clients/portal/views/edit/edit.hbt',
            'custom/clients/portal/views/edit/edit.hbt',
            //END SUGARCRM flav=ent ONLY
            'clients/base/views/edit/edit.hbt',
            'custom/clients/base/views/edit/edit.hbt',
        );
        
        foreach ( $filesToCheck as $filename ) {
            if ( file_exists($filename) ) {
                $this->oldFiles[$filename] = file_get_contents($filename);
            } else {
                $this->oldFiles[$filename] = '_NO_FILE';
            }
        }

        $dirsToMake = array(
            //BEGIN SUGARCRM flav=ent ONLY
            'clients/portal/views/edit',
            'custom/clients/portal/views/edit',
            //END SUGARCRM flav=ent ONLY
            'clients/base/views/edit',
            'custom/clients/base/views/edit',
        );

        foreach ($dirsToMake as $dir ) {
            if (!is_dir($dir) ) {
                mkdir($dir,0777,true);
            }
        }
        
        //BEGIN SUGARCRM flav=ent ONLY
        // Make sure we get it when we ask for portal
        file_put_contents($filesToCheck[0],'PORTAL CODE');
        $restReply = $this->_restCall('metadata/public?type_filter=view_templates&platform=portal');
        $this->assertEquals('PORTAL CODE',$restReply['reply']['view_templates']['edit'],"Didn't get portal code when that was the direct option");


        // Make sure we get it when we ask for portal, even though there is base code there
        file_put_contents($filesToCheck[1],'BASE CODE');
        $restReply = $this->_restCall('metadata/public?type_filter=view_templates&platform=portal');
        $this->assertEquals('PORTAL CODE',$restReply['reply']['view_templates']['edit'],"Didn't get portal code when base code was there.");
        //END SUGARCRM flav=ent ONLY

        // Make sure we get the base code when we ask for it.
        $restReply = $this->_restCall('metadata/public?type_filter=view_templates&platform=base');
        $this->assertEquals('BASE CODE',$restReply['reply']['view_templates']['edit'],"Didn't get base code when it was the direct option");

        //BEGIN SUGARCRM flav=ent ONLY
        // Delete the portal template and make sure it falls back to base
        unlink($filesToCheck[0]);
        $restReply = $this->_restCall('metadata/public?type_filter=view_templates&platform=portal');
        $this->assertEquals('BASE CODE',$restReply['reply']['view_templates']['edit'],"Didn't fall back to base code when portal code wasn't there.");


        // Make sure the portal code is loaded before the non-custom base code
        file_put_contents($filesToCheck[2],'CUSTOM PORTAL CODE');
        $restReply = $this->_restCall('metadata/public?type_filter=view_templates&platform=portal');
        $this->assertEquals('CUSTOM PORTAL CODE',$restReply['reply']['view_templates']['edit'],"Didn't use the custom portal code.");
        //END SUGARCRM flav=ent ONLY
        
        // Make sure custom base code works
        file_put_contents($filesToCheck[3],'CUSTOM BASE CODE');
        $restReply = $this->_restCall('metadata/public?type_filter=view_templates');
        $this->assertEquals('CUSTOM BASE CODE',$restReply['reply']['view_templates']['edit'],"Didn't use the custom base code.");

    }
}
