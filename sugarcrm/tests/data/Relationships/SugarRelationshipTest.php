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

class SugarRelationshipTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $hooks;

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        LogicHook::refreshHooks();
        $this->hooks = array(
            array('Opportunities', 'after_relationship_update', Array(1, 'Opportunities::after_relationship_update', __FILE__, 'SugarRelationshipTestHook', 'testFunction')),
            array('Contacts', 'after_relationship_update', Array(1, 'Contacts::after_relationship_update', __FILE__, 'SugarRelationshipTestHook', 'testFunction'))
        );
        foreach ($this->hooks as $hook) {
            call_user_func_array('check_logic_hook_file', $hook);
        }
    }

    public function tearDown()
    {
        foreach ($this->hooks as $hook) {
            call_user_func_array('remove_logic_hook', $hook);
        }
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
    }
    
    public function testCallAfterUpdate()
    {
        $opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $contact = SugarTestContactUtilities::createContact();
        $opportunity->load_relationship('contacts');
        $opportunity->contacts->add($contact->id);
        // clear log
        SugarRelationshipTestHook::$log = array();
        // adding existing relationship should call 'after_relationship_update' hook
        $opportunity->contacts->add($contact->id);
        $this->assertEquals($contact->id, SugarRelationshipTestHook::$log[$opportunity->id]['after_relationship_update'], "Logic hook not triggered for Opportunities:after_relationship_update:Contacts");
        $this->assertEquals($opportunity->id, SugarRelationshipTestHook::$log[$contact->id]['after_relationship_update'], "Logic hook not triggered for Contacts:after_relationship_update:Opportunities");
    }
}
 
class SugarRelationshipTestHook
{
    static public $log = array();

    public function testFunction($bean, $event, $arguments)
    {
        self::$log[$bean->id][$event] = $arguments['related_id'];
    }
}
