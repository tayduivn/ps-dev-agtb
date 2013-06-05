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

require_once 'vendor/nusoap//nusoap.php';
require_once 'modules/DynamicFields/FieldCases.php';

/**
 * Bug #58138
 * Web Service get_relationships doesn't work with related_module_query parameter when using custom fields
 *
 * @author mgusev@sugarcrm.com
 * @ticked 58138
 */
class Bug58138Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var nusoapclient
     */
    protected $soap = null;

    /**
     * @var DynamicField
     */
    protected $dynamicField = null;

    /**
     * @var TemplateText
     */
    protected $field = null;

    /**
     * @var Contact
     */
    protected $module = null;

    /**
     * @var Account
     */
    protected $account = null;

    /**
     * @var Contact
     */
    protected $contact = null;

    /**
     * Creating new field, account, contact with filled custom field, relationship between them
     */
    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', array(true, true));

        $this->field = get_widget('varchar');
        $this->field->id = 'Contactstest_c';
        $this->field->name = 'test_c';
        $this->field->type = 'varchar';
        $this->field->len = 255;
        $this->field->importable = 'true';

        $this->field->label = '';

        $this->module = new Contact();

        $this->dynamicField = new DynamicField('Contacts');

        $this->dynamicField->setup($this->module);
        $this->dynamicField->addFieldObject($this->field);

        SugarTestHelper::setUp('dictionary');
        $GLOBALS['reload_vardefs'] = true;

        $this->account = SugarTestAccountUtilities::createAccount();

        $this->contact = SugarTestContactUtilities::createContact();
        $this->contact->account_id = $this->account->id;
        $this->contact->test_c = 'test value';
        $this->contact->load_relationship('accounts');
        $this->contact->accounts->add($this->account->id);
        $this->contact->save();

        $GLOBALS['db']->commit();
    }

    /**
     * Removing field, account, contact
     */
    public function tearDown()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();

        $this->dynamicField->deleteField($this->field);

        SugarTestHelper::tearDown();
    }

    /**
     * Test asserts that contact can be found by custom field
     *
     * @group 58138
     */
    public function testSoap()
    {
        $soap_url = $GLOBALS['sugar_config']['site_url'] . '/soap.php';
        $this->soap = new nusoapclient($soap_url);

        $result = $this->soap->call('login', array(
                'user_auth' => array(
                    'user_name' => $GLOBALS['current_user']->user_name,
                    'password' => $GLOBALS['current_user']->user_hash,
                    'version' => '.01'
                ),
                'application_name' => 'SoapTest'
            )
        );

        $actual = $this->soap->call('get_relationships', array(
            'session' => $result['id'],
            'module_name' => 'Accounts',
            'module_id' => $this->account->id,
            'link_field_name' => 'Contacts',
            'related_module_query' => "contacts_cstm.test_c = 'test value' ",
            'deleted' => '1',
        ));

        $this->assertInternalType('array', $actual, 'Incorrect response');

        if (empty($actual['ids']))
        {
            $this->fail('Data is not present');
        }

        $actual = reset($actual['ids']);
        $this->assertEquals($this->contact->id, $actual['id'], 'Contact is incorrect');
    }

    public static function dataProvider()
    {
        return array(
            array('/service/v2/soap.php'),
            array('/service/v2_1/soap.php'),
            array('/service/v3/soap.php'),
            array('/service/v3_1/soap.php'),
            array('/service/v4/soap.php'),
            array('/service/v4_1/soap.php')
        );
    }

    /**
     * Test asserts that contact can be found by custom field
     *
     * @group 58138
     * @dataProvider dataProvider
     */
    public function testSoapVersions($url)
    {
        $soap_url = $GLOBALS['sugar_config']['site_url'] . $url;
        $this->soap = new nusoapclient($soap_url);

        $result = $this->soap->call('login', array(
            'user_auth' => array(
                'user_name' => $GLOBALS['current_user']->user_name,
                'password' => $GLOBALS['current_user']->user_hash,
                'version' => '.01'
            ),
            'application_name' => 'SoapTest'
            )
        );

        $actual = $this->soap->call('get_relationships', array(
            'session' => $result['id'],
            'module_name' => 'Accounts',
            'module_id' => $this->account->id,
            'link_field_name' => 'contacts',
            'related_module_query' => "contacts_cstm.test_c = 'test value' ",
            'link_module_fields' => array('id'),
            'deleted' => '1',
        ));

        $this->assertInternalType('array', $actual, 'Incorrect response');

        if (empty($actual['entry_list']))
        {
            $this->fail('Data is not present');
        }

        $actual = reset($actual['entry_list']);
        $this->assertEquals($this->contact->id, $actual['id'], 'Contact is incorrect');
    }
}
