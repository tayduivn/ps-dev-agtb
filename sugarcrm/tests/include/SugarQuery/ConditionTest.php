<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

require_once 'include/database/DBManagerFactory.php';
require_once 'modules/Contacts/Contact.php';
require_once 'tests/include/database/TestBean.php';
require_once 'include/SugarQuery/SugarQuery.php';

class ConditionTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var DBManager
     */
    private static $db;
    protected static $opportunities = array();
    protected static $oppIds = array();

    protected $created = array();

    protected $backupGlobals = FALSE;

    protected $contacts = array();
    protected $accounts = array();

    static public function setupBeforeClass()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');

        if(empty(self::$db)){
            self::$db = DBManagerFactory::getInstance();
        }


        // "Delete" all the opportunities that may currently exist
        $sql = "SELECT id FROM opportunities WHERE deleted = 0";
        $res = self::$db->query($sql);
        while ($row = self::$db->fetchRow($res)) {
            self::$oppIds[] = $row['id'];
        }

        if (self::$oppIds) {
            $sql = "UPDATE opportunities SET deleted = 1 WHERE id IN ('" . implode("','", self::$oppIds) . "')";
            self::$db->query($sql);
        }

        for($x=100;$x<=300;$x++) {
            // create a new contact
            $opp = BeanFactory::newBean('Opportunities');
            $opp->name = "SugarQuery Unit Test {$x}";
            $opp->amount = $x;
            $opp->date_modifeid = date('Y-m-d');
            $opp->date_closed = date('Y-m-d');
            $opp->save();
            self::$opportunities[] = $opp;
        }

        unset($opp);
    }

    static public function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
        if( !empty(self::$opportunities) ) {
            $oppList = array();
            foreach(self::$opportunities as $opp) {
                $oppList[] = $opp->id;
            }

            self::$db->query("DELETE FROM opportunities WHERE id IN ('" . implode("','", $oppList) . "')");

            if (self::$db->tableExists('opportunities_cstm')) {
                self::$db->query("DELETE FROM opportunities_cstm WHERE id_c IN ('" . implode("','", $oppList) . "')");
            }
        }

        if (self::$oppIds) {
            $sql = "UPDATE opportunities SET deleted = 0 WHERE id IN ('" . implode("','", self::$oppIds) . "')";
            self::$db->query($sql);
        }
    }

    public function setUp()
    {
        $this->opportunity_bean = BeanFactory::newBean('Opportunities');
    }

    public function tearDown()
    {

    }

    public function testEquals()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "amount"));
        $sq->from($this->opportunity_bean);
        $sq->where()->equals('amount', 200, $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 1, "Wrong row count, actually received: " . count($result) . " back.");

        foreach($result AS $opp) {
            $this->assertEquals(200,$opp['amount'], "The amount was not less than 2000 it was: {$opp['amount']}");
        }
    }

    public function testContains()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "amount"));
        $sq->from($this->opportunity_bean);
        $sq->where()->contains('name', 'Query Unit Test 10', $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 10, "Wrong row count, actually received: " . count($result) . " back.");

        foreach($result AS $opp) {
            $test_string = strstr($opp['name'], '10');
            $this->assertTrue(!empty($test_string), "The name did not contain 10 it was: {$opp['name']}");
        }
    }

    public function testStartsWith()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "amount"));
        $sq->from($this->opportunity_bean);
        $sq->where()->starts('name', 'SugarQuery Unit Test 10', $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 10, "Wrong row count, actually received: " . count($result) . " back.");

        foreach($result AS $opp) {
            $test_string = stristr($opp['name'], 'SugarQuery Unit Test 10');
            $this->assertTrue(!empty($test_string), "The name did not start with SugarQuery Unit Test 10 it was: {$opp['name']}");
        }
    }

    public function testLessThan()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "amount"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->lt('amount',200, $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 100, "Wrong row count, actually received: " . count($result) . " back.");

        foreach($result AS $opp) {
            $this->assertLessThan(200,$opp['amount'], "The amount was not less than 2000 it was: {$opp['amount']}");
        }
    }

    public function testLessThanEquals()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "amount"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->lte('amount',200, $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 101, "Wrong row count, actually received: " . count($result) . " back.");

        foreach($result AS $opp) {
            $this->assertLessThanOrEqual(200,$opp['amount'], "The amount was not less than 2000 it was: {$opp['amount']}");
        }
    }

    public function testGreaterThan()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "amount"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->gt('amount',200, $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 100, "Wrong row count, actually received: " . count($result) . " back.");

        foreach($result AS $opp) {
            $this->assertGreaterThan(200,$opp['amount'], "The amount was not less than 2000 it was: {$opp['amount']}");
        }
    }

    public function testGreaterThanEquals()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "amount"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->gte('amount',200, $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 101, "Wrong row count, actually received: " . count($result) . " back.");

        foreach($result AS $opp) {
            $this->assertGreaterThanOrEqual(200,$opp['amount'], "Wrong amount value detected.");
        }
    }

    public function testIn()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "amount"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->in('amount',array(100,101,102,103,104,105), $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 6, "Wrong row count, actually received: " . count($result) . " back.");
    }

    public function testNotIn()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "amount"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->notIn('amount', array(100,101,102,103,104,105));

        $result = $sq->execute();

        $this->assertEquals(195, count($result), "Wrong row count, actually received: " . count($result) . " back.");
    }
    public function testBetween()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "amount"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->between('amount',110, 120, $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 11, "Wrong row count, actually received: " . count($result) . " back.");
    }

    public function testNotNull() {
        $sq = new SugarQuery();

        $sq->select(array("name", "amount"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->notNull('amount', $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 201, "Wrong row count, actually received: " . count($result) . " back.");

    }

    public function testNull() {
        $sq = new SugarQuery();

        $sq->select(array("name", "amount"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->isNull('amount', $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 0, "Wrong row count, actually received: " . count($result) . " back.");

    }

    public function testRaw() {
        $sq = new SugarQuery();

        $sq->select(array("name", "amount"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->addRaw("name = 'SugarQuery Unit Test 131'");

        $result = $sq->execute();

        $this->assertEquals(count($result), 1, "Wrong row count, actually received: " . count($result) . " back.");

        $result = reset($result);

        $this->assertEquals($result['name'], "SugarQuery Unit Test 131", "Wrong record returned, received: " . $result['name']);

    }

    public function testOrderByLimit() {
        $sq = new SugarQuery();
        $sq->select("name", "amount");
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->orderBy("amount", "ASC");
        $sq->limit(2);

        $result = $sq->execute();

        $this->assertEquals(count($result), 2, "Wrong row count, actually received: " . count($result) . " back.");

        $low = $result[0]['amount'];
        $high = $result[1]['amount'];

        $this->assertGreaterThan($low, $high, "{$high} is not greater than {$low}");

        $sq = new SugarQuery();
        $sq->select("name", "amount");
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->orderBy("amount", "ASC");
        $sq->limit(2);
        $sq->offset(1);

        $result = $sq->execute();

        $this->assertEquals(count($result), 2, "Wrong row count, actually received: " . count($result) . " back.");

        $low = $result[0]['amount'];
        $high = $result[1]['amount'];

        $this->assertGreaterThan($low, $high, "{$high} is not greater than {$low}");


    }

}
