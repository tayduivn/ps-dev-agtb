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
//FILE SUGARCRM flav=ent ONLY
require_once 'include/database/DBManagerFactory.php';

class Bug57563Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $_db;

    public function setUp()
    {
        if(empty($this->_db)){
            $this->_db = DBManagerFactory::getInstance();
        }
    }

    public function tearDown()
    {
    }

    public function test_union_query_for_limitQuery()
    {
        $query_object = new CustomQuery();

        $m_closed = $query_object->db->convert('opportunities.date_closed', 'month');
        $today = $query_object->db->convert('', 'today');
        $m_date[0] = $query_object->db->convert($today, 'month');
        for($i=1; $i<6; $i++)
        {
            $m_date[$i] = $query_object->db->convert($query_object->db->convert($today, 'add_date', array($i, 'month')), 'month');
        }
        $m_date5 = $query_object->db->convert($today, 'add_date', array(5, "month"));

        $query = "(
            SELECT
             'New Business' \"Opportunity Type\",
            sum( case when $m_closed = $m_date[0] then opportunities.amount_usdollar else 0 end ) \"{sc}0{sc}0\",
            sum( case when $m_closed = $m_date[1] then opportunities.amount_usdollar else 0 end ) \"{sc}0{sc}1\",
            sum( case when $m_closed = $m_date[2] then opportunities.amount_usdollar else 0 end ) \"{sc}0{sc}2\",
            sum( case when $m_closed = $m_date[3] then opportunities.amount_usdollar else 0 end ) \"{sc}0{sc}3\",
            sum( case when $m_closed = $m_date[4] then opportunities.amount_usdollar else 0 end ) \"{sc}0{sc}4\",
            sum( case when $m_closed = $m_date[5] then opportunities.amount_usdollar else 0 end ) \"{sc}0{sc}5\",
            SUM(opportunities.amount_usdollar) AS \"Total Revenue\"
            FROM opportunities
             LEFT JOIN accounts_opportunities
            ON opportunities.id=accounts_opportunities.opportunity_id
            LEFT JOIN accounts
            ON accounts_opportunities.account_id=accounts.id
            WHERE opportunities.date_closed <= $m_date5 AND  opportunities.date_closed >= $today AND opportunities.opportunity_type = 'New Business'
            ) UNION (
            SELECT
             'Existing Business' \"Opportunity Type\",
            sum( case when $m_closed = $m_date[0] then opportunities.amount_usdollar else 0 end ) \"{sc}0{sc}0\",
            sum( case when $m_closed = $m_date[1] then opportunities.amount_usdollar else 0 end ) \"{sc}0{sc}1\",
            sum( case when $m_closed = $m_date[2] then opportunities.amount_usdollar else 0 end ) \"{sc}0{sc}2\",
            sum( case when $m_closed = $m_date[3] then opportunities.amount_usdollar else 0 end ) \"{sc}0{sc}3\",
            sum( case when $m_closed = $m_date[4] then opportunities.amount_usdollar else 0 end ) \"{sc}0{sc}4\",
            sum( case when $m_closed = $m_date[5] then opportunities.amount_usdollar else 0 end ) \"{sc}0{sc}5\",
            SUM(opportunities.amount_usdollar) AS \"Total Revenue\"
            FROM opportunities
             LEFT JOIN accounts_opportunities
            ON opportunities.id=accounts_opportunities.opportunity_id
            LEFT JOIN accounts
            ON accounts_opportunities.account_id=accounts.id
            WHERE opportunities.date_closed <= $m_date5 AND  opportunities.date_closed >= $today AND opportunities.opportunity_type = 'Existing Business'
            ) UNION (
            SELECT
             'Total Revenue' \"Opportunity Type\",
            sum( case when $m_closed = $m_date[0] then opportunities.amount_usdollar else 0 end ) \"{sc}0{sc}0\",
            sum( case when $m_closed = $m_date[1] then opportunities.amount_usdollar else 0 end ) \"{sc}0{sc}1\",
            sum( case when $m_closed = $m_date[2] then opportunities.amount_usdollar else 0 end ) \"{sc}0{sc}2\",
            sum( case when $m_closed = $m_date[3] then opportunities.amount_usdollar else 0 end ) \"{sc}0{sc}3\",
            sum( case when $m_closed = $m_date[4] then opportunities.amount_usdollar else 0 end ) \"{sc}0{sc}4\",
            sum( case when $m_closed = $m_date[5] then opportunities.amount_usdollar else 0 end ) \"{sc}0{sc}5\",
            SUM(opportunities.amount_usdollar) AS \"Total Revenue\"
            FROM opportunities
             LEFT JOIN accounts_opportunities
            ON opportunities.id=accounts_opportunities.opportunity_id
            LEFT JOIN accounts
            ON accounts_opportunities.account_id=accounts.id
            WHERE opportunities.date_closed <= $m_date5 AND  opportunities.date_closed >= $today
            )";

        $this->_db->limitQuery($query, 0, 1);

        $this->assertEmpty($this->_db->lastError(), "lastError should return empty");
    }
}
