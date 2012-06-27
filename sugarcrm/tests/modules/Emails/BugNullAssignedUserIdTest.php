<?php 
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
 
require_once('modules/Emails/Email.php');

/**
 * Test case for Bugs 50972, 50973 and 50979
 */
class BugNullAssignedUserIdTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $email;
	
	public function setUp()
	{
	    global $current_user;
		
	    $current_user = SugarTestUserUtilities::createAnonymousUser();
	    $this->email = new Email();
	    $this->email->email2init();

        // Set some values for some fields so the query is actually built
        $this->email->id = '1';
        $this->email->created_by = $current_user->id;
        $this->email->date_modified = date('Y-m-d H:i:s');

        // Specify an empty assigned user id for testing nulls
        $this->email->assigned_user_id = '';
	}
	
	public function tearDown()
	{
		unset($this->email);
		SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
		unset($GLOBALS['current_user']);
	}

    public function testNullAssignedUserIdConvertedToEmptyInSave() {
        $query = $this->email->db->updateSQL($this->email);
        $this->assertContains("assigned_user_id=''", $query, 'Assigned user id set to empty string not found');
    }

    public function testNullAssignedUserIdInSave() {
        $this->email->setFieldNullable('assigned_user_id');
        $query = $this->email->db->updateSQL($this->email);
        $this->email->revertFieldNullable('assigned_user_id');
        $this->assertContains('assigned_user_id=NULL', $query, 'Assigned user id set to DB NULL value not found');
    }
}
?>