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


require_once "tests/upgrade/UpgradeTestCase.php";
require_once "modules/RevenueLineItems/upgrade/scripts/post/2_RevenueLineItemMakeVisible.php";


class RevenueLineItemMakeVisibleTest extends UpgradeTestCase
{
    /**
     * @var SugarTestDatabaseMock
     */
    protected $db;

    public function setUp()
    {
        parent::setUp();

        $this->db = new SugarTestDatabaseMock();
        $this->db->setUp();
    }

    public function tearDown()
    {
        $this->db->tearDown();
        parent::tearDown();
    }

    public function dataProviderUpgradeMakeRLIVisible()
    {
        return array(
            array('6.7.6', '7.2.2', 'ent', 'ent', 1), // should run as we are coming from 6.x to 7.x
            array('7.2.2', '7.2.2', 'pro', 'ent', 1), // should run as we are doing a flavor conversion from pro to ent
            array('7.2.2', '7.2.2', 'corp', 'ent', 1), // should run as we are doing a flavor conversion from corp to ent
            array('7.2.1', '7.2.2', 'ent', 'ent', 0), // should not run as we are upgrading ent
            array('7.2.2', '7.2.2', 'ent', 'ult', 0), // should not run as we are doing a conversion from ent to ult
        );
    }

    /**
     * @dataProvider dataProviderUpgradeMakeRLIVisible
     */
    public function testUpgradeMakeRLIVisible($from_version, $to_version, $from_flavor, $to_flavor, $query_run_count)
    {
        $this->db->queries['select_setting'] = array(
            'match' => '/SELECT value FROM config/',
            'rows' => array(
                array(
                    'value' => base64_encode(serialize(array('Accounts')))
                )
            )
        );

        $this->db->queries['update_setting'] = array(
            'match' => '/UPDATE config/'
        );

        $this->upgrader->from_version = $from_version;
        $this->upgrader->to_version = $to_version;
        $this->upgrader->from_flavor = $from_flavor;
        $this->upgrader->to_flavor = $to_flavor;
        $this->upgrader->db = $this->db;

        $upgradeTask = new SugarUpgradeRevenueLineItemMakeVisible($this->upgrader);
        $upgradeTask->run();

        if ($query_run_count > 0) {
            $this->assertEquals($query_run_count, $this->db->queries['select_setting']['runCount']);
            $this->assertEquals($query_run_count, $this->db->queries['update_setting']['runCount']);
        } else {
            $this->assertFalse(isset($this->db->queries['select_setting']['runCount']));
            $this->assertFalse(isset($this->db->queries['update_setting']['runCount']));
        }
    }
}
