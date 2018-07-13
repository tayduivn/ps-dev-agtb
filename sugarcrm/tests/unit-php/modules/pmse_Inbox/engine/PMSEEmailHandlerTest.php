<?php
//FILE SUGARCRM flav=ent ONLY
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
namespace Sugarcrm\SugarcrmTestsUnit\modules\pmse_Inbox\engine;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \PMSEEmailHandler
 */
class PMSEEmailHandlerTest extends TestCase
{
    /**
     * @covers ::isUserActiveForEmail
     * @dataProvider providerIsUserActiveForEmail
     */
    public function providerIsUserActiveForEmail()
    {
        return array(
            array(
                array(
                    'full_name' => 'John Doe',
                    'email1' => 'john.doe@example.com',
                    'status' => 'Active',
                    'employee_status' => 'Active',
                ),
                true,
            ),
            array(
                array(
                    'full_name' => '',
                    'email1' => 'jane.elliot@abc.com',
                    'status' => 'Active',
                    'employee_status' => 'Active',
                ),
                false,
            ),
            array(
                array(
                    'full_name' => 'Bob Smith',
                    'email1' => '',
                    'status' => 'Active',
                    'employee_status' => 'Active',
                ),
                false,
            ),
            array(
                array(
                    'full_name' => 'Mary Jones',
                    'email1' => 'mjones@xyz.com',
                    'status' => 'Inactive',
                    'employee_status' => 'Active',
                ),
                false,
            ),
            array(
                array(
                    'full_name' => 'Jim Brown',
                    'email1' => 'jim.brown@foo.com',
                    'status' => 'Active',
                    'employee_status' => '',
                ),
                false,
            ),
        );
    }

    /**
     * @covers ::isUserActiveForEmail
     * @dataProvider providerIsUserActiveForEmail
     */
    public function testIsUserActiveForEmail($userAttr, $expected)
    {
        $user = $this->createMock('\User');
        foreach ($userAttr as $prop => $val) {
            $user->$prop = $val;
        }

        $emailHandlerMock = $this->getMockBuilder('\PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $result = $emailHandlerMock->isUserActiveForEmail($user);
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers ::getRecipients
     */
    public function testGetRecipients()
    {
        $eventDefBean = $this->getMockBuilder('pmse_BpmEventDefinition')
            ->disableOriginalConstructor()
            ->getMock();

        $eventDefBean->evn_params = '{"to":[{"value":"a@a.com","type":"email","label":"a@a.com"},' .
            '{"value":"b@b.com","type":"email","label":"b@b.com"}],' .
            '"cc":[{"value":"c@c.com","type":"email","label":"c@c.com"}],' .
            '"bcc":[{"value":"bc@bc.com","type":"email","label":"bc@bc.com"}]}';
        $targetBean = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->getMock();
        $emailHandlerMock = $this->getMockBuilder('\PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $recipients = $emailHandlerMock->getRecipients($eventDefBean, $targetBean, []);
        $this->assertEquals('a@a.com', $recipients->to[0]->address);
        $this->assertEquals('b@b.com', $recipients->to[1]->address);
        $this->assertEquals('c@c.com', $recipients->cc[0]->address);
        $this->assertEquals('bc@bc.com', $recipients->bcc[0]->address);
    }

    /**
     * @covers ::getEmailBody
     */
    public function testGetEmailBody()
    {
        $templateMock = $this->getMockBuilder('pmse_Emails_Templates')
            ->disableOriginalConstructor()
            ->getMock();

        $templateMock->body = '';
        $templateMock->body_html = '<p>Body</p>';
        $targetBean = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->getMock();
        $targetBean->id = 'id';
        $emailHandlerMock = $this->getMockBuilder('\PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(['fromHtml'])
            ->getMock();

        $emailHandlerMock->method('fromHtml')->will($this->returnArgument(0));
        $emailBody = $emailHandlerMock->getEmailBody($templateMock, $targetBean);

        $this->assertEquals('Body', $emailBody['textBody']);
        $this->assertEquals('<p>Body</p>', $emailBody['htmlBody']);
    }

    /**
     * @covers ::getSubject
     */
    public function testGetSubject()
    {
        $templateMock = $this->getMockBuilder('pmse_Emails_Templates')
            ->disableOriginalConstructor()
            ->getMock();

        $templateMock->subject = 'Hello';
        $targetBean = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->getMock();
        $emailHandlerMock = $this->getMockBuilder('\PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(['fromHtml'])
            ->getMock();
        $emailHandlerMock->method('fromHtml')->will($this->returnArgument(0));

        $subject = $emailHandlerMock->getSubject($templateMock, $targetBean);
        $this->assertEquals('Hello', $subject);
    }

    /**
     * @covers ::getSender
     */
    public function testGetSender()
    {
        $templateMock = $this->getMockBuilder('pmse_Emails_Templates')
            ->disableOriginalConstructor()
            ->getMock();

        $templateMock->from_name = 'Jim';
        $templateMock->from_address = 'jim@jim.com';

        $emailHandlerMock = $this->getMockBuilder('\PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $sender = $emailHandlerMock->getSender($templateMock);
        $this->assertEquals('Jim', $sender['name']);
        $this->assertEquals('jim@jim.com', $sender['address']);
    }

    /**
     * @covers ::saveEmailContent
     */
    public function testSaveEmailContent()
    {
        $eventDefinitionBeanMock = $this->getMockBuilder('pmse_BpmEventDefinition')
            ->disableOriginalConstructor()
            ->getMock();

        $eventDefinitionBeanMock->evn_params = '{"to":[{"value":"a@a.com","type":"email","label":"a@a.com"},' .
            '{"value":"b@b.com","type":"email","label":"b@b.com"}],' .
            '"cc":[{"value":"c@c.com","type":"email","label":"c@c.com"}],' .
            '"bcc":[{"value":"bc@bc.com","type":"email","label":"bc@bc.com"}]}';

        $eventDefinitionBeanMock->id = 'eventId';

        $templateMock = $this->getMockBuilder('pmse_Emails_Templates')
            ->disableOriginalConstructor()
            ->getMock();

        $templateMock->id = 'templateId';
        $templateMock->subject = 'Subject';
        $templateMock->body_html = '<p>Body</p>';

        $emailHandlerMock = $this->getMockBuilder('\PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(['fromHtml', 'getBeansForEmailContentSave'])
            ->getMock();

        $emailHandlerMock->method('fromHtml')->will($this->returnArgument(0));

        $targetBeanMock = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->getMock();

        $targetBeanMock->id = 'id';

        $emailMessageMock = $this->getMockBuilder('pmse_EmailMessage')
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();

        $emailHandlerMock->method('getBeansForEmailContentSave')
            ->willReturn([$eventDefinitionBeanMock, $templateMock, $targetBeanMock, $emailMessageMock]);

        $flowData = ['id' => 'flowid'];
        $emailHandlerMock->saveEmailContent($flowData);

        $this->assertEquals('Subject', $emailMessageMock->subject);
        $this->assertEquals('Body', $emailMessageMock->body);
        $this->assertEquals('<p>Body</p>', $emailMessageMock->body_html);
        $this->assertEquals(
            '[{"name":"a@a.com","address":"a@a.com"},{"name":"b@b.com","address":"b@b.com"}]',
            $emailMessageMock->to_addrs
        );
        $this->assertEquals('[{"name":"c@c.com","address":"c@c.com"}]', $emailMessageMock->cc_addrs);
        $this->assertEquals('[{"name":"bc@bc.com","address":"bc@bc.com"}]', $emailMessageMock->bcc_addrs);
    }
}
