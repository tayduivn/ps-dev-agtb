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

require_once 'include/SugarSQLValidate.php';

class QuerySanitizeTest extends Sugar_PHPUnit_Framework_TestCase
{

    public function getQueries()
    {
        return array(
            array("", "", true),
            array("", "accounts.name", true),
            array("", "something BAD", false),
            array("", "something BAD", false),
            array("accounts.name like 'Underwater%'", "", true),
            array("name like 'Underwater%'", "accounts.name", true),
            array("name like 'Underwater%' AND MONTH(accounts.date_created) < MONTH(opportunities.date_modified)+1", "date_created DESC, lcase(account.name) ASC", true),
            array("accounts.name like 'Underwater%'", "something BAD", false),
            array("accounts.name like 'Underwater%'", "also, something BAD", false),
            array("z=1 UNION SELECT * from users", "", false),
            array("z=1 UNION ALL SELECT * from users", "", false),
            array("z=1 UNION ALL SELECT * from users#", "", false),
            array("z=1 UNION ALL SELECT * from users -- test", "", false),
            array("", "something BAD", false),
            array("id='' AND 1=0 UNION SELECT from_addr,1,to_addrs,description FROM emails_text LIMIT 1#", "", false),
            array("", "foo UNION ALL SELECT * from users", false),
            array("", "(leads.status='' OR leads.status IS NULL) DESC,leads.status='New' DESC,leads.status='Assigned' DESC,leads.status='In Process' DESC,leads.status='Converted' DESC,leads.status='Recycled' DESC,leads.status='Dead' DESC", true),
            // OPI email query, should pass
            array("contacts.assigned_user_id = '1' AND (contacts.first_name like '%collin.c.lee@gmail.com%' OR contacts.last_name like '%collin.c.lee@gmail.com%' OR contacts.id IN (SELECT eabr.bean_id FROM email_addr_bean_rel eabr JOIN email_addresses ea ON (ea.id = eabr.email_address_id) WHERE eabr.deleted=0 AND ea.email_address LIKE 'collin.c.lee@gmail.com%'))", "contacts.last_name asc", true),
            // Evil subselect, should not pass
            array("1=1 AND EXISTS (SELECT * FROM users WHERE is_admin=1 and id=(select id from users where is_admin=1 order by id limit 1) and ((ord(substring(id, 1, 1)) >> 5) & 1))", "", false),
            // OPI email query with evil mods, should not pass
            array("contacts.assigned_user_id = '1' AND (contacts.first_name like '%collin.c.lee@gmail.com%' OR contacts.last_name like '%collin.c.lee@gmail.com%' OR contacts.id IN (SELECT eabr.bean_id FROM email_addr_bean_rel eabr JOIN email_addresses ea ON (ea.id = eabr.email_address_id) JOIN users WHERE users.is_admin='1' AND eabr.deleted=0 AND ea.email_address LIKE 'collin.c.lee@gmail.com%'))", "contacts.last_name asc", false),
            // bug 50336
            array('contacts.id IN (SELECT email_addr_bean_rel.bean_id FROM email_addr_bean_rel, email_addresses WHERE email_addresses.id = email_addr_bean_rel.email_address_id AND email_addr_bean_rel.deleted = 0 AND email_addr_bean_rel.bean_module = \'Contacts\' AND email_addresses.email_address IN ("odemendez@starbucks.fr"))', '', true),
            // bug 50487 - Quoted identifiers
            array("`users`.`user_name` = 'admin'", "", true),
            array("`users`.`user_name` = 'admin' and `users`.`first_name` = 'george'", "", true),
            array("`users`.`user_name` = 'admin' and `users`.`first_name` = 'george'", "`users`.`first_name`", true),
            array("`users.user_name = 'admin'`", "", false),
            );
    }

    /**
     * @dataProvider  getQueries
     *
     */
    public function testCheckQuery($where, $order_by, $ok)
    {
        $helper = new SugarSQLValidate();
        $res = $helper->validateQueryClauses($where, $order_by);
        $params = array($where, $order_by);
        if($ok) {
            $this->assertTrue($res, string_format("Failed asserting that where: {0} and order by: {1} is valid", $params));
        } else {
            $this->assertFalse($res, string_format("Failed asserting that where: {0} and order by: {1} is invalid", $params));
        }
    }
}