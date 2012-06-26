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

require_once 'modules/UpgradeWizard/SidecarUpdate/SidecarMetaDataUpgrader.php';

class SidecarMetaDataUpgraderTest extends Sugar_PHPUnit_Framework_TestCase  {
    protected $legacyFilePaths = array(
        'portal' => array(
            'custom'  => 'custom/portal/',
            'history' => 'custom/portal/',
            'working' => 'custom/working/portal/',
        ),
        'wireless' => array(
            'custom'  => 'custom/',
            'history' => 'custom/history/',
            'working' => 'custom/working/',
        ),
    );
    
    protected $modulesToTest = array(
        'portal' => array(
            'Bugs' => array('edit', 'list'), 
            'Cases' => array('detail', 'search'), 
            'Leads' => array('edit', 'detail', 'list'),
        ),
        'wireless' => array(
            'Accounts' => array('list'), 
            'Bugs' => array('edit', 'detail'), 
            'Calls' => array('edit', 'list', 'detail'), 
            'Notes' => array('edit', 'search'),
        ),
    );
    
    protected $builder;
    
    public function setup() {
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $this->builder = new SidecarMetaDataFileBuilder();
        foreach ($this->modulesToTest as $client => $modules) {
            foreach ($modules as $module => $viewtypes) {
                foreach ($this->legacyFilePaths[$client] as $paths) {
                    foreach ($paths as $type => $path) {
                        $this->builder->buildFile($path, $module, $type, $client);
                    }
                }
            }
        }
    }
    
    public function tearDown() {
        $this->builder->teardownFiles();
    }
    
    public function testLegacyMetadataLocations() {
        $upgrader = new SidecarMetaDataUpgrader();
        $upgrader->upgrade();
    }
}

class SidecarMetaDataFileBuilder {
    private $existing = array();
    private $files = array();

    /**
     * Maps of old metadata file names
     *
     * @var array
     */
    protected $legacyMetaDataFileNames = array(
        //BEGIN SUGARCRM flav=pro || flav=sales ONLY
        'wirelessedit'   => 'wireless.editviewdefs',
        'wirelessdetail' => 'wireless.detailviewdefs',
        'wirelesslist'   => 'wireless.listviewdefs',
        'wirelesssearch' => 'wireless.searchdefs',
        //END SUGARCRM flav=pro || flav=sales ONLY
        //BEGIN SUGARCRM flav=ent ONLY
        'portaledit'     => 'editviewdefs',
        'portaldetail'   => 'detailviewdefs',
        'portallist'     => 'listviewdefs',
        'portalsearch'   => 'searchformdefs',
        //END SUGARCRM flav=ent ONLY
    );
    
    public function buildFile($path, $module, $viewtype, $client) {
        $file = "{$path}modules/$module/metadata/{$this->legacyMetaDataFileName[$client.$viewtype]}";
    }
    
    public function teardownFiles() {
        
    }
}