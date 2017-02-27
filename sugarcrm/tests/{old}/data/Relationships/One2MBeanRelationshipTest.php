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

class One2MBeanRelationshipTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function testProperRhsFieldIsSet()
    {
        $primaryBean = SugarTestKBContentUtilities::createBean(array(
            'kbdocument_id' => create_guid(),
        ));
        $relatedBean = SugarTestKBContentUtilities::createBean();

        $primaryBean->load_relationship('localizations');
        $primaryBean->localizations->add($relatedBean);

        $this->assertEquals($primaryBean->kbdocument_id, $relatedBean->kbdocument_id);

        SugarTestKBContentUtilities::removeAllCreatedBeans();
    }

    /**
     * @dataProvider addProvider
     * @param $relationshipExists
     * @param $rowCompared
     * @param $isRHSMany_return
     * @param $removeAll_return
     * @param $result
     */
    public function testAdd(
        $relationshipExists,
        $rowCompared,
        $isRHSMany_return,
        $removeAll_return,
        $result
    ) {

        $additionalFields = array(
            'return' => $result,
        );

        $mock = $this->getMockBuilder('SugarO2mMock')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $mock->relationshipExists = $relationshipExists;
        $mock->rowCompared = $rowCompared;
        $mock->isRHSMany_return = $isRHSMany_return;
        $mock->removeAll_return = $removeAll_return;

        $beanMock = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->getMock();

        $value = $mock->add($beanMock, $beanMock, $additionalFields);

        $this->assertEquals($result, $value);
    }

    public function addProvider()
    {
        return array(
            array(false, false, true, true, true),
            array(true, false, true, true, true),
            array(false, true, true, true, true),
            array(false, false, false, true, true),
            array(true, false, false, true, true),
            array(false, true, false, true, true),
            array(false, false, true, false, false),
            array(true, false, true, false, false),
            array(false, true, true, false, false),
            array(false, false, false, false, false),
            array(true, false, false, false, false),
            array(false, true, false, false, false),
            array(true, true, true, true, false),
        );
    }
}

/**
 * test class for mocking parents
 */
class SugarO2mMockParent extends One2MRelationship
{
    public function add($lhs, $rhs, $additionalFields = array())
    {
        return $additionalFields['return'];
    }
}

/**
 * Test class used for exposing/mocking protected methods
 */
class SugarO2mMock extends SugarO2mMockParent
{
    public $rowToInsert = null;
    public $relationshipExists = null;
    public $rowCompared = null;
    public $isRHSMany_return = null;
    public $removeAll_return = null;

    public function getRowToInsert()
    {
        return $this->rowToInsert;
    }

    public function relationship_exists($lhs, $rhs)
    {
        return $this->relationshipExists;
    }

    public function compareRow()
    {
        return $this->rowCompared;
    }

    public function isRHSMany()
    {
        return $this->isRHSMany_return;
    }

    public function removeAll()
    {
        return $this->removeAll_return;
    }
}
