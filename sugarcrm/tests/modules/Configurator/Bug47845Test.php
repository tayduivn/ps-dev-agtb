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

require_once('include/Localization/Localization.php');
require_once('modules/Configurator/views/view.edit.php');

class Bug47845Test extends Sugar_PHPUnit_Framework_OutputTestCase
{

public function setUp()
{
    global $current_user, $mod_strings, $app_strings, $app_list_strings, $sugar_config, $locale;
    $mod_strings = return_module_language($GLOBALS['current_language'], "Configurator");
    $current_user = SugarTestUserUtilities::createAnonymousUser();
    $app_strings = return_application_language($GLOBALS['current_language']);
    $app_list_strings = return_app_list_strings_language($GLOBALS['current_language']);
    $sugar_config = $GLOBALS['sugar_config'];
    $locale = new Localization();
}

public function tearDown()
{
    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
}

public function testMailMergeAvailability()
{
    $configView = new ConfiguratorViewEdit();
    $configView->ss = new Sugar_Smarty();
    $configView->display();

    //BEGIN SUGARCRM flav!=com ONLY
    $this->expectOutputRegex('/system_mailmerge_on/');
    //END SUGARCRM flav!=com ONLY

    //BEGIN SUGARCRM flav=com ONLY
    $this->expectOutputNotRegex('/system_mailmerge_on/');
    //END SUGARCRM flav=com ONLY
}


public function testImportMapLinkedInPHPFileExists()
{
    //BEGIN SUGARCRM flav!=com ONLY
    $this->assertTrue(file_exists('modules/Import/maps/ImportMapLinkedin.php'), 'Assert that ImportMapLinkedin.php file exists for non-com flavor');
    //END SUGARCRM flav!=com ONLY

    //BEGIN SUGARCRM flav=com ONLY
    $this->assertFalse(file_exists('modules/Import/maps/ImportMapLinkedin.php'), 'Assert that ImportMapLinkedin.php file does not exist for com flavor');
    //END SUGARCRM flav=com ONLY
}

}