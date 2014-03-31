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

/**
 * @ticket 64655
 */
class Bug64655Test extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var SugarBean */
    private $bean;

    protected function setUp()
    {
        parent::setUp();

        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');

        $this->bean = new Bug64655Test_SugarBean1();
    }

    protected function tearDown()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testPopulateFromRow()
    {
        /** @var User */
        global $current_user;
        $current_user->setPreference('default_locale_name_format', 'f l');

        $this->bean->populateFromRow(
            array(
                'rel_contact_name_first_name' => 'John',
                'rel_contact_name_last_name' => 'Doe',
            )
        );

        $this->assertEquals('John Doe', $this->bean->contact_name);
    }

    public function testFillInRelationshipFields()
    {
        /** @var User */
        global $current_user;
        $current_user->setPreference('default_locale_name_format', 'l, f');

        $contact = SugarTestContactUtilities::createContact();
        $contact->first_name = 'John';
        $contact->last_name = 'Doe';
        $contact->save();
        $this->bean->contact_id = $contact->id;

        $this->bean->fill_in_relationship_fields();

        $this->assertEquals('Doe, John', $this->bean->contact_name);
    }

    /**
     * @param array $rel_field_defs
     * @param string $alias
     * @param string $expected
     *
     * @dataProvider provider
     */
    public function testGetRelateFieldQuery(array $rel_field_defs, $alias, $expectedSelect, $expectedFields)
    {
        /** @var User */
        global $current_user;
        $current_user->setPreference('default_locale_name_format', 'f b');

        $bean = new Bug64655Test_SugarBean2();
        $bean->field_defs = $rel_field_defs;
        $query = $bean->getRelateFieldQuery($this->bean->field_defs['contact_name'], $alias);
        $this->assertEquals($expectedSelect, $query['select']);
        $this->assertEquals($expectedFields, $query['fields']);
    }

    public function testCustomFieldsInFormat()
    {
        /** @var User */
        global $current_user;
        $current_user->setPreference('default_locale_name_format', 'f b');

        $bean = new Bug64655Test_SugarBean3();
        $query = $bean->getRelateFieldQuery($this->bean->field_defs['contact_name'], 'jt');

        $this->assertContains('jt.foo rel_contact_name_foo', $query['select']);
        $this->assertContains('jt_cstm.bar rel_contact_name_bar', $query['select']);
        $this->assertContains('LEFT JOIN bug64655test2_cstm jt_cstm ON jt_cstm.id_c = jt.id', $query['join']);
    }

    public static function provider()
    {
        return array(
            'empty-vardefs' => array(
                array(), 'jt1', '', array()
            ),
            'non-name-field' => array(
                array(
                    'name' => array(
                        'name' => 'name',
                        'type' => 'varchar',
                    ),
                ),
                'jt2',
                'jt2.name contact_name',
                array(
                    'contact_name' => 'jt2.name',
                ),
            ),
            'name-field' => array(
                array(
                    'name' => array(
                        'name' => 'name',
                        'type' => 'fullname',
                    ),
                ),
                'jt3',
                'jt3.foo rel_contact_name_foo, jt3.bar rel_contact_name_bar',
                array(
                    'rel_contact_name_foo' => 'jt3.foo',
                    'rel_contact_name_bar' => 'jt3.bar',
                ),
            ),
        );
    }
}

class Bug64655Test_SugarBean1 extends SugarBean
{
    public $object_name = 'Bug64655Test1';
    public $field_defs = array(
        'contact_name' => array(
            'name' => 'contact_name',
            'rname' => 'name',
            'type' => 'relate',
            'module' => 'Contacts',
            'id_name' => 'contact_id',
        ),
    );
}

class Bug64655Test_SugarBean2 extends SugarBean
{
    public $object_name = 'Bug64655Test2';
    public $name_format_map = array(
        'f' => 'foo',
        'b' => 'bar',
    );
}

class Bug64655Test_SugarBean3 extends SugarBean
{
    public $object_name = 'Bug64655Test3';
    public $name_format_map = array(
        'f' => 'foo',
        'b' => 'bar',
    );
    public $table_name = 'bug64655test2';
    public $field_defs = array(
        'name' => array(
            'name' => 'name',
            'type' => 'fullname',
        ),
        'foo' => array(
            'name' => 'foo',
        ),
        'bar' => array(
            'name' => 'bar',
            'custom_module' => 'Bug64655Test2',
        ),
    );
}
