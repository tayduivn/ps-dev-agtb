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
    public function setUp()
    {
        $str1 = <<<STR1
"date_entered","description"
"3/26/2011 10:02am","test description"
STR1;
        file_put_contents($GLOBALS['sugar_config']['tmp_dir'].'test1.csv', $str1);

        $str2 = '"date_entered"'."\t".'"description"'."\n".
'"2011-3-26 10:2 am"'."\t".'"test description"'."\t";
        file_put_contents($GLOBALS['sugar_config']['tmp_dir'].'test2.csv', $str2);

        $str3 = <<<STR3
"date_entered","description"
"3.26.2011 15.02","test description"
STR3;
        file_put_contents($GLOBALS['sugar_config']['tmp_dir'].'test3.csv', $str3);

        $str4 = <<<STR4
"3/26/2011 10:02am","some text"
"4/26/2011 11:20am","some other text"
STR4;
        file_put_contents($GLOBALS['sugar_config']['tmp_dir'].'test4.csv', $str4);
    }

    public function tearDown()
    {
        unlink($GLOBALS['sugar_config']['tmp_dir'].'test1.csv');
        unlink($GLOBALS['sugar_config']['tmp_dir'].'test2.csv');
        unlink($GLOBALS['sugar_config']['tmp_dir'].'test3.csv');
        unlink($GLOBALS['sugar_config']['tmp_dir'].'test4.csv');
    }

    public function providerCsvData()
    {
        return array(
            array($GLOBALS['sugar_config']['tmp_dir'].'test1.csv', ',', '"', 'm/d/Y', 'h:ia', true),
            array($GLOBALS['sugar_config']['tmp_dir'].'test2.csv', "\t", '"', 'Y-m-d', 'h:i a', true),
            array($GLOBALS['sugar_config']['tmp_dir'].'test3.csv', ",", '"', 'm.d.Y', 'H.i', true),
            array($GLOBALS['sugar_config']['tmp_dir'].'test4.csv', ',', '"', 'm/d/Y', 'h:ia', false),
            );
    }

    /**
     * @dataProvider providerCsvData
     */
    public function testGetCsvProperties($file, $delimiter, $enclosure, $date, $time, $header)
    {
        $auto = new CsvAutoDetect($file);
        $del = $enc = $hasHeader = false;
        $ret = $auto->getCsvSettings($del, $enc);
        $this->assertTrue($ret);

        // delimiter
        $this->assertEquals($delimiter, $del);

        // enclosure
        $this->assertEquals($enclosure, $enc);

        // date format
        $date_format = $auto->getDateFormat();
        $this->assertEquals($date, $date_format);

        // time format
        $time_format = $auto->getTimeFormat();
        $this->assertEquals($time, $time_format);

        // header
        $auto->hasHeader($hasHeader, 'Accounts');
        $this->assertEquals($header, $hasHeader);
    }

}
