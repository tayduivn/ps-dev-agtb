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

class Bug45907Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // if beanList got unset, set it back
        if (!isset($GLOBALS['beanList'])) {
            require('include/modules.php');
            $GLOBALS['beanList'] = $beanList;
        }
    }

    public function tearDown()
    {
    }

    /**
     * @ticket 45907
     */
    public function testCsvWithExtraInfo()
    {
        $sample_file = $GLOBALS['sugar_config']['upload_dir'].'/Bug45907Test.csv';
        $file = 'tests/modules/Import/Bug45907Test.csv';
        copy($file, $sample_file);

        $auto = new CsvAutoDetect($file, 4); // parse only the first 4 lines
        $del = $enc = $hasHeader = false;

        // there is extra non csv info at the bottom of the file
        // but it should still parse ok because we only parse the first 4 lines
        $ret = $auto->getCsvSettings($del, $enc);
        $this->assertEquals(true, $ret, 'Failed to parse and get csv properties');

        // delimiter
        $this->assertEquals(',', $del, 'Incorrect delimiter');

        // enclosure
        $this->assertEquals('"', $enc, 'Incorrect enclosure');

        // header
        $ret = $auto->hasHeader($hasHeader, 'Accounts');
        $this->assertTrue($ret, 'Failed to detect header');
        $this->assertTrue($hasHeader, 'Incorrect header');

        // remove temp file
        unlink($sample_file);
    }

}
