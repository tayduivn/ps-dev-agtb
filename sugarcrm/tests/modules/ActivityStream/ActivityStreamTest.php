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


require_once 'modules/ActivityStream/ActivityStream.php';
require_once 'modules/ActivityStream/vardefs.php';

class ActivityStreamTest extends Sugar_PHPUnit_Framework_TestCase {
    private $account;
    
    public function setUp() {
        global $dictionary;
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();  
        $this->account = SugarTestAccountUtilities::createAccount();  
        $this->account->module_name = 'Accounts';
        if(!isset($dictionary['ActivityComments'])) {
            $dictionary['ActivityComments'] =
            array ( 'table' => 'activity_comments',
                    'fields' => array (
                            'id'=> array('name' =>'id', 'type' =>'id', 'len'=>'36','required'=>true),
                            'activity_id'=>array('name' =>'activity_id', 'type' =>'id', 'len'=>'36','required'=>true),
                            'date_created'=>array('name' =>'date_created','type' => 'datetime'),
                            'created_by'=>array('name' =>'created_by','type' => 'varchar','len' => 36),
                            'comment_body'=>array('name' =>'comment_body','type' => 'text'),
                    )
            );
        } 
    }

    public function tearDown() {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset( $GLOBALS['current_user']);
        if(!empty($this->account)) {
            $GLOBALS['db']->query("DELETE FROM accounts WHERE id = '".$this->account->id."'");
            $activities = ActivityStream::getActivities($this->account);
            if(!empty($activities)) {
                $activityIds = array();
                foreach($activities as $activity) {
                    $activityIds[] = $activity['id'];
                }
                if(!empty($activityIds)) {
                    $GLOBALS['db']->query("DELETE FROM activity_comments WHERE activity_id IN ('" . implode("', '", $activityIds) . "')");
                }
                $GLOBALS['db']->query("DELETE FROM activity_stream WHERE target_module = 'Accounts' AND target_id = '".$this->account->id."'");
            }            
        }
    }

    public function testActivityStream() {
        $this->account->name = 'this is a test';
        $sql = "SELECT * FROM accounts WHERE id = '".$this->account->id."'";
        $result = $GLOBALS['db']->query($sql);
        $this->account->fetched_row = $GLOBALS['db']->fetchByAssoc($result);
        $activity = new ActivityStream();
        $result = $activity->addActivity($this->account, ActivityStream::ACTIVITY_TYPE_UPDATE);
        $this->assertEquals(true, $result);   
        $result = $activity->addPost('this is a test', 'Accounts', $this->account->id);
        $this->assertEquals(true, $result);
        $activities = $activity->getActivities('Accounts', $this->account->id);
        $this->assertGreaterThanOrEqual(2, count($activities));
        $activity->loadFromRow($activities[0]);
        $result = $activity->addComment('this is a test');
        $this->assertEquals(true, $result);
        $comments = $activity->getComments();
        $this->assertGreaterThanOrEqual(1, count($comments));       
    } 
}
