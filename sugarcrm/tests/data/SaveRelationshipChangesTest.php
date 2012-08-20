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


class SaveRelationshipChangesTest extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        $GLOBALS['current_user'] = BeanFactory::getBean("Users", 1);
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    public function setRelationshipInfoDataProvider()
    {
        return array(
            array(
                1,
                'accounts_contacts',
                array(1, 'contacts'),
            ),
            array(
                1,
                'member_accounts',
                array(1, 'member_of'),
            ),
            array(
                1,
                'accounts_opportunities',
                array(1, 'opportunities'),
            ),
        );
    }


    /**
     * @dataProvider setRelationshipInfoDataProvider
     */
    public function testSetRelationshipInfoViaRequestVars($id, $rel, $expected)
    {
        $bean = new MockAccountSugarBean();

        $_REQUEST['relate_to'] = $rel;
        $_REQUEST['relate_id'] = $id;

        $return = $bean->set_relationship_info();

        $this->assertSame($expected, $return);
    }

    /**
     * @dataProvider setRelationshipInfoDataProvider
     */
    public function testSetRelationshipInfoViaBeanProperties($id, $rel, $expected)
    {
        $bean = new MockAccountSugarBean();

        $bean->not_use_rel_in_req = true;
        $bean->new_rel_id = $id;
        $bean->new_rel_relname = $rel;

        $return = $bean->set_relationship_info();

        $this->assertSame($expected, $return);
    }

    public function testHandlePresetRelationshipsAdd()
    {
        $acc = SugarTestAccountUtilities::createAccount();

        $macc = new MockAccountSugarBean();
        $macc->disable_row_level_security = true;
        $macc->retrieve($acc->id);

        // create an contact
        $contact = SugarTestContactUtilities::createContact();

        // set the contact id from the bean.
        $macc->contact_id = $contact->id;

        $new_rel_id = $macc->handle_preset_relationships($contact->id, 'contacts');

        $this->assertFalse($new_rel_id);

        // make sure the relationship exists

        $sql = "SELECT account_id, contact_id from accounts_contacts where account_id = '" . $macc->id . "' AND contact_id = '" . $contact->id . "' and deleted = 0;";
        $result = $GLOBALS['db']->query($sql);
        $row = $GLOBALS['db']->fetchByAssoc($result);

        $this->assertSame(array('account_id' => $macc->id, 'contact_id' => $contact->id), $row);

        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();

        unset($macc);

    }

    public function testHandlePresetRelationshipsDelete()
    {
        $acc = SugarTestAccountUtilities::createAccount();

        $macc = new MockAccountSugarBean();
        $macc->disable_row_level_security = true;
        $macc->retrieve($acc->id);

        // create an contact
        $contact = SugarTestContactUtilities::createContact();


        // insert a dummy row
        $rel_row_id = create_guid();
        $sql = "INSERT INTO accounts_contacts (id, account_id, contact_id) VALUES ('" . $rel_row_id . "','" . $macc->id . "','" . $contact->id . "');";
        $GLOBALS['db']->query($sql);
        $GLOBALS['db']->commit();

        // set the contact id from the bean.
        $macc->rel_fields_before_value['contact_id'] = $contact->id;

        $new_rel_id = $macc->handle_preset_relationships($contact->id, 'contacts');

        $this->assertEquals($contact->id, $new_rel_id);

        // make sure the relationship exists

        $sql = "SELECT account_id, contact_id from accounts_contacts where account_id = '" . $macc->id . "' AND contact_id = '" . $contact->id . "' and deleted = 0;";
        $result = $GLOBALS['db']->query($sql);
        $row = $GLOBALS['db']->fetchByAssoc($result);

        $this->assertFalse($row);

        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();

        unset($macc);

    }

    public function testHandleRemainingRelateFields()
    {
        // create a test relationship
        // save cache reset value
        $_cacheResetValue = SugarCache::$isCacheReset;
        //$rel = $this->createRelationship('Accounts');

        $rel = SugarTestRelationshipUtilities::createRelationship(array(
                    'relationship_type' => 'one-to-many',
                    'lhs_module' => 'Accounts',
                    'rhs_module' => 'Accounts',
                ));

        if($rel == false) {
            $this->fail('Relationship Not Created');
        }

        $rel_name = $rel->getName();
        $id = $rel->getIDName('Accounts');

        $acc1 = SugarTestAccountUtilities::createAccount();
        $acc2 = SugarTestAccountUtilities::createAccount();

        $macc = new MockAccountSugarBean();
        $macc->disable_row_level_security = true;
        $macc->retrieve($acc2->id);

        $macc->$id = $acc1->id;

        $ret = $macc->handle_remaining_relate_fields();
        $this->assertContains($rel_name, $ret['add']['success']);

        $macc->rel_fields_before_value[$id] = $acc1->id;
        $macc->$id = '';
        $ret = $macc->handle_remaining_relate_fields();

        $this->assertContains($rel_name, $ret['remove']['success']);

        // variable cleanup
        // delete the test relationship
        //$this->removeRelationship($rel_name, 'Accounts');
        SugarTestRelationshipUtilities::removeAllCreatedRelationships();

        unset($macc);
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        // reset the isCacheReset Value since this is all one request.
        SugarCache::$isCacheReset = $_cacheResetValue;
    }

    public function handleRequestRelateProvider()
    {
        return array(
            array('member_of', true),
            array('MEMBER_OF', true),
            array(time(), false),
        );
    }

    /**
     *
     * @dataProvider handleRequestRelateProvider
     * @param $rel_link_name
     */
    public function testHandleRequestRelate($rel_link_name, $expected)
    {
        $acc1 = SugarTestAccountUtilities::createAccount();
        $acc2 = SugarTestAccountUtilities::createAccount();

        $macc = new MockAccountSugarBean();
        $macc->retrieve($acc2->id);


        $ret = $macc->handle_request_relate($acc1->id, $rel_link_name);

        $this->assertSame($expected, $ret);

        unset($macc);
        SugarTestAccountUtilities::removeAllCreatedAccounts();

    }
}

class MockAccountSugarBean extends Account
{
    public function set_relationship_info(array $exclude = array())
    {
        return parent::set_relationship_info($exclude);
    }

    public function handle_preset_relationships($new_rel_id, $new_rel_name, $exclude = array())
    {
        return parent::handle_preset_relationships($new_rel_id, $new_rel_name, $exclude);
    }

    public function handle_remaining_relate_fields($exclude = array())
    {
        return parent::handle_remaining_relate_fields($exclude);
    }

    public function handle_request_relate($new_rel_id, $new_rel_link)
    {
        return parent::handle_request_relate($new_rel_id, $new_rel_link);
    }
}
