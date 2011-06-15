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
 
require_once 'modules/Users/User.php';
require_once "modules/Notes/Note.php";

/**
 * @group bug40263
 */
class Bug40263Test extends Sugar_PHPUnit_Framework_TestCase
{
	var $user;
	var $note;

	public function setUp()
    {
		global $current_user;

		$this->user = SugarTestUserUtilities::createAnonymousUser();//new User();
		$this->user->first_name = "test";
		$this->user->last_name = "user";
		$this->user->user_name = "test_test";
		$this->user->save();
		$current_user=$this->user;

		$this->note = new Note();
		$this->note->name = "Bug40263 test Note";
		$this->note->save();
	}

	public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $this->note->mark_deleted($this->note->id);
        $this->note->db->query("DELETE FROM notes WHERE id='{$this->note->id}'");
	}

	public function testGetListViewQueryCreatedBy()
    {
		require_once("include/ListView/ListViewDisplay.php");
        include("modules/Notes/metadata/listviewdefs.php");
        $displayColumns = array(
            'NAME' => array (
			    'width' => '40%',
			    'label' => 'LBL_LIST_SUBJECT',
			    'link' => true,
			    'default' => true,
			 ),
			 'CREATED_BY_NAME' => array (
			     'type' => 'relate',
			     'label' => 'LBL_CREATED_BY',
			     'width' => '10%',
			     'default' => true,
			 ),
		);
		$lvd = new ListViewDisplay();
		$lvd->displayColumns = $displayColumns;
		$fields = $lvd->setupFilterFields();
    	$query = $this->note->create_new_list_query('', 'id="' . $this->note->id . '"', $fields);
    	$regex = '/select.* created_by_name.*LEFT JOIN\s*users jt\d ON\s*notes.created_by\s*=\s*jt\d\.id.*/si';
    	return $this->assertRegExp($regex, $query, "Unable to find the created user in the notes list view query: $query");
    }

}

