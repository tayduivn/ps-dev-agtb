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


require_once('tests/rest/RestTestBase.php');

class RestExportTest extends RestTestBase
{
    public function setUp()
    {
        parent::setUp();

        $account = BeanFactory::newBean('Accounts');
        $account->id = 'UNIT-TEST-' . create_guid_section(10);
        $account->new_with_id = true;
        $account->name = "TEST Account";
        $account->billing_address_postalcode = "90210";
        $account->email1 = 'abc@sugarcrm.com';
        $account->save();
        $this->accounts[] = $account;

    }

    public function tearDown()
    {
        parent::tearDown();

        $this->_cleanUpRecords();
    }

    public function testExportWithFilter()
    {
        $reply = $this->_restCall('Accounts/export?filter='.urlencode('[{"name":"TEST Account"}]'));
        $this->assertContains($this->accounts[0]->name, $reply['replyRaw'], 'Reply does not contain '.$this->accounts[0]->name);

    }

    public function testExportWithUid()
    {
        $reply = $this->_restCall('Accounts/export?uid='.$this->accounts[0]->id);
        $this->assertContains($this->accounts[0]->name, $reply['replyRaw'], 'Reply does not contain '.$this->accounts[0]->name);
    }

    public function testExportWithoutFilter()
    {
        $reply = $this->_restCall('Accounts/export');
        $this->assertContains($this->accounts[0]->name, $reply['replyRaw'], 'Reply does not contain '.$this->accounts[0]->name);
    }

    public function testExportSample()
    {
        $reply = $this->_restCall('Accounts/export?sample=true$all=true');
        $this->assertContains('This is a sample import file', $reply['replyRaw'], 'Reply does not contain description text');
    }

}