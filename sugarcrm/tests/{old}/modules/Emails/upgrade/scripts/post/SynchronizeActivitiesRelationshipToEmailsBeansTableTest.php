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
    protected $parent;

    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('beanList');

        // Create a mock from a fake module.
        $this->parent = $this->createMock('SugarBean');
        $this->parent->module_name = 'SGR_Widgets';

        // Prepare BeanFactory with the fake module and bean.
        $parentClassName = get_class($this->parent);
        BeanFactory::setBeanClass($this->parent->module_name, $parentClassName);
        $GLOBALS['beanList'][$this->parent->module_name] = $parentClassName;
    }

    protected function tearDown()
    {
        BeanFactory::unregisterBean($this->parent);
        BeanFactory::unsetBeanClass($this->parent->module_name);
        unset($GLOBALS['beanList'][$this->parent->module_name]);
        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestContactUtilities::removeAllCreatedContacts();
        parent::tearDown();
    }

    public function runProvider()
    {
        return [
            'activities relationship added via studio' => [
                'sgr_widgets_activities_1_emails',
                1,
                0,
            ],
            'activities relationship added via module builder' => [
                'sgr_widgets_activities_emails',
                0,
                1,
            ],
        ];
    }

    /**
     * @dataProvider runProvider
     * @covers ::run
     * @param string $linkName
     * @param int $link2CallCount
     * @param int $link3CallCount
     */
    public function testRun($linkName, $link2CallCount, $link3CallCount)
    {
        $email = BeanFactory::newBean('Emails');
        $email->id = Uuid::uuid1();

        // Pretend that all relationships can be loaded.
        $this->parent->method('load_relationship')->willReturn(true);

        // Link instance for the "emails" link.
        $link1 = $this->createPartialMock('Link2', ['add']);
        $link1->expects($this->never())->method('add');

        // Link instance for the "sgr_widgets_activities_1_emails" link.
        $link2 = $this->createPartialMock('Link2', ['add']);
        $link2->expects($this->exactly($link2CallCount))
            ->method('add')
            ->with($this->callback(function ($bean) use ($email) {
                return $bean->id === $email->id;
            }))
            ->willReturn(true);

        // Link instance for the "sgr_widgets_activities_emails" link.
        $link3 = $this->createPartialMock('Link2', ['add']);
        $link3->expects($this->exactly($link3CallCount))
            ->method('add')
            ->with($this->callback(function ($bean) use ($email) {
                return $bean->id === $email->id;
            }))
            ->willReturn(true);

        // Initialize the parent record.
        $this->parent->id = Uuid::uuid1();
        $this->parent->emails = $link1;
        $this->parent->sgr_widgets_activities_1_emails = $link2;
        $this->parent->sgr_widgets_activities_emails = $link3;
        BeanFactory::registerBean($this->parent);

        // Insert an email into the database whose parent is $this->parent. This email will be loaded by
        // SugarUpgradeSynchronizeActivitiesRelationshipToEmailsBeansTable::getEmails().
        DBManagerFactory::getInstance()->insertParams(
            'emails',
            $email->field_defs,
            [
                'id' => $email->id,
                'state' => 'Archived',
                'name' => 'foo',
                'parent_type' => $this->parent->module_name,
                'parent_id' => $this->parent->id,
            ]
        );
        SugarTestEmailUtilities::setCreatedEmail($email->id);

        $script = $this->createPartialMock(
            'SugarUpgradeSynchronizeActivitiesRelationshipToEmailsBeansTable',
            [
                'getModulesWithActivitiesRelationship',
                'log',
            ]
        );
        $script->method('getModulesWithActivitiesRelationship')->willReturn([$this->parent->module_name => $linkName]);
        $script->db = $GLOBALS['db'];
        $script->version = '8.0.0';
        $script->from_version = '7.9.0.0';
        $script->run();
    }

    /**
     * @covers ::run
     */
    public function testRun_EmailSenderAndRecipientsAreUpgradedFrom79DuringUpgrade()
    {
        $contact = SugarTestContactUtilities::createContact();

        $email = BeanFactory::newBean('Emails');
        $email->id = Uuid::uuid1();

        // Pretend that all relationships can be loaded.
        $this->parent->method('load_relationship')->willReturn(true);

        // Link instance for the "sgr_widgets_activities_1_emails" link.
        $link = $this->createPartialMock('Link2', ['add']);
        $link->method('add')->willReturn(true);

        // Initialize the parent record.
        $this->parent->id = Uuid::uuid1();
        $this->parent->sgr_widgets_activities_1_emails = $link;
        BeanFactory::registerBean($this->parent);

        // Insert an email into the database whose parent is $this->parent. This email will be loaded by
        // SugarUpgradeSynchronizeActivitiesRelationshipToEmailsBeansTable::getEmails().
        DBManagerFactory::getInstance()->insertParams(
            'emails',
            $email->field_defs,
            [
                'id' => $email->id,
                'state' => 'Archived',
                'name' => 'foo',
                'parent_type' => $this->parent->module_name,
                'parent_id' => $this->parent->id,
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

        $script = $this->createPartialMock(
            'SugarUpgradeSynchronizeActivitiesRelationshipToEmailsBeansTable',
            [
                'getModulesWithActivitiesRelationship',
                'log',
            ]
        );
        $script->method('getModulesWithActivitiesRelationship')
            ->willReturn([$this->parent->module_name => 'sgr_widgets_activities_1_emails']);
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
