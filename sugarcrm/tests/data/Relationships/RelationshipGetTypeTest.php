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

require_once 'data/Relationships/SugarRelationship.php';
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
