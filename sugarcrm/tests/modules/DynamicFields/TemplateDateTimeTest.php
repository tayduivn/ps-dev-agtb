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

    }

    public function tearDown()
    {

    }

    public function testDefaultValues()
    {
        $tdt = new TemplateDatetimecombo();
        //Verify that each of the default values for TemplateDateTime modify the date correctly
        //When testing "now"/"today", we can't realiably get this to pass. Just checking the string now
        $this->assertEquals("now",$tdt->dateStrings['today']);

        $expected = new SugarDateTime("2012-10-07 16:10:30");
        $result = new SugarDateTime("2012-10-08 16:10:30");
        $result->modify($tdt->dateStrings['yesterday']);
        $this->assertEquals($expected->asDb(false), $result->asDb(false));

        $expected = new SugarDateTime("2012-10-09 16:10:30");
        $result = new SugarDateTime("2012-10-08 16:10:30");
        $result->modify($tdt->dateStrings['tomorrow']);
        $this->assertEquals($expected->asDb(false), $result->asDb(false));


        $expected = new SugarDateTime("2012-10-15 16:10:30");
        $result = new SugarDateTime("2012-10-08 16:10:30");
        $result->modify($tdt->dateStrings['next week']);
        $this->assertEquals($expected->asDb(false), $result->asDb(false));


        $expected = new SugarDateTime("2012-10-15 16:10:30");
        $result = new SugarDateTime("2012-10-08 16:10:30");
        $result->modify($tdt->dateStrings['next monday']);
        $this->assertEquals($expected->asDb(false), $result->asDb(false));


        $expected = new SugarDateTime("2012-10-12 16:10:30");
        $result = new SugarDateTime("2012-10-08 16:10:30");
        $result->modify($tdt->dateStrings['next friday']);
        $this->assertEquals($expected->asDb(false), $result->asDb(false));

        $expected = new SugarDateTime("2012-10-22 16:10:30");
        $result = new SugarDateTime("2012-10-08 16:10:30");
        $result->modify($tdt->dateStrings['two weeks']);
        $this->assertEquals($expected->asDb(false), $result->asDb(false));

        $expected = new SugarDateTime("2012-11-08 16:10:30");
        $result = new SugarDateTime("2012-10-08 16:10:30");
        $result->modify($tdt->dateStrings['next month']);
        $this->assertEquals($expected->asDb(false), $result->asDb(false));


        $expected = new SugarDateTime("2012-11-01 16:10:30");
        $result = new SugarDateTime("2012-10-08 16:10:30");
        $result->modify($tdt->dateStrings['first day of next month']);
        $this->assertEquals($expected->asDb(false), $result->asDb(false));

        $expected = new SugarDateTime("2013-01-08 16:10:30");
        $result = new SugarDateTime("2012-10-08 16:10:30");
        $result->modify($tdt->dateStrings['three months']);
        $this->assertEquals($expected->asDb(false), $result->asDb(false));

        $expected = new SugarDateTime("2013-04-08 16:10:30");
        $result = new SugarDateTime("2012-10-08 16:10:30");
        $result->modify($tdt->dateStrings['six months']);
        $this->assertEquals($expected->asDb(false), $result->asDb(false));

        $expected = new SugarDateTime("2013-10-08 16:10:30");
        $result = new SugarDateTime("2012-10-08 16:10:30");
        $result->modify($tdt->dateStrings['next year']);
        $this->assertEquals($expected->asDb(false), $result->asDb(false));
    }


}

?>