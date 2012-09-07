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
 
require_once('modules/Emails/EmailUI.php');

/**
 * Test cases for Bug 9755
 */
class FindEmailFromBeanIdsTest extends Sugar_PHPUnit_Framework_TestCase
{
	private $emailUI;
	private $beanIds, $beanType, $whereArr;
	private $resultQuery, $expectedQuery;
	
	function setUp()
	{
		global $current_user, $currentModule ;
		$current_user = SugarTestUserUtilities::createAnonymousUser();
		$this->emailUI = new EmailUI();
		$this->beanIds[] = '8744c7d9-9e4b-2338-cb76-4ab0a3d0a651';
		$this->beanIds[] = '8749a110-1d85-4562-fa23-4ab0a3c65e12';
		$this->beanIds[] = '874c1242-4645-898d-238a-4ab0a3f7e7c3';
		$this->beanType = 'users';
		$this->whereArr['first_name'] = 'testfn';
		$this->whereArr['last_name'] = 'testln';
		$this->whereArr['email_address'] = 'test@example.com';
		$this->expectedQuery = <<<EOQ
SELECT users.id, users.first_name, users.last_name, eabr.primary_address, ea.email_address, 'Users' module FROM users JOIN email_addr_bean_rel eabr ON (users.id = eabr.bean_id and eabr.deleted=0) JOIN email_addresses ea ON (eabr.email_address_id = ea.id)  WHERE (users.deleted = 0 AND eabr.primary_address = 1 AND users.id in ('8744c7d9-9e4b-2338-cb76-4ab0a3d0a651','8749a110-1d85-4562-fa23-4ab0a3c65e12','874c1242-4645-898d-238a-4ab0a3f7e7c3')) AND (first_name LIKE 'testfn%' OR last_name LIKE 'testln%' OR email_address LIKE 'test@example.com%')
EOQ;
	}
	
	function tearDown()
	{
		unset($this->emailUI);
		SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
	}
	
	function testFindEmailFromBeanIdTest()
	{
		//$this->resultQuery = $this->emailUI->findEmailFromBeanIds('', $this->beanType, $this->whereArr);
		$this->resultQuery = $this->emailUI->findEmailFromBeanIds($this->beanIds, $this->beanType, $this->whereArr);
		$this->assertEquals($this->expectedQuery, $this->resultQuery);
	}
}
