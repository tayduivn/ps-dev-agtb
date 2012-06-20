<?php
//FILE SUGARCRM flav=pro ONLY
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


require_once 'modules/ModuleBuilder/parsers/relationships/DeployedRelationships.php' ;
require_once 'modules/MySettings/TabController.php';
require_once 'include/SubPanel/SubPanelDefinitions.php';
require_once 'modules/ModuleBuilder/parsers/views/SubpanelMetaDataParser.php';

/**
 * Bug #52361
 * Relate field data is not displayed in subpanel
 *
 * @author mgusev@sugarcrm.com
 * @ticked 52361
 */
class Bug52361Test extends Sugar_PHPUnit_Framework_OutputTestCase
{
    /**
     * @var User
     */
    protected $user = null;

    /**
     * @var DeployedRelationships
     */
    protected $relationships = null;

    /**
     * @var OneToOneRelationship
     */
    protected $relationship = null;

    /**
     * @var TabController
     */
    protected $tabs = null;

    /**
     * @var bool
     */
    protected $isTabsUpdated = false;

    /**
     * @var bool
     */
    protected $isSubPanelUpdated = false;

    public function setUp()
    {
        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');

        parent::setUp();

        // Adding products to visible modules
        $this->tabs = new TabController();
        $tabs = $this->tabs->get_system_tabs();
        if (isset($tabs['Products']) == false)
        {
            $tabs['Products'] = 'Products';
            $this->tabs->set_system_tabs($tabs);
            $this->isTabsUpdated = true;
        }

        // Adding products to visible subpanels
        $subpanels = SubPanelDefinitions::get_hidden_subpanels();
        if (isset($subpanels['products']))
        {
            unset($subpanels['products']);
            SubPanelDefinitions::set_hidden_subpanels($subpanels);
            $this->isSubPanelUpdated = true;
        }

        // Adding relation between products and users
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
        LanguageManager::clearLanguageCache('Products');
        LanguageManager::clearLanguageCache('Users');

        // Updating $dictionary by created relation
        global $dictionary;
        $dictionary = array();
        $moduleInstaller = new ModuleInstaller();
        $moduleInstaller->silent = true;
        $moduleInstaller->rebuild_tabledictionary();
        require 'modules/TableDictionary.php';

        // Updating vardefs of Products and Users
        VardefManager::$linkFields = array();
        VardefManager::clearVardef();
        VardefManager::refreshVardefs('Products', BeanFactory::getObjectName('Products'));
        VardefManager::refreshVardefs('Users', BeanFactory::getObjectName('Users'));

        SugarRelationshipFactory::rebuildCache();

        // Creating local user for relations
        $this->user = SugarTestUserUtilities::createAnonymousUser();
    }

    public function tearDown()
    {
        // Removing relation between products and users
        $this->relationships->delete($this->relationship->getName());
        $this->relationships->save();
        SugarRelationshipFactory::deleteCache();
        LanguageManager::clearLanguageCache('Products');
        LanguageManager::clearLanguageCache('Users');
        global $dictionary;
        $dictionary = array();
        $moduleInstaller = new ModuleInstaller();
        $moduleInstaller->silent = true;
        $moduleInstaller->rebuild_tabledictionary();
        require 'modules/TableDictionary.php';


        // Updating vardefs of Products and Users
        VardefManager::$linkFields = array();
        VardefManager::clearVardef();
        VardefManager::refreshVardefs('Products', BeanFactory::getObjectName('Products'));
        VardefManager::refreshVardefs('Users', BeanFactory::getObjectName('Users'));
        SugarRelationshipFactory::rebuildCache();

        // Hiding products from subpanels
        if ($this->isSubPanelUpdated == true)
        {
            $subpanels = SubPanelDefinitions::get_hidden_subpanels();
            $subpanels['products'] = 'products';
            SubPanelDefinitions::set_hidden_subpanels($subpanels);
            $this->isSubPanelUpdated = false;
        }

        // Hiding products from modules
        if ($this->isTabsUpdated == true)
        {
            $tabs = $this->tabs->get_system_tabs();
            unset($tabs[array_search('Products', $tabs)]);
            $this->tabs->set_system_tabs($tabs);
            $this->isTabsUpdated = false;
        }

        // Restoring $GLOBALS
        parent::tearDown();
        $_REQUEST = array();
        unset($_SERVER['REQUEST_METHOD']);

        // Removing temp data
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestProductUtilities::removeAllCreatedProducts();
        SugarTestHelper::tearDown();
    }

