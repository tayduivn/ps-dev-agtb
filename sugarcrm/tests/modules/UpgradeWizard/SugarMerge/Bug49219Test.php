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
/**
 * Bug49219Test.php
 * @author Collin Lee
 *
 * This test will attempt to assert two things:
 * 1) That upgrade for Meetings quickcreatedefs.php correctly remove footerTpl and headerTpl metadata attributes from
 * custom quickcreatedefs.php files (since we removed them from code base)
 * 2) That the SubpanelQuickCreate changes done for this bug can correctly pick up metadata footerTpl and headerTpl
 * attributes
 */
require_once 'include/dir_inc.php';
require_once 'include/EditView/SubpanelQuickCreate.php';

class Bug49219Test extends Sugar_PHPUnit_Framework_TestCase  {

var $merge;

function setUp() {
   global $beanList, $beanFiles, $current_user;
   require('include/modules.php');
   $current_user = SugarTestUserUtilities::createAnonymousUser();
   SugarTestMergeUtilities::setupFiles(array('Meetings'), array('quickcreatedefs'), 'tests/modules/UpgradeWizard/SugarMerge/metadata_files');
}


function tearDown() {
   SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
   SugarTestMergeUtilities::teardownFiles();
   unset($current_user);
}


/**
 * testUpgradeMeetingsQuickCreate641
 * @outputBuffering enabled
 * This test asserts that the footerTpl and headerTpl form attributes are removed from quickcreatedefs.php when
 * upgrading to 641
 */
function testUpgradeMeetingsQuickCreate641() {
   require('custom/modules/Meetings/metadata/quickcreatedefs.php');
   $this->assertArrayHasKey('headerTpl', $viewdefs['Meetings']['QuickCreate']['templateMeta']['form'], 'Unit test setup failed');
   $this->assertArrayHasKey('footerTpl', $viewdefs['Meetings']['QuickCreate']['templateMeta']['form'], 'Unit test setup failed');
   require_once 'modules/UpgradeWizard/SugarMerge/QuickCreateMerge.php';
   $this->merge = new QuickCreateMerge();
   $this->merge->merge('Meetings', 'tests/modules/UpgradeWizard/SugarMerge/metadata_files/640/modules/Meetings/metadata/quickcreatedefs.php','modules/Meetings/metadata/quickcreatedefs.php','custom/modules/Meetings/metadata/quickcreatedefs.php');
   SugarAutoLoader::buildCache();
   require('custom/modules/Meetings/metadata/quickcreatedefs.php');
   $this->assertArrayNotHasKey('headerTpl', $viewdefs['Meetings']['QuickCreate']['templateMeta']['form'], 'SugarMerge code does not remove headerTpl from quickcreatedefs.php');
   $this->assertArrayNotHasKey('footerTpl', $viewdefs['Meetings']['QuickCreate']['templateMeta']['form'], 'SugarMerge code does not remove footerTpl from quickcreatedefs.php');
}


/**
 * testSubpanelQuickCreate
 * @outputBuffering enabled
 * This test asserts that we can pick up the footerTpl and headerTpl attributes in the quickcreatedefs.php files
 */
function testSubpanelQuickCreate()
{
    $quickCreate = new SubpanelQuickCreate('Meetings', 'QuickCreate', true);
    $this->assertEquals('modules/Meetings/tpls/header.tpl', $quickCreate->ev->defs['templateMeta']['form']['headerTpl'], 'SubpanelQuickCreate fails to pick up headerTpl attribute');
    $this->assertEquals('modules/Meetings/tpls/footer.tpl', $quickCreate->ev->defs['templateMeta']['form']['footerTpl'], 'SubpanelQuickCreate fails to pick up footerTpl attribute');
    require_once 'modules/UpgradeWizard/SugarMerge/QuickCreateMerge.php';
    $this->merge = new QuickCreateMerge();
    $this->merge->merge('Meetings', 'tests/modules/UpgradeWizard/SugarMerge/metadata_files/640/modules/Meetings/metadata/quickcreatedefs.php','modules/Meetings/metadata/quickcreatedefs.php','custom/modules/Meetings/metadata/quickcreatedefs.php');
    SugarAutoLoader::buildCache();
    $quickCreate = new SubpanelQuickCreate('Meetings', 'QuickCreate', true);
    $this->assertEquals('include/EditView/header.tpl', $quickCreate->ev->defs['templateMeta']['form']['headerTpl'], 'SubpanelQuickCreate fails to pick up default headerTpl attribute');
    $this->assertEquals('include/EditView/footer.tpl', $quickCreate->ev->defs['templateMeta']['form']['footerTpl'], 'SubpanelQuickCreate fails to pick up default footerTpl attribute');

}

}