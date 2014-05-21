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


/**
 * Bug #PAT-543
 * Email invitation language does not adjust
 *
 * @author bsitnikovski@sugarcrm.com
 * @ticket PAT-543
 */
class BugPAT543Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $current_user;
        $current_user = SugarTestUserUtilities::createAnonymousUser();
    }

    public function tearDown()
    {
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function meetingLanguagesProvider()
    {
        return array(
            array('en_us'),
            array('es_ES')
        );
    }

    /**
     * Test the functionality based on data provider
     *
     * @dataProvider meetingLanguagesProvider
     */
    public function testMeetingLanguage($lang)
    {
        global $current_user;

        $current_user->preferred_language = $lang;
        $bean = SugarTestMeetingUtilities::createMeeting();
        $tpl = $bean->getNotificationEmailTemplate();

        $htmltpl = file_get_contents(get_notify_template_file($lang));

        $this->assertEquals($tpl->filecontents, $htmltpl);
    }
}
