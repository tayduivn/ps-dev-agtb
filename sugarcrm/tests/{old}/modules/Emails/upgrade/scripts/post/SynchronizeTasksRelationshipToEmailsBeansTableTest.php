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

use Sugarcrm\Sugarcrm\Util\Uuid;

require_once 'tests/{old}/upgrade/UpgradeTestCase.php';
require_once 'modules/Emails/upgrade/scripts/post/4_SynchronizeTasksRelationshipToEmailsBeansTable.php';

/**
 * @coversDefaultClass SugarUpgradeSynchronizeTasksRelationshipToEmailsBeansTable
 */
class SynchronizeTasksRelationshipToEmailsBeansTableTest extends UpgradeTestCase
{
    protected function tearDown()
    {
        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestTaskUtilities::removeAllCreatedTasks();
        SugarTestContactUtilities::removeAllCreatedContacts();
        parent::tearDown();
    }

    /**
     * @covers ::run
     */
    public function testRun()
    {
        $task = SugarTestTaskUtilities::createTask();

        $email = BeanFactory::newBean('Emails');
        $email->id = Uuid::uuid1();

        // Insert an email into the database whose parent is $task. This email will be loaded by
        // SugarUpgradeSynchronizeTasksRelationshipToEmailsBeansTable::getEmails().
        DBManagerFactory::getInstance()->insertParams(
            'emails',
            $email->field_defs,
            [
                'id' => $email->id,
                'state' => 'Archived',
                'name' => 'foo',
                'parent_type' => 'Tasks',
                'parent_id' => $task->id,
            ]
        );
        SugarTestEmailUtilities::setCreatedEmail($email->id);

        $script = $this->upgrader->getScript('post', '4_SynchronizeTasksRelationshipToEmailsBeansTable');
        $script->db = $GLOBALS['db'];
        $script->version = '8.0.0';
        $script->from_version = '7.9.0.0';
        $script->run();

        $tasks = $email->get_linked_beans('tasks', 'Task');

        $this->assertCount(1, $tasks);
        $this->assertSame($task->id, $tasks[0]->id);
    }

    /**
     * @covers ::run
     */
    public function testRun_EmailSenderAndRecipientsAreUpgradedFrom79DuringUpgrade()
    {
        $contact = SugarTestContactUtilities::createContact();
        $task = SugarTestTaskUtilities::createTask();

        $email = BeanFactory::newBean('Emails');
        $email->id = Uuid::uuid1();

        // Insert an email into the database whose parent is $task. This email will be loaded by
        // SugarUpgradeSynchronizeTasksRelationshipToEmailsBeansTable::getEmails().
        DBManagerFactory::getInstance()->insertParams(
            'emails',
            $email->field_defs,
            [
                'id' => $email->id,
                'state' => 'Archived',
                'name' => 'foo',
                'parent_type' => 'Tasks',
                'parent_id' => $task->id,
            ]
        );
        SugarTestEmailUtilities::setCreatedEmail($email->id);

        // Insert an emails_text row for the email. This resembles the way data is stored in 7.9 when OPI archives
        // emails.
        $text = BeanFactory::newBean('EmailText');
        DBManagerFactory::getInstance()->insertParams(
            'emails_text',
            $text->field_defs,
            [
                'email_id' => $email->id,
                'from_addr' => "{$contact->name} <{$contact->email1}>",
                'to_addrs' => "{$GLOBALS['current_user']->name} <{$GLOBALS['current_user']->email1}>",
                'description' => 'test',
                'description_html' => '<p>test</p>',
            ]
        );

        $script = $this->upgrader->getScript('post', '4_SynchronizeTasksRelationshipToEmailsBeansTable');
        $script->db = $GLOBALS['db'];
        $script->version = '8.0.0';
        $script->from_version = '7.9.0.0';
        $script->run();

        // Verify that the email's sender and recipients were upgraded correctly when the email was retrieved as a
        // side-effect of the upgrade script.
        $email->retrieveEmailText();
        $this->assertSame($contact->email1, $email->from_addr_name);
        $this->assertSame($GLOBALS['current_user']->email1, $email->to_addrs_names);

        $email->retrieveEmailAddresses();
        $this->assertSame($contact->email1, $email->from_addr);
        $this->assertSame($GLOBALS['current_user']->email1, $email->to_addrs);
    }
}
