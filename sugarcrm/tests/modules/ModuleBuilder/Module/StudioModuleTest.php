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
 
require_once("modules/ModuleBuilder/Module/StudioModule.php");

class StudioModuleTest extends Sugar_PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
    {
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);

    }
    
    public static function tearDownAfterClass()
    {
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['beanList']);
        unset($GLOBALS['current_user']);
        unset($GLOBALS['app_list_strings']);
    }

    /**
     * @ticket 39407
     *
     */
    public function testRemoveFieldFromLayoutsDocumentsException()
    {
        $this->markTestSkipped('Skip this test');
    	$SM = new StudioModule("Documents");
        try {
            $SM->removeFieldFromLayouts("aFieldThatDoesntExist");
            $this->assertTrue(true);
        } catch (Exception $e) {
            //Studio module threw exception
            $this->assertTrue(true);
        }
    }

    public function providerGetType()
    {
        return array(
            array('Meetings', 'basic'),
            array('Calls', 'basic'),
            array('Accounts', 'company'),
            array('Contacts', 'person'),
            array('Leads', 'person'),
            array('Cases', 'basic'),
        );
    }

    /**
     * @ticket 50977
     *
     * @dataProvider providerGetType
     */
    public function testGetTypeFunction($module, $type) {
        $SM = new StudioModule($module);
        $this->assertEquals($type, $SM->getType(), 'Failed asserting that module:' . $module . ' is of type:' . $type);
    }


    public function providerBWCHasSearch()
    {
        return array(
            array('Meetings', true),
            array('Accounts', false),
            array('Documents', true),
            array('Calls', false),
        );
    }
    /**
    * @dataProvider providerBWCHasSearch
    * @bug SC-519
    */
    public function testBWCHasSearch($module, $isBWC)
    {
        $this->markTestIncomplete('Needs to be fixed by FRM team.');
        $SM = new StudioModule($module);
        $layouts = $SM->getLayouts();
        $this->assertEquals($isBWC, !empty($layouts[translate('LBL_SEARCH', "ModuleBuilder")]),
            'Failed asserting that module:' . $module . ' has a search layout when BWC');
    }
}
