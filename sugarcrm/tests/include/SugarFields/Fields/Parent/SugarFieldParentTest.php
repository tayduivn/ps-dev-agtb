<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Customer_Center/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
require_once('include/SugarFields/Fields/Parent/SugarFieldParent.php');
/**
 * Class SugarFieldParentTest
 *
 * Test cases for SugarFieldParent class
 */
class SugarFieldParentTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function sugarFieldParentDataProvider()
    {
        return array(
            array(
                array(), array(), 'Custom', array(), array(), $this->createMock('ServiceBase'),
            )
        );
    }
    /**
     * @dataProvider sugarFieldParentDataProvider
     */
    public function testFormatFieldNonExistingParentType($data, $args, $fieldName, $properties, $fieldList, $service)
    {
        $sugarField = $this->getMockBuilder('SugarFieldParent')
            ->setMethods(array('ensureApiFormatFieldArguments'))
            ->disableOriginalConstructor()
            ->getMock();
        $bean = $this->createMock('SugarBean');
        $bean->parent_type = 'NonExistingClass';
        $sugarField->expects(static::once())
            ->method('ensureApiFormatFieldArguments')
            ->with($fieldList, $service);
        $sugarField->apiFormatField($data, $bean, $args, $fieldName, $properties, $fieldList, $service);
        static::assertEquals(array(), $data['parent']);
    }
}
