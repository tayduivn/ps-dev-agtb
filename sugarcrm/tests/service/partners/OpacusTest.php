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

require_once('include/SugarSQLValidate.php');

class OpacusTest extends Sugar_PHPUnit_Framework_TestCase
{


/**
 * getEntryListQueries
 *
 * These are some of the queries that may come in to the get_entry_list method from the Thunderbird plugin
 */
public function getEntryListThunderbirdPluginQueries()
{
    return array(
        array("(project_task.project_id IN (SELECT project_id FROM projects_contacts pc INNER JOIN email_addr_bean_relÊ
        eabr ON eabr.bean_id = pc.contact_id AND eabr.bean_module='Contacts' inner join email_addresses ea5 ON
        eabr.email_address_id = ea5.id WHERE ea5.email_address LIKE 'test%' AND eabr.deleted = '0' AND ea5.deleted = '0'
        AND pc.deleted = '0'))"),
    );
}

/**
 * testGetEntryListThunderbirdPlugin
 *
 * This method tests the SugarSQLValidate.php's validateQuery method.
 *
 * @param $sql String of the test SQL to simulate the Word plugin
 *
 *
 * @dataProvider getEntryListThunderbirdPluginQueries
 */
public function testGetEntryListThunderbirdPlugin($sql)
{
    $this->markTestIncomplete('Need to resolve the above query or investigate a workaround for Opacus');
    $valid = new SugarSQLValidate();
    $this->assertTrue($valid->validateQueryClauses($sql), "SugarSQLValidate found Bad query: {$sql}");
}

}
