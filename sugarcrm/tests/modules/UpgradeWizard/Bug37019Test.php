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
 
class Bug37019Test extends Sugar_PHPUnit_Framework_TestCase 
{
	var $report_id;
	
    public function setUp() 
    {
    	$this->report_id = create_guid();
		$sql = "INSERT INTO saved_reports (id, name, module, report_type, content, deleted, date_entered, date_modified, assigned_user_id, modified_user_id, created_by, team_id, team_set_id, is_published, chart_type, schedule_type, favorite) VALUES ('{$this->report_id}','Bug37019','Opportunities','tabular','{\"display_columns\":[{\"name\":\"name\",\"label\":\"Opportunity Name\",\"table_key\":\"self\"},{\"name\":\"amount\",\"label\":\"Amount\",\"table_key\":\"self\"}],\"module\":\"Opportunities\",\"group_defs\":[],\"summary_columns\":[],\"report_name\":\"Bug37019\",\"numerical_chart_column\":\"\",\"numerical_chart_column_type\":\"\",\"assigned_user_id\":\"1\",\"report_type\":\"tabular\",\"full_table_list\":{\"self\":{\"value\":\"Opportunities\",\"module\":\"Opportunities\",\"label\":\"Opportunities\"},\"Opportunities:assigned_user_link\":{\"name\":\"Opportunities  >  Assigned to User \",\"parent\":\"self\",\"link_def\":{\"name\":\"assigned_user_link\",\"relationship_name\":\"opportunities_assigned_user\",\"bean_is_lhs\":false,\"link_type\":\"one\",\"label\":\"Assigned to User\",\"table_key\":\"Opportunities:assigned_user_link\"},\"dependents\":[\"Filter.1_table_filter_row_1\"],\"module\":\"Users\",\"label\":\"Assigned to User\"},\"Opportunities:assigned_user_link:teams\":{\"name\":\"Opportunities  >  Assigned to User  >  Teams\",\"parent\":\"Opportunities:assigned_user_link\",\"link_def\":{\"name\":\"teams\",\"relationship_name\":\"team_memberships\",\"bean_is_lhs\":false,\"link_type\":\"many\",\"label\":\"Teams\",\"table_key\":\"Opportunities:assigned_user_link:teams\"},\"dependents\":[\"Filter.1_table_filter_row_1\"],\"module\":\"Teams\",\"label\":\"Teams\"}},\"filters_def\":{\"Filter_1\":{\"operator\":\"AND\",\"0\":{\"name\":\"name\",\"table_key\":\"Opportunities:assigned_user_link:teams\",\"qualifier_name\":\"is\",\"runtime\":1,\"input_name0\":\"d82ff668-398d-b503-106d-4a69323ddddc\",\"input_name1\":\"(admin)\"}}},\"chart_type\":\"none\"}',0,'2010-04-20 19:03:59','2010-04-20 19:03:59','1','1','1','1','1',0,'none','pro',0)";    		
        $GLOBALS['db']->query($sql);
    }

    public function tearDown() 
    {
    	$sql = "DELETE FROM saved_reports WHERE id = '{$this->report_id}'";
    	$GLOBALS['db']->query($sql);
    }

    public function test_fix_report_relationships() {
    	
    	require_once('modules/UpgradeWizard/uw_utils.php');

    	$sql = "SELECT id, content FROM saved_reports WHERE id ='{$this->report_id}'";
        $result = $GLOBALS['db']->query($sql);    
        $old_content = '';	
        while($row = $GLOBALS['db']->fetchByAssoc($result)) {
        	  $old_content = $row['content'];
        	  $old_content = str_replace('&quot;', '"', $old_content);
        }
    	
    	fix_report_relationships();
    	
    	$new_content = '';
        $result = $GLOBALS['db']->query($sql);    	
        while($row = $GLOBALS['db']->fetchByAssoc($result)) {
        	  $new_content = $row['content'];
        	  $new_content = str_replace('&quot;', '"', $new_content);
        }
        
        $this->assertNotEquals($old_content, $new_content, 'Assert that the contents in saved_reports have been updated.');
        $this->assertEquals(!preg_match('/\:assigned_user_link\:teams/', $new_content), true, 'assigned_user_link:teams has been renamed');
        $this->assertEquals(!preg_match('/\{\"name\":\"teams\",\"relationship_name\":\"team_memberships\"/', $new_content), true, 'Assert that teams relationship_name has been removed');
        $this->assertEquals(preg_match('/\:assigned_user_link\:team_memberships/', $new_content), 1, 'assigned_user_link:teams has been renamed to assigned_user_link:team_memberships');
        $this->assertEquals(preg_match('/\{\"name\":\"team_memberships\",\"relationship_name\":\"team_memberships\"/', $new_content), 1, 'Assert that teams relationship_name has been renamed to team_memberships'); 
    }
}
?>