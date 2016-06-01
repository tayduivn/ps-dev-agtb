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

use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings;

/**
 * Class AdapterCRYS1686Test
 * @package Sugarcrm\SugarcrmTests\Dav\Cal\Adapter
 */
class AdapterCRYS1686Test extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|\CalDavEventCollection */
    protected $calDavEventCollectionMock;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->calDavEventCollectionMock = $this->getMock('\CalDavEventCollection', null);
        $this->calDavEventCollectionMock->id = \Sugarcrm\Sugarcrm\Util\Uuid::uuid1();
    }

    /**
     * Provider for check the setting of invitees.
     *
     * @see Sugarcrm\SugarcrmTests\Dav\Cal\Adapter\AdapterCRYS1686Test::testSetCalDavInvitees
     * @return array
     */
    public function setCalDavInviteesProvider()
    {
        $contactId = \Sugarcrm\Sugarcrm\Util\Uuid::uuid1();

        return array(
            'EqualEmailAndEqualName' => array(
                'value' => array(
                    'added' => array(
                        array('Contacts', $contactId, 'one@email.com', 'none', 'One Name'),
                        array('Leads',    \Sugarcrm\Sugarcrm\Util\Uuid::uuid1(), 'one@email.com', 'none', 'One Name'),
                    ),
                ),
                'expected' => array(2, $contactId, 'Contacts'),
            ),
            'EmptyEmailAndEqualName' => array(
                'value' => array(
                    'added' => array(
                        array('Contacts', $contactId, '', 'none', 'One Name'),
                        array('Leads',    \Sugarcrm\Sugarcrm\Util\Uuid::uuid1(), '', 'none', 'One Name'),
                    ),
                ),
                'expected' => array(2, $contactId, 'Contacts'),
            ),
        );
    }

    /**
     * Check the setting of invitees.
     *
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Adapter\AdapterAbstract::setCalDavInvitees
     * @dataProvider Sugarcrm\SugarcrmTests\Dav\Cal\Adapter\AdapterCRYS1686Test::setCalDavInviteesProvider
     * @param array $value
     * @param array $expected
     */
    public function testSetCalDavInvitees($value, $expected)
    {
        $event = $this->calDavEventCollectionMock->getParent();

        $reflectionMethod = new \ReflectionMethod('\Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings', 'setCalDavInvitees');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invokeArgs(new Meetings(), array(
            $value,
            $event,
            true,
            null,
        ));

        $this->assertEquals($expected, array(
            count($event->getParticipants()),
            $event->getParticipants()[0]->getBeanId(),
            $event->getParticipants()[0]->getBeanName(),
        ));
    }

    /**
     * Provider for check setting invitee on delete.
     *
     * @see Sugarcrm\SugarcrmTests\Dav\Cal\Adapter\AdapterCRYS1686Test::testVerifyImportAfterExport
     * @return array
     */
    public function verifyImportAfterExportProvider()
    {
        return array(
            'EqualEmailAndEqualName' => array(
                'importData' => array(
                    array('override', 'Meetings', '4a892354-28b8-11e6-bd4d-e03f490dc267', null, null, '1'),
                    array(
                        'name' => array('test meeting'),
                        'date_start' => array('2016-06-02 12:00:00'),
                        'date_end' => array('2016-06-02 12:30:00'),
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
                    array(
                        'added' => array(
                            array(
                                'Contacts',
                                '33f52a7e-227e-11e6-86a4-e03f490dc267',
                                'one@email.com',
                                'none',
                                'One Name',
                            ),
                            array(
                                'Leads',
                                'ed1d6452-227f-11e6-a4f1-e03f490dc267',
                                'one@email.com',
                                'none',
                                'One Name',
                            ),
                        ),
                    ),
                ),
                'exportData' => array(
                    array(
                        'override',
                        '4acdb17c-28b8-11e6-be6e-e03f490dc267',
                        null,
                        null,
                        null,
                        'e5a78c72-28b8-11e6-b6a7-e03f490dc267',
                    ),
                    array(
                        'timezone' => array('Europe/Minsk', null),
                        'title' => array('test meeting'),
                        'description' => array(null),
                        'location' => array(null),
                        'status' => array(null),
                        'date_start' => array('2016-06-02 12:00:00'),
                        'date_end' => array('2016-06-02 12:30:00'),
                    ),
                    array(
                        'added' => array(
                            array('Contacts', '33f52a7e-227e-11e6-86a4-e03f490dc267', '', 'NEEDS-ACTION', 'One Name'),
                        ),
                    ),
                ),
                'expected' => array(
                    array(
                        'update',
                        '4acdb17c-28b8-11e6-be6e-e03f490dc267',
                        null,
                        null,
                        null,
                        'e5a78c72-28b8-11e6-b6a7-e03f490dc267',
                    ),
                    array(
                        'timezone' => array('Europe/Minsk', null),
                    ),
                    array(
                        'deleted' => array(
                            array('Leads', 'ed1d6452-227f-11e6-a4f1-e03f490dc267', '', 'none', 'One Name'),
                        ),
                    ),
                ),
            ),
            'EmptyEmailAndEqualName' => array(
                'importData' => array(
                    array('override', 'Meetings', '4a892354-28b8-11e6-bd4d-e03f490dc267', null, null, '1'),
                    array(
                        'name' => array('test meeting'),
                        'date_start' => array('2016-06-02 12:00:00'),
                        'date_end' => array('2016-06-02 12:30:00'),
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
                    array(
                        'added' => array(
                            array('Contacts', '33f52a7e-227e-11e6-86a4-e03f490dc267', '', 'none', 'One Name'),
                            array('Leads', 'ed1d6452-227f-11e6-a4f1-e03f490dc267', '', 'none', 'One Name'),
                        ),
                    ),
                ),
                'exportData' => array(
                    array(
                        'override',
                        '4acdb17c-28b8-11e6-be6e-e03f490dc267',
                        null,
                        null,
                        null,
                        'e5a78c72-28b8-11e6-b6a7-e03f490dc267',
                    ),
                    array(
                        'timezone' => array('Europe/Minsk', null),
                        'title' => array('test meeting'),
                        'description' => array(null),
                        'location' => array(null),
                        'status' => array(null),
                        'date_start' => array('2016-06-02 12:00:00'),
                        'date_end' => array('2016-06-02 12:30:00'),
                    ),
                    array(
                        'added' => array(
                            array('Contacts', '33f52a7e-227e-11e6-86a4-e03f490dc267', '', 'NEEDS-ACTION', 'One Name'),
                        ),
                    ),
                ),
                'expected' => array(
                    array(
                        'update',
                        '4acdb17c-28b8-11e6-be6e-e03f490dc267',
                        null,
                        null,
                        null,
                        'e5a78c72-28b8-11e6-b6a7-e03f490dc267',
                    ),
                    array(
                        'timezone' => array('Europe/Minsk', null),
                    ),
                    array(
                        'deleted' => array(
                            array('Leads', 'ed1d6452-227f-11e6-a4f1-e03f490dc267', '', 'none', 'One Name'),
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * Check setting invitee on delete.
     *
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Adapter\AdapterAbstract::verifyImportAfterExport
     * @dataProvider Sugarcrm\SugarcrmTests\Dav\Cal\Adapter\AdapterCRYS1686Test::verifyImportAfterExportProvider
     * @param $exportData
     * @param $importData
     * @param $expected
     */
    public function qtestVerifyImportAfterExport($exportData, $importData, $expected)
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Meetings $adapterMock */
        $adapterMock = $this->getMock('Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings', null);
        $actual = $adapterMock->verifyImportAfterExport($exportData, $importData, $this->calDavEventCollectionMock);
        $this->assertEquals($expected, $actual);
    }
}
