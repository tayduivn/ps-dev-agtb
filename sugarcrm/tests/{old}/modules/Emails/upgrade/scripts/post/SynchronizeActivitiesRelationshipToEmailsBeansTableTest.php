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

require_once 'modules/Emails/upgrade/scripts/post/4_SynchronizeActivitiesRelationshipToEmailsBeansTable.php';

/**
 * @coversDefaultClass SugarUpgradeSynchronizeActivitiesRelationshipToEmailsBeansTable
 */
class SynchronizeActivitiesRelationshipToEmailsBeansTableTest extends UpgradeTestCase
{
    protected function tearDown()
    {
        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestContactUtilities::removeAllCreatedContacts();
        parent::tearDown();
    }

    /**
     * @covers ::run
     */
    public function testRun()
    {
        $contact = SugarTestContactUtilities::createContact();
        $email = BeanFactory::newBean('Emails');
        $email->id = Uuid::uuid1();

        // Insert an email into the database whose parent is $contact. This email will be loaded by
        // SugarUpgradeSynchronizeActivitiesRelationshipToEmailsBeansTable::getEmails().
        DBManagerFactory::getInstance()->insertParams(
            'emails',
            $email->field_defs,
            [
                'id' => $email->id,
                'state' => 'Archived',
                'name' => 'foo',
                'parent_type' => 'Contacts',
                'parent_id' => $contact->id,
            ]
        );
        SugarTestEmailUtilities::setCreatedEmail($email->id);

        $contacts = $email->get_linked_beans('contacts', 'Contact');
        $this->assertCount(0, $contacts, 'Should not be linked yet');

        $script = $this->createPartialMock(
            'SugarUpgradeSynchronizeActivitiesRelationshipToEmailsBeansTable',
            [
                'getModulesWithActivitiesRelationship',
                'log',
            ]
        );
        $script->method('getModulesWithActivitiesRelationship')->willReturn([$contact->module_name]);
        $script->db = $GLOBALS['db'];
        $script->version = '8.0.0';
        $script->from_version = '7.9.0.0';
        $script->run();

        $email->contacts->resetLoaded();
        $contacts = $email->get_linked_beans('contacts', 'Contact');
        $this->assertCount(1, $contacts, 'Should be linked now');
        $this->assertSame($contact->id, $contacts[0]->id, 'The IDs do not match');
    }

    /**
     * @covers ::getEmails
     */
    public function testGetEmails()
    {
        $db = DBManagerFactory::getInstance();

        $contact = SugarTestContactUtilities::createContact();
        $seed = BeanFactory::newBean('Emails');

        // Email is linked to its parent. Should not be returned.
        $email1 = [
            'id' => Uuid::uuid1(),
            'state' => 'Archived',
            'name' => 'email1',
            'parent_type' => 'Contacts',
            'parent_id' => $contact->id,
        ];
        $db->insertParams('emails', $seed->field_defs, $email1);
        SugarTestEmailUtilities::setCreatedEmail($email1['id']);
        $this->linkEmailToBean($email1);

        // Email is linked to a different contact. Should be returned.
        $email2 = [
            'id' => Uuid::uuid1(),
            'state' => 'Archived',
            'name' => 'email2',
            'parent_type' => 'Contacts',
            'parent_id' => $contact->id,
        ];
        $db->insertParams('emails', $seed->field_defs, $email2);
        SugarTestEmailUtilities::setCreatedEmail($email2['id']);
        $this->linkEmailToBean(array_merge($email2, ['parent_id' => Uuid::uuid1()]));

        // Row in emails_beans is invalid; parent_id is empty. Should be returned.
        $email3 = [
            'id' => Uuid::uuid1(),
            'state' => 'Archived',
            'name' => 'email3',
            'parent_type' => 'Contacts',
            'parent_id' => $contact->id,
        ];
        $db->insertParams('emails', $seed->field_defs, $email3);
        SugarTestEmailUtilities::setCreatedEmail($email3['id']);
        $this->linkEmailToBean(array_merge($email3, ['parent_id' => '']));

        // Row in emails_beans is deleted. Should be returned.
        $email4 = [
            'id' => Uuid::uuid1(),
            'state' => 'Archived',
            'name' => 'email4',
            'parent_type' => 'Contacts',
            'parent_id' => $contact->id,
        ];
        $db->insertParams('emails', $seed->field_defs, $email4);
        SugarTestEmailUtilities::setCreatedEmail($email4['id']);
        $this->linkEmailToBean(array_merge($email4, ['deleted' => 1]));

        // Email is linked to its parent and a different contact. Should not be returned.
        $email5 = [
            'id' => Uuid::uuid1(),
            'state' => 'Archived',
            'name' => 'email5',
            'parent_type' => 'Contacts',
            'parent_id' => $contact->id,
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
            'parent_type' => 'Contacts',
            'parent_id' => $contact->id,
        ];
        $db->insertParams('emails', $seed->field_defs, $email6);
        SugarTestEmailUtilities::setCreatedEmail($email6['id']);

        // Email doesn't have a parent because parent_id is empty. Should not be returned.
        $email7 = [
            'id' => Uuid::uuid1(),
            'state' => 'Archived',
            'name' => 'email7',
            'parent_type' => 'Contacts',
            'parent_id' => '',
        ];
        $db->insertParams('emails', $seed->field_defs, $email7);
        SugarTestEmailUtilities::setCreatedEmail($email7['id']);

        // Email doesn't have a parent. Should not be returned.
        $email8 = [
            'id' => Uuid::uuid1(),
            'state' => 'Archived',
            'name' => 'email8',
            'parent_type' => '',
            'parent_id' => '',
        ];
        $db->insertParams('emails', $seed->field_defs, $email8);
        SugarTestEmailUtilities::setCreatedEmail($email8['id']);

        // Email doesn't have a parentType but has a parent_id. Should not be returned.
        $email9 = [
            'id' => Uuid::uuid1(),
            'state' => 'Archived',
            'name' => 'email9',
            'parent_type' => '',
            'parent_id' => $contact->id,
        ];
        $db->insertParams('emails', $seed->field_defs, $email9);
        SugarTestEmailUtilities::setCreatedEmail($email9['id']);

        $script = $this->createPartialMock(
            'SugarUpgradeSynchronizeActivitiesRelationshipToEmailsBeansTable',
            ['log']
        );
        $script->db = $db;
        $script->version = '8.0.0';
        $script->from_version = '7.9.0.0';

        $emails = SugarTestReflection::callProtectedMethod($script, 'getEmails', [['Contacts']]);

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
