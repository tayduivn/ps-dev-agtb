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

namespace Sugarcrm\SugarcrmTests\Dav\Cal\Adapter;

/**
 * Class verifyImportAfterExportTest
 *
 * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\AdapterAbstract
 */
class verifyImportAfterExportTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Sugarcrm\Sugarcrm\Dav\Cal\Adapter\AdapterAbstract */
    protected $adapterMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\CalDavEventCollection $collectionMock */
    protected $collectionMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event $eventMock */
    protected $eventMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->adapterMock =
            $this->getMock('Sugarcrm\Sugarcrm\Dav\Cal\Adapter\AdapterAbstract', null);
        $this->collectionMock = $this->getMock('CalDavEventCollection');
        $this->eventMock = $this->getMock('Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event');
    }

    /**
     * Data provider for testVerifyImportAfterExportWithLockedFields.
     *
     * @see testVerifyImportAfterExportWithLockedFields
     *
     * @return array
     */
    public function verifyImportAfterExportWithLockedFieldsProvider()
    {
        $calDavBeanId = create_guid();

        $firstOrderDate = new \SugarDateTime('2016-02-26 08:30:00', new \DateTimeZone('UTC'));
        $secondOrderDate = new \SugarDateTime('2016-02-27 08:30:00', new \DateTimeZone('UTC'));

        return array(
            'singleNewEvent' => array(
                'lockedEvent' => array(
                    'getTitle' => 'Locked title',
                    'getDescription' => '',
                    'getStartDate' => new \SugarDateTime('2016-02-26 08:30:00', new \DateTimeZone('UTC')),
                    'getEndDate' => new \SugarDateTime('2016-02-26 09:00:00', new \DateTimeZone('UTC')),
                    'getLocation' => '',
                    'getStatus' => '',
                ),
                'exportData' => array(
                    array(
                        'override',
                        'Meetings',
                        '9fb85cb6-0eef-8041-3ddd-56d00b69fdfc',
                        null,
                        null,
                    ),
                    array(
                        'name' => array('test'),
                        'date_start' => array('2016-02-26 08:30:00'),
                        'date_end' => array('2016-02-26 09:00:00'),
                        'repeat_type' => array(''),
                        'repeat_interval' => array(null),
                        'repeat_dow' => array(''),
                        'repeat_until' => array(''),
                        'repeat_count' => array(0),
                        'repeat_selector' => array('None'),
                        'repeat_days' => array(''),
                        'repeat_ordinal' => array(''),
                        'repeat_unit' => array(''),
                        'repeat_parent_id' => array(''),
                    ),
                    array(),
                ),
                'importData' => array(),
                'sugarChildrenOrder' => array(),
                'davChildrenOrder' => array(),
                'expectedImportData' => array(
                    array(
                        'update',
                        $calDavBeanId,
                        null,
                        null,
                        null,
                    ),
                    array('title' => array('Locked title')),
                    array(),
                ),
                'calDavBeanId' => $calDavBeanId,
            ),
            'singleUpdatedEvent' => array(
                'lockedEvent' => array(
                    'getTitle' => 'Locked title',
                    'getDescription' => '',
                    'getStartDate' => new \SugarDateTime('2016-02-26 08:30:00', new \DateTimeZone('UTC')),
                    'getEndDate' => new \SugarDateTime('2016-02-26 09:00:00', new \DateTimeZone('UTC')),
                    'getLocation' => 'office',
                    'getStatus' => '',
                ),
                'exportData' => array(
                    array(
                        'update',
                        'Meetings',
                        '1a0523f2-60d5-3509-7d0f-56d03534f0d6',
                        null,
                        null,
                    ),
                    array(
                        'name' => array('Locked title 1', 'Locked title'),
                        'location' => array('home', null),
                    ),
                    array(),
                ),
                'importData' => array(
                    array(
                        'update',
                        '67887879-ea25-a6f8-12de-56d035b8fcdc',
                        null,
                        null,
                        null,
                    ),
                    array('location' => array('office', null)),
                    array(),
                ),
                'sugarChildrenOrder' => array(),
                'davChildrenOrder' => array(),
                'expectedImportData' => array(
                    array(
                        'update',
                        '67887879-ea25-a6f8-12de-56d035b8fcdc',
                        null,
                        null,
                        null,
                    ),
                    array('title' => array('Locked title'), 'location' => array('office')),
                    array(),
                ),
                'calDavBeanId' => $calDavBeanId,
            ),
            'singleUpdatedEventWithoutDiff' => array(
                'lockedEvent' => array(
                    'getTitle' => 'Locked title',
                    'getDescription' => '',
                    'getStartDate' => new \SugarDateTime('2016-02-26 08:30:00', new \DateTimeZone('UTC')),
                    'getEndDate' => new \SugarDateTime('2016-02-26 09:00:00', new \DateTimeZone('UTC')),
                    'getLocation' => '',
                    'getStatus' => '',
                ),
                'exportData' => array(
                    array(
                        'update',
                        'Meetings',
                        '1a0523f2-60d5-3509-7d0f-56d03534f0d6',
                        null,
                        null,
                    ),
                    array(
                        'name' => array('Locked title 1', 'Locked title'),
                    ),
                    array(),
                ),
                'importData' => array(),
                'sugarChildrenOrder' => array(),
                'davChildrenOrder' => array(),
                'expectedImportData' => array(
                    array(
                        'update',
                        $calDavBeanId,
                        null,
                        null,
                        null,
                    ),
                    array('title' => array('Locked title')),
                    array(),
                ),
                'calDavBeanId' => $calDavBeanId
            ),
            'recurringUpdatedEventWithoutDiff' => array(
                'lockedEvent' => array(
                    'getTitle' => 'Locked title',
                    'getDescription' => '',
                    'getStartDate' => new \SugarDateTime('2016-02-26 08:30:00', new \DateTimeZone('UTC')),
                    'getEndDate' => new \SugarDateTime('2016-02-26 09:00:00', new \DateTimeZone('UTC')),
                    'getLocation' => '',
                    'getStatus' => '',
                ),
                'exportData' => array(
                    array(
                        'update',
                        'Meetings',
                        'd8aff3a1-b1ae-f682-555f-56d0475142d3',
                        '87943032-1511-bd7d-942e-56d04754a6df',
                        null,
                    ),
                    array(
                        'name' => array('Locked title 1', 'Locked title'),
                    ),
                    array(),
                ),
                'importData' => array(),
                'sugarChildrenOrder' => array(
                    '87943032-1511-bd7d-942e-56d04754a6df',
                    'd8aff3a1-b1ae-f682-555f-56d0475142d3'
                ),
                'davChildrenOrder' => array(
                    $firstOrderDate->getTimestamp() => $firstOrderDate,
                    $secondOrderDate->getTimestamp() => $secondOrderDate,
                ),
                'expectedImportData' => array(
                    array(
                        'update',
                        $calDavBeanId,
                        'd8aff3a1-b1ae-f682-555f-56d0475142d3',
                        null,
                        1,
                    ),
                    array('title' => array('Locked title')),
                    array(),
                ),
                'calDavBeanId' => $calDavBeanId
            ),
        );
    }

    /**
     * Checks import values after verifyImportAfterExport when workflow lock some fields.
     *
     * @param array $eventInfo
     * @param array $exportData
     * @param array $importData
     * @param array $sugarChildrenOrder
     * @param array $davChildrenOrder
     * @param array $expectedImportData
     * @param string $calDavBeanId
     *
     * @covers       \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\AdapterAbstract::verifyImportAfterExport
     *
     * @dataProvider verifyImportAfterExportWithLockedFieldsProvider
     */
    public function testVerifyImportAfterExportWithLockedFields(
        array $eventInfo,
        array $exportData,
        array $importData,
        array $sugarChildrenOrder,
        array $davChildrenOrder,
        array $expectedImportData,
        $calDavBeanId
    ) {
        $this->collectionMock->id = $calDavBeanId;
        $this->collectionMock->method('getSugarChildrenOrder')->willReturn($sugarChildrenOrder);
        $this->collectionMock->method('getAllChildrenRecurrenceIds')->willReturn($davChildrenOrder);
        $this->collectionMock->method('getParent')->willReturn($this->eventMock);
        $this->collectionMock->method('getChild')->willReturn($this->eventMock);

        foreach ($eventInfo as $method => $value) {
            $this->eventMock->method($method)->willReturn($value);
        }

        $result = $this->adapterMock->verifyImportAfterExport($exportData, $importData, $this->collectionMock);
        $this->assertEquals($expectedImportData, $result);
    }
}
