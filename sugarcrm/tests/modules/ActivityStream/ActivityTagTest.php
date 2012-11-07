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

class ActivityTagTest extends Sugar_PHPUnit_Framework_TestCase {   
    public function setUp() {
        global $dictionary;
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();  

        if(!isset($dictionary['ActivityComments'])) {
            $dictionary['ActivityComments'] =
            array ( 'table' => 'activity_comments',
                    'fields' => array (
                            'id'=> array('name' =>'id', 'type' =>'id', 'len'=>'36','required'=>true),
                            'activity_id'=>array('name' =>'activity_id', 'type' =>'id', 'len'=>'36','required'=>true),
                            'date_created'=>array('name' =>'date_created','type' => 'datetime'),
                            'created_by'=>array('name' =>'created_by','type' => 'varchar','len' => 36),
                            'value'=>array('name' =>'value','type' => 'text'),
                    )
            );
        } 
        
        if(!isset($dictionary['ActivityTags'])) {
            $dictionary['ActivityTags'] =
            array ( 'table' => 'activity_tags',
                    'fields' => array (
                          'activity_id'=>array('name' =>'activity_id', 'type' =>'id', 'len'=>'36'),
                          'tag'=>array('name' =>'tag', 'type' =>'varchar', 'len'=>'100'),
                          'count'=>array('name' =>'count','type' => 'int', 'len'=>'10'),
                          'date_modified'=>array('name' =>'date_modified','type' => 'datetime'),
                    )
            );
        }        
    }

    public function tearDown() {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset( $GLOBALS['current_user']);
        $GLOBALS['db']->query("DELETE FROM activity_comments WHERE 1");
        $GLOBALS['db']->query("DELETE FROM activity_stream WHERE 1");
        $GLOBALS['db']->query("DELETE FROM activity_tags WHERE 1");
    }

    public function testActivityTag() {
        $activity = new ActivityStream();   
        $postId = $activity->addPost('', '', 'hello @[Users:1]');
        $commentId = $activity->addComment('hello @[Users:1]');
        $count = $GLOBALS['db']->getOne("SELECT count FROM activity_tags WHERE activity_id ='".$postId."'");
        $this->assertEquals(2, $count);
        $activity->deleteComment($commentId);
        $activity->deletePost($postId);
        $count = $GLOBALS['db']->getOne("SELECT count FROM activity_tags WHERE activity_id ='".$postId."'");
        $this->assertEquals(0, $count);       
    } 
}