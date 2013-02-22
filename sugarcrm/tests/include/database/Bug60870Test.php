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
require_once 'modules/Bugs/Bug.php';
require_once 'include/database/MysqlManager.php';

class Bug60780Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $has_disable_count_query_enabled;

    public function setUp()
    {
        global $sugar_config;

        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
        $this->has_disable_count_query_enabled = !empty($sugar_config['disable_count_query']);
        if(!$this->has_disable_count_query_enabled) {
           $sugar_config['disable_count_query'] = true;
        }
    }

    public function tearDown()
    {
        global $sugar_config;
        if(!empty($this->bugid)) {
            $GLOBALS['db']->query("DELETE FROM bugs WHERE id='{$this->bugid}'");
        }
        if(!$this->has_disable_count_query_enabled) {
           unset($sugar_config['disable_count_query']);
        }
        SugarTestHelper::tearDown();
    }

    public function testCreateBug()
    {
        $bug = BeanFactory::newBean('Bugs');
        $bug->id = $this->bugid = create_guid();
        $bug->new_with_id = true;
        $bug->name = "Module Contains Field With 'select'; Test Info";
        $bug->description = file_get_contents(dirname(__FILE__)."/bug_60870_text.txt");
        $bug->save();

        $bug = new Bug();
        $bug->retrieve($this->bugid);
        $this->assertEquals($this->bugid, $bug->id);
    }

    //BEGIN SUGARCRM flav=pro ONLY
    public function testAddDistinct()
    {
        $query = "SELECT accounts.*,accounts_cstm.selected_c FROM accounts  INNER JOIN (select tst.team_set_id from team_sets_teams tst INNER JOIN team_memberships team_memberships ON tst.team_id = team_memberships.team_id
                                        AND team_memberships.user_id = 'seed_jim_id'
                                        AND team_memberships.deleted=0 group by tst.team_set_id) accounts_tf on accounts_tf.team_set_id  = accounts.team_set_id LEFT JOIN users
                                        ON accounts.assigned_user_id=users.id  LEFT JOIN  team_sets ts ON accounts.team_set_id=ts.id  AND ts.deleted=0
                LEFT JOIN  teams teams ON teams.id=ts.id AND teams.deleted=0 AND teams.deleted=0";
        $db = new Bug60780Test_Db($GLOBALS['db']);
        $db->addDistinctClause($query);
        $this->assertContains("INNER JOIN team_sets_teams tst ON tst.team_set_id = accounts.team_set_id", $query);
        $this->assertContains("accounts_cstm.selected_c", $query);
    }

    //END SUGARCRM flav=pro ONLY

}

class Bug60780Test_Db extends MysqlManager
{
    public function __construct($db) {
        $this->db = $db;
    }

    public function addDistinctClause(&$sql)
    {
        return $this->db->addDistinctClause($sql);
    }
}