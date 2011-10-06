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


require_once('modules/Reports/Report.php');

/**
 * Bug #40433
 * SQL error when Edit 'Opportunities By Lead Source' chart using MSSQL
 *
 * @author mgusev
 *
 */
class Bug46805Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Test emulate mssql connection and tries to assert number of left and right brackets from generated query.
     * @ticket 40433
     * @return void
     */
    function testOrderFields()
    {
        $db = new stdClass();
        $db->dbType = 'mssql';
        $report = new Report();
        $report->db = $db;
        $report->select_fields = array(
            'test1'
        );
        $report->from = ' FROM test';

        $report->order_by_arr = array(
            '(test.test1=\'\' OR test.test1 IS NULL)  DESC, test.test1=\'1\'  DESC'
        );
        $report->create_query();

        $report->order_by_arr = array(
            '([!@#$%^&*()_+ ].[!@#$%^&*()_+ ]=\'\' OR [!@#$%^&*()_+ ].[!@#$%^&*()_+ ] IS NULL)  DESC, [!@#$%^&*()_+ ].[!@#$%^&*()_+ ]=\'1\'  DESC'
        );
        $report->create_query();

        $report->order_by_arr = array(
            '(test1=\'\' OR test1 IS NULL)  DESC, test1=\'1\'  DESC'
        );
        $report->create_query();

        $report->order_by_arr = array(
            '([!@#$%^&*()_+ ]=\'\' OR [!@#$%^&*()_+ ] IS NULL)  DESC, [!@#$%^&*()_+ ]=\'1\'  DESC'
        );
        $report->create_query();

        foreach ($report->query_list as $query)
        {
            $query = preg_replace('/\[[^\]]+\]/', '', $query);
            $query = preg_replace('/[^\(\)]/', '', $query);
            // Compare number of left and right brackets. Query is not valid if their number is not equal.
            $this->assertEquals(substr_count($query, '('), substr_count($query, ')'), 'Number of left/right brackets should be equal');
        }
    }
}