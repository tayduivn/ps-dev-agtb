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
 
require_once('include/SugarFolders/SugarFolders.php');

/**
 * @ticket 33404
 */
class Bug33906Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $folder = null;
    protected $_user = null;
    
	public function setUp()
    {
        global $current_user, $currentModule;

        $this->_user = SugarTestUserUtilities::createAnonymousUser();
		 $GLOBALS['current_user'] = $this->_user;
		$this->folder = new SugarFolder(); 
	}

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        
        $GLOBALS['db']->query("DELETE FROM folders_subscriptions WHERE assigned_user_id='{$this->_user->id}'");
        $GLOBALS['db']->query("DELETE FROM folders_subscriptions WHERE folder_id='{$this->folder->id}'");
        $GLOBALS['db']->query("DELETE FROM folders WHERE id='{$this->folder->id}'");
        
        unset($this->folder);
    }
    
	public function testSaveFolderNoSubscriptions()
	{
	    global $current_user;
	    $this->folder->save();

	    $result = $GLOBALS['db']->query("SELECT count(*) as cnt FROM folders_subscriptions where folder_id='{$this->folder->id}'");
	    $rs = $GLOBALS['db']->fetchByAssoc($result);

	    $this->assertGreaterThan(0, $rs['cnt'], "Could not create folder subscriptions properly." );
    }
    
	public function testSaveFolderWithSubscriptions()
	{
        global $current_user;
	    $this->folder->save(FALSE);

	    $result = $GLOBALS['db']->query("SELECT count(*) as cnt FROM folders_subscriptions where folder_id='{$this->folder->id}'");
	    $rs = $GLOBALS['db']->fetchByAssoc($result);

	    $this->assertEquals(0, $rs['cnt'], "Created folder subscriptions when none should have been created." );
    }
}