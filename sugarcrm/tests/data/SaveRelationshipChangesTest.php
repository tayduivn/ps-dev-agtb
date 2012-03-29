<?php
/**
 * Created by JetBrains PhpStorm.
 * User: jwhitcraft
 * Date: 3/26/12
 * Time: 10:03 AM
 * To change this template use File | Settings | File Templates.
 */


class SaveRelationshipChangesTest extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);

        $GLOBALS['current_user'] = new User();
        $GLOBALS['current_user']->retrieve('1');
    }

    public function tearDown()
    {
        unset($GLOBALS['current_user']);
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

        $new_rel_id = $macc->handle_preset_relationships($contact->id);

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

        $new_rel_id = $macc->handle_preset_relationships($contact->id);

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
        $rel = $this->createRelationship('Accounts');

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
        $this->removeRelationship($rel_name, 'Accounts');
        unset($macc);
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        // reset the isCacheReset Value since this is all one request.
        SugarCache::$isCacheReset = $_cacheResetValue;
        // if this is set, remove it as it shouldn't be set.
        if(isset($GLOBALS['reload_vardefs'])) {
            unset($GLOBALS['reload_vardefs']);
        }

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

    protected function createRelationship($module)
    {
        require_once 'modules/ModuleBuilder/parsers/relationships/DeployedRelationships.php' ;
        $relationships = new DeployedRelationships ($module);

        $_REQUEST = array(
            'relationship_type' => 'one-to-many',
            'lhs_module' => $module,
            'rhs_module' => 'Accounts',
            'rhs_label' => 'Accounts',
            'rhs_subpanel' => 'default',
            'view_module' => $module,
        );

        $relationship = $relationships->addFromPost();
        $relationships->save();
        $relationships->build();
        LanguageManager::clearLanguageCache($module);

        SugarRelationshipFactory::rebuildCache();

        return $relationship;
    }

    protected function removeRelationship($name, $module)
    {
        require_once 'modules/ModuleBuilder/parsers/relationships/DeployedRelationships.php' ;
        $relationships = new DeployedRelationships($module);

        $relationships->delete($name);

        $relationships->save();
        $relationships->build();
        LanguageManager::clearLanguageCache($module);
        require_once("data/Relationships/RelationshipFactory.php");
        SugarRelationshipFactory::deleteCache();

        SugarRelationshipFactory::rebuildCache();
    }
}

class MockAccountSugarBean extends Account
{
    public function set_relationship_info(array $exclude = array())
    {
        return parent::set_relationship_info($exclude);
    }

    public function handle_preset_relationships($new_rel_id, $exclude = array())
    {
        return parent::handle_preset_relationships($new_rel_id, $exclude);
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
