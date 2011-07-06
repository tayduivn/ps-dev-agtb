<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

 
require_once 'modules/Import/CsvAutoDetect.php';

class CsvAutoDetectTest extends Sugar_PHPUnit_Framework_TestCase
{
    private static $CsvContent = array (
        0 => "\"date_entered\",\"description\"\n\"3/26/2011 10:02am\",\"test description\"",
        1 => "\"date_entered\"\t\"description\"\n\"2011-3-26 10:2 am\"\t\"test description\"",
        2 => "\"date_entered\",\"description\"\n\"3.26.2011 15.02\",\"test description\"",
        3 => "\"3/26/2011 10:02am\",\"some text\"\n\"4/26/2011 11:20am\",\"some more text\"",
    );

    public function setUp()
    {
    }

    public function tearDown()
    {
        unlink($GLOBALS['sugar_config']['tmp_dir'].'test.csv');
    }

    public function providerCsvData()
    {
        return array(
            array(0, ',', '"', 'm/d/Y', 'h:ia', true),
            array(1, "\t", '"', 'Y-m-d', 'h:i a', true),
            array(2, ",", '"', 'm.d.Y', 'H.i', true),
            array(3, ',', '"', 'm/d/Y', 'h:ia', false),
            );
    }

    /**
     * @dataProvider providerCsvData
     */
    public function testGetCsvProperties($content_idx, $delimiter, $enclosure, $date, $time, $header)
    {
        $file = $GLOBALS['sugar_config']['tmp_dir'].'test.csv';
        $ret = file_put_contents($file, self::$CsvContent[$content_idx]);
        $this->assertGreaterThan(0, $ret, 'Failed to write to '.$file .' for content '.$content_idx);

        $auto = new CsvAutoDetect($file);
        $del = $enc = $hasHeader = false;
        $ret = $auto->getCsvSettings($del, $enc);
        $this->assertEquals(true, $ret, 'Failed to parse and get csv properties');

        // delimiter
        $this->assertEquals($delimiter, $del, 'Incorrect delimiter');

        // enclosure
        $this->assertEquals($enclosure, $enc, 'Incorrect enclosure');

        // date format
        $date_format = $auto->getDateFormat();
        $this->assertEquals($date, $date_format, 'Incorrect date format');

        // time format
        $time_format = $auto->getTimeFormat();
        $this->assertEquals($time, $time_format, 'Incorrect time format');

        // test
        $this->assertTrue(isset($GLOBALS['beanList']['Accounts']), 'beanList Accounts not defined');
        $this->assertTrue(isset($GLOBALS['current_language']), 'current language not defined');
        $mod_strings = return_module_language($GLOBALS['current_language'], 'Accounts');
        $this->assertGreaterThan(0, count($mod_strings), 'mod strings empty');

        // header
        $auto->hasHeader($hasHeader, 'Accounts');
        $this->assertEquals($header, $hasHeader, 'Incorrect header');
    }

}