    /**
     * Test creates relation between product account and user
     * and tries to assert that user name is present in product subpanel in accounts
     *
     * @group 52361
     * @return void
     */
    public function testAccounts()
    {
        // Adding username field to subpanel of products
        $studio = new SubpanelMetaDataParser('products', 'Accounts', '');
        foreach ($studio->getFieldDefs() as $name => $def)
        {
            if (isset($def['type']) == false || $def['type'] != 'relate')
            {
                continue;
            }
            if ($def['link'] != $this->relationship->getName())
            {
                continue;
            }
            $studio->_viewdefs[$name] = $def;
            break;
        }
        $studio->handleSave(false);

        // Creating beans and relations
        $field = $this->relationship->getName();
        $account = SugarTestAccountUtilities::createAccount();
        $product = SugarTestProductUtilities::createProduct();
        $product->load_relationship($field);
        $product->{$field}->add($this->user);
        $product->account_id = $account->id;
        $product->account_name = $account->name;
        $product->save();

        // Getting data of subpanel
        $_REQUEST['module'] = 'Accounts';
        $_REQUEST['action'] = 'DetailView';
        $_REQUEST['record'] = $account->id;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        unset($GLOBALS['focus']);
        $subpanels = new SubPanelDefinitions($account, 'Accounts');
        $subpanelDef = $subpanels->load_subpanel('products');
        $subpanel = new SubPanel('Accounts', $account->id, 'products', $subpanelDef, 'Accounts');
        $subpanel->setTemplateFile('include/SubPanel/SubPanelDynamic.html');
        $subpanel->display();
        $actual = $this->getActualOutput();

        unset($studio->_viewdefs[$name]);
        $studio->handleSave(false);

        $this->assertContains($this->user->name, $actual, 'User name is not displayed in subpanel');
    }

    /**
     * Test creates relation between product contact and user
     * and tries to assert that user name is present in product subpanel in contacts
     *
     * @group 52361
     * @return void
     */
    public function testContacts()
    {
        // Adding username field to subpanel of products
        $studio = new SubpanelMetaDataParser('products', 'Contacts', '');
        foreach ($studio->getFieldDefs() as $name => $def)
        {
            if (isset($def['type']) == false || $def['type'] != 'relate')
            {
                continue;
            }
            if ($def['link'] != $this->relationship->getName())
            {
                continue;
            }
            $studio->_viewdefs[$name] = $def;
            break;
        }
        $studio->handleSave(false);

        // Creating beans and relations
        $field = $this->relationship->getName();
        $contact = SugarTestContactUtilities::createContact();
        $product = SugarTestProductUtilities::createProduct();
        $product->load_relationship($field);
        $product->{$field}->add($this->user);
        $product->contact_id = $contact->id;
        $product->contact_name = $contact->name;
        $product->save();

        // Getting data of subpanel
        $_REQUEST['module'] = 'Contacts';
        $_REQUEST['action'] = 'DetailView';
        $_REQUEST['record'] = $contact->id;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        unset($GLOBALS['focus']);
        $subpanels = new SubPanelDefinitions($contact, 'Contacts');
        $subpanelDef = $subpanels->load_subpanel('products');
        $subpanel = new SubPanel('Contacts', $contact->id, 'products', $subpanelDef, 'Contacts');
        $subpanel->setTemplateFile('include/SubPanel/SubPanelDynamic.html');
        $subpanel->display();
        $actual = $this->getActualOutput();

        unset($studio->_viewdefs[$name]);
        $studio->handleSave(false);

        $this->assertContains($this->user->name, $actual, 'User name is not displayed in subpanel');
    }
}
