<?php
//FILE SUGARCRM flav=pro ONLY
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


require_once('include/SugarCharts/ChartDisplay.php');
require_once('modules/Reports/Report.php');

class ChartDisplayMock47148 extends ChartDisplay
{
    /**
     * Overwrite this method to not actually run a report
     */
    public function setReporter(Report $reporter)
    {
        $this->reporter = $reporter;
    }

    public function get_row_remap($row)
    {
        return parent::get_row_remap($row);
    }
}

/**
 * Bug47148Test.php
 * Reporter has a big problem with big numbers
 * @ticket 47148
 */
class Bug47148Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $_backup = array();

    public function setUp()
    {
        $this->_backup['do_thousands'] = (isset($GLOBALS['do_thousands'])) ? $GLOBALS['do_thousands'] : false;
        $GLOBALS['do_thousands'] = true;
    }

    public function tearDown()
    {
        $GLOBALS['do_thousands'] = $this->_backup['do_thousands'];
    }

    public function testBigNumber()
    {
        // big number from database, it has to be string
        $expected = '1000000000000000';

        // define fake of row result
        $row = array(
            'cells' => array(
                0 => array(
                    'val' => $expected
                )
            )
        );
        $row['count'] = count($row['cells']);

        // define fake of report
        $report = new Report();
        $report->chart_numerical_position = 0;
        $report->chart_header_row = array(
            0 => array(
                'label' => 'test',
                'column_key' => 0
            )
        );
        $report->module = null;
        $report->report_def = array(
            'group_defs' => array()
        );

        $cdm = new ChartDisplayMock47148();
        $cdm->setReporter($report);

        $actual = $cdm->get_row_remap($row);
        $actual = $actual['numerical_value'] * 1000; // recovery of division by 1000 from get_row_remap function
        $actual = sprintf('%0.0f', $actual); // getting float as string

        $this->assertEquals($expected, $actual, 'Big number is not valid');
    }
}