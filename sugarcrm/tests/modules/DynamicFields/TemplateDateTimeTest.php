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

require_once 'modules/DynamicFields/templates/Fields/TemplateDatetimecombo.php';



class TemplateDateTimeTest extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        global $timedate;
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        //Set the now on timedate correctly for consistent testing
        $now = $timedate->getNow(true)->setDate(2012,10,8)->setTime(16, 10);
        $timedate->setNow($now);

    }

    public function tearDown()
    {
        global $timedate;
        $timedate->setNow(new SugarDateTime());
        SugarTestHelper::tearDown();
    }

    public function testDefaultValues()
    {
        global $timedate;
        $tdt = new TemplateDatetimecombo();

        $fakeBean = new TemplateDateTimeMockBean();
        //Verify that each of the default values for TemplateDateTime modify the date correctly
        $expected = clone $timedate->getNow();
        //We have to make sure to run through parseDateDefault and set a time as on some versions of php,
        //setting the day will reset the time to midnight.
        //ex. in php 5.3.2 'next monday' will not change the time. In php 5.3.6 it will set the time to midnight
        $result = $fakeBean->parseDateDefault($tdt->dateStrings['today'] . "&04:10pm", true);
        $this->assertEquals($timedate->asUser($expected), $result);

        $expected->setDate(2012,10,7);
        $result = $fakeBean->parseDateDefault($tdt->dateStrings['yesterday'] . "&04:10pm", true);
        $this->assertEquals($timedate->asUser($expected), $result);

        $expected->setDate(2012,10,9);
        $result = $fakeBean->parseDateDefault($tdt->dateStrings['tomorrow'] . "&04:10pm", true);
        $this->assertEquals($timedate->asUser($expected), $result);


        $expected->setDate(2012,10,15);
        $result = $fakeBean->parseDateDefault($tdt->dateStrings['next week'] . "&04:10pm", true);
        $this->assertEquals($timedate->asUser($expected), $result);


        $expected->setDate(2012,10,15);
        $result = $fakeBean->parseDateDefault($tdt->dateStrings['next monday'] . "&04:10pm", true);
        $this->assertEquals($timedate->asUser($expected), $result);

        $expected->setDate(2012,10,12);
        $result = $fakeBean->parseDateDefault($tdt->dateStrings['next friday'] . "&04:10pm", true);
        $this->assertEquals($timedate->asUser($expected), $result);

        $expected->setDate(2012,10,22);
        $result = $fakeBean->parseDateDefault($tdt->dateStrings['two weeks'] . "&04:10pm", true);
        $this->assertEquals($timedate->asUser($expected), $result);

        $expected->setDate(2012,11,8);
        $result = $fakeBean->parseDateDefault($tdt->dateStrings['next month'] . "&04:10pm", true);
        $this->assertEquals($timedate->asUser($expected), $result);


        $expected->setDate(2012,11,01);
        $result = $fakeBean->parseDateDefault($tdt->dateStrings['first day of next month'] . "&04:10pm", true);
        $this->assertEquals($timedate->asUser($expected), $result);

        $expected->setDate(2013,01,8);
        $result = $fakeBean->parseDateDefault($tdt->dateStrings['three months'] . "&04:10pm", true);
        $this->assertEquals($timedate->asUser($expected), $result);

        $expected->setDate(2013,04,8);
        $result = $fakeBean->parseDateDefault($tdt->dateStrings['six months'] . "&04:10pm", true);
        $this->assertEquals($timedate->asUser($expected), $result);

        $expected->setDate(2013,10,8);
        $result = $fakeBean->parseDateDefault($tdt->dateStrings['next year'] . "&04:10pm", true);
        $this->assertEquals($timedate->asUser($expected), $result);
    }
}

class TemplateDateTimeMockBean extends SugarBean {


    public function parseDateDefault($value, $time = false) {
        return parent::parseDateDefault($value, $time);
    }
}


?>