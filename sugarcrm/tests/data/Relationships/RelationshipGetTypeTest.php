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

/**
 * Testing relationships getType
 */
class RelationshipGetTypeTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var DeployedRelationships
     */
    protected $relationships = null;

    /**
     * @var SugarRelationship
     */
    protected $relationship = null;

    protected function setUp()
    {
        global $reload_vardefs;

        parent::setUp();
        SugarTestHelper::setUp('current_user', array(true, true));
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');

        $reload_vardefs = true;
    }

    protected function tearDown()
    {
        global $reload_vardefs;
        $this->relationships->delete($this->relationship->getName());
        $this->relationships->save();
        SugarTestHelper::tearDown();
        parent::tearDown();
        $reload_vardefs = false;
    }

    /**
     * @param mixed $def additional definition of a relationship
     * @param mixed $result additional parameters and results of a test
     * @dataProvider defsProvider
     */
    public function testGetType($def, $result)
    {
        $this->relationships = new DeployedRelationships('Accounts');
        $definition = array_merge(
            array(
                'lhs_module' => 'Accounts',
                'rhs_module' => 'Contacts',
            ),
            $def
        );
        $this->relationship = RelationshipFactory::newRelationship($definition);
        $this->relationships->add($this->relationship);
        $this->relationships->save();
        $this->relationships->build();
        SugarTestHelper::setUp('relation', array('Accounts', 'Contacts'));
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship($this->relationship->getName());
        foreach ($result as $side => $type) {
            $this->assertEquals($type, $relationship->getType($side));
        }
    }

    public function defsProvider()
    {
        return array(
            array(
                array(
                    'relationship_type' => REL_MANY_MANY,
                    'true_relationship_type' => REL_ONE_MANY,
                ),
                array(
                    REL_LHS => REL_TYPE_MANY,
                    REL_RHS => REL_TYPE_ONE,
                ),
            ),
            array(
                array(
                    'relationship_type' => REL_ONE_MANY,
                    'true_relationship_type' => '',
                ),
                array(
                    REL_LHS => REL_TYPE_MANY,
                    REL_RHS => REL_TYPE_ONE,
                ),
            ),
            array(
                array(
                    'relationship_type' => REL_ONE_ONE,
                    'true_relationship_type' => REL_ONE_ONE,
                ),
                array(
                    REL_LHS => REL_TYPE_ONE,
                    REL_RHS => REL_TYPE_ONE,
                ),
            ),
            array(
                array(
                    'relationship_type' => REL_MANY_MANY,
                    'true_relationship_type' => REL_MANY_MANY,
                ),
                array(
                    REL_LHS => REL_TYPE_MANY,
                    REL_RHS => REL_TYPE_MANY,
                ),
            ),
        );
    }
}
