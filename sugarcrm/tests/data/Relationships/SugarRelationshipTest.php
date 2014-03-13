<?php
/*
* By installing or using this file, you are confirming on behalf of the entity
* subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
* the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
* http://www.sugarcrm.com/master-subscription-agreement
*
* If Company is not bound by the MSA, then by installing or using this file
* you are agreeing unconditionally that Company will be bound by the MSA and
* certifying that you have authority to bind Company accordingly.
*
* Copyright (C) 2004-2013 SugarCRM Inc. All rights reserved.
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
