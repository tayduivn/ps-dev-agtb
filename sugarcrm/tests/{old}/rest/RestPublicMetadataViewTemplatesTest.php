<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once('tests/{old}/rest/RestTestBase.php');

class RestPublicMetadataViewTemplatesTest extends RestTestBase {

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
    public function testMetadataViewTemplatesHbs() {
        $filesToCheck = array(
            //BEGIN SUGARCRM flav=ent ONLY
            'portal' => array(
                'clients/portal/views/edit/edit.hbs',
                'custom/clients/portal/views/edit/edit.hbs',
            ),
            //END SUGARCRM flav=ent ONLY
            'base' => array(
                'clients/base/views/edit/edit.hbs',
                'custom/clients/base/views/edit/edit.hbs',
            ),
        );
        SugarTestHelper::saveFile($filesToCheck);

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
