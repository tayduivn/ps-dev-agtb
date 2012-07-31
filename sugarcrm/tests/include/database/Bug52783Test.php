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
require_once 'include/database/MssqlManager.php';

/**
 * 
 * Test is used for testing returnOrderBy() through limitQuery() because returnOrderBy() is private
 * In MSSQL we use a different way of limiting a query using TOP and ROW_NUMBER() OVER( ORDER BY ordering )
 * the 'ordering' attribute is tested on correctness with this test
 * 
 * @author avucinic@sugarcrm.com
 *
 */
class Bug52783Test extends Sugar_PHPUnit_Framework_TestCase
{
	private $_db;

	public function setUp()
	{
		$this->_db = new MssqlManager();
	}

	public function tearDown()
	{
	}

	/**
	 * Test if returnOrderBy() inside limitQuery() method returns a valid MSSQL ordering column for use in OVER()
	 * @dataProvider orderProvider
	 */
	public function testReturnOrderBy($sql, $expected)
	{
		$expected = strtolower($expected);
		$result = strtolower($this->_db->limitQuery($sql, 21, 20, false, '', false));
		$this->assertContains($expected, $result);
	}

	public function orderProvider()
	{
		return array(
		0 => array(
				"SELECT  contacts.id , LTRIM(RTRIM(ISNULL(contacts.first_name,'')+' '+ISNULL(contacts.last_name,''))) as name, contacts.first_name , contacts.last_name , contacts.salutation  , accounts.name account_name, jtl0.account_id account_id, contacts.title , contacts.phone_work  , LTRIM(RTRIM(ISNULL(jt1.first_name,'')+' '+ISNULL(jt1.last_name,''))) assigned_user_name , jt1.created_by assigned_user_name_owner  , 'Users' assigned_user_name_mod, contacts.date_entered , contacts.assigned_user_id  , sfav.id is_favorite  FROM contacts   LEFT JOIN  accounts_contacts jtl0 ON contacts.id=jtl0.contact_id AND jtl0.deleted=0
				LEFT JOIN  accounts accounts ON accounts.id=jtl0.account_id AND accounts.deleted=0
				AND accounts.deleted=0  LEFT JOIN  users jt1 ON contacts.assigned_user_id=jt1.id AND jt1.deleted=0
				AND jt1.deleted=0 LEFT JOIN  sugarfavorites sfav ON sfav.module ='Contacts' AND sfav.record_id=contacts.id AND sfav.created_by='1' AND sfav.deleted=0  where contacts.deleted=0 ORDER BY name",
			
				"LTRIM(RTRIM(ISNULL(contacts.first_name,'')+' '+ISNULL(contacts.last_name,'')))"
			),
			1 => array(
				"SELECT  contacts.id , LTRIM(RTRIM(ISNULL(contacts.first_name,'')+' '+ISNULL(contacts.last_name,''))) as name, contacts.first_name , contacts.last_name , contacts.salutation  , accounts.name account_name, jtl0.account_id account_id, contacts.title , contacts.phone_work  , LTRIM(RTRIM(ISNULL(jt1.first_name,'')+' '+ISNULL(jt1.last_name,''))) assigned_user_name , jt1.created_by assigned_user_name_owner  , 'Users' assigned_user_name_mod, contacts.date_entered , contacts.assigned_user_id  , sfav.id is_favorite  FROM contacts   LEFT JOIN  accounts_contacts jtl0 ON contacts.id=jtl0.contact_id AND jtl0.deleted=0
				LEFT JOIN  accounts accounts ON accounts.id=jtl0.account_id AND accounts.deleted=0
				AND accounts.deleted=0  LEFT JOIN  users jt1 ON contacts.assigned_user_id=jt1.id AND jt1.deleted=0
				AND jt1.deleted=0 LEFT JOIN  sugarfavorites sfav ON sfav.module ='Contacts' AND sfav.record_id=contacts.id AND sfav.created_by='1' AND sfav.deleted=0  where contacts.deleted=0 ORDER BY name ASC",
			
				"LTRIM(RTRIM(ISNULL(contacts.first_name,'')+' '+ISNULL(contacts.last_name,'')))"
			),
			2 => array(
				"SELECT  contacts.id , LTRIM(RTRIM(ISNULL(contacts.first_name,'')+' '+ISNULL(contacts.last_name,''))) as name, contacts.first_name , contacts.last_name , contacts.salutation  , accounts.name account_name, jtl0.account_id account_id, contacts.title , contacts.phone_work  , LTRIM(RTRIM(ISNULL(jt1.first_name,'')+' '+ISNULL(jt1.last_name,''))) assigned_user_name , jt1.created_by assigned_user_name_owner  , 'Users' assigned_user_name_mod, contacts.date_entered , contacts.assigned_user_id  , sfav.id is_favorite  FROM contacts   LEFT JOIN  accounts_contacts jtl0 ON contacts.id=jtl0.contact_id AND jtl0.deleted=0
				LEFT JOIN  accounts accounts ON accounts.id=jtl0.account_id AND accounts.deleted=0
				AND accounts.deleted=0  LEFT JOIN  users jt1 ON contacts.assigned_user_id=jt1.id AND jt1.deleted=0
				AND jt1.deleted=0 LEFT JOIN  sugarfavorites sfav ON sfav.module ='Contacts' AND sfav.record_id=contacts.id AND sfav.created_by='1' AND sfav.deleted=0  where contacts.deleted=0 ORDER BY contacts.name ASC",
			
				"contacts.name"
			),
			3 => array(
				"SELECT  contacts.id , ISNULL(contacts.last_name,''))) as name, contacts.first_name , contacts.last_name , contacts.salutation  , accounts.name account_name, jtl0.account_id account_id, contacts.title , contacts.phone_work  , LTRIM(RTRIM(ISNULL(jt1.first_name,'')+' '+ISNULL(jt1.last_name,''))) assigned_user_name , jt1.created_by assigned_user_name_owner  , 'Users' assigned_user_name_mod, contacts.date_entered , contacts.assigned_user_id  , sfav.id is_favorite  FROM contacts   LEFT JOIN  accounts_contacts jtl0 ON contacts.id=jtl0.contact_id AND jtl0.deleted=0
				LEFT JOIN  accounts accounts ON accounts.id=jtl0.account_id AND accounts.deleted=0
				AND accounts.deleted=0  LEFT JOIN  users jt1 ON contacts.assigned_user_id=jt1.id AND jt1.deleted=0
				AND jt1.deleted=0 LEFT JOIN  sugarfavorites sfav ON sfav.module ='Contacts' AND sfav.record_id=contacts.id AND sfav.created_by='1' AND sfav.deleted=0  where contacts.deleted=0 ORDER BY name ASC",
			
				"ISNULL(contacts.last_name,'')))"
			),
		);
	}
}
