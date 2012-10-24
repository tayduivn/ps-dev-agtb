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
 * Bug57252Test.php
 * @author Matt Marum
 *
 * This unit test checks to make sure that we are pulling the Admin panel date/time format
 * preferences when a user does not have an existing date/time format preference.
 *
 */
class Bug57252Test extends Sugar_PHPUnit_Framework_TestCase
{

    var $testUser;

    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->testUser = SugarTestUserUtilities::createAnonymousUser();
        $this->testUser->save();
    }

    public function tearDown()
    {
        $this->testUser->resetPreferences();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    /**
     * @group bug57252
     *
     */
    public function testDefaultDateTimeFormatFromSystemConfig()
    {
        global $sugar_config;

        $this->assertEquals($this->testUser->getPreference('datef'), $sugar_config['default_date_format']);
        $this->assertEquals($this->testUser->getPreference('timef'), $sugar_config['default_time_format']);

    }

    /**
     * @group bug57252
     *
     */
    public function testDefaultDateTimeFormatFromUserPref()
    {

        $this->testUser->setPreference('datef','d/m/Y', 0, 'global');
        $this->testUser->setPreference('timef','h.iA',0,'global');

        $this->assertEquals($this->testUser->getPreference('datef'), 'd/m/Y');
        $this->assertEquals($this->testUser->getPreference('timef'), 'h.iA');

    }


}
