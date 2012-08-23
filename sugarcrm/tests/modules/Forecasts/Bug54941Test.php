<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


require_once('modules/Forecasts/Common.php');

class Bug54941 extends Sugar_PHPUnit_Framework_TestCase
{
	protected $db;

    public function setUp()
    {
        $this->db = DBManagerFactory::getInstance();

		// forecast schedule item insert
		$query = "INSERT INTO `forecast_schedule` (`id`, `timeperiod_id`, `user_id`, `cascade_hierarchy`, `forecast_start_date`, `status`, `created_by`, `date_entered`, `date_modified`, `deleted`) VALUES ('2557277b-3164-fbea-ea6b-503606745568', '73c179c8-9de1-c859-772b-503603116e6d', '1', 0, '2012-06-01', 'Active', '1', '2012-08-23 10:30:58', '2012-08-23 10:30:58', 0);";
		$this->db->query($query);

		// time period item insert
		$query = "INSERT INTO `timeperiods` (`id`, `name`, `parent_id`, `start_date`, `end_date`, `created_by`, `date_entered`, `date_modified`, `deleted`, `is_fiscal_year`) VALUES ('73c179c8-9de1-c859-772b-503603116e6d', 'FY-2012-August', '', '2012-08-01', '2012-08-31', '1', '2012-08-23 10:16:34', '2012-08-23 10:16:34', 0, 0);";
		$this->db->query($query);
    }


    public function tearDown()
    {
        // forecast schedule item removal
		$query = "DETETE FROM `forecast_schedule` WHERE id='2557277b-3164-fbea-ea6b-503606745568'";
		$this->db->query($query);

		// time period item removal
		$query = "DETETE FROM `timeperiods` WHERE id='73c179c8-9de1-c859-772b-503603116e6d';";
		$this->db->query($query);
    }

	/**
	 * @return array asserting data
	 */
	public function getData()
	{
		return array(
			array('2012-08-31', 1),
			array('2012-08-30', 1),
			array('2012-09-01', 0),
		);
	}

	/**
	 * @dataProvider getData
	 */
    public function testTimePeriodsIntervals($nowDate, $resultsFoundExpected)
	{
		$common = new Common();
		$nowDate = $this->db->quoted($nowDate);

		$query = "SELECT a.timeperiod_id, b.name, b.start_date, b.end_date, a.user_id, a.cascade_hierarchy"
		            . " FROM forecast_schedule a, timeperiods b"
		            . " WHERE a.timeperiod_id = b.id"
		            . " AND b.id = '73c179c8-9de1-c859-772b-503603116e6d' "
		            . " AND a.forecast_start_date <= $nowDate "
		            . " AND b.end_date >= $nowDate "
		            . " AND a.deleted = 0"
		            . " AND b.deleted = 0"
		            . " AND a.status = 'Active'"
		            . " ORDER BY b.start_date, b.end_date";

		$result = $common->db->query($query);
		$resultsFoundActual = $common->db->getRowCount($result);

		$this->assertEquals($resultsFoundExpected, $resultsFoundActual);
    }
}