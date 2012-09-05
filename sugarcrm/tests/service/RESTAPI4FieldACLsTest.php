<?php
//FILE SUGARCRM flav=pro ONLY
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

require_once('service/v3/SugarWebServiceUtilv3.php');
require_once('tests/service/APIv3Helper.php');
require_once 'service/v4/SugarWebServiceUtilv4.php';

class RESTAPI4FieldACLsTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $v4;
    
    public function setUp() {
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;

        //Reload langauge strings
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Accounts');
        //Create an anonymous user for login purposes/
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        
        $this->v4 = new SugarWebServiceUtilv4();
    }
    
    public function tearDown() {
        // Copied from RESTAPI4Test, minus the isset check which is unnecessary
        unset($GLOBALS['listViewDefs']);
        unset($GLOBALS['viewdefs']);
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['app_list_strings']);
        unset($GLOBALS['app_strings']);
        unset($GLOBALS['mod_strings']);
        unset($GLOBALS['current_user']);
    }
    
    /**
     * @dataProvider _wirelessListProvider
     * @param $module
     * @param $metadatafile
     */
    public function testAddFieldLevelACLsToWirelessList($module, $metadatafile) {
        $defs = $this->v4->get_module_view_defs($module, 'wireless', 'list');
        
        // $defs should be converted and ACLed at this point
        $this->assertArrayHasKey('NAME', $defs, 'NAME index is missing');
        $this->assertArrayHasKey('acl', $defs['NAME'], 'NAME field has no ACL attached to it');
        
        // Get the known metadata
        require $metadatafile;
        $known = $viewdefs[$module]['mobile']['view']['list'];
        
        $this->assertArrayHasKey('panels', $known, 'No panels array found in the known metadata');
        $this->assertEquals(count($defs), count($known['panels'][0]['fields']), 'Metadata converted field count different than known count');
    }
    
    /**
     * @dataProvider _wirelessGridProvider
     * @param $module
     * @param $view
     */
    public function testAddFieldLevelACLsToWirelessGrid($module, $view, $metadatafile) {
        $defs = $this->v4->get_module_view_defs($module, 'wireless', $view);
        
        // $defs should be converted and ACLed at this point
        $this->assertTrue(isset($defs['panels']), 'panels index not found in viewdef return');
        
        // Compare with known metadata
        require $metadatafile;
        $known = $viewdefs[$module]['mobile']['view'][$view];
        $this->assertArrayHasKey('panels', $known, 'No panels array found in the known metadata');
        $this->assertEquals(count($defs['panels']), count($known['panels'][0]['fields']), 'Metadata converted field count different than known count');
    }
    
    /**
     * ANY ENTRY MADE TO THIS RETURN SHOULD HAVE A CORRESPONDING LEGACY METADATA
     * FILE SAVED IN tests/service/metadata AND NAMED $module . 'legacy' . $view . '.php'
     * 
     * @return array
     */
    public function _wirelessGridProvider() {
        return array(
            array('module' => 'Accounts', 'view' => 'edit', 'metadatafile' => 'modules/Accounts/clients/mobile/views/edit/edit.php',),
            array('module' => 'Accounts', 'view' => 'detail', 'metadatafile' => 'modules/Accounts/clients/mobile/views/detail/detail.php',),
        );
    }
    
    /**
     * ANY ENTRY MADE TO THIS RETURN SHOULD HAVE A CORRESPONDING LEGACY METADATA
     * FILE SAVED IN tests/service/metadata AND NAMED $module . 'legacy' . $view . '.php'
     * 
     * @return array
     */
    public function _wirelessListProvider() {
        return array(
            array('module' => 'Cases', 'metadatafile' => 'modules/Cases/clients/mobile/views/list/list.php',),
        );
    }
}