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
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once 'modules/UpgradeWizard/SidecarUpdate/SidecarLeadConvertMetaDataUpgrader.php';
require_once 'modules/UpgradeWizard/SidecarUpdate/SidecarMetaDataUpgrader.php';
require_once 'modules/Leads/ConvertLayoutMetadataParser.php';

/**
 * @group leadconvert
 */
class SidecarLeadConvertUpgraderTest extends PHPUnit_Framework_TestCase
{
    protected $upgrader;
    protected $convertUpgrader;
    protected $oldDefs;
    protected $expectedDefs;
    protected $oldFileName = 'custom/modules/Leads/metadata/convertdefs_test.php';

    protected function setUp()
    {
        $this->upgrader = new SidecarMetaDataUpgrader();
        $this->metadataParser = new ConvertLayoutMetadataParser("Contacts");
    }

    protected function tearDown()
    {
        unset($this->convertUpgrader);
        if(file_exists($this->oldFileName)) {
            unlink($this->oldFileName);
        }
    }

    /**
     * @covers SidecarLeadConvertMetaDataUpgrader::convertSingleModuleDef()
     */
    public function testConvertSingleModuleDef_OnlyRequiredAndCopyDataSettingsCarriedOver()
    {
        $oldDef = array(
            'ConvertLead' => array(
                'required' => 'bar',
                'copyData' => 'baz',
                'select' => 'foo_name',
                'default_action' => 'create',
                'panels' =>array (
                    'LNK_NEW_FOO' => array (
                        array (
                            'first_name',
                            'last_name',
                        ),
                    ),
                ),
            ),
        );
        $expectedDef = array(
            'module' => 'Foo',
            'required' => 'bar',
            'copyData' => 'baz',
        );

        $this->setUpConvertUpgrader();
        $newDef = $this->convertUpgrader->convertSingleModuleDef('Foo', $oldDef);
        $this->assertEquals($expectedDef, $newDef, 'Only required and copyData should be copied over.');
    }

    /**
     * @covers SidecarLeadConvertMetaDataUpgrader::upgrade()
     */
    public function testUpgrade_ModuleOrderIsEnforced()
    {
        $oldDef = array(
            'Foo' => array(
                'ConvertLead' => array(
                    'required' => true,
                ),
            ),
            'Accounts' => array(
                'ConvertLead' => array(
                    'required' => true,
                ),
            ),
            'Contacts' => array(
                'ConvertLead' => array(
                    'required' => true,
                ),
            ),
            'Opportunities' => array(
                'ConvertLead' => array(
                    'required' => true,
                ),
            ),
        );
        $expectedOrder = array('Contacts', 'Accounts', 'Opportunities', 'Foo');

        $this->createOldDefFile($oldDef);
        $this->setUpConvertUpgrader();
        $this->convertUpgrader->upgrade();

        $actualOrder = array();
        foreach($this->convertUpgrader->sidecarViewdefs['modules'] as $moduleDef) {
            $actualOrder[] = $moduleDef['module'];
        }

        $this->assertEquals($expectedOrder, $actualOrder, 'Modules should be in the correct order');
    }

    /**
     * @covers SidecarLeadConvertMetaDataUpgrader::upgrade()
     */
    public function testUpgrade_AccountIsRequiredWhenOpportunityIsIncluded()
    {
        $oldDef = array(
            'Accounts' => array(
                'ConvertLead' => array(
                    'required' => false,
                ),
            ),
            'Opportunities' => array(
                'ConvertLead' => array(
                    'required' => true,
                ),
            ),
        );
        $this->createOldDefFile($oldDef);
        $this->setUpConvertUpgrader();
        $this->convertUpgrader->upgrade();

        $this->assertEquals(2, count($this->convertUpgrader->sidecarViewdefs['modules']), "Should be two modules");
        foreach($this->convertUpgrader->sidecarViewdefs['modules'] as $moduleDef) {
            if ($moduleDef['module'] === 'Accounts') {
                $this->assertTrue($moduleDef['required'], 'Account should be required because Opp is included');
            }
        }
    }

    /**
     * @covers SidecarLeadConvertMetaDataUpgrader::upgrade()
     */
    public function testUpgrade_ModulesInBwcOrExcludeListShouldNotBeIncluded()
    {
        global $bwcModules;

        $originalBWCModules = $bwcModules;
        $bwcModules = array('BWCModule');
        $oldDef = array(
            'Contacts' => array(
                'ConvertLead' => array(
                    'required' => true,
                ),
            ),
            //Excluded Module
            'Activities' => array(
                'ConvertLead' => array(
                    'required' => true,
                ),
            ),
            //BWC Module
            'BWCModule' => array(
                'ConvertLead' => array(
                    'required' => true,
                ),
            ),
        );
        $expectedModules = array('Contacts');

        $this->createOldDefFile($oldDef);
        $this->setUpConvertUpgrader();
        $this->convertUpgrader->upgrade();

        $actualModules = array();
        foreach($this->convertUpgrader->sidecarViewdefs['modules'] as $moduleDef) {
            $actualModules[] = $moduleDef['module'];
        }

        $this->assertEquals($expectedModules, $actualModules, 'Only expected modules should be included');

        //restore
        $bwcModules = $originalBWCModules;
    }

    /**
     * @covers SidecarLeadConvertMetaDataUpgrader::upgrade()
     */
    public function testUpgrade_AccountCanBeOptionalWhenOpportunityIsNotIncluded()
    {
        $oldDef = array(
            'Accounts' => array(
                'ConvertLead' => array(
                    'required' => false,
                ),
            ),
        );
        $this->createOldDefFile($oldDef);
        $this->setUpConvertUpgrader();
        $this->convertUpgrader->upgrade();

        $this->assertEquals(1, count($this->convertUpgrader->sidecarViewdefs['modules']), "Should be one module");
        foreach($this->convertUpgrader->sidecarViewdefs['modules'] as $moduleDef) {
            if ($moduleDef['module'] === 'Accounts') {
                $this->assertFalse($moduleDef['required'], 'Account should be optional because Opp is not included and legacy was optional');
            }
        }
    }

    protected function createOldDefFile($oldDef)
    {
        if (!is_dir(dirname($this->oldFileName))) {
            sugar_mkdir(dirname($this->oldFileName), null, true);
        }
        write_array_to_file(
            "viewdefs",
            $oldDef,
            $this->oldFileName
        );
    }

    protected function setUpConvertUpgrader()
    {
        $fileArray = array(
            'module' => 'Leads',
            'client' => 'base',
            'fullpath' => $this->oldFileName,
        );
        $this->convertUpgrader = new SidecarLeadConvertUpgraderMock($this->upgrader, $fileArray);
    }

}

class SidecarLeadConvertUpgraderMock extends SidecarLeadConvertMetaDataUpgrader
{
    public $sidecarViewdefs  = 'bad default';
    public $newPath = 'my/test/file.php';

    public function handleSave()
    {
        // do nothing
    }

    public function convertSingleModuleDef($module, $oldDef)
    {
        return parent::convertSingleModuleDef($module, $oldDef);
    }
}
