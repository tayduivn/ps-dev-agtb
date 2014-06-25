<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */


class SaveRelationshipChangesTest extends Sugar_PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        global $reload_vardefs;
        parent::setUp();
        SugarTestHelper::setUp('dictionary');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user', array(true, 1));
        $reload_vardefs = true;
    }

    protected function tearDown()
    {
        global $reload_vardefs;
        SugarTestRelationshipUtilities::removeAllCreatedRelationships();
        SugarTestHelper::tearDown();
        parent::tearDown();
        $reload_vardefs = false;
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

        $sql = "SELECT account_id, contact_id from accounts_contacts where account_id = '" . $macc->id . "' AND contact_id = '" . $contact->id . "' and deleted = 0";
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
        $sql = "INSERT INTO accounts_contacts (id, account_id, contact_id) VALUES ('" . $rel_row_id . "','" . $macc->id . "','" . $contact->id . "')";
        $GLOBALS['db']->query($sql);
        $GLOBALS['db']->commit();

        // set the contact id from the bean.
        $macc->rel_fields_before_value['contact_id'] = $contact->id;

        $new_rel_id = $macc->handle_preset_relationships($contact->id, 'contacts');

        $this->assertEquals($contact->id, $new_rel_id);

        // make sure the relationship exists

        $sql = "SELECT account_id, contact_id from accounts_contacts where account_id = '" . $macc->id . "' AND contact_id = '" . $contact->id . "' and deleted = 0";
        $result = $GLOBALS['db']->query($sql);
        $row = $GLOBALS['db']->fetchByAssoc($result);

        $this->assertFalse($row);

        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();

        unset($macc);

    }

    /**
     * @large
     */
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

        // Getting the name on a self-referencing relationship is hard, we want the right hand side
        // so we have to manually tweak it.
        $rel_name = $rel->getName().'_right';
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
