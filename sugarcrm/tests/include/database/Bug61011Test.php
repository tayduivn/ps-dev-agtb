<?php
//FILE SUGARCRM flav=pro ONLY
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

/**
 * @ticket 61011
 */
class Bug61011Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $has_disable_count_query_enabled;

    public function setUp()
    {
        global $sugar_config;
        $this->has_disable_count_query_enabled = !empty($sugar_config['disable_count_query']);
        $sugar_config['disable_count_query'] = true;
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    public function tearDown()
    {
        global $sugar_config;
        if($this->has_disable_count_query_enabled) {
            $sugar_config['disable_count_query'] = true;
        } else {
           unset($sugar_config['disable_count_query']);
        }
        SugarTestHelper::tearDown();
    }

    public function testAddDistinctIgnoresAggregates()
    {
        $query = <<<END
SELECT sum(IFNULL(t6_test61011.sum1,0)/IFNULL(t6_test61011_currencies.conversion_rate,1))*1 t6_test61011_sum_sum1,sum(IFNULL(t6_test61011.sum2,0)/IFNULL(t6_test61011_currencies.conversion_rate,1))*1 t6_test61011_sum_sum2,sum(IFNULL(t6_test61011.sum3,0)/IFNULL(t6_test61011_currencies.conversion_rate,1))*1 t6_test61011_sum_sum3,sum(IFNULL(t6_test61011.sum4,0)/IFNULL(t6_test61011_currencies.conversion_rate,1))*1 t6_test61011_sum_sum4
FROM t6_test61011
 INNER JOIN (select tst.team_set_id from team_sets_teams tst INNER JOIN team_memberships team_memberships ON tst.team_id = team_memberships.team_id
                                        AND team_memberships.user_id = 'seed_max_id'
                                        AND team_memberships.deleted=0 group by tst.team_set_id) t6_test61011_tf on t6_test61011_tf.team_set_id  = t6_test61011.team_set_id LEFT JOIN currencies t6_test61011_currencies ON t6_test61011.currency_id=t6_test61011_currencies.id AND t6_test61011_currencies.deleted=0
 WHERE ((((t6_test61011.name IS NOT NULL AND t6_test61011.name <> ''))))
AND  t6_test61011.deleted=0
END;
        $db = new Bug61011Test_Db($GLOBALS['db']);
        $db->addDistinctClause($query);
        $this->assertContains("SELECT sum(", $query);
        $this->assertContains("group by tst.team_set_id", $query);
    }
}

class Bug61011Test_Db extends MysqlManager
{
    public function __construct($db) {
        $this->db = $db;
    }

    public function addDistinctClause(&$sql)
    {
        return $this->db->addDistinctClause($sql);
    }
}