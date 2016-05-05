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

class Bug39598Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $relationshipFileDir;
    private $relationshipFilePath;

    public function setUp()
    {
        $this->relationshipFilePath = 'custom/modulebuilder/packages/Bug39598Test/relationships.php';
        $this->relationshipFileDir = dirname($this->relationshipFilePath);
        sugar_mkdir($this->relationshipFileDir, null, true);

        $contents = '<?php  $relationships = array (
           \'pk8_mod11_accounts\' =>
           array (
           \'rhs_label\' => \'MyAccounts\',
           \'lhs_label\' => \'Mod11s\',
           \'lhs_subpanel\' => \'default\',
           \'rhs_subpanel\' => \'default\',
           \'lhs_module\' => \'Pk8_Mod11\',
           \'rhs_module\' => \'Accounts\',
           \'relationship_type\' => \'many-to-many\',
           \'readonly\' => false,
           \'deleted\' => false,
           \'relationship_only\' => false,
           \'for_activities\' => false,
           \'is_custom\' => false,
           \'from_studio\' => false,
           \'relationship_name\' => \'pk8_mod11_accounts\',
           ),
        );';
        file_put_contents($this->relationshipFilePath, $contents);
    }

    public function tearDown()
    {
        unlink($this->relationshipFilePath);
        rmdir($this->relationshipFileDir);
    }

    public function testRelationshipName()
    {
        $mbModule = new MBModule('newname', $this->relationshipFileDir, 'test', 'test');
        $expectedNewName = 'test_newname';
        $mbModule->renameRelationships($this->relationshipFileDir, $expectedNewName);
        $relationships = array();
        include $this->relationshipFilePath;
        $this->assertEquals(
            $expectedNewName,
            $relationships['test_newname_accounts']['lhs_module'],
            'Module name replaced correctly in relationships metadata'
        );

    }
}
