<?php
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


class SugarUpgradeOpportunityFixSalesStageFieldDefinitionTest extends UpgradeTestCase
{
    protected function setUp() : void
    {
        parent::setUp();
        $this->db = DBManagerFactory::getInstance();
        $this->scriptFileName = '6_OpportunityFixSalesStageFieldDefinition';
    }

    protected function tearDown() : void
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * @covers ::run
     */
    public function testFixSalesStageField_IncorrectFlavor_DoesNotUpgrade()
    {
        $this->upgrader->setVersions('8.0.0', 'pro', '9.1.0', 'pro');
        $this->upgrader->setDb($this->db);

        $script = $this->upgrader->getScript('post', $this->scriptFileName);

        $this->assertNotEmpty($script);

        $mock = $this->getMockBuilder(get_class($script))
            ->setMethods(['fixSalesStageFieldDefinition'])
            ->setConstructorArgs([$this->upgrader])
            ->getMock();

        Opportunity::$settings = array(
            'opps_view_by' => 'RevenueLineItems',
        );

        $mock->expects($this->never())->method('fixSalesStageFieldDefinition');
        $mock->run();
    }

    /**
     * @covers ::run
     */
    public function testFixSalesStageField_OppsOnly_DoesNotUpgrade()
    {
        Opportunity::$settings = array(
            'opps_view_by' => 'Opportunities',
        );

        $this->upgrader->setVersions('8.0.0', 'ent', '9.1.0', 'ent');
        $this->upgrader->setDb($this->db);

        $script = $this->upgrader->getScript('post', $this->scriptFileName);

        $this->assertNotEmpty($script);

        $mock = $this->getMockBuilder(get_class($script))
            ->setMethods(['fixSalesStageFieldDefinition'])
            ->setConstructorArgs([$this->upgrader])
            ->getMock();

        $mock->expects($this->never())->method('fixSalesStageFieldDefinition');
        $mock->run();
    }

    /**
     * @covers ::fixSalesStageFieldDefinition
     */
    public function testFixSalesStageFieldDefinition()
    {
        $this->upgrader->setVersions('8.0.0', 'ent', '9.1.0', 'ent');
        $this->upgrader->setDb($this->db);
        $script = $this->upgrader->getScript('post', $this->scriptFileName);

        $fieldDef = array(
            'name' => 'sales_stage',
            'vname' => 'LBL_SALES_STAGE',
            'type' => 'enum',
            'options' => 'sales_stage_dom',
            'default' => 'Prospecting',
            'len' => 255,
            'reaonly' => false,
            'merge_filter' => 'disabled',
            'importable' => false,
            'audited' => true,
            'required' => false,
            'massupdate' => false,
            'reportable' => true,
        );

        $mockOpp = $this->createPartialMock('Opportunity', array('getFieldDefinition'));
        $mockOpp->expects($this->once())
            ->method('getFieldDefinition')
            ->with('sales_stage')
            ->willReturn($fieldDef);

        $mockStandardField = $this->createPartialMock('StandardField', array('setup'));
        $mockStandardField->expects($this->once())
            ->method('setup')
            ->with($mockOpp);

        $mockEnumTemplateField = $this->createPartialMock('TemplateEnum', array('save'));
        $mockEnumTemplateField->expects($this->once())
            ->method('save')
            ->with($mockStandardField);

        $script->oppBean = $mockOpp;
        $script->standardField = $mockStandardField;
        $script->enumTemplateField = $mockEnumTemplateField;

        SugarTestReflection::callProtectedMethod(
            $script,
            'fixSalesStageFieldDefinition'
        );

        $results = $script->enumTemplateField->get_field_def();

        $this->assertSame(true, $results['calculated'], 'The calculated attribute was not populated correctly.');
        $this->assertSame(
            'opportunitySalesStage($revenuelineitems, "sales_stage")',
            $results['formula'],
            'The formula attribute was not populated correctly.'
        );
    }
}
