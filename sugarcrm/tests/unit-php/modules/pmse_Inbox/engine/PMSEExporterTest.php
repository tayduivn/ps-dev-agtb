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
 * @coversDefaultClass \PMSEExporter
 */
class PMSEExporterTest extends TestCase
{
    /**
     * @covers ::getProject
     */
    public function testGetProject()
    {
        $exporterMock = $this->getMockBuilder('PMSEExporter')
            ->setMethods(array( 'retrieveBean', 'getMetadata'))
            ->disableOriginalConstructor()
            ->getMock();

        $bean = new \stdClass();
        $bean->fetched_row = array('id' => 'asdf', 'name' => 'project def');
        $exporterMock->setBean($bean);

        $result = $exporterMock->getProject(array('id'=>'1234'));
        $this->assertArrayHasKey('metadata', $result);
        $this->assertArrayHasKey('project', $result);

        $result = $exporterMock->getProject(array('id' => '1234', 'project_only' => true));
        $this->assertArrayNotHasKey('metadata', $result);
        $this->assertArrayHasKey('project', $result);
    }

    /**
     * @covers ::getExporter
     * @dataProvider getExporterData
     * @param $type
     * @param $instance
     */
    public function testGetExporter($type, $instance)
    {
        $exporterMock = new \PMSEExporter();
        $exporter = $exporterMock->getExporter($type);

        $this->assertInstanceOf($instance, $exporter);
    }
    
    public function getExporterData()
    {
        return [
            [
                'name' => 'project',
                'instance' => 'PMSEProjectExporter',
            ],
            [
                'name' => 'business_rule',
                'instance' => 'PMSEBusinessRuleExporter',
            ],
            [
                'name' => 'email_template',
                'instance' => 'PMSEEmailTemplateExporter',
            ],
        ];
    }
}
