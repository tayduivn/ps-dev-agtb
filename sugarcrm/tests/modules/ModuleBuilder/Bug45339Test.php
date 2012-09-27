<?php

/* * *******************************************************************************
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
 * ****************************************************************************** */

/**
 * Bug #45339
 * Export Customizations Does Not Cleanly Handle Relationships.
 *
 * @ticket 45339
 */
class Bug45339Test extends Sugar_PHPUnit_Framework_TestCase
{

    private $relationAccountContact = null;
    private $relationContactAccount = null;
    private $mbPackage = null;
    private $keys = array(
        'module' => "ModuleBuilder",
        'action' => "SaveRelationship",
        'remove_tables' => "true",
        'view_module' => "",
        'relationship_lang' => "en_us",
        'relationship_name' => "",
        'lhs_module' => "",
        'relationship_type' => "many-to-many",
        'rhs_module' => "",
        'lhs_label' => "",
        'rhs_label' => "",
        'lhs_subpanel' => "default",
        'rhs_subpanel' => "default",
    );
    private $packName = 'test_package';

    public function setUp()
    {
        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('app_strings');

        $_REQUEST = $this->keys;

        $_REQUEST['view_module'] = "Accounts";
        $_REQUEST['lhs_module'] = "Accounts";
        $_REQUEST['rhs_module'] = "Contacts";
        $_REQUEST['lhs_label'] = "Accounts";
        $_REQUEST['rhs_label'] = "Contacts";

        $relationAccountContact = new DeployedRelationships($_REQUEST['view_module']);
        $this->relationAccountContact = $relationAccountContact->addFromPost();
        $relationAccountContact->save();
        $relationAccountContact->build();
        LanguageManager::clearLanguageCache($_REQUEST['view_module']);

        $_REQUEST['view_module'] = "Contacts";
        $_REQUEST['lhs_module'] = "Contacts";
        $_REQUEST['rhs_module'] = "Accounts";
        $_REQUEST['lhs_label'] = "Contacts";
        $_REQUEST['rhs_label'] = "Accounts";

        $relationContactAccount = new DeployedRelationships($_REQUEST['view_module']);
        $this->relationContactAccount = $relationContactAccount->addFromPost();
        $relationContactAccount->save();
        $relationContactAccount->build();
        LanguageManager::clearLanguageCache($_REQUEST['view_module']);

        $this->mbPackage = new Bug45339MBPackageMock($this->packName);
    }

    public function tearDown()
    {
        $relationshipAccountContact = new DeployedRelationships($this->relationAccountContact->getLhsModule());
        $relationshipAccountContact->delete($this->relationAccountContact->getName());
        $relationshipAccountContact->save();

        $relationshipContactAccount = new DeployedRelationships($this->relationContactAccount->getLhsModule());
        $relationshipContactAccount->delete($this->relationContactAccount->getName());
        $relationshipContactAccount->save();

        SugarRelationshipFactory::deleteCache();

        unset($_REQUEST);

        SugarTestHelper::tearDown();
    }

    /**
     * @group 45339
     */
    public function testGetCustomRelationshipsByModuleName()
    {
        /* @var $this->mbPackage MBPackage */
        $accountsAllCustomRelationships = $this->mbPackage->getCustomRelationshipsByModuleName('Accounts');
        // Created in the Account module.
        $accountsLhsCustomRelationships = $this->mbPackage->getCustomRelationshipsByModuleName('Accounts', true);
        $wrongModuleName = $this->mbPackage->getCustomRelationshipsByModuleName('Wrong_module_name');
        
        $this->assertArrayHasKey($this->relationAccountContact->getName(), $accountsAllCustomRelationships);
        $this->assertArrayHasKey($this->relationContactAccount->getName(), $accountsAllCustomRelationships);

        $this->assertArrayHasKey($this->relationAccountContact->getName(), $accountsLhsCustomRelationships);
        $this->assertArrayNotHasKey($this->relationContactAccount->getName(), $accountsLhsCustomRelationships);
        
        $this->assertFalse($wrongModuleName); // check
    }

