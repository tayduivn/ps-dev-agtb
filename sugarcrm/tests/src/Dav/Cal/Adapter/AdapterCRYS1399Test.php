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
 * Class AdapterCRYS1399Test
 * @covers Sugarcrm\Sugarcrm\Dav\Cal\Adapter\AdapterAbstract
 */
class AdapterCRYS1399Test extends \PHPUnit_Framework_TestCase
{
    /** @var \User */
    protected $origUser;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->origUser = $GLOBALS['current_user'];

        $userMock = $this->getMock(get_class($GLOBALS['current_user']), array('getPreference'));
        $userMock->method('getPreference')->will($this->returnValueMap(array(
            array('timezone', 'global', 'Europe/Minsk'),
        )));

        $GLOBALS['current_user'] = $userMock;
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $GLOBALS['current_user'] = $this->origUser;
        parent::tearDown();
    }

    /**
     * Get source file.
     *
     * @param string $name
     * @return string
     */
    protected static function getSourceIcsFile($name)
    {
        return file_get_contents(__DIR__ . '/sources/' . $name . '.ics');
    }

    /**
     * Provider for testPrepareForImport.
     *
     * @see Sugarcrm\SugarcrmTests\Dav\Cal\Adapter\AdapterCRYS1399Test::testPrepareForImport
     * @return array
     */
    public static function prepareForImportProvider()
    {
        $addressees = array(
            'test_1@test.com' => array('beanName' => 'Addressees', 'beanId' => create_guid()),
            'test_2@test.com' => array('beanName' => 'Addressees', 'beanId' => create_guid()),
            'test_3@test.com' => array('beanName' => 'Addressees', 'beanId' => create_guid()),
        );

        $groupId = create_guid();

        $participants_links = json_encode($addressees);

        return array(
            'Add invite only for parent' => array(
                'participants_links' => $participants_links,
                // event every day 7 times (1.03 Tue - 7.03 Mon). List invitees is empty.
                'before' => static::getSourceIcsFile('AddInviteOnlyForParent.before'),
                // Add invitee for only parent test_1@test.com (Sammo Hung Kam-Bo).
                'after' => static::getSourceIcsFile('AddInviteOnlyForParent.after'),
                'expected' => array(
                    array(
                        array('update', null, array(), '2016-03-01 06:00:00', null, $groupId),
                        array(),
                        array(
                            'added' => array(
                                array(
                                    'Addressees',
                                    $addressees['test_1@test.com']['beanId'],
                                    'test_1@test.com',
                                    null,
                                    'Sammo Hung Kam-Bo',
                                ),
                            ),
                        ),
                    ),
                ),
                'groupId' => $groupId,
            ),
            'Change title events for all and one to four event' => array(
                'participants_links' => $participants_links,
                // event every day 7 times (1.03 Tue - 7.03 Mon). Invitee has only parent.
                'before' => static::getSourceIcsFile('ChangeTitleEventsForAllAndOneToFourEvent.before'),
                'after' => static::getSourceIcsFile('ChangeTitleEventsForAllAndOneToFourEvent.after'),
                'expected' => array(
                    array(
                        array('update', null, array(), '2016-03-01 06:00:00', null, $groupId),
                        array(
                            'title' => array('Week Events Change Name For All', 'Week Events'),
                        ),
                        array(),
                    ),
                    array(
                        array('update', null, array(), '2016-03-02 06:00:00', 1, $groupId),
                        array(
                            'title' => array('Week Events Change Name For All', 'Week Events'),
                        ),
                        array(),
                    ),
                    array(
                        array('update', null, array(), '2016-03-03 06:00:00', 2, $groupId),
                        array(
                            'title' => array('Week Events Change Name For All', 'Week Events'),
                        ),
                        array(),
                    ),
                    array(
                        array('update', null, array(), '2016-03-04 06:00:00', 3, $groupId),
                        array(
                            'title' => array('Week Events Change Name For 4', 'Week Events'),
                        ),
                        array(),
                    ),
                    array(
                        array('update', null, array(), '2016-03-05 06:00:00', 4, $groupId),
                        array(
                            'title' => array('Week Events Change Name For All', 'Week Events'),
                        ),
                        array(),
                    ),
                    array(
                        array('update', null, array(), '2016-03-06 06:00:00', 5, $groupId),
                        array(
                            'title' => array('Week Events Change Name For All', 'Week Events'),
                        ),
                        array(),
                    ),
                    array(
                        array('update', null, array(), '2016-03-07 06:00:00', 6, $groupId),
                        array(
                            'title' => array('Week Events Change Name For All', 'Week Events'),
                        ),
                        array(),
                    ),
                ),
                'groupId' => $groupId,
            ),
            'Change the time of the events in the parent' => array(
                'participants_links' => $participants_links,
                'before' => static::getSourceIcsFile('ChangeTheTimeOfTheEventsInTheParent.before'),
                'after' => static::getSourceIcsFile('ChangeTheTimeOfTheEventsInTheParent.after'),
                'expected' => array(
                    array(
                        array('update', null, array(), '2016-03-01 06:00:00', null, $groupId),
                        array(
                            'date_start' => array('2016-03-11 06:00:00', '2016-03-01 06:00:00'),
                            'date_end' => array('2016-03-11 07:00:00', '2016-03-01 07:00:00'),
                        ),
                        array(),
                    ),
                ),
                'groupId' => $groupId,
            ),
            'Added invitees' => array(
                'participants_links' => $participants_links,
                'before' => static::getSourceIcsFile('AddedInvitees.before'),
                'after' => static::getSourceIcsFile('AddedInvitees.after'),
                'expected' => array(
                    array(
                        array('update', null, array(), '2016-03-01 06:00:00', null, $groupId),
                        array(
                            'rrule_action' => 'updated',
                            'rrule_count' => array('1', '7'),
                        ),
                        array(),
                    ),

                    array(
                        array('update', null, array(), '2016-03-01 06:00:00', null, $groupId),
                        array(),
                        array(
                            'added' => array(
                                array(
                                    'Addressees',
                                    $addressees['test_2@test.com']['beanId'],
                                    'test_2@test.com',
                                    null,
                                    'test_2@test.com',
                                ),
                            ),
                        ),
                    )
                ),
                'groupId' => $groupId,
            ),
            'Single event' => array(
                'participants_links' => $participants_links,
                'before' => static::getSourceIcsFile('SingleEvent.before'),
                'after' => static::getSourceIcsFile('SingleEvent.after'),
                'expected' => array(
                    array(
                        array('update', null, array(), '2016-03-10 09:00:00', null, $groupId),
                        array(
                            'timezone' => array('Europe/Minsk', 'UTC'),
                            'title' => array('Single Event', 'Single'),
                            'date_start' => array('2016-03-10 09:00:00', '2016-03-01 21:00:00'),
                            'date_end' => array('2016-03-10 10:00:00', '2016-03-02 21:00:00'),
                        ),
                        array(
                            'added' => array(
                                array(
                                    'Addressees',
                                    $addressees['test_3@test.com']['beanId'],
                                    'test_3@test.com',
                                    null,
                                    'Mark Dacascos',
                                ),
                            ),
                        ),
                    ),
                ),
                'groupId' => $groupId,
            ),
            'Weekly event' => array(
                'participants_links' => $participants_links,
                'before' => static::getSourceIcsFile('WeeklyEvent.before'),
                'after' => static::getSourceIcsFile('WeeklyEvent.after'),
                'expected' => array(
                    array(
                        array('update', null, array(), '2016-03-02 06:00:00', null, $groupId),
                        array(
                            'rrule_action' => 'updated',
                            'rrule_until' => array('2016-03-22 20:59:00', '2016-03-29 20:59:00'),
                        ),
                        array(),
                    ),
                    array(
                        array('update', null, array(), '2016-03-02 06:00:00', null, $groupId),
                        array(
                            'date_start' => array('2016-03-02 06:00:00', '2016-03-01 06:00:00'),
                            'date_end' => array('2016-03-02 07:00:00', '2016-03-01 07:00:00'),
                        ),
                        array(),
                    ),
                    array(
                        array('override', null, array(), '2016-03-09 06:00:00', 1, $groupId),
                        array(
                            'date_start' => array('2016-03-09 06:00:00'),
                            'date_end' => array('2016-03-09 07:00:00'),
                            'title' => array('Weekly Event'),
                            'description' => array(null),
                            'location' => array(null),
                            'status' => array(null),
                        ),
                        array(),
                    ),
                    array(
                        array('override', null, array(), '2016-03-16 06:00:00', 2, $groupId),
                        array(
                            'date_start' => array('2016-03-16 06:00:00'),
                            'date_end' => array('2016-03-16 07:00:00'),
                            'title' => array('Weekly Event'),
                            'description' => array(null),
                            'location' => array(null),
                            'status' => array(null),
                        ),
                        array(),
                    ),
                ),
                'groupId' => $groupId,
            ),
            'Remove event' => array(
                'participants_links' => $participants_links,
                'before' => static::getSourceIcsFile('RemoveEvent.before'),
                'after' => static::getSourceIcsFile('RemoveEvent.after'),
                'expected' => array(
                    array(
                        array('delete', null, array(), '2016-03-09 06:00:00', 1, $groupId),
                        array(),
                        array(),
                    ),
                ),
                'groupId' => $groupId,
            ),
            'New events' => array(
                'participants_links' => $participants_links,
                'before' => '',
                'after' => static::getSourceIcsFile('NewEvents.after'),
                'expected' => array(
                    array(
                        array('override', null, array(), '2016-03-01 06:00:00', null, $groupId),
                        array(
                            'rrule_action' => 'added',
                            'rrule_frequency' => array('DAILY'),
                            'rrule_interval' => array('1'),
                            'rrule_count' => array('7'),
                            'rrule_until' => array(null),
                            'rrule_byday' => array(null),
                            'rrule_bymonthday' => array(null),
                            'rrule_bysetpos' => array(null),
                            'timezone' => array('Europe/Minsk', null),
                            'title' => array('Week Events Change Name For All'),
                            'date_start' => array('2016-03-01 06:00:00'),
                            'date_end' => array('2016-03-01 07:00:00'),
                            'description' => array(null),
                            'location' => array('Minsk, SugaCRM Office'),
                            'status' => array(null),
                        ),
                        array(
                            'added' => array(
                                array(
                                    'Addressees',
                                    $addressees['test_1@test.com']['beanId'],
                                    'test_1@test.com',
                                    null,
                                    'Sammo Hung Kam-Bo',
                                ),
                            ),
                        ),
                    ),
                    array(
                        array('override', null, array(), '2016-03-02 06:00:00', 1, $groupId),
                        array(
                            'title' => array('Week Events Change Name For All'),
                            'date_start' => array('2016-03-02 06:00:00'),
                            'date_end' => array('2016-03-02 07:00:00'),
                            'description' => array(null),
                            'location' => array('Minsk, SugaCRM Office'),
                            'status' => array(null),
                        ),
                        array(),
                    ),
                    array(
                        array('override', null, array(), '2016-03-03 06:00:00', 2, $groupId),
                        array(
                            'title' => array('Week Events Change Name For All'),
                            'date_start' => array('2016-03-03 06:00:00'),
                            'date_end' => array('2016-03-03 07:00:00'),
                            'description' => array(null),
                            'location' => array('Minsk, SugaCRM Office'),
                            'status' => array(null),
                        ),
                        array(),
                    ),
                    array(
                        array('restore', null, array(), '2016-03-04 06:00:00', 3, $groupId),
                        array(
                            'title' => array('Week Events Change Name For 4'),
                            'date_start' => array('2016-03-04 06:00:00'),
                            'date_end' => array('2016-03-04 07:00:00'),
                            'description' => array(null),
                            'location' => array('Minsk, SugaCRM Office'),
                            'status' => array(null),
                        ),
                        array(),
                    ),
                    array(
                        array('override', null, array(), '2016-03-05 06:00:00', 4, $groupId),
                        array(
                            'title' => array('Week Events Change Name For All'),
                            'date_start' => array('2016-03-05 06:00:00'),
                            'date_end' => array('2016-03-05 07:00:00'),
                            'description' => array(null),
                            'location' => array('Minsk, SugaCRM Office'),
                            'status' => array(null),
                        ),
                        array(),
                    ),
                    array(
                        array('override', null, array(), '2016-03-06 06:00:00', 5, $groupId),
                        array(
                            'title' => array('Week Events Change Name For All'),
                            'date_start' => array('2016-03-06 06:00:00'),
                            'date_end' => array('2016-03-06 07:00:00'),
                            'description' => array(null),
                            'location' => array('Minsk, SugaCRM Office'),
                            'status' => array(null),
                        ),
                        array(),
                    ),
                    array(
                        array('override', null, array(), '2016-03-07 06:00:00', 6, $groupId),
                        array(
                            'title' => array('Week Events Change Name For All'),
                            'date_start' => array('2016-03-07 06:00:00'),
                            'date_end' => array('2016-03-07 07:00:00'),
                            'description' => array(null),
                            'location' => array('Minsk, SugaCRM Office'),
                            'status' => array(null),
                        ),
                        array(),
                    ),
                ),
                'groupId' => $groupId,
            ),
            'Add times' => array(
                'participants_links' => $participants_links,
                'before' => static::getSourceIcsFile('AddTimes.before'),
                'after' => static::getSourceIcsFile('AddTimes.after'),
                'expected' => array(
                    array(
                        array('update', null, array(), '2016-03-06 06:00:00', null, $groupId),
                        array(
                            'rrule_action' => 'updated',
                            'rrule_count' => array('5', '3'),
                        ),
                        array(),
                    ),
                    array(
                        array('override', null, array(), '2016-03-07 06:00:00', 1, $groupId),
                        array(
                            'title' => array('deswvdswg'),
                            'description' => array(null),
                            'location' => array(null),
                            'status' => array(null),
                            'date_start' => array('2016-03-07 06:00:00'),
                            'date_end' => array('2016-03-07 07:00:00'),
                        ),
                        array(),
                    ),
                    array(
                        array('override', null, array(), '2016-03-08 06:00:00', 2, $groupId),
                        array(
                            'title' => array('deswvdswg'),
                            'description' => array(null),
                            'location' => array(null),
                            'status' => array(null),
                            'date_start' => array('2016-03-08 06:00:00'),
                            'date_end' => array('2016-03-08 07:00:00'),
                        ),
                        array(),
                    ),
                    array(
                        array('override', null, array(), '2016-03-09 06:00:00', 3, $groupId),
                        array(
                            'title' => array('deswvdswg'),
                            'description' => array(null),
                            'location' => array(null),
                            'status' => array(null),
                            'date_start' => array('2016-03-09 06:00:00'),
                            'date_end' => array('2016-03-09 07:00:00'),
                        ),
                        array(),
                    ),
                    array(
                        array('override', null, array(), '2016-03-10 06:00:00', 4, $groupId),
                        array(
                            'title' => array('deswvdswg'),
                            'description' => array(null),
                            'location' => array(null),
                            'status' => array(null),
                            'date_start' => array('2016-03-10 06:00:00'),
                            'date_end' => array('2016-03-10 07:00:00'),
                        ),
                        array(),
                    ),
                ),
                'groupId' => $groupId,
            ),
            'Change date time start events' => array(
                'participants_links' => $participants_links,
                'before' => static::getSourceIcsFile('ChangeDateTimeStartEvents.before'),
                'after' => static::getSourceIcsFile('ChangeDateTimeStartEvents.after'),
                'expected' => array(
                    array(
                        array('update', null, array(), '2016-03-13 08:00:00', null, $groupId),
                        array(
                            'date_start' => array('2016-03-13 08:00:00', '2016-03-13 06:00:00'),
                            'date_end' => array('2016-03-13 09:00:00', '2016-03-13 07:00:00'),
                        ),
                        array(),
                    ),
                    array(
                        array('update', null, array(), '2016-03-14 08:00:00', 1, $groupId),
                        array(
                            'date_start' => array('2016-03-14 08:00:00'),
                            'date_end' => array('2016-03-14 09:00:00'),
                            'title' => array('Test with Time'),
                            'description' => array(null),
                            'location' => array(null),
                            'status' => array(null),
                        ),
                        array(),
                    ),
                    array(
                        array('update', null, array(), '2016-03-15 08:00:00', 2, $groupId),
                        array(
                            'date_start' => array('2016-03-15 08:00:00'),
                            'date_end' => array('2016-03-15 09:00:00'),
                            'title' => array('Test with Time'),
                            'description' => array(null),
                            'location' => array(null),
                            'status' => array(null),
                        ),
                        array(),
                    ),
                ),
                'groupId' => $groupId,
            ),

            'Change until date' => array(
                'participants_links' => $participants_links,
                'before' => static::getSourceIcsFile('ChangeUntilDate.before'),
                'after' => static::getSourceIcsFile('ChangeUntilDate.after'),
                'expected' => array(
                    array(
                        array('update', null, array(), '2016-03-14 06:00:00', null, $groupId),
                        array(
                            'date_start' => array('2016-03-14 06:00:00', '2016-03-13 06:00:00'),
                            'date_end' => array('2016-03-14 07:00:00', '2016-03-13 07:00:00'),
                        ),
                        array(),
                    ),
                    array(
                        array('update', null, array(), '2016-03-15 06:00:00', 1, $groupId),
                        array(
                            'date_start' => array('2016-03-15 06:00:00'),
                            'date_end' => array('2016-03-15 07:00:00'),
                            'title' => array('until the end of the week'),
                            'description' => array(null),
                            'location' => array(null),
                            'status' => array(null),
                        ),
                        array(),
                    ),
                    array(
                        array('update', null, array(), '2016-03-16 06:00:00', 2, $groupId),
                        array(
                            'date_start' => array('2016-03-16 06:00:00'),
                            'date_end' => array('2016-03-16 07:00:00'),
                            'title' => array('until the end of the week'),
                            'description' => array(null),
                            'location' => array(null),
                            'status' => array(null),
                        ),
                        array(),
                    ),
                    array(
                        array('update', null, array(), '2016-03-17 06:00:00', 3, $groupId),
                        array(
                            'date_start' => array('2016-03-17 06:00:00'),
                            'date_end' => array('2016-03-17 07:00:00'),
                            'title' => array('until the end of the week'),
                            'description' => array(null),
                            'location' => array(null),
                            'status' => array(null),
                        ),
                        array(),
                    ),
                    array(
                        array('update', null, array(), '2016-03-18 06:00:00', 4, $groupId),
                        array(
                            'date_start' => array('2016-03-18 06:00:00'),
                            'date_end' => array('2016-03-18 07:00:00'),
                            'title' => array('until the end of the week'),
                            'description' => array(null),
                            'location' => array(null),
                            'status' => array(null),
                        ),
                        array(),
                    ),
                    array(
                        array('update', null, array(), '2016-03-19 06:00:00', 5, $groupId),
                        array(
                            'date_start' => array('2016-03-19 06:00:00'),
                            'date_end' => array('2016-03-19 07:00:00'),
                            'title' => array('until the end of the week'),
                            'description' => array(null),
                            'location' => array(null),
                            'status' => array(null),
                        ),
                        array(),
                    ),
                ),
                'groupId' => $groupId,
            ),
        );
    }

    /**
     * Checking the data preparation for imports.
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Adapter\AdapterAbstract::prepareForImport
     * @dataProvider prepareForImportProvider
     * @param string $participantsLinks
     * @param string $before
     * @param string $after
     * @param array $expected
     * @param string $groupId
     */
    public function testPrepareForImport($participantsLinks, $before, $after, array $expected, $groupId)
    {
        $before = preg_replace('/\n */', "\n", trim($before));
        $after = preg_replace('/\n */', "\n", trim($after));

        /** @var \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings|\PHPUnit_Framework_MockObject_MockObject $mockAdapter */
        $mockAdapter = $this->getMock('\Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings', array('createGroupId'));
        $mockAdapter->method('createGroupId')->willReturn($groupId);

        $collection = new \CalDavEventCollection();
        $collection->setData($after);
        $collection->participants_links = $participantsLinks;

        $actual = $mockAdapter->prepareForImport($collection, array('update', $before));
        $this->assertEquals($expected, $actual);
    }
}
