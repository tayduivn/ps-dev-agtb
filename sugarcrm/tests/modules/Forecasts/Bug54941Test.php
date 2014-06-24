<?php
//FILE SUGARCRM flav=pro ONLY
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */


require_once('modules/Forecasts/Common.php');

class Bug54941Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var DBManager
     */
    protected $db;

    /**
     * @var string
     */
    protected $guid1;

    /**
     * @var string
     */
    protected $guid2;
    public function setUp()
    {
        $this->db = DBManagerFactory::getInstance();

        $this->guid1 = create_guid();
        $this->guid2 = create_guid();

		// forecast schedule item insert
        $query = "
            INSERT INTO
                forecast_schedule
                (
                    id,
                    timeperiod_id,
                    user_id,
                    cascade_hierarchy,
                    forecast_start_date,
                    status,
                    created_by,
                    date_entered,
                    date_modified,
                    deleted
                )
                VALUES
                (
                    " . $this->db->quoted($this->guid1) . ",
                    " . $this->db->quoted($this->guid2) . ",
                    '1',
                    0,
                    " . $this->db->convert($this->db->quoted('2012-06-01'), 'date') . ",
                    'Active',
                    '1',
                    " . $this->db->convert($this->db->quoted('2012-08-23 10:30:58'), 'datetime') . ",
                    " . $this->db->convert($this->db->quoted('2012-08-23 10:30:58'), 'datetime') . ",
                    0
                )
        ";
		$this->db->query($query);

		// time period item insert
        $query = "
            INSERT INTO
                timeperiods
                (
                    id,
                    name,
                    parent_id,
                    start_date,
                    end_date,
                    created_by,
                    date_entered,
                    date_modified,
                    deleted,
                    is_fiscal_year
                )
                VALUES
                (
                    " . $this->db->quoted($this->guid2) . ",
                    'FY-2012-August',
                    '',
                    " . $this->db->convert($this->db->quoted('2012-08-01'), 'date') . ",
                    " . $this->db->convert($this->db->quoted('2012-08-31'), 'date') . ",
                    '1',
                    " . $this->db->convert($this->db->quoted('2012-08-23 10:16:34'), 'datetime') . ",
                    " . $this->db->convert($this->db->quoted('2012-08-23 10:16:34'), 'datetime') . ",
                    0,
                    0)
        ";
		$this->db->query($query);
    }


    public function tearDown()
    {
        // forecast schedule item removal
        $query = "DELETE FROM forecast_schedule WHERE id=" . $this->db->quoted($this->guid1);
		$this->db->query($query);

		// time period item removal
        $query = "DELETE FROM timeperiods WHERE id=" . $this->db->quoted($this->guid2);
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

		$query = "SELECT a.timeperiod_id, b.name, b.start_date, b.end_date, a.user_id, a.cascade_hierarchy"
		            . " FROM forecast_schedule a, timeperiods b"
		            . " WHERE a.timeperiod_id = b.id"
                    . " AND b.id = " . $this->db->quoted($this->guid2)
                    . " AND a.forecast_start_date <= " . $this->db->convert($this->db->quoted($nowDate), 'date')
                    . " AND b.end_date >= " . $this->db->convert($this->db->quoted($nowDate), 'date')
		            . " AND a.deleted = 0"
		            . " AND b.deleted = 0"
		            . " AND a.status = 'Active'"
		            . " ORDER BY b.start_date, b.end_date";

		$result = $common->db->query($query);
        $actual = 0;
        while ($this->db->fetchByAssoc($result))
        {
            $actual ++;
        }

		$this->assertEquals($resultsFoundExpected, $actual);
    }
}
