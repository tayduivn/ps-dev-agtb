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

        $tasks = $email->get_linked_beans('tasks', 'Task');
        $this->assertCount(0, $tasks, 'Should not be linked yet');

        $script = $this->upgrader->getScript('post', '4_SynchronizeTasksRelationshipToEmailsBeansTable');
        $script->db = $GLOBALS['db'];
        $script->version = '8.0.0';
        $script->from_version = '7.9.0.0';
        $script->run();

        $email->tasks->resetLoaded();
        $tasks = $email->get_linked_beans('tasks', 'Task');
        $this->assertCount(1, $tasks, 'Should be linked now');
        $this->assertSame($task->id, $tasks[0]->id, 'The IDs do not match');
    }

    /**
     * @covers ::getEmails
     */
    public function testGetEmails()
    {
        $db = DBManagerFactory::getInstance();

        $task = SugarTestTaskUtilities::createTask();
        $seed = BeanFactory::newBean('Emails');

        // Email is linked to its parent. Should not be returned.
        $email1 = [
            'id' => Uuid::uuid1(),
            'state' => 'Archived',
            'name' => 'email1',
            'parent_type' => 'Tasks',
            'parent_id' => $task->id,
        ];
        $db->insertParams('emails', $seed->field_defs, $email1);
        SugarTestEmailUtilities::setCreatedEmail($email1['id']);
        $this->linkEmailToBean($email1);

        // Email is linked to a different task. Should be returned.
        $email2 = [
            'id' => Uuid::uuid1(),
            'state' => 'Archived',
            'name' => 'email2',
            'parent_type' => 'Tasks',
            'parent_id' => $task->id,
        ];
        $db->insertParams('emails', $seed->field_defs, $email2);
        SugarTestEmailUtilities::setCreatedEmail($email2['id']);
        $this->linkEmailToBean(array_merge($email2, ['parent_id' => Uuid::uuid1()]));

        // Row in emails_beans is invalid; parent_id is empty. Should be returned.
        $email3 = [
            'id' => Uuid::uuid1(),
            'state' => 'Archived',
            'name' => 'email3',
            'parent_type' => 'Tasks',
            'parent_id' => $task->id,
        ];
        $db->insertParams('emails', $seed->field_defs, $email3);
        SugarTestEmailUtilities::setCreatedEmail($email3['id']);
        $this->linkEmailToBean(array_merge($email3, ['parent_id' => '']));

        // Row in emails_beans is deleted. Should be returned.
        $email4 = [
            'id' => Uuid::uuid1(),
            'state' => 'Archived',
            'name' => 'email4',
            'parent_type' => 'Tasks',
            'parent_id' => $task->id,
        ];
        $db->insertParams('emails', $seed->field_defs, $email4);
        SugarTestEmailUtilities::setCreatedEmail($email4['id']);
        $this->linkEmailToBean(array_merge($email4, ['deleted' => 1]));

        // Email is linked to its parent and a different task. Should not be returned.
        $email5 = [
            'id' => Uuid::uuid1(),
            'state' => 'Archived',
            'name' => 'email5',
            'parent_type' => 'Tasks',
            'parent_id' => $task->id,
        ];
        $db->insertParams('emails', $seed->field_defs, $email5);
        SugarTestEmailUtilities::setCreatedEmail($email5['id']);
        $this->linkEmailToBean($email5);
        $this->linkEmailToBean(array_merge($email5, ['parent_id' => Uuid::uuid1()]));

        // Email is not linked to its parent. Should be returned.
        $email6 = [
            'id' => Uuid::uuid1(),
            'state' => 'Archived',
            'name' => 'email6',
            'parent_type' => 'Tasks',
            'parent_id' => $task->id,
        ];
        $db->insertParams('emails', $seed->field_defs, $email6);
        SugarTestEmailUtilities::setCreatedEmail($email6['id']);

        // Email doesn't have a parent because parent_id is empty. Should not be returned.
        $email7 = [
            'id' => Uuid::uuid1(),
            'state' => 'Archived',
            'name' => 'email7',
            'parent_type' => 'Tasks',
            'parent_id' => '',
        ];
        $db->insertParams('emails', $seed->field_defs, $email7);
        SugarTestEmailUtilities::setCreatedEmail($email7['id']);

        // Email doesn't have a parent. Should not be returned.
        $email7 = [
            'id' => Uuid::uuid1(),
            'state' => 'Archived',
            'name' => 'email7',
            'parent_type' => '',
            'parent_id' => '',
        ];
        $db->insertParams('emails', $seed->field_defs, $email7);
        SugarTestEmailUtilities::setCreatedEmail($email7['id']);

        $script = $this->createPartialMock(
            'SugarUpgradeSynchronizeTasksRelationshipToEmailsBeansTable',
            ['log']
        );
        $script->db = $db;
        $script->version = '8.0.0';
        $script->from_version = '7.9.0.0';

        $emails = SugarTestReflection::callProtectedMethod($script, 'getEmails');

        $this->assertCount(4, $emails, 'Four emails should have been returned');
        $this->assertArrayHasKey($email2['id'], $emails, '$email2 should have been found');
        $this->assertArrayHasKey($email3['id'], $emails, '$email3 should have been found');
        $this->assertArrayHasKey($email4['id'], $emails, '$email4 should have been found');
        $this->assertArrayHasKey($email6['id'], $emails, '$email6 should have been found');
    }

    private function linkEmailToBean(array $row)
    {
        $db = DBManagerFactory::getInstance();

        $row = [
            $db->quoted(Uuid::uuid1()),
            $db->quoted($row['id']),
            $db->quoted($row['parent_type']),
            $db->quoted($row['parent_id']),
            $db->quoted(TimeDate::getInstance()->nowDb()),
            isset($row['deleted']) ? $row['deleted'] : 0,
        ];
        $row = '(' . implode(',', $row) . ')';

        $sql = "INSERT INTO emails_beans (id,email_id,bean_module,bean_id,date_modified,deleted) VALUES {$row}";
        $db->query($sql);
    }
}
