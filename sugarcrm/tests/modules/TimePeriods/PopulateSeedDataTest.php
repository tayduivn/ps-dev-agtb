<?php

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

require_once('modules/TimePeriods/TimePeriodsSeedData.php');

class PopulateTimePeriodsSeedDataTest extends Sugar_PHPUnit_Framework_TestCase
{

private $createdTimePeriods;

function setUp()
{
    $GLOBALS['db']->query("UPDATE timeperiods SET deleted = 1");
}

function tearDown()
{
    $GLOBALS['db']->query("DELETE FROM timeperiods WHERE deleted = 0");
    $GLOBALS['db']->query("UPDATE timeperiods SET deleted = 0");
}

/**
 */
function testPopulateSeedData()
{
    $this->createdTimePeriods = TimePeriodsSeedData::populateSeedData();
    $this->assertEquals(20, count($this->createdTimePeriods));
    $total = $GLOBALS['db']->getOne("SELECT count(id) as total FROM timeperiods WHERE deleted = 0");
    $this->assertEquals(25, $total);
}


}
