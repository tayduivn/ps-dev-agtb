<?php
<<<<<<< HEAD
=======
//FILE SUGARCRM flav=pro ONLY
>>>>>>> 6_5_6
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

require_once 'data/SugarBean.php';
require_once 'modules/Expressions/Expression.php';
require_once 'modules/ModuleBuilder/parsers/relationships/DeployedRelationships.php' ;

class Bug53218Test extends Sugar_PHPUnit_Framework_OutputTestCase
{

    /**
     * @var DeployedRelationships
     */
    protected $relationships = null;

    /**
     * @var OneToOneRelationship
     */
    protected $relationship = null;

    public function setUp()
    {
        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        parent::setUp();
        $this->relationships = new DeployedRelationships('Products');
        $definition = array(
            'lhs_module' => 'Products',
            'relationship_type' => 'one-to-one',
            'rhs_module' => 'Users'
        );
        $this->relationship = RelationshipFactory::newRelationship($definition);
        $this->relationships->add($this->relationship);
        $this->relationships->save();
        $this->relationships->build();
        SugarTestHelper::setUp('relation', array(
            'Products',
            'Users'
        ));
    }

    public function tearDown()
    {
        $this->relationships->delete($this->relationship->getName());
        $this->relationships->save();
        parent::tearDown();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestProductUtilities::removeAllCreatedProducts();
        SugarTestHelper::tearDown();
    }

    /**
     * @group 53218
     */
    public function testWorkFlowConditionModules()
    {
        $_GET['opener_id']= 'rel_module';
        $expression = new Expression();
        $relations = $expression->get_selector_array('field', '', 'Products');
        $this->assertContains('Users (products_users_1)', $relations);
        $this->assertContains('products_users_1', $relations);
    }
	
    /**
     * @group 53218
     */
    public function testDuplicateRelations()
    {
        $temp_module = SugarModule::get('Products')->loadBean();
        $temp_module->call_vardef_handler();
        $temp_select_array = $temp_module->vardef_handler->get_vardef_array(false, false, true, false);
        $field_defs = $temp_module->vardef_handler->module_object->field_defs;
        unset($field_defs['products_users_1_name']['vname']);
        $temp_select_array = getDuplicateRelationListWithTitle($temp_select_array, $field_defs, $temp_module->vardef_handler->module_object->module_dir);
        $this->assertEquals('Users (products_users_1_name)', $temp_select_array['products_users_1_name']);
    }

}
