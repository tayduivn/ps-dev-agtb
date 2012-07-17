<?php
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
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;

        global $current_user, $app_strings, $mod_strings, $app_list_strings;
        $current_user = SugarTestUserUtilities::createAnonymousUser(true, 1);
        $app_strings = return_application_language($GLOBALS['current_language']);
        $mod_strings = return_module_language($GLOBALS['current_language'], 'ProductTemplates');
        $app_list_strings = return_app_list_strings_language($GLOBALS['current_language']);

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
        LanguageManager::clearLanguageCache($this->lhs_module);
        LanguageManager::clearLanguageCache($this->rhs_module);

        //Updating $dictionary
        global $dictionary;
        $moduleInstaller = new ModuleInstaller();
        $moduleInstaller->silent = true;
        $moduleInstaller->rebuild_tabledictionary();
        require 'modules/TableDictionary.php';

        //Updating vardefs
        VardefManager::$linkFields = array();
        VardefManager::clearVardef();
        VardefManager::refreshVardefs($this->lhs_module, BeanFactory::getObjectName($this->lhs_module));
        VardefManager::refreshVardefs($this->rhs_module, BeanFactory::getObjectName($this->rhs_module));
        SugarRelationshipFactory::rebuildCache();
    }

    public function tearDown()
    {
        //Removing created relationship
        $this->relationships = new DeployedRelationships($this->lhs_module);
        $this->relationships->delete($this->relationship->getName());
        $this->relationships->save();
        SugarRelationshipFactory::deleteCache();
        LanguageManager::clearLanguageCache($this->lhs_module);
        LanguageManager::clearLanguageCache($this->rhs_module);

        //Updating $dictionary
        global $dictionary;
        $dictionary = array();
        $moduleInstaller = new ModuleInstaller();
        $moduleInstaller->silent = true;
        $moduleInstaller->rebuild_tabledictionary();
        require 'modules/TableDictionary.php';

        //Updating vardefs
        VardefManager::$linkFields = array();
        VardefManager::clearVardef();
        VardefManager::refreshVardefs($this->lhs_module, BeanFactory::getObjectName($this->lhs_module));
        VardefManager::refreshVardefs($this->rhs_module, BeanFactory::getObjectName($this->rhs_module));
        SugarRelationshipFactory::rebuildCache();

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);

        unset($GLOBALS['beanFiles'], $GLOBALS['beanList']);
        unset($GLOBALS['app_strings'], $GLOBALS['app_list_strings'], $GLOBALS['mod_strings']);

        unset($this->account);
        unset($this->pt);
    }

    public function testRelationOneToOne()
    {
        //Creating new Account
        $this->account = new Account;
        $this->account->name = "Bug53944Account".time();
        $this->account->save();

        $_REQUEST['relate_to']=$this->rhs_module;
        $rel_name = $this->relationship->getName();
        $ida = $rel_name.'accounts_ida';
        $name = $rel_name.'_name';

        //Creating new ProductTemplate
        $this->pt = new ProductTemplate();
        $this->pt->name = "Bug53944ProductTemplates".time();
        $this->pt->$ida = $this->account->id;
        $this->pt->$name = $this->account->name;
        $this->pt->save();

        $query = "SELECT ". $this->relationship->joinKeyLHS." FROM ".$rel_name."_c WHERE ".$rel_name."_c.".$this->relationship->joinKeyRHS."='".$this->pt->id."'";
        $result = $GLOBALS['db']->query($query);
        $row = $GLOBALS['db']->fetchRow($result);

        $this->assertNotNull($row[$ida],'Table account_producttemplates_c was not filled');
    }
}
