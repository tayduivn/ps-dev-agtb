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

require_once 'modules/Emails/EmailsHookHandler.php';

/**
 * @coversDefaultClass EmailsHookHandler
 */
class EmailsHookHandlerTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass()
    {
        SugarTestEmailUtilities::removeAllCreatedEmails();
        parent::tearDownAfterClass();
    }

    /**
     * @covers ::updateAttachmentVisibility
     * @covers Email::updateAttachmentVisibility
     * @covers Note::save
     */
    public function testUpdateAttachmentVisibility()
    {
        $teams = BeanFactory::getBean('TeamSets');
        $data = array(
            'state' => Email::STATE_ARCHIVED,
            'assigned_user_id' => $GLOBALS['current_user']->id,
            'team_id' => 'East',
            'team_set_id' => $teams->addTeams(array('East', 'West')),
            //BEGIN SUGARCRM flav=ent ONLY
            'team_set_selected_id' => 'East',
            //END SUGARCRM flav=ent ONLY
        );
        $email = SugarTestEmailUtilities::createEmail('', $data);
        $note1 = SugarTestNoteUtilities::createNote();
        $note2 = SugarTestNoteUtilities::createNote();

        $email->load_relationship('attachments');
        $email->attachments->add(array($note1, $note2));

        $this->assertEquals(
            $email->assigned_user_id,
            $note1->assigned_user_id,
            'note1.assigned_user_id does not match'
        );
        $this->assertEquals(
            $email->assigned_user_id,
            $note2->assigned_user_id,
            'note2.assigned_user_id does not match'
        );
        $this->assertEquals($email->team_set_id, $note1->team_set_id, 'note1.team_set_id does not match');
        $this->assertEquals($email->team_set_id, $note2->team_set_id, 'note2.team_set_id does not match');
        $this->assertEquals($email->team_id, $note1->team_id, 'note1.team_id does not match');
        $this->assertEquals($email->team_id, $note2->team_id, 'note2.team_id does not match');
        //BEGIN SUGARCRM flav=ent ONLY
        $this->assertEquals(
            $email->team_set_selected_id,
            $note1->team_set_selected_id,
            'note1.team_set_selected_id does not match'
        );
        $this->assertEquals(
            $email->team_set_selected_id,
            $note2->team_set_selected_id,
            'note2.team_set_selected_id does not match'
        );
        //END SUGARCRM flav=ent ONLY
    }
}
