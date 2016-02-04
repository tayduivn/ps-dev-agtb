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

use \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Participant;

/**
 * Class ImportTestCRYS1160
 *
 * @coversDefaultClass Sugarcrm\Sugarcrm\Dav\Cal\JobQueue\Import
 */
class ImportTestCRYS1160 extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var CalDavEventCollection
     */
    protected $eventCollection;

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        BeanFactory::unregisterBean('CalDavEvents', $this->eventCollection->id);
        if ($this->eventCollection->id) {
            $GLOBALS['db']->
            query("DELETE FROM {$this->eventCollection->table_name} WHERE id = '{$this->eventCollection->id}'");
        }
        parent::tearDown();
        SugarTestHelper::tearDown();
    }

    /**
     * @param boolean $sendInvites Meeting $send_invites expected value.
     * @param array $participants Participants of the Event.
     * @param array $contactsArray Meeting $contacts_arr expected value.
     * @param array $leadsArray Meeting $leads_arr expected value.
     * @dataProvider invitesProvider
     */
    public function testImportSetUpInvitesForNewBean($sendInvites, $participants, $contactsArray, $leadsArray)
    {
        $event = $this->getMock('Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event', array('getParticipants', 'getStartDate'));
        $event->expects($this->any())
            ->method('getParticipants')
            ->will($this->returnValue($participants));
        $event->expects($this->any())
            ->method('getStartDate')
            ->will($this->returnValue(null));

        $eventCollection = $this->getMock('CalDavEventCollection', array('getBean', 'setBean', 'getParent', 'sync'));
        $eventCollection->expects($this->any())->method('getParent')->will($this->returnValue($event));
        $eventCollection->expects($this->any())->method('getBean')->will($this->returnValue(null));

        // This is the main assertion.
        $eventCollection->expects($this->any())
            ->method('setBean')
            ->will($this->returnCallback(function ($bean) use ($sendInvites, $contactsArray, $leadsArray) {
                $this->assertEquals($sendInvites, $bean->send_invites);
                $this->assertEquals($contactsArray, $bean->contacts_arr);
                $this->assertEquals($leadsArray, $bean->leads_arr);
            }));

        $eventCollection->parent_type = 'Meetings';
        $eventCollection->save();
        $this->eventCollection = $eventCollection;
        BeanFactory::setBeanClass('CalDavEvents', get_class($eventCollection));
        BeanFactory::registerBean($eventCollection);

        $adapter = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings')
            ->disableOriginalConstructor()
            ->setMethods(array('getBeanForImport', 'import', 'export'))
            ->getMock();
        $adapter->expects($this->any())->method('getBeanForImport')->will($this->returnValue(new Meeting()));
        $adapter->expects($this->any())->method('import')->will($this->returnValue(false));
        $adapterFactory = $this->getMock('Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory', array('getAdapter'));
        $adapterFactory->expects($this->any())->method('getAdapter')->will($this->returnValue($adapter));

        $import = $this->getMock(
            'Sugarcrm\Sugarcrm\Dav\Cal\JobQueue\Import',
            array('getAdapterFactory'),
            array($eventCollection->module_name, $eventCollection->id, array(), 0)
        );
        $import->expects($this->any())
            ->method('getAdapterFactory')
            ->will($this->returnValue($adapterFactory));

        $import->run();
    }

    /**
     * Get test cases for testImportSetUpInvitesForNewBean().
     * @return array
     */
    public static function invitesProvider()
    {
        return array(
            array(
                true,
                self::getParticipants(),
                array('123'),
                array('456'),
                'There are 2 participants',
            ),
            array(
                false,
                array(),
                array(),
                array(),
                'There are no participants',
            ),
        );
    }

    /**
     * Get Participants.
     * @return array Array of participants.
     */
    public static function getParticipants()
    {
        $participant1 = new Participant(null, 'Contacts', '123');
        $participant2 = new Participant(null, 'Leads', '456');
        return array($participant1, $participant2);
    }
}
