<?php

/* * *******************************************************************************
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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 * ****************************************************************************** */

require_once('modules/Users/User.php');
/**
 * Bug #47156
 * Reassigning Users With Instance That Has Numeric Ids
 * @ticket 47156
 */
class Bug47156Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $user1;
    private $user2;
    
    private function createUser($id = '', $status = '')
    {
        $time = mt_rand();
        $userId = 'SugarUser';
        $user = new User();
        $user->user_name = $userId . $time;
        $user->user_hash = md5($userId.$time);
        $user->first_name = $userId;
        $user->last_name = $time;
        if (!empty($status))
        {
            $user->status=$status;
        }
        else
        {
            $user->status='Active';
        }
        
        $user->default_team = '1'; //Set Default Team to Global
        if(!empty($id))
        {
            $user->new_with_id = true;
            $user->id = $id;
        }

        $user->save();
        $user->fill_in_additional_detail_fields();
        
        return $user;
    }
    
    /**
     * @group 47156
     */
    public function testCorrectUserListOutput()
    {
        $this->user1 = $this->createUser(11, 'Active');
        $this->user2 = $this->createUser(12, 'Inactive');
        
        $allUsers = User::getAllUsers(); 
        
        $this->assertArrayHasKey($this->user1->id, $allUsers);
        $this->assertArrayHasKey($this->user2->id, $allUsers);
        
        $GLOBALS['db']->query('DELETE FROM users WHERE id IN (' . $this->user1->id . ', ' . $this->user2->id . ')');
        $GLOBALS['db']->query('DELETE FROM user_preferences WHERE assigned_user_id IN (' . $this->user1->id . ', ' . $this->user2->id . ')');
        $GLOBALS['db']->query('DELETE FROM teams WHERE associated_user_id IN (' . $this->user1->id . ', ' . $this->user2->id . ')');
        $GLOBALS['db']->query('DELETE FROM team_memberships WHERE user_id IN (' . $this->user1->id . ', ' . $this->user2->id . ')');
    }
}
?>