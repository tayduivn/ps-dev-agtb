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

use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\MeetingsAdapter\DataAdapter as MeetingsDataAdapter;

/**
 * Class AdapterCRYS1607Test
 * @covers Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Helper\AbstractAdapter
 */
class AdapterCRYS1607Test extends \Sugar_PHPUnit_Framework_TestCase
{
    public function verifyExportAfterImportProvider()
    {
        $beanMeeting = $this->getMock('\Meeting', array());
        $beanMeeting->id = create_guid();

        return array(
            'Create new empty event' => array(
                'importData' => array(
                    array(
                        'override',
                        '11b32c6b-0b8c-4b0f-ec20-570f9b0e3747',
                        array(),
                        '2016-04-25 06:00:00',
                        null,
                        '20731ec7-a97e-cad0-639e-570f9ba8a4a0'
                    ),
                    array(
                        'timezone' => array('Europe/Minsk', null),
                        'title' => array('New Event'),
                        'date_start' => array('2016-04-25 06:00:00'),
                        'date_end' => array('2016-04-25 07:00:00'),
                    ),
                    array(),
                    array(),
                ),
                'exportData' => array(
                    array('override', 'Meeting', $beanMeeting->id, null, null, '1'),
                    array(
                        'name' => array('New Event'),
                        'description' => array(''),
                        'location' => array(''),
                        'date_start' => array('2016-04-25 06:00:00'),
                        'date_end' => array('2016-04-25 07:00:00'),
                        'status' => array('Planned'),
                        'repeat_type' => array(''),
                        'repeat_interval' => array(null),
                        'repeat_dow' => array(''),
                        'repeat_until' => array(''),
                        'repeat_count' => array(''),
                        'repeat_selector' => array(''),
                        'repeat_days' => array(''),
                        'repeat_ordinal' => array(''),
                        'repeat_unit' => array(''),
                        'repeat_parent_id' => array(''),
                    ),
                    array()
                ),
                'bean' => $beanMeeting,
                'expected' => array(
                    array('update', 'Meeting', $beanMeeting->id, null, null, '1'),
                    array(
                        'status' => array('Planned'),
                    ),
                    array(),
                ),
            ),

            'Add recurring rule' => array(
                'importData' => array(
                    array(
                        'update',
                        '11b32c6b-0b8c-4b0f-ec20-570f9b0e3747',
                        array(),
                        '2016-04-25 06:00:00',
                        null,
                        'c00ae777-baad-7fde-a8bc-570f9e0e3f07'
                    ),
                    array(
                        'rrule_action' => 'added',
                        'rrule_frequency' => array('DAILY'),
                        'rrule_interval' => array('1'),
                        'rrule_count' => array('3'),
                        'rrule_until' => array(null),
                        'rrule_byday' => array(null),
                        'rrule_bymonthday' => array(null),
                        'rrule_bysetpos' => array(null),
                    ),
                    array(),
                    array(),
                ),
                'exportData' => array(
                    array('override', 'Meeting', $beanMeeting->id, null, array(
                        'repeat_type' => 'Daily',
                        'repeat_interval' => '1',
                        'repeat_count' => '3',
                        'repeat_unit' => '',
                        'repeat_dow' => '',
                        'repeat_selector' => 'None',
                        'repeat_days' => '',
                        'repeat_ordinal' => '',
                        'repeat_until' => '',
                    ), '1'),
                    array(
                        'repeat_type' => array('Daily'),
                        'repeat_count' => array('3'),
                        'repeat_selector' => array('None'),
                    ),
                    array()
                ),
                'bean' => $beanMeeting,
                'expected' => false,
            ),

            'Create child event' => array(
                'importData' => array(
                    array(
                        'override',
                        'a062603e-3779-1611-c6a1-570fa9d6a358',
                        array(),
                        '2016-04-19 06:00:00',
                        1,
                        '3a391def-340c-ed5c-ced9-570fa919d107'
                    ),
                    array(
                        'title' => array('Awasome event'),
                        'date_start' => array('2016-04-19 06:00:00'),
                        'date_end' => array('2016-04-19 07:00:00'),
                        'status' => array('Planned'),
                    ),
                    array(),
                    array(),
                ),
                'exportData' => array(
                    array('override', 'Meeting', $beanMeeting->id, 'd1d41ff0-d014-b421-fbcf-570fa9ce35f5', null, '1'),
                    array(
                        'name' => array('Awasome event'),
                        'description' => array(''),
                        'location' => array(''),
                        'date_start' => array('2016-04-19 06:00:00'),
                        'date_end' => array('2016-04-19 07:00:00'),
                        'status' => array('Planned'),
                        'repeat_parent_id' => array('d1d41ff0-d014-b421-fbcf-570fa9ce35f5'),
                    ),
                    array(),
                ),
                'bean' => $beanMeeting,
                'expected' => false,
            ),
        );
    }

    /**
     * Test verify data for export after import.
     *
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Helper\AbstractDataAdapter::verifyExportAfterImport
     * @dataProvider verifyExportAfterImportProvider
     * @param array $importData
     * @param array $exportData
     * @param \SugarBean $bean
     * @param array|bool $expected
     */
    public function testVerifyExportAfterImport($importData, $exportData, $bean, $expected)
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|MeetingsDataAdapter $mockAdapter */
        $mockAdapter = $this->getMock('Sugarcrm\Sugarcrm\Dav\Cal\Adapter\MeetingsAdapter\DataAdapter', null);
        $actual = $mockAdapter->verifyExportAfterImport($importData, $exportData, $bean);
        $this->assertEquals($expected, $actual);
    }
}
