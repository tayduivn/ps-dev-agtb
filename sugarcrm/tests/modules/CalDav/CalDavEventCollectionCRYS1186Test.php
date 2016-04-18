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

namespace Sugarcrm\SugarcrmTests\modules\CalDav;

use CalDavEventCollection;

class CalDavEventCollectionCRYS1186Test extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var CalDavEventCollection */
    protected $collection = null;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->collection = new CalDavEventCollection();
    }

    /**
     * Data provider for testGetDiffStructure.
     *
     * @see CalDavEventCollectionCRYS1186Test::testGetDiffStructure
     * @return array
     */
    public static function getDiffStructureProvider()
    {
        return array(
            'wasBaseBecameCustom' => array(
                'before' => '
                    BEGIN:VCALENDAR
                    VERSION:2.0
                    PRODID:-//Apple Inc.//Mac OS X 10.11.2//EN
                    CALSCALE:GREGORIAN
                    BEGIN:VTIMEZONE
                    TZID:Europe/Minsk
                    BEGIN:DAYLIGHT
                    TZOFFSETFROM:+0200
                    RRULE:FREQ=YEARLY;UNTIL=20100328T000000Z;BYMONTH=3;BYDAY=-1SU
                    DTSTART:19930328T020000
                    TZNAME:GMT+3
                    TZOFFSETTO:+0300
                    END:DAYLIGHT
                    BEGIN:STANDARD
                    TZOFFSETFROM:+0200
                    DTSTART:20110327T020000
                    TZNAME:GMT+3
                    TZOFFSETTO:+0300
                    RDATE:20110327T020000
                    END:STANDARD
                    END:VTIMEZONE
                    BEGIN:VEVENT
                    CREATED:20160114T145444Z
                    UID:2A5C0463-3F6A-4BBB-8D79-7BB4CA964910
                    RRULE:FREQ=DAILY;INTERVAL=1;COUNT=5
                    DTEND;TZID=Europe/Minsk:20160111T200000
                    TRANSP:OPAQUE
                    X-APPLE-TRAVEL-ADVISORY-BEHAVIOR:AUTOMATIC
                    SUMMARY:Custom Event
                    DTSTART;TZID=Europe/Minsk:20160111T190000
                    DTSTAMP:20160114T145504Z
                    SEQUENCE:0
                    END:VEVENT
                    END:VCALENDAR
                ',
                'after' => '
                    BEGIN:VCALENDAR
                    VERSION:2.0
                    PRODID:-//Apple Inc.//Mac OS X 10.11.2//EN
                    CALSCALE:GREGORIAN
                    BEGIN:VTIMEZONE
                    TZID:Europe/Minsk
                    BEGIN:DAYLIGHT
                    TZOFFSETFROM:+0200
                    RRULE:FREQ=YEARLY;UNTIL=20100328T000000Z;BYMONTH=3;BYDAY=-1SU
                    DTSTART:19930328T020000
                    TZNAME:GMT+3
                    TZOFFSETTO:+0300
                    END:DAYLIGHT
                    BEGIN:STANDARD
                    TZOFFSETFROM:+0200
                    DTSTART:20110327T020000
                    TZNAME:GMT+3
                    TZOFFSETTO:+0300
                    RDATE:20110327T020000
                    END:STANDARD
                    END:VTIMEZONE
                    BEGIN:VEVENT
                    CREATED:20160114T145444Z
                    UID:2A5C0463-3F6A-4BBB-8D79-7BB4CA964910
                    RRULE:FREQ=DAILY;INTERVAL=1;COUNT=5
                    DTEND;TZID=Europe/Minsk:20160111T200000
                    TRANSP:OPAQUE
                    SUMMARY:Custom Event
                    DTSTART;TZID=Europe/Minsk:20160111T190000
                    DTSTAMP:20160114T145504Z
                    X-APPLE-TRAVEL-ADVISORY-BEHAVIOR:AUTOMATIC
                    SEQUENCE:0
                    END:VEVENT
                    BEGIN:VEVENT
                    CREATED:20160114T145444Z
                    UID:2A5C0463-3F6A-4BBB-8D79-7BB4CA964910
                    DTEND;TZID=Europe/Minsk:20160112T200000
                    TRANSP:OPAQUE
                    X-APPLE-TRAVEL-ADVISORY-BEHAVIOR:AUTOMATIC
                    SUMMARY:Custom Event 1
                    DTSTART;TZID=Europe/Minsk:20160112T190000
                    DTSTAMP:20160114T145556Z
                    SEQUENCE:0
                    RECURRENCE-ID;TZID=Europe/Minsk:20160112T190000
                    END:VEVENT
                    END:VCALENDAR
                ',
                'expected' => array(
                    'children' => array(
                        '2016-01-12 16:00:00' => array(
                            'update',
                            array('title' => array('Custom Event 1', 'Custom Event')),
                            array(),
                        ),
                    ),
                ),
            ),
            'wasCustomBecameBase' => array(
                'before' => '
                    BEGIN:VCALENDAR
                    VERSION:2.0
                    PRODID:-//Apple Inc.//Mac OS X 10.11.2//EN
                    CALSCALE:GREGORIAN
                    BEGIN:VTIMEZONE
                    TZID:Europe/Minsk
                    BEGIN:DAYLIGHT
                    TZOFFSETFROM:+0200
                    RRULE:FREQ=YEARLY;UNTIL=20100328T000000Z;BYMONTH=3;BYDAY=-1SU
                    DTSTART:19930328T020000
                    TZNAME:GMT+3
                    TZOFFSETTO:+0300
                    END:DAYLIGHT
                    BEGIN:STANDARD
                    TZOFFSETFROM:+0200
                    DTSTART:20110327T020000
                    TZNAME:GMT+3
                    TZOFFSETTO:+0300
                    RDATE:20110327T020000
                    END:STANDARD
                    END:VTIMEZONE
                    BEGIN:VEVENT
                    CREATED:20160114T145444Z
                    UID:2A5C0463-3F6A-4BBB-8D79-7BB4CA964910
                    RRULE:FREQ=DAILY;INTERVAL=1;COUNT=5
                    DTEND;TZID=Europe/Minsk:20160111T200000
                    TRANSP:OPAQUE
                    SUMMARY:Custom Event
                    DTSTART;TZID=Europe/Minsk:20160111T190000
                    DTSTAMP:20160114T145504Z
                    X-APPLE-TRAVEL-ADVISORY-BEHAVIOR:AUTOMATIC
                    SEQUENCE:0
                    END:VEVENT
                    BEGIN:VEVENT
                    CREATED:20160114T145444Z
                    UID:2A5C0463-3F6A-4BBB-8D79-7BB4CA964910
                    DTEND;TZID=Europe/Minsk:20160112T200000
                    TRANSP:OPAQUE
                    X-APPLE-TRAVEL-ADVISORY-BEHAVIOR:AUTOMATIC
                    SUMMARY:Custom Event 1
                    DTSTART;TZID=Europe/Minsk:20160112T190000
                    DTSTAMP:20160114T145556Z
                    SEQUENCE:0
                    RECURRENCE-ID;TZID=Europe/Minsk:20160112T190000
                    END:VEVENT
                    END:VCALENDAR
                ',
                'after' => '
                    BEGIN:VCALENDAR
                    VERSION:2.0
                    PRODID:-//Apple Inc.//Mac OS X 10.11.2//EN
                    CALSCALE:GREGORIAN
                    BEGIN:VTIMEZONE
                    TZID:Europe/Minsk
                    BEGIN:DAYLIGHT
                    TZOFFSETFROM:+0200
                    RRULE:FREQ=YEARLY;UNTIL=20100328T000000Z;BYMONTH=3;BYDAY=-1SU
                    DTSTART:19930328T020000
                    TZNAME:GMT+3
                    TZOFFSETTO:+0300
                    END:DAYLIGHT
                    BEGIN:STANDARD
                    TZOFFSETFROM:+0200
                    DTSTART:20110327T020000
                    TZNAME:GMT+3
                    TZOFFSETTO:+0300
                    RDATE:20110327T020000
                    END:STANDARD
                    END:VTIMEZONE
                    BEGIN:VEVENT
                    CREATED:20160114T145444Z
                    UID:2A5C0463-3F6A-4BBB-8D79-7BB4CA964910
                    RRULE:FREQ=DAILY;INTERVAL=1;COUNT=5
                    DTEND;TZID=Europe/Minsk:20160111T200000
                    TRANSP:OPAQUE
                    X-APPLE-TRAVEL-ADVISORY-BEHAVIOR:AUTOMATIC
                    SUMMARY:Custom Event
                    DTSTART;TZID=Europe/Minsk:20160111T190000
                    DTSTAMP:20160114T145504Z
                    SEQUENCE:0
                    END:VEVENT
                    END:VCALENDAR
                ',
                'expected' => array(
                    'children' => array(
                        '2016-01-12 16:00:00' => array(
                            'restore',
                            array(
                                'title' => array('Custom Event', null),
                                'description' => array(null, null),
                                'location' => array(null, null),
                                'status' => array(null, null),
                                'date_start' => array('2016-01-12 16:00:00', null),
                                'date_end' => array('2016-01-12 17:00:00', null),
                            ),
                            array(),
                        ),
                    ),
                ),
            ),
            'wasBaseBecameDeleted' => array(
                'before' => '
                    BEGIN:VCALENDAR
                    VERSION:2.0
                    PRODID:-//Apple Inc.//Mac OS X 10.11.2//EN
                    CALSCALE:GREGORIAN
                    BEGIN:VTIMEZONE
                    TZID:Europe/Minsk
                    BEGIN:DAYLIGHT
                    TZOFFSETFROM:+0200
                    RRULE:FREQ=YEARLY;UNTIL=20100328T000000Z;BYMONTH=3;BYDAY=-1SU
                    DTSTART:19930328T020000
                    TZNAME:GMT+3
                    TZOFFSETTO:+0300
                    END:DAYLIGHT
                    BEGIN:STANDARD
                    TZOFFSETFROM:+0200
                    DTSTART:20110327T020000
                    TZNAME:GMT+3
                    TZOFFSETTO:+0300
                    RDATE:20110327T020000
                    END:STANDARD
                    END:VTIMEZONE
                    BEGIN:VEVENT
                    CREATED:20160114T145444Z
                    UID:2A5C0463-3F6A-4BBB-8D79-7BB4CA964910
                    RRULE:FREQ=DAILY;INTERVAL=1;COUNT=5
                    DTEND;TZID=Europe/Minsk:20160111T200000
                    TRANSP:OPAQUE
                    X-APPLE-TRAVEL-ADVISORY-BEHAVIOR:AUTOMATIC
                    SUMMARY:Custom Event
                    DTSTART;TZID=Europe/Minsk:20160111T190000
                    DTSTAMP:20160114T145504Z
                    SEQUENCE:0
                    END:VEVENT
                    END:VCALENDAR
                ',
                'after' => '
                    BEGIN:VCALENDAR
                    VERSION:2.0
                    PRODID:-//Apple Inc.//Mac OS X 10.11.2//EN
                    CALSCALE:GREGORIAN
                    BEGIN:VTIMEZONE
                    TZID:Europe/Minsk
                    BEGIN:DAYLIGHT
                    TZOFFSETFROM:+0200
                    RRULE:FREQ=YEARLY;UNTIL=20100328T000000Z;BYMONTH=3;BYDAY=-1SU
                    DTSTART:19930328T020000
                    TZNAME:GMT+3
                    TZOFFSETTO:+0300
                    END:DAYLIGHT
                    BEGIN:STANDARD
                    TZOFFSETFROM:+0200
                    DTSTART:20110327T020000
                    TZNAME:GMT+3
                    TZOFFSETTO:+0300
                    RDATE:20110327T020000
                    END:STANDARD
                    END:VTIMEZONE
                    BEGIN:VEVENT
                    CREATED:20160114T145444Z
                    UID:2A5C0463-3F6A-4BBB-8D79-7BB4CA964910
                    RRULE:FREQ=DAILY;INTERVAL=1;COUNT=5
                    DTEND;TZID=Europe/Minsk:20160111T200000
                    EXDATE;TZID=Europe/Minsk:20160112T190000
                    TRANSP:OPAQUE
                    SUMMARY:Custom Event
                    DTSTART;TZID=Europe/Minsk:20160111T190000
                    DTSTAMP:20160114T145504Z
                    X-APPLE-TRAVEL-ADVISORY-BEHAVIOR:AUTOMATIC
                    SEQUENCE:0
                    END:VEVENT
                    END:VCALENDAR
                ',
                'expected' => array(
                    'children' => array(
                        '2016-01-12 16:00:00' => array(
                            'delete',
                            array(),
                            array(),
                        ),
                    ),
                ),
            ),
            'wasDeletedBecameBase' => array(
                'before' => '
                    BEGIN:VCALENDAR
                    VERSION:2.0
                    PRODID:-//Apple Inc.//Mac OS X 10.11.2//EN
                    CALSCALE:GREGORIAN
                    BEGIN:VTIMEZONE
                    TZID:Europe/Minsk
                    BEGIN:DAYLIGHT
                    TZOFFSETFROM:+0200
                    RRULE:FREQ=YEARLY;UNTIL=20100328T000000Z;BYMONTH=3;BYDAY=-1SU
                    DTSTART:19930328T020000
                    TZNAME:GMT+3
                    TZOFFSETTO:+0300
                    END:DAYLIGHT
                    BEGIN:STANDARD
                    TZOFFSETFROM:+0200
                    DTSTART:20110327T020000
                    TZNAME:GMT+3
                    TZOFFSETTO:+0300
                    RDATE:20110327T020000
                    END:STANDARD
                    END:VTIMEZONE
                    BEGIN:VEVENT
                    CREATED:20160114T145444Z
                    UID:2A5C0463-3F6A-4BBB-8D79-7BB4CA964910
                    RRULE:FREQ=DAILY;INTERVAL=1;COUNT=5
                    DTEND;TZID=Europe/Minsk:20160111T200000
                    EXDATE;TZID=Europe/Minsk:20160112T190000
                    TRANSP:OPAQUE
                    SUMMARY:Custom Event
                    DTSTART;TZID=Europe/Minsk:20160111T190000
                    DTSTAMP:20160114T145504Z
                    X-APPLE-TRAVEL-ADVISORY-BEHAVIOR:AUTOMATIC
                    SEQUENCE:0
                    END:VEVENT
                    END:VCALENDAR
                ',
                'after' => '
                    BEGIN:VCALENDAR
                    VERSION:2.0
                    PRODID:-//Apple Inc.//Mac OS X 10.11.2//EN
                    CALSCALE:GREGORIAN
                    BEGIN:VTIMEZONE
                    TZID:Europe/Minsk
                    BEGIN:DAYLIGHT
                    TZOFFSETFROM:+0200
                    RRULE:FREQ=YEARLY;UNTIL=20100328T000000Z;BYMONTH=3;BYDAY=-1SU
                    DTSTART:19930328T020000
                    TZNAME:GMT+3
                    TZOFFSETTO:+0300
                    END:DAYLIGHT
                    BEGIN:STANDARD
                    TZOFFSETFROM:+0200
                    DTSTART:20110327T020000
                    TZNAME:GMT+3
                    TZOFFSETTO:+0300
                    RDATE:20110327T020000
                    END:STANDARD
                    END:VTIMEZONE
                    BEGIN:VEVENT
                    CREATED:20160114T145444Z
                    UID:2A5C0463-3F6A-4BBB-8D79-7BB4CA964910
                    RRULE:FREQ=DAILY;INTERVAL=1;COUNT=5
                    DTEND;TZID=Europe/Minsk:20160111T200000
                    TRANSP:OPAQUE
                    X-APPLE-TRAVEL-ADVISORY-BEHAVIOR:AUTOMATIC
                    SUMMARY:Custom Event
                    DTSTART;TZID=Europe/Minsk:20160111T190000
                    DTSTAMP:20160114T145504Z
                    SEQUENCE:0
                    END:VEVENT
                    END:VCALENDAR
                ',
                'expected' => array(
                    'children' => array(
                        '2016-01-12 16:00:00' => array(
                            'restore',
                            array(
                                'title' => array('Custom Event', null),
                                'description' => array(null, null),
                                'location' => array(null, null),
                                'status' => array(null, null),
                                'date_start' => array('2016-01-12 16:00:00', null),
                                'date_end' => array('2016-01-12 17:00:00', null),
                            ),
                            array(),
                        ),
                    ),
                ),
            ),
            'wasCustomBecameDeleted' => array(
                'before' => '
                    BEGIN:VCALENDAR
                    VERSION:2.0
                    PRODID:-//Apple Inc.//Mac OS X 10.11.2//EN
                    CALSCALE:GREGORIAN
                    BEGIN:VTIMEZONE
                    TZID:Europe/Minsk
                    BEGIN:DAYLIGHT
                    TZOFFSETFROM:+0200
                    RRULE:FREQ=YEARLY;UNTIL=20100328T000000Z;BYMONTH=3;BYDAY=-1SU
                    DTSTART:19930328T020000
                    TZNAME:GMT+3
                    TZOFFSETTO:+0300
                    END:DAYLIGHT
                    BEGIN:STANDARD
                    TZOFFSETFROM:+0200
                    DTSTART:20110327T020000
                    TZNAME:GMT+3
                    TZOFFSETTO:+0300
                    RDATE:20110327T020000
                    END:STANDARD
                    END:VTIMEZONE
                    BEGIN:VEVENT
                    CREATED:20160114T145444Z
                    UID:2A5C0463-3F6A-4BBB-8D79-7BB4CA964910
                    RRULE:FREQ=DAILY;INTERVAL=1;COUNT=5
                    DTEND;TZID=Europe/Minsk:20160111T200000
                    TRANSP:OPAQUE
                    SUMMARY:Custom Event
                    DTSTART;TZID=Europe/Minsk:20160111T190000
                    DTSTAMP:20160114T145504Z
                    X-APPLE-TRAVEL-ADVISORY-BEHAVIOR:AUTOMATIC
                    SEQUENCE:0
                    END:VEVENT
                    BEGIN:VEVENT
                    CREATED:20160114T145444Z
                    UID:2A5C0463-3F6A-4BBB-8D79-7BB4CA964910
                    DTEND;TZID=Europe/Minsk:20160112T200000
                    TRANSP:OPAQUE
                    X-APPLE-TRAVEL-ADVISORY-BEHAVIOR:AUTOMATIC
                    SUMMARY:Custom Event 1
                    DTSTART;TZID=Europe/Minsk:20160112T190000
                    DTSTAMP:20160114T145817Z
                    SEQUENCE:0
                    RECURRENCE-ID;TZID=Europe/Minsk:20160112T190000
                    END:VEVENT
                    END:VCALENDAR
                ',
                'after' => '
                    BEGIN:VCALENDAR
                    VERSION:2.0
                    PRODID:-//Apple Inc.//Mac OS X 10.11.2//EN
                    CALSCALE:GREGORIAN
                    BEGIN:VTIMEZONE
                    TZID:Europe/Minsk
                    BEGIN:DAYLIGHT
                    TZOFFSETFROM:+0200
                    RRULE:FREQ=YEARLY;UNTIL=20100328T000000Z;BYMONTH=3;BYDAY=-1SU
                    DTSTART:19930328T020000
                    TZNAME:GMT+3
                    TZOFFSETTO:+0300
                    END:DAYLIGHT
                    BEGIN:STANDARD
                    TZOFFSETFROM:+0200
                    DTSTART:20110327T020000
                    TZNAME:GMT+3
                    TZOFFSETTO:+0300
                    RDATE:20110327T020000
                    END:STANDARD
                    END:VTIMEZONE
                    BEGIN:VEVENT
                    CREATED:20160114T145444Z
                    UID:2A5C0463-3F6A-4BBB-8D79-7BB4CA964910
                    RRULE:FREQ=DAILY;INTERVAL=1;COUNT=5
                    DTEND;TZID=Europe/Minsk:20160111T200000
                    EXDATE;TZID=Europe/Minsk:20160112T190000
                    TRANSP:OPAQUE
                    SUMMARY:Custom Event
                    DTSTART;TZID=Europe/Minsk:20160111T190000
                    DTSTAMP:20160114T145504Z
                    X-APPLE-TRAVEL-ADVISORY-BEHAVIOR:AUTOMATIC
                    SEQUENCE:0
                    END:VEVENT
                    END:VCALENDAR
                ',
                'expected' => array(
                    'children' => array(
                        '2016-01-12 16:00:00' => array(
                            'delete',
                            array(),
                            array(),
                        ),
                    ),
                ),
            ),
            'wasDeletedBecameCustom' => array(
                'before' => '
                    BEGIN:VCALENDAR
                    VERSION:2.0
                    PRODID:-//Apple Inc.//Mac OS X 10.11.2//EN
                    CALSCALE:GREGORIAN
                    BEGIN:VTIMEZONE
                    TZID:Europe/Minsk
                    BEGIN:DAYLIGHT
                    TZOFFSETFROM:+0200
                    RRULE:FREQ=YEARLY;UNTIL=20100328T000000Z;BYMONTH=3;BYDAY=-1SU
                    DTSTART:19930328T020000
                    TZNAME:GMT+3
                    TZOFFSETTO:+0300
                    END:DAYLIGHT
                    BEGIN:STANDARD
                    TZOFFSETFROM:+0200
                    DTSTART:20110327T020000
                    TZNAME:GMT+3
                    TZOFFSETTO:+0300
                    RDATE:20110327T020000
                    END:STANDARD
                    END:VTIMEZONE
                    BEGIN:VEVENT
                    CREATED:20160114T145444Z
                    UID:2A5C0463-3F6A-4BBB-8D79-7BB4CA964910
                    RRULE:FREQ=DAILY;INTERVAL=1;COUNT=5
                    DTEND;TZID=Europe/Minsk:20160111T200000
                    EXDATE;TZID=Europe/Minsk:20160112T190000
                    TRANSP:OPAQUE
                    SUMMARY:Custom Event
                    DTSTART;TZID=Europe/Minsk:20160111T190000
                    DTSTAMP:20160114T145504Z
                    X-APPLE-TRAVEL-ADVISORY-BEHAVIOR:AUTOMATIC
                    SEQUENCE:0
                    END:VEVENT
                    END:VCALENDAR
                ',
                'after' => '
                    BEGIN:VCALENDAR
                    VERSION:2.0
                    PRODID:-//Apple Inc.//Mac OS X 10.11.2//EN
                    CALSCALE:GREGORIAN
                    BEGIN:VTIMEZONE
                    TZID:Europe/Minsk
                    BEGIN:DAYLIGHT
                    TZOFFSETFROM:+0200
                    RRULE:FREQ=YEARLY;UNTIL=20100328T000000Z;BYMONTH=3;BYDAY=-1SU
                    DTSTART:19930328T020000
                    TZNAME:GMT+3
                    TZOFFSETTO:+0300
                    END:DAYLIGHT
                    BEGIN:STANDARD
                    TZOFFSETFROM:+0200
                    DTSTART:20110327T020000
                    TZNAME:GMT+3
                    TZOFFSETTO:+0300
                    RDATE:20110327T020000
                    END:STANDARD
                    END:VTIMEZONE
                    BEGIN:VEVENT
                    CREATED:20160114T145444Z
                    UID:2A5C0463-3F6A-4BBB-8D79-7BB4CA964910
                    RRULE:FREQ=DAILY;INTERVAL=1;COUNT=5
                    DTEND;TZID=Europe/Minsk:20160111T200000
                    TRANSP:OPAQUE
                    SUMMARY:Custom Event
                    DTSTART;TZID=Europe/Minsk:20160111T190000
                    DTSTAMP:20160114T145504Z
                    X-APPLE-TRAVEL-ADVISORY-BEHAVIOR:AUTOMATIC
                    SEQUENCE:0
                    END:VEVENT
                    BEGIN:VEVENT
                    CREATED:20160114T145444Z
                    UID:2A5C0463-3F6A-4BBB-8D79-7BB4CA964910
                    DTEND;TZID=Europe/Minsk:20160112T200000
                    TRANSP:OPAQUE
                    X-APPLE-TRAVEL-ADVISORY-BEHAVIOR:AUTOMATIC
                    SUMMARY:Custom Event 1
                    DTSTART;TZID=Europe/Minsk:20160112T190000
                    DTSTAMP:20160114T145817Z
                    SEQUENCE:0
                    RECURRENCE-ID;TZID=Europe/Minsk:20160112T190000
                    END:VEVENT
                    END:VCALENDAR
                ',
                'expected' => array(
                    'children' => array(
                        '2016-01-12 16:00:00' => array(
                            'restore',
                            array(
                                'title' => array('Custom Event 1', null),
                                'description' => array(null, null),
                                'location' => array(null, null),
                                'status' => array(null, null),
                                'date_start' => array('2016-01-12 16:00:00', null),
                                'date_end' => array('2016-01-12 17:00:00', null),
                            ),
                            array(),
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * Covers correct calculation of difference between two events.
     *
     * @dataProvider getDiffStructureProvider
     * @covers CalDavEventCollection::getDiffStructure
     * @param string $before
     * @param string $after
     * @param mixed $expected
     */
    public function testGetDiffStructure($before, $after, $expected)
    {
        $before = preg_replace('/\n */', "\n", trim($before));
        $after = preg_replace('/\n */', "\n", trim($after));
        $this->collection->setData($after);
        $actual = $this->collection->getDiffStructure($before);
        $this->assertEquals($expected, $actual);
    }
}