    /**
     * @group 45339
     */
    public function testGetCustomRelationshipsMetaFilesByModuleName()
    {
        $accountContactMetaPath = 'custom/metadata/' . $this->relationAccountContact->getName() . 'MetaData.php';
        $accountContactTablePath = 'custom/Extension/application/Ext/TableDictionary/' . $this->relationAccountContact->getName() . '.php';
        $contactAccountMetaPath = 'custom/metadata/' . $this->relationContactAccount->getName() . 'MetaData.php';

        /* @var $this->mbPackage MBPackage */
        $accountsAllFiles = $this->mbPackage->getCustomRelationshipsMetaFilesByModuleName('Accounts');
        $accountsOnlyMetaFile = $this->mbPackage->getCustomRelationshipsMetaFilesByModuleName('Accounts', true, true);
        $wrongModuleName = $this->mbPackage->getCustomRelationshipsMetaFilesByModuleName('Wrong_module_name');

        $this->assertContains($accountContactMetaPath, $accountsAllFiles);
        $this->assertContains($accountContactTablePath, $accountsAllFiles);
        $this->assertContains($contactAccountMetaPath, $accountsAllFiles);

        $this->assertContains($accountContactMetaPath, $accountsOnlyMetaFile);
        $this->assertNotContains($contactAccountMetaPath, $accountsOnlyMetaFile);

        $this->assertInternalType('array', $wrongModuleName);
        $this->assertEmpty($wrongModuleName);
    }

    /**
     * @group 45339
     */
    public function testGetExtensionsList()
    {
        // Create new relationship between Leads and Accounts
        $_REQUEST['view_module'] = "Leads";
        $_REQUEST['lhs_module'] = "Leads";
        $_REQUEST['rhs_module'] = "Accounts";
        $_REQUEST['lhs_label'] = "Leads";
        $_REQUEST['rhs_label'] = "Accounts";

        $deployedRelation = new DeployedRelationships($_REQUEST['view_module']);
        $relationLeadAccount = $deployedRelation->addFromPost();
        $deployedRelation->save();
        $deployedRelation->build();
        LanguageManager::clearLanguageCache($_REQUEST['view_module']);

        $accountContactRelInAccountVardefExtensions = 'custom/Extension/modules/Accounts/Ext/Vardefs/' . $this->relationAccountContact->getName() . '_Accounts.php';
        $contactAccountRelInAccountVardefExtensions = 'custom/Extension/modules/Accounts/Ext/Vardefs/' . $this->relationContactAccount->getName() . '_Accounts.php';
        $leadAccountRelInAccountVardefExtensions = 'custom/Extension/modules/Accounts/Ext/Vardefs/' . $relationLeadAccount->getName() . '_Accounts.php';

        /* @var $this->mbPackage MBPackage */
        $accountAllExtensions = $this->mbPackage->getExtensionsList('Accounts');
        $accountExtContacts = $this->mbPackage->getExtensionsList('Accounts', array('Contacts'));
        $accountExtWithWrongRelationship = $this->mbPackage->getExtensionsList('Accounts', array(''));
        $wrongModuleName = $this->mbPackage->getExtensionsList('Wrong_module_name');

        // Remove relationship
        $deployedRelation->delete($relationLeadAccount->getName());
        $deployedRelation->save();
        SugarRelationshipFactory::deleteCache();

        $this->assertContains($accountContactRelInAccountVardefExtensions, $accountAllExtensions);
        $this->assertContains($contactAccountRelInAccountVardefExtensions, $accountAllExtensions);
        $this->assertContains($leadAccountRelInAccountVardefExtensions, $accountAllExtensions);

        $this->assertContains($accountContactRelInAccountVardefExtensions, $accountExtContacts);
        $this->assertContains($contactAccountRelInAccountVardefExtensions, $accountExtContacts);
        $this->assertNotContains($leadAccountRelInAccountVardefExtensions, $accountExtContacts);

        $this->assertEmpty($accountExtWithWrongRelationship);

        $this->assertInternalType('array', $wrongModuleName);
        $this->assertEmpty($wrongModuleName);
    }

    /**
     * @group 45339
     */
    public function testGetExtensionsManifestForPackage()
    {
        /* @var $this->mbPackage MBPackage */
        $this->mbPackage->exportCustom(array('Accounts'), false, false);
        $installDefs = array();
        $packExtentionsPath = $this->mbPackage->getBuildDir() . '/Extension/modules';

        $this->mbPackage->getExtensionsManifestForPackageTest($this->mbPackage->getBuildDir(), $installDefs);

        $recursiveIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($packExtentionsPath, RecursiveDirectoryIterator::SKIP_DOTS));

        $expected = iterator_count($recursiveIterator);
        
        $this->mbPackage->delete();
        $this->mbPackage->deleteBuild();

        $this->assertEquals($expected, count($installDefs['copy']));
    }

}

class Bug45339MBPackageMock extends MBPackage
{

    public function getExtensionsManifestForPackageTest($path, &$installdefs)
    {
        return $this->getExtensionsManifestForPackage($path, $installdefs);
    }

}
