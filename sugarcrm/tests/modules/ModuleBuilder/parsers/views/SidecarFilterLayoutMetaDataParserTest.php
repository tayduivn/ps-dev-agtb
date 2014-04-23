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
 * Copyright (C) 2004-2013 SugarCRM Inc. All rights reserved.
 */

/**
 * @covers SidecarFilterLayoutMetaDataParser
 */
class SidecarFilterLayoutMetaDataParserTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function testRemoveExistingField()
    {
        $parser = $this->getRemoveFieldMock();
        $result = $parser->removeField('field1');
        $this->assertTrue($result, 'The field should have been successfully removed');
        $this->assertArrayNotHasKey('field1', $parser->_viewdefs, 'The field should not be contained in metadata');
    }

    public function testRemoveNonExistingField()
    {
        $parser = $this->getRemoveFieldMock();
        $result = $parser->removeField('field2');
        $this->assertFalse($result, 'The field should not have been removed');
    }

    /**
     * @return SidecarFilterLayoutMetaDataParser
     */
    private function getRemoveFieldMock()
    {
        /** @var SidecarFilterLayoutMetaDataParser $parser */
        $parser = $this->getMockBuilder('SidecarFilterLayoutMetaDataParser')
            ->setMethods(array('dummy'))
            ->disableOriginalConstructor()
            ->getMock();
        $parser->_viewdefs = array(
            'fields' => array(
                'field1' => array(),
            ),
        );

        return $parser;
    }
}
