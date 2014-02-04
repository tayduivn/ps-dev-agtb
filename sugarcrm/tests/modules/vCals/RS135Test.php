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

require_once 'modules/vCals/vCal.php';

/**
 * RS-135: Prepare vCals Module
 * Test cover correct execution of methods, not their logic
 */
class RS135Test extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var vCal */
    protected $bean = null;

    public function setup()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('timedate');
        $this->bean = new vCal();
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    public function testGetFreeBusyLinesCache()
    {
        $actual = $this->bean->get_freebusy_lines_cache($GLOBALS['current_user']);
        $this->assertEmpty($actual);
    }

    public function testCreateSugarFreeBusy()
    {
        $actual = $this->bean->create_sugar_freebusy($GLOBALS['current_user'], new SugarDateTime(), new SugarDateTime());
        $this->assertEmpty($actual);
    }

    public function testGetVcalFreeBusy()
    {
        $actual = $this->bean->get_vcal_freebusy($GLOBALS['current_user']);
        $this->assertNotEmpty($actual);
    }

    public function testCacheSugarVcal()
    {
        $actual = vCal::cache_sugar_vcal($GLOBALS['current_user']);
        $this->assertEmpty($actual);
    }

    public function testCacheSugarVcalFreeBusy()
    {
        $actual = vCal::cache_sugar_vcal_freebusy($GLOBALS['current_user']);
        $this->assertEmpty($actual);
    }

    public function testGetIcalEvent()
    {
        $meeting = new Meeting();
        $meeting->date_start = '2013-01-01 00:00:00';
        $meeting->date_end = '2013-01-01 02:00:00';
        $actual = vCal::get_ical_event($meeting, $GLOBALS['current_user']);
        $this->assertNotEmpty($actual);
    }
}
