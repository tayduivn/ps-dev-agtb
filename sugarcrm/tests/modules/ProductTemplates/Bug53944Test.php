<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License. Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party. Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited. You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution. See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License. Please refer to the License for the specific language
 * governing these rights and limitations under the License. Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * Bug #53944
 *
 * Product Catalog | One-to-One Relationship with Accounts to Product Catalog does not work properly
 * @ticket 53944
 * @author imatsiushyna@sugarcrm.com
 */

class Bug53944Test extends Sugar_PHPUnit_Framework_OutputTestCase
{
    /**
     * @var string
     */
    var $lhs_module=null;

    /**
     * @var string
     */
    var $rhs_module=null;

    /**
     * @var DeployedRelationships
     */
    protected $relationships = null;

    /**
     * @var OneToManyRelationship
     */
    protected $relationship = null;

    /**
     * @var Account
     */
    private $account;

    /**
     * @var ProductTemplate
     */
    private $pt;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', array('ProductTemplates'));

        //Adding relationship between module Accounts and new module
        $this->lhs_module='Accounts';
        $this->rhs_module='ProductTemplates';

        $this->relationships = new DeployedRelationships($this->lhs_module);
        $definition = array(
            'lhs_module' => $this->lhs_module,
            'lhs_label'=> $this->lhs_module,
            'relationship_type' => 'one-to-one',
            'rhs_module' => $this->rhs_module,
            'rhs_label' => $this->rhs_module,
            'rhs_subpanel' => 'default',
        );
        $this->relationship = RelationshipFactory::newRelationship($definition);
        $this->relationships->add($this->relationship);
        $this->relationships->save();
        $this->relationships->build();
        SugarTestHelper::setUp('relation', array(
            $this->lhs_module,
            $this->rhs_module
        ));
    }

    public function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        if ($this->pt)
        {
            $this->pt->mark_deleted($this->pt->id);
        }

        //Removing created relationship
        $this->relationships = new DeployedRelationships($this->lhs_module);
        $this->relationships->delete($this->relationship->getName());
        $this->relationships->save();

        SugarTestHelper::tearDown();
    }

    public function testRelationOneToOne()
    {
        //Creating new Account
        $this->account = SugarTestAccountUtilities::createAccount();

        $_REQUEST['relate_to'] = $this->rhs_module;

        //Creating new ProductTemplate
        $this->pt = new ProductTemplate();
        $this->pt->name = "Bug53944ProductTemplates" . time();
        $rel_name = $this->relationship->getName();
        $ida = $this->pt->field_defs[$rel_name]['id_name'];
        $this->pt->$ida = $this->account->id;
        $this->pt->save();

        $this->pt->load_relationship($rel_name);
        $actual = $this->pt->$rel_name->getBeans();

        $this->assertArrayHasKey($this->account->id, $actual, 'Relationship was not created');
    }
}
