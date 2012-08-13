<?php
//FILE SUGARCRM flav=pro ONLY

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

class HierarchyQueriesTest extends Sugar_PHPUnit_Framework_TestCase
{

private $employee1;
private $employee2;
private $employee3;
private $employee4;
private $opp1;
private $opp2;
private $opp3;
private $opp4;
private $opp5;

public function setUp()
{
    global $beanList, $beanFiles, $current_user;
    require('include/modules.php');

    $GLOBALS['db']->preInstall();

    $current_user = SugarTestUserUtilities::createAnonymousUser();
    $current_user->user_name = 'employee0';
    $current_user->save();

    $this->employee1 = SugarTestUserUtilities::createAnonymousUser();
    $this->employee1->reports_to_id = $current_user->id;
    $this->employee1->user_name = 'employee1';
    $this->employee1->save();

    $this->employee2 = SugarTestUserUtilities::createAnonymousUser();
    $this->employee2->reports_to_id = $current_user->id;
    $this->employee2->user_name = 'employee2';
    $this->employee2->save();

    $this->employee3 = SugarTestUserUtilities::createAnonymousUser();
    $this->employee3->reports_to_id = $this->employee2->id;
    $this->employee3->user_name = 'employee3';
    $this->employee3->save();

    $this->employee4 = SugarTestUserUtilities::createAnonymousUser();
    $this->employee4->reports_to_id = $this->employee3->id;
    $this->employee4->user_name = 'employee4';
    $this->employee4->save();

    $this->opp1 = SugarTestOpportunityUtilities::createOpportunity();
    $this->opp1->assigned_user_id = $current_user->id;
    $this->opp1->probability = '10';
    $this->opp1->best_case = '1300';
    $this->opp1->likely_case = '1200';
    $this->opp1->worst_case = '1100';
    $this->opp1->save();

    $line_1 = SugarTestProductUtilities::createProduct();
    $line_1->opportunity_id = $this->opp1->id;
    $line_1->team_set_id = $current_user->id;
    $line_1->team_id = $current_user->id;
    $line_1->save();

    $this->opp2 = SugarTestOpportunityUtilities::createOpportunity();
    $this->opp2->assigned_user_id = $this->employee1->id;
    $this->opp2->probability = '10';
    $this->opp2->best_case = '1300';
    $this->opp2->likely_case = '1200';
    $this->opp2->worst_case = '1100';
    $this->opp2->save();

    $line_2 = SugarTestProductUtilities::createProduct();
    $line_2->opportunity_id = $this->opp2->id;
    $line_2->team_set_id = $this->employee1->id;
    $line_2->team_id = $this->employee1->id;
    $line_2->save();

    $this->opp3 = SugarTestOpportunityUtilities::createOpportunity();
    $this->opp3->assigned_user_id = $this->employee2->id;
    $this->opp3->probability = '10';
    $this->opp3->best_case = '1300';
    $this->opp3->likely_case = '1200';
    $this->opp3->worst_case = '1100';
    $this->opp3->save();

    $line_3 = SugarTestProductUtilities::createProduct();
    $line_3->opportunity_id = $this->opp3->id;
    $line_3->team_set_id = $this->employee2->id;
    $line_3->team_id = $this->employee2->id;
    $line_3->save();

    $this->opp4 = SugarTestOpportunityUtilities::createOpportunity();
    $this->opp4->assigned_user_id = $this->employee3->id;
    $this->opp4->probability = '10';
    $this->opp4->best_case = '1300';
    $this->opp4->likely_case = '1200';
    $this->opp4->worst_case = '1100';
    $this->opp4->save();

    $line_4 = SugarTestProductUtilities::createProduct();
    $line_4->opportunity_id = $this->opp4->id;
    $line_4->team_set_id = $this->employee3->id;
    $line_4->team_id = $this->employee3->id;
    $line_4->save();

    $this->opp5 = SugarTestOpportunityUtilities::createOpportunity();
    $this->opp5->assigned_user_id = $this->employee4->id;
    $this->opp5->probability = '10';
    $this->opp5->best_case = '1300';
    $this->opp5->likely_case = '1200';
    $this->opp5->worst_case = '1100';
    $this->opp5->save();

    $line_5 = SugarTestProductUtilities::createProduct();
    $line_5->opportunity_id = $this->opp5->id;
    $line_5->team_set_id = $this->employee4->id;
    $line_5->team_id = $this->employee4->id;
    $line_5->save();
}

public function tearDown()
{
    $GLOBALS['db']->dropTableName('_hierarchy_return_set');
    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    SugarTestProductUtilities::removeAllCreatedProducts();
    SugarTestOpportunityUtilities::removeAllCreatedOpps();
}

/**
 * @group hierarchies
 */
public function testForecastTree()
{
    global $current_user;
    $sql = $GLOBALS['db']->getRecursiveSelectSQL('users', 'id', 'reports_to_id', 'id, user_name, reports_to_id', false, "status = 'Active' and user_name like 'employee%'");
    $result = $GLOBALS['db']->query($sql);
    while($row = $GLOBALS['db']->fetchByAssoc($result))
    {
        switch($row['id'])
        {
            case $this->employee1->id:
            case $this->employee2->id:
                $this->assertEquals($current_user->id, $row['reports_to_id']);
            break;

            case $this->employee3->id:
                $this->assertEquals($this->employee2->id, $row['reports_to_id']);
            break;

            case $this->employee4->id:
                $this->assertEquals($this->employee3->id, $row['reports_to_id']);
            break;
        }
    }

    $result = $GLOBALS['db']->query("SELECT id, parent_id FROM product_categories WHERE deleted = 0");
    $products = array();
    while($row = $GLOBALS['db']->fetchByAssoc($result))
    {
        if(!empty($row['parent_id']))
        {
            $products[$row['id']] = $row['parent_id'];
        }
    }

    $sql = $GLOBALS['db']->getRecursiveSelectSQL('product_categories', 'id', 'parent_id', 'id, parent_id, assigned_user_id, name', false, "deleted = 0");
    $result = $GLOBALS['db']->query($sql);
    while($row = $GLOBALS['db']->fetchByAssoc($result))
    {
        if(!empty($row['parent_id']))
        {
            $this->assertEquals($row['parent_id'], $products[$row['id']]);
        }
    }

    //Finally test a reporting-like query we may encounter
    //This query gets the upstream users for employee4
    $hierarchy_sql = $GLOBALS['db']->getRecursiveSelectSQL('users', 'id', 'reports_to_id', 'id', true, "status = 'Active' AND id = '{$this->employee4->id}'");
    $result = $GLOBALS['db']->query($hierarchy_sql);
    $hierarchy = array();
    while($row = $GLOBALS['db']->fetchByAssoc($result))
    {
       $hierarchy[] = $row['id'];
    }

    $hierarchy = "'" . implode("','", $hierarchy) . "'";

    //Would we really do it this way with an in clause substituted?
    $sql = "SELECT IFNULL(opportunities.id,'') primaryid
    ,IFNULL(opportunities.name,'') opportunities_name
    ,opportunities.probability
    ,opportunities.best_case
    ,opportunities.worst_case
    ,IFNULL(l1.id,'') l1_id
    ,l1.user_name l1_user_name,IFNULL(l2.id,'') l2_id
    ,l2.id l2_product_id,l2.quantity l2_quantity

    FROM opportunities
    LEFT JOIN  users l1 ON opportunities.assigned_user_id=l1.id AND l1.deleted=0

    LEFT JOIN  products l2 ON opportunities.id=l2.opportunity_id AND l2.deleted=0

     AND l2.team_set_id IN (SELECT  tst.team_set_id from team_sets_teams
                                        tst INNER JOIN team_memberships team_memberships ON tst.team_id =
                                        team_memberships.team_id AND team_memberships.user_id in ({$hierarchy}) AND team_memberships.deleted=0)
     WHERE (((l1.id in ({$hierarchy})
    )) AND opportunities.team_set_id IN (SELECT tst.team_set_id FROM
                                    team_sets_teams tst INNER JOIN team_memberships team_memberships ON
                                    tst.team_id = team_memberships.team_id AND team_memberships.user_id in ({$hierarchy})
                                    AND team_memberships.deleted=0))
    AND  opportunities.deleted=0
     LIMIT 0,100";

    $result = $GLOBALS['db']->query($sql);
    $opportunities = 0;
    while($row = $GLOBALS['db']->fetchByAssoc($result))
    {
       $opportunities++;
    }

    $this->assertEquals(4, $opportunities);
}

/*
public function testForecastTreeWithSubSelectOnTempTable()
{
    global $current_user;
    //Here is the alternate method where we use a sub-select against the temporary table created
    $hierarchy_sql = $GLOBALS['db']->getRecursiveSelectSQL('users', 'id', 'reports_to_id', 'id', true, "status = 'Active' AND id = '{$current_user->id}'");
    $result = $GLOBALS['db']->query($hierarchy_sql);

    $hierarchy = "select _id from _hierarchy_return_set";

    $sql = "SELECT IFNULL(opportunities.id,'') primaryid
    ,IFNULL(opportunities.name,'') opportunities_name
    ,opportunities.probability
    ,opportunities.best_case
    ,opportunities.worst_case
    ,IFNULL(l1.id,'') l1_id
    ,l1.user_name l1_user_name,IFNULL(l2.id,'') l2_id
    ,l2.product_id l2_product_id,l2.quantity l2_quantity

    FROM opportunities
    LEFT JOIN  users l1 ON opportunities.assigned_user_id=l1.id AND l1.deleted=0

    LEFT JOIN  products l2 ON opportunities.id=l2.opportunity_id AND l2.deleted=0

     AND l2.team_set_id IN (SELECT  tst.team_set_id from team_sets_teams
                                        tst INNER JOIN team_memberships team_memberships ON tst.team_id =
                                        team_memberships.team_id AND team_memberships.user_id in ({$hierarchy}) AND team_memberships.deleted=0)
     WHERE (((l1.id in ({$hierarchy})
    )) AND opportunities.team_set_id IN (SELECT tst.team_set_id FROM
                                    team_sets_teams tst INNER JOIN team_memberships team_memberships ON
                                    tst.team_id = team_memberships.team_id AND team_memberships.user_id in ({$hierarchy})
                                    AND team_memberships.deleted=0))
    AND  opportunities.deleted=0
     LIMIT 0,100";

    $result = $GLOBALS['db']->query($sql);
    $opportunities = 0;
    while($row = $GLOBALS['db']->fetchByAssoc($result))
    {
       $opportunities++;
    }

    $this->assertEquals(4, $opportunities);
}
*/
}