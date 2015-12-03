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

/**
 * This class is meant to test everything for InboundEmail
 *
 */
class InboundEmailCRYS1191Test extends Sugar_PHPUnit_Framework_TestCase
{
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

    public function parseAndUpdateStatusForInviteeProvider()
    {
        return array(
            array('hasXProperty' => false, $participantsResult = null),
            array('hasXProperty' => true, $participantsResult = array(
                'module name' => array(
                    'module id' => array(
                        'email' => 'test@test.com',
                        'accept_status' => 'status'
                    )
                )
            ))
        );
    }

    /**
     * @covers InboundEmail::parseAndUpdateStatusForInvitee
     *
     * @dataProvider parseAndUpdateStatusForInviteeProvider
     */
    function testParseAndUpdateStatusForInvitee($hasXProperty, $participantsResult)
    {
        $xSugarId = $this->getMock('stdClass', array('getValue'));
        $xSugarId->method('getValue')->willReturn('sugar module id');
        $xSugarName = $this->getMock('stdClass', array('getValue'));
        $xSugarName->method('getValue')->willReturn('sugar module name');

        $vEvent = new stdClass();

        if ($hasXProperty) {
            $vEvent->{'X-SUGAR-ID'} = $xSugarId;
            $vEvent->{'X-SUGAR-NAME'} = $xSugarName;
        }

        $vCalendarEventResult = new stdClass();
        $vCalendarEventResult->VEVENT = $vEvent;

        $calDavEventMock = $this->getMock('CalDavEvent', array('setCalendarEventData', 'getVCalendarEvent', 'setBean', 'getParticipants'));
        $calDavEventMock->expects($this->once())->method('setCalendarEventData')->with($this->stringContains('content ics'));
        $calDavEventMock->expects($this->once())->method('getVCalendarEvent')->willReturn($vCalendarEventResult);

        $inboundEmailMock = $this->getMock('InboundEmail', array('getFactoryBean', 'updateStatusForInvitee'));

        $eventMock = $this->getMock('SugarBean');

        if ($hasXProperty) {
            $inboundEmailMock->expects($this->at(0))->method('getFactoryBean')->with($this->equalTo('CalDavEvents'))->willReturn($calDavEventMock);
            $inboundEmailMock->expects($this->at(1))->method('getFactoryBean')->with($this->equalTo('sugar module name'), $this->equalTo('sugar module id'))->willReturn($eventMock);

            $calDavEventMock->expects($this->once())->method('setBean')->with($this->equalTo($eventMock));
            $calDavEventMock->expects($this->once())->method('getParticipants')->willReturn($participantsResult);

            $inviteeMock = $this->getMock('SugarBean');

            $inboundEmailMock->expects($this->at(2))->method('getFactoryBean')->with($this->equalTo('module name'), $this->equalTo('module id'))->willReturn($inviteeMock);
            $inboundEmailMock->expects($this->once())->method('updateStatusForInvitee')->with($this->equalTo($eventMock), $this->equalTo($inviteeMock), 'status')->willReturn(true);

        } else {
            $inboundEmailMock->expects($this->once())->method('getFactoryBean')->with($this->equalTo('CalDavEvents'))->willReturn($calDavEventMock);
            $calDavEventMock->expects($this->never())->method('setBean');
            $calDavEventMock->expects($this->never())->method('getParticipants');
            $inboundEmailMock->expects($this->never())->method('updateStatusForInvitee');
        }

        $inboundEmailMock->parseAndUpdateStatusForInvitee('content ics', 'test@test.com');
    }

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
     * @covers InboundEmail::getOneCalDAVInbound
     *
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
