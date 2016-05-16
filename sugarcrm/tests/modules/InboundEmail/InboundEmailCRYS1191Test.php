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

/**
 * This class is meant to test everything for InboundEmail
 *
 */
class InboundEmailCRYS1191Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @inheritdoc
     */
    public function setUp()
    {
        BeanFactory::setBeanClass('CalDavEvents', 'CalDavEvents1322');
        BeanFactory::setBeanClass('Users', 'Users1322');
        BeanFactory::setBeanClass('Meeting', 'Meeting1322');
        BeanFactory::setBeanClass('Call', 'Call1322');
    }

    /**
     * @inheritdoc
     */
    public function tearDown()
    {
        BeanFactory::setBeanClass('CalDavEvents');
        BeanFactory::setBeanClass('Users');
        BeanFactory::setBeanClass('Meeting');
        BeanFactory::setBeanClass('Call');
    }


    /**
     * @covers InboundEmail::handleMailboxType
     */
    public function testHandleMailboxType()
    {
        $emailMock = $this->getMock('Email');

        $inboundEmailMock = $this->getMock('InboundEmail', array('handleCalDAV'));
        $inboundEmailMock->mailbox_type = 'caldav';
        $inboundEmailMock->expects($this->once())->method('handleCalDAV')->with($this->equalTo($emailMock));
        $inboundEmailMock->handleMailboxType($emailMock, new stdClass());
    }

    public function noteBeansProvider()
    {
        return array(
            array('returnGetBean' => null),
            array('returnGetBean' => array('has note bean'))
        );
    }

    /**
     * @covers InboundEmail::handleCalDAV
     *
     * @dataProvider noteBeansProvider
     */
    public function testHandleCalDAV($returnGetBean)
    {
        $notesMock = $this->getMock('stdClass', array('getBeans'));
        $notesMock->expects($this->once())->method('getBeans')->willReturn($returnGetBean);

        $emailMock = $this->getMock('Email', array('load_relationship'));
        $emailMock->description = 'Description Test';
        $emailMock->from_addr = 'test@test.com';
        $emailMock->notes = $notesMock;
        $emailMock->expects($this->once())->method('load_relationship')->with($this->equalTo('notes'));

        $inboundEmailMock = $this->getMock('InboundEmail', array('parseAndUpdateStatusForInvitee', 'getAttachmentFromNoteBean', 'getContentFile'));

        $attachMock = $this->getMock('stdClass', array('getName', 'getPath'));
        $attachMock->method('getName')->willReturn('invite.ics');
        $attachMock->method('getPath')->willReturn('Path to file invite.ics');
        
        if ($returnGetBean) {
            $inboundEmailMock->expects($this->once())->method('getAttachmentFromNoteBean')->with($this->equalTo($returnGetBean[0]))->willReturn($attachMock);
            $inboundEmailMock->expects($this->once())->method('getContentFile')->with($this->equalTo('Path to file invite.ics'))->willReturn($emailMock->description);
        } else {
            $inboundEmailMock->expects($this->never())->method('getAttachmentFromNoteBean');
            $inboundEmailMock->expects($this->never())->method('getContentFile');
        }

        $inboundEmailMock->expects($this->once())->method('parseAndUpdateStatusForInvitee')->with($this->equalTo($emailMock->description), $this->equalTo($emailMock->from_addr));

        $inboundEmailMock->handleCalDAV($emailMock);
    }

    /**
     * Provider for testParseAndUpdateStatusForInvitee.
     *
     * @see InboundEmailCRYS1191Test::testParseAndUpdateStatusForInvitee
     * @return array
     */
    public function parseAndUpdateStatusForInviteeProvider()
    {
        return array(
            'xPropertyNotSet' => array(
                'hasXProperty' => false,
                'content' => 'content ics',
                'email' => '',
                'statusCalDav' => '',
                'statusSugar' => '',
                'beanId' => '',
                'beanName' => '',
            ),
            'beanInstanceOfMeeting' => array(
                'hasXProperty' => true,
                'content' => 'content ics',
                'email' => 'test@test.com',
                'statusCalDav' => 'TENTATIVE',
                'statusSugar' => 'tentative',
                'beanId' => 'sugar module id',
                'beanName' => 'Meeting',
            ),
            'beanInstanceOfCall' => array(
                'hasXProperty' => true,
                'content' => 'content ics',
                'email' => 'test@test.com',
                'statusCalDav' => 'TENTATIVE',
                'statusSugar' => 'tentative',
                'beanId' => 'sugar module id',
                'beanName' => 'Call',
            ),
            'beanInstanceOfContact' => array(
                'hasXProperty' => true,
                'content' => 'content ics',
                'email' => 'test@test.com',
                'statusCalDav' => 'TENTATIVE',
                'statusSugar' => 'tentative',
                'beanId' => 'sugar module id',
                'beanName' => 'Contact',
            )
        );
    }

    /**
     * Test get new status of invitee and update in the sugar.
     *
     * @param bool $hasXProperty
     * @param string $content
     * @param string $email
     * @param string $statusCalDav
     * @param string $statusSugar
     * @param string $beanId
     * @param string $beanName
     *
     * @covers InboundEmail::parseAndUpdateStatusForInvitee
     * @dataProvider parseAndUpdateStatusForInviteeProvider
     */
    function testParseAndUpdateStatusForInvitee(
        $hasXProperty,
        $content,
        $email,
        $statusCalDav,
        $statusSugar,
        $beanId,
        $beanName
    )
    {
        $xSugarId = $this->getMock('stdClass', array('getValue'));
        $xSugarId->method('getValue')->willReturn($beanId);
        $xSugarName = $this->getMock('stdClass', array('getValue'));
        $xSugarName->method('getValue')->willReturn($beanName);

        $oEvent = new stdClass();

        if ($hasXProperty) {
            $oEvent->{'X-SUGAR-ID'} = $xSugarId;
            $oEvent->{'X-SUGAR-NAME'} = $xSugarName;
        }

        $eventMock = $this->getMock('Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event', array('getObject', 'getParticipants'));
        $eventMock->expects($this->any())->method('getObject')->willReturn($oEvent);

        $eventCollection = BeanFactory::getBean('CalDavEvents');
        $eventCollection->setParent($eventMock);

        $inboundEmailMock = $this->getMock('InboundEmail', array('updateStatusForInvitee'));
        $participantMock = $this->getMock('Sugarcrm\Sugarcrm\Dav\Cal\Structures\Participant', array('getEmail', 'getStatus'));

        $participantMock->method('getEmail')->willReturn($email);
        $participantMock->method('getStatus')->willReturn($statusCalDav);

        if (in_array($beanName, array('Call', 'Meeting'))) {
            $eventMock->expects($this->once())->method('getParticipants')->willReturn(array(
                $participantMock
            ));

            $beanModuleMock = BeanFactory::getBean($beanName, $beanId);
            $inviteeMock = BeanFactory::getBean('Users', '1');

            $inboundEmailMock
                ->method('updateStatusForInvitee')
                ->with($this->equalTo($beanModuleMock), $this->equalTo($inviteeMock), $this->equalTo($statusSugar));
        } else {
            $eventMock->expects($this->never())->method('getParticipants');
            $inboundEmailMock
                ->method('updateStatusForInvitee');
        }

        $inboundEmailMock->parseAndUpdateStatusForInvitee($content, $email);
    }

    /**
     * Provider for testGetOneCalDAVInbound.
     *
     * @see InboundEmailCRYS1191Test::testGetOneCalDAVInbound
     * @return array
     */
    public function getOneCalDAVInboundProvider()
    {
        return array(
            array(
                array('item 1', 'item 2'),
                'item 1'
            ),
            array(
                array(),
                null
            )
        );
    }

    /**
     * Test get first email from list for CalDAV.
     *
     * @covers InboundEmail::getOneCalDAVInbound
     * @dataProvider getOneCalDAVInboundProvider
     */
    function testGetOneCalDAVInbound($returnData, $resultMethod)
    {
        $whereMock = $this->getMock('stdClass', array('equals'));
        $whereMock->expects($this->at(0))->method('equals')->with($this->equalTo('mailbox_type'), $this->equalTo('caldav'));
        $whereMock->expects($this->at(1))->method('equals')->with($this->equalTo('status'), $this->equalTo('Active'));

        $inboundEmailMock = $this->getMock('InboundEmail', array('getSugarQuery', 'fetchFromQuery'));
        $queryMock = $this->getMockBuilder('SugarQuery')->setMethods(array('from', 'where', 'limit'))->disableOriginalConstructor()->getMock();

        $inboundEmailMock->expects($this->once())->method('getSugarQuery')->willReturn($queryMock);

        $queryMock->expects($this->once())->method('from')->with($this->equalTo($inboundEmailMock));
        $queryMock->expects($this->at(1))->method('where')->willReturn($whereMock);
        $queryMock->expects($this->at(2))->method('where')->willReturn($whereMock);
        $queryMock->expects($this->once(0))->method('limit')->with($this->equalTo(1));

        $inboundEmailMock->expects($this->once())->method('fetchFromQuery')->with($queryMock)->willReturn($returnData);

        $this->assertEquals($resultMethod, $inboundEmailMock->getOneCalDAVInbound());
    }
}

/**
 * Stub class for CalDavEventCollection bean
 */
class CalDavEvents1322 extends CalDavEventCollection
{
    public static $parent;

    public function setData() {}

    public function sync() {
        $this->participants_links = json_encode(array(
            'test@test.com' => array('beanId' => '1', 'beanName' => 'Users')
        ));
    }

    public function getParent()
    {
        return self::$parent;
    }

    /**
     * Method for test
     */
    public function setParent($parent)
    {
        self::$parent = $parent;
    }
}

/**
 * Stub class for user bean
 */
class Users1322 extends User
{
    public function retrieve($id)
    {
        $this->id = $id;
        return $this;
    }
}

/**
 * Stub class for Meeting bean
 */
class Meeting1322 extends Meeting
{
    public function __construct() {
        parent::__construct();
        $this->added_custom_field_defs = true;
    }

    public function retrieve($id)
    {
        $this->id = $id;
        return $this;
    }
}

/**
 * Stub class for Call bean
 */
class Call1322 extends Call
{
    public function __construct() {
        parent::__construct();
        $this->added_custom_field_defs = true;
    }

    public function retrieve($id)
    {
        $this->id = $id;
        return $this;
    }
}
