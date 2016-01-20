<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once 'modules/Calendar/CalendarUtils.php';
require_once 'modules/Meetings/Meeting.php';


class CalendarCRYS1322 extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @inheritdoc
     */
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        BeanFactory::setBeanClass('Meetings', 'MeetingCRYS1322');
        $GLOBALS['db'] = $this->getMock('MysqlManager', array('query', 'fetchByAssoc', 'insertParams'));
    }

    /**
     * @inheritdoc
     */
    public function tearDown()
    {
        BeanFactory::setBeanClass('Meetings', 'MeetingCRYS1322');
        $GLOBALS['db'] = DBManagerFactory::getInstance();
        SugarTestHelper::tearDown();
    }

    /**
     * Test save recurring
     *
     * @cover CalendarUtils::saveRecurring
     */
    public function testSaveRecurring()
    {
        $sqls = array();

        $GLOBALS['db']->expects($this->any())->method('query')
            ->will($this->returnCallback(function($sql) use (&$sqls) {
                $sqls[] = $sql;
                return '__result_query__';
            }));

        $GLOBALS['db']->expects($this->at(11))->method('fetchByAssoc')
            ->with($this->stringContains('__result_query__'), $this->isTrue())
            ->willReturn(array('addressee_id' => '_id_1'));

        $GLOBALS['db']->expects($this->once())->method('insertParams');


        $meeting = BeanFactory::getBean('Meetings', '__ID__');
        CalendarUtils::saveRecurring($meeting, array('2015-12-16 14:34'));

        $this->assertContains( "SELECT * FROM meetings_addresses WHERE deleted = 0 AND meeting_id = '__ID__'", $sqls );
    }
}

/**
 * Stub class for Meeting bean
 */
class MeetingCRYS1322 extends Meeting
{
    public function retrieve($id)
    {
        $this->populateFromRow(array(
            'name' => 'Meeting1102415055',
            'date_entered' => '2015-12-16 13:34:54',
            'date_modified' => '2015-12-16 13:34:54',
            'modified_user_id' => '531fc10a-78e6-157a-cced-567168676bd3',
            'created_by' => '_user_id_',
            'description' => null,
            'deleted' => '0',
            'location' => null,
            'duration_hours' => '0',
            'duration_minutes' => '15',
            'date_start' => '2015-12-16 13:34:54',
            'date_end' => '2015-12-16 13:49:54',
            'parent_type' => null,
            'status' => 'Planned',
            'type' => 'Sugar',
            'parent_id' => null,
            'reminder_time' => '-1',
            'email_reminder_time' => '-1',
            'email_reminder_sent' => '0',
            'sequence' => '0',
            'repeat_type' => null,
            'repeat_interval' => '1',
            'repeat_dow' => null,
            'repeat_until' => null,
            'repeat_count' => null,
            'repeat_parent_id' => null,
            'recurring_source' => null,
            'assigned_user_id' => '531fc10a-78e6-157a-cced-567168676bd3',
        ));

        $this->id = $id;
        $this->fetched_row = $this->toArray(true);
        return $this;
    }
}
