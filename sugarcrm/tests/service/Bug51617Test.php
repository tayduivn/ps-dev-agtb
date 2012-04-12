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

require_once 'tests/service/SOAPTestCase.php';

class Bug51617Test extends SOAPTestCase
{
    protected $_account;

    public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/service/v2/soap.php';

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->status = 'Active';
        $GLOBALS['current_user']->is_admin = 1;
        $GLOBALS['current_user']->save();

        $this->field = get_widget('varchar');
        $this->field->id = 'Accountstest_custom_c';
        $this->field->name = 'test_custom_c';
        $this->field->vanme = 'LBL_TEST_CUSTOM_C';
        $this->field->comments = NULL;
        $this->field->help = NULL;
        $this->field->custom_module = 'Accounts';
        $this->field->type = 'varchar';
        $this->field->label = 'LBL_TEST_CUSTOM_C';
        $this->field->len = 255;
        $this->field->required = 0;
        $this->field->default_value = NULL;
        $this->field->date_modified = '2009-09-14 02:23:23';
        $this->field->deleted = 0;
        $this->field->audited = 0;
        $this->field->massupdate = 0;
        $this->field->duplicate_merge = 0;
        $this->field->reportable = 1;
        $this->field->importable = 'true';
        $this->field->ext1 = NULL;
        $this->field->ext2 = NULL;
        $this->field->ext3 = NULL;
        $this->field->ext4 = NULL;

        $this->df = new DynamicField('Accounts');
        $this->mod = new Account();
        $this->df->setup($this->mod);
        $this->df->addFieldObject($this->field);
        $this->df->buildCache('Accounts');
        VardefManager::clearVardef();
        VardefManager::refreshVardefs('Accounts', 'Account');
        $this->mod->field_defs = $GLOBALS['dictionary']['Account']['fields'];

        $this->_account = SugarTestAccountUtilities::createAccount();

        $this->_account->test_custom_c = 'Custom Field';
        $this->_account->save();

        $GLOBALS['db']->commit(); // Making sure we commit any changes

        parent::setUp();
    }

    public function tearDown()
    {
        $this->df->deleteField($this->field);

        SugarTestAccountUtilities::removeAllCreatedAccounts();

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);

        parent::tearDown();

        global $soap_version_test_accountId, $soap_version_test_opportunityId, $soap_version_test_contactId;
        unset($soap_version_test_accountId);
        unset($soap_version_test_opportunityId);
        unset($soap_version_test_contactId);
    }

    public function testGetEntryListWithCustomField()
    {
        $this->_login();
        $result = $this->_soapClient->call('get_entry_list',
            array(
                 'session'=>$this->_sessionId,
                 "module_name" => 'Accounts',
                 '',
                 '',
                 0,
                 "select_fields" => array('id', 'name', 'test_custom_c'),
                 null,
                 'max_results' => 1
            )
        );

        $this->assertTrue($result['result_count'] > 0,
            'Get_entry_list failed: Fault code: '.$this->_soapClient->faultcode.', fault string:'.$this->_soapClient->faultstring.', fault detail: '.$this->_soapClient->faultdetail);

        $row = array();
        $row = $result['entry_list'][0]['name_value_list'];

        // find the custom field
        foreach($row as $r) {
            // just make sure they are all not empty
            $this->assertNotEmpty($r['value']);
            // make sure that the test field has our value in it
            if($r['name'] == "test_custom_c") {
                $this->assertEquals("Custom Field", $r['value']);
            }
        }

    } // fn
}
