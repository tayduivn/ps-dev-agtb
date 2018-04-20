<?php
//FILE SUGARCRM flav=ent ONLY
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTestsUnit\modules\pmse_Inbox\engine;

use Sugarcrm\Sugarcrm\ProcessManager;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \PMSEImporter
 */
class PMSEImporterTest extends TestCase
{

    /**
     * @var \User
     */
    protected $currentUserBackUp;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        if (!empty($GLOBALS['current_user'])) {
            $this->currentUserBackUp = $GLOBALS['current_user'];
        }
        $GLOBALS['current_user'] = $this->createMock(\User::class);
        $GLOBALS['current_user']->user_name = 'dump_user_name';
        $GLOBALS['current_user']->id = 'user';
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        if ($this->currentUserBackUp) {
            $GLOBALS['current_user'] = $this->currentUserBackUp;
        } else {
            unset($GLOBALS['current_user']);
        }
    }

    /**
     * @covers PMSEImporter::importDependencies
     */
    public function testImportDependencies()
    {
        $importerMock = $this->getMockBuilder('PMSEImporter')
            ->setMethods(array(
                'getBean',
                'unsetCommonFields',
                'getNameWithSuffix',
                'saveProjectActivitiesData',
                'saveProjectEventsData',
                'saveProjectGatewaysData',
                'saveProjectElementsData',
                'saveProjectFlowsData',
                'processDefaultFlows',
                'processImport',
            ))
            ->getMock();
        $importerMock->method('processImport')->willReturn(array('id' => 'newId1'));
        $dependencies = array(
            'business_rule' => array(array('id' => 'oldId1', 'name' => 'br1')),
        );
        $selectedIds = ['oldId1'];
        $importerMock->importDependencies($dependencies, $selectedIds);
        $dependencyKeys = $importerMock->getDependencyKeys();
        $this->assertArrayHasKey('oldId1', $dependencyKeys);
    }

    /**
     * @covers PMSEImporter::processImport
     */
    public function testProcessImport()
    {
        $importer = ProcessManager\Factory::getPMSEObject('PMSEImporter');
        $etImporterMock = $this->getMockBuilder('PMSEEmailTemplateImporter')
            ->setMethods(array(
                'getBean',
                'unsetCommonFields',
                'getNameWithSuffix',
                'saveProjectActivitiesData',
                'saveProjectEventsData',
                'saveProjectGatewaysData',
                'saveProjectElementsData',
                'saveProjectFlowsData',
                'processDefaultFlows',
            ))
            ->getMock();

        $beanMock = $this->createPartialMock('pmse_Emails_Templates', array('get_full_list', 'save', 'in_save'));
        $beanMock->method('save')
            ->willReturn(array('id'=> 'newId', 'success' => true));

        $etImporterMock->setBean($beanMock);
        $def = array('id' => 'oldId', 'name' => 'et');
        $result = $importer->processImport($etImporterMock, $def);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('id', $result);
    }
}
