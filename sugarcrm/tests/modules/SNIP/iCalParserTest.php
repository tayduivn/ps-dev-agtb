<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/


require_once ('modules/SNIP/iCalParser.php');

/**
 * Tests SNIP's iCal Parser
 */
class iCalParserTest extends Sugar_PHPUnit_Framework_TestCase
{
    static protected $e;

    static public function setUpBeforeClass() {
        $meeting = SugarTestMeetingUtilities::createMeeting();

        // email with description that contains meeting id
        self::$e = SugarTestEmailUtilities::createEmail();
        self::$e->description = 'record=' . $meeting->id . "&gt";
        self::$e->save();
    }

    static public function tearDownAfterClass() {
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestEmailUtilities::removeAllCreatedEmails();
        // delete it in case it's created, outlook_id is from Bug53942Test.ics
        $GLOBALS['db']->query('delete from meetings where outlook_id='."'".'73fc8eef-bacc-4d7b-94eb-af2080437132'."'");
    }

    protected function getEmailCount() {
        return $GLOBALS['db']->getOne("select count(*) from meetings where deleted = 0");
    }

    /**
     * @ticket 66027
     */
    public function testForwardedEmailWithMeetingId()
    {
        $beforeCount = $this->getEmailCount();

        // to test createSugarEvents
        $ic = new iCalendar();
        $ic->parse(file_get_contents(dirname(__FILE__).'/Bug53942Test.ics'));
        // this should not create new meeting since meeting id is in email description
        $ic->createSugarEvents(self::$e);

        $afterCount = $this->getEmailCount();

        $this->assertEquals($beforeCount, $afterCount);
    }
}
