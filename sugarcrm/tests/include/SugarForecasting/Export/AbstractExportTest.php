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
require_once('include/SugarForecasting/Export/AbstractExport.php');

class SugarForecasting_Export_AbstractExportTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var MockSugarForecasting_Abstract
     */
    protected static $obj;

    public static function setUpBeforeClass()
    {
        self::$obj = new MockSugarForecasting_AbstractExport(array());
    }

    /**
     * getFilenameProvider
     *
     */
    public function getFilenameProvider()
    {
        return array
        (
            array(false, '', '', false, '/\d+\.csv/'),
            array(true, 'abc', '123', false, '/individual\.csv/'),
            array(true, 'abc', '123', true, '/manager\.csv/'),
        );
    }

    /**
     * This is a function to test the getFilename function
     *
     * @dataProvider getFileNameProvider
     */
    public function testGetFilename($setArgs, $timePeriod, $userId, $isManager, $expectedRegex)
    {
        if($setArgs)
        {
            self::$obj->setArg('timeperiod_id', $timePeriod);
            self::$obj->setArg('user_id', $userId);
            self::$obj->isManager = $isManager;
        }
        $this->assertRegExp($expectedRegex, self::$obj->getFilename());
    }
}

class MockSugarForecasting_AbstractExport extends SugarForecasting_Export_AbstractExport
{
    public $isManager;

    public function getFilename()
    {
        return parent::getFilename();
    }

    public function process() {
        return parent::process();
    }


}