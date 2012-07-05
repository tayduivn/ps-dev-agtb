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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('tests/service/SOAPTestCase.php');

/**
 * Bug #41392
 * Wildcard % searching does not return email addresses when searching with outlook plugin
 *
 * @author mgusev@sugarcrm.com
 * @ticket 41392
 */
class Bug41392Test extends SOAPTestCase
{
    public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/soap.php';
        parent::setUp();
    }

    /**
     * Test creates new account and tries to find the account by wildcard of its email
     *
     * @group 41392
     */
    public function testSearchByModule()
    {
        $user = new User();
        $user->retrieve(1);

        $account = new Account();
        $account->name = 'Bug4192Test';
        $account->email1 = 'Bug4192Test@example.com';
        $account->save();
        $GLOBALS['db']->commit();

        $params = array(
            'user_name' => $user->user_name,
            'password' => $user->user_hash,
            'search_string' => '%@example.com',
            'modules' => array(
                'Accounts'
            ),
            'offset' => 0,
            'max_results' => 30
        );

        $actual = $this->_soapClient->call('search_by_module', $params);
        $account->mark_deleted($account->id);

        $this->assertGreaterThan(0, $actual['result_count'], 'Call must return one bean minimum');
        $this->assertEquals('Accounts', $actual['entry_list'][0]['module_name'], 'Bean must be account');
        $this->assertEquals($account->id, $actual['entry_list'][0]['id'], 'Bean id must be same as id of created account');
    }
}
