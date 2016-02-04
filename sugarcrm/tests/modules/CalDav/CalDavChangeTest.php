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

require_once 'tests/SugarTestCalDavUtilites.php';

class CalDavChangeTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function addChangeProvider()
    {
        return array(
            array(
                'calendarID' => 1,
                'objectURI' => 'test',
                'operation' => 2,
                'syncToken' => 5,
                'expectedSyncToken' => 5,
            ),
        );
    }

    /**
     * @param string $calendarID
     * @param string $objectURI
     * @param int $operation
     * @param int $syncToken
     * @param int $expectedSyncToken
     *
     * @covers       \CalDavChange::add
     *
     * @dataProvider addChangeProvider
     */
    public function testAddChange($calendarID, $objectURI, $operation, $syncToken, $expectedSyncToken)
    {
        $calendarMock = $this->getMockBuilder('CalDavCalendar')
                             ->disableOriginalConstructor()
                             ->setMethods(array('save'))
                             ->getMock();

        $changeMock = $this->getMockBuilder('CalDavChange')
                           ->disableOriginalConstructor()
                           ->setMethods(array('save'))
                           ->getMock();

        $calendarMock->id = $calendarID;
        $calendarMock->synctoken = $syncToken;

        $changeMock->expects($this->once())->method('save');

        $changeMock->add($calendarMock, $objectURI, $operation);

        $this->assertEquals($calendarID, $changeMock->calendarid);
        $this->assertEquals($objectURI, $changeMock->uri);
        $this->assertEquals($operation, $changeMock->operation);
        $this->assertEquals($expectedSyncToken, $calendarMock->synctoken);
    }
}
