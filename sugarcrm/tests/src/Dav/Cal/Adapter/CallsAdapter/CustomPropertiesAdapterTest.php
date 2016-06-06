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

namespace Sugarcrm\SugarcrmTests\Dav\Cal\Adapter\CallsAdapter;

use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\CallsAdapter\CustomPropertiesAdapter;

/**
 * Class CustomPropertiesAdapterTest
 * @package Sugarcrm\SugarcrmTests\Dav\Cal\Adapter\CallsAdapter
 * @covers Sugarcrm\Sugarcrm\Dav\Cal\Adapter\CallsAdapter\CustomPropertiesAdapter
 */
class CustomPropertiesAdapterTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var CustomPropertiesAdapter|\PHPUnit_Framework_MockObject_MockObject */
    protected $adapterMock;

    /** @var \CalDavEventCollection|\PHPUnit_Framework_MockObject_MockObject */
    protected $collectionMock = null;

    /** @var \SugarConfig|\PHPUnit_Framework_MockObject_MockObject */
    protected $sugarConfigMock = null;

    /** @var \User|\PHPUnit_Framework_MockObject_MockObject */
    protected $userMock;

    /** @var \Call|\PHPUnit_Framework_MockObject_MockObject */
    protected $callMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->userMock = $this->getMock('User');
        $this->callMock = $this->getMock('Call');
        $this->collectionMock = $this->getMock('CalDavEventCollection', null);
        $this->sugarConfigMock = $this->getMock('SugarConfig');

        $this->adapterMock = $this->getMock(
            'Sugarcrm\Sugarcrm\Dav\Cal\Adapter\CallsAdapter\CustomPropertiesAdapter',
            array(
                'getCallDirections',
                'getSugarConfig',
            )
        );
        $this->adapterMock->method('getSugarConfig')->willReturn($this->sugarConfigMock);
    }

    /**
     * Get ics source file.
     *
     * @param string $name
     * @return string
     */
    protected static function getSourceIcsFile($name)
    {
        return file_get_contents(__DIR__ . '/sources/CustomPropertiesAdapterTest.' . $name . '.ics');
    }

    /**
     * Data provider for testSetCollectionProperties.
     *
     * @see CustomPropertiesAdapterTest::testSetCollectionProperties
     * @return array
     */
    public function providerSetCollectionProperties()
    {
        return array(
            'doesNotChangeCallDirectionIsSetBefore' => array(
                'calendarSource' => 'inbound-call-direction',
                'isUser' => false,
                'callDirectionUserPreference' => null,
                'defaultCallDirection' => null,
                'supportedCallDirections' => array('inbound', 'outbound'),
                'expectedCallDirection' => 'inbound',
            ),
            'setCallDirectionFromPreferencesIfNoSetBefore' => array(
                'calendarSource' => 'no-call-direction',
                'isUser' => true,
                'callDirectionUserPreference' => 'outbound',
                'defaultCallDirection' => null,
                'supportedCallDirections' => array('inbound', 'outbound'),
                'expectedCallDirection' => 'outbound',
            ),
            'setCallDirectionFromDefaultIfNoSetBefore' => array(
                'calendarSource' => 'no-call-direction',
                'isUser' => false,
                'callDirectionUserPreference' => null,
                'defaultCallDirection' => 'outbound',
                'supportedCallDirections' => array('inbound', 'outbound'),
                'expectedCallDirection' => 'outbound',
            ),
            'setCallDirectionFromPreferencesIfNotInSupportedCallDirections' => array(
                'calendarSource' => 'wrong-call-direction',
                'isUser' => true,
                'callDirectionUserPreference' => 'inbound',
                'defaultCallDirection' => null,
                'supportedCallDirections' => array('inbound', 'outbound'),
                'expectedCallDirection' => 'inbound',
            ),
            'setCallDirectionFromDefaultIfNotInSupportedCallDirections' => array(
                'calendarSource' => 'wrong-call-direction',
                'isUser' => false,
                'callDirectionUserPreference' => null,
                'defaultCallDirection' => 'outbound',
                'supportedCallDirections' => array('inbound', 'outbound'),
                'expectedCallDirection' => 'outbound',
            ),
        );
    }

    /**
     * Covers correct setting call direction if not exist.
     *
     * @dataProvider providerSetCollectionProperties
     * @covers       CustomPropertiesAdapter::setCollectionProperties
     * @param string $calendarSource
     * @param bool $isUser
     * @param string $callDirectionUserPreference
     * @param string $defaultCallDirection
     * @param array $supportedCallDirections
     * @param string $expectedCallDirection
     */
    public function testSetCollectionProperties(
        $calendarSource,
        $isUser,
        $callDirectionUserPreference,
        $defaultCallDirection,
        $supportedCallDirections,
        $expectedCallDirection
    ) {
        $this->adapterMock->method('getCallDirections')->willReturn($supportedCallDirections);

        $this->collectionMock->parent_type = 'Calls';
        $this->collectionMock->calendar_data = static::getSourceIcsFile($calendarSource);

        $this->sugarConfigMock->method('get')->willReturnMap(array(
            array('default_caldav_call_direction', null, $defaultCallDirection)
        ));

        $this->userMock->method('getPreference')->willReturnMap(array(
            array('caldav_call_direction', 'global', $callDirectionUserPreference)
        ));
        $user = $isUser ? $this->userMock : null;

        $this->adapterMock->setCollectionProperties($this->collectionMock, $user);

        $this->assertEquals(
            $expectedCallDirection,
            $this->collectionMock->getParent()->getCustomProperty(
                CustomPropertiesAdapter::CALL_DIRECTION_EVENT_PROPERTY_NAME
            )
        );
    }

    /**
     * Data provider for testSetBeanProperties.
     *
     * @see CustomPropertiesAdapterTest::testSetBeanProperties
     * @return array
     */
    public function providerSetBeanProperties()
    {
        return array(
            'doesNotChangeBeanDirectionIsSetBefore' => array(
                'calendarSource' => 'inbound-call-direction',
                'isCollection' => true,
                'isUser' => false,
                'beanCallDirection' => 'any-direction',
                'callDirectionUserPreference' => null,
                'defaultCallDirection' => 'outbound',
                'supportedCallDirections' => array('inbound', 'outbound'),
                'expectedCallDirection' => 'any-direction',
            ),
            'doesNotChangeBeanDirectionIfNoCollection' => array(
                'calendarSource' => 'inbound-call-direction',
                'isCollection' => false,
                'isUser' => false,
                'beanCallDirection' => '',
                'callDirectionUserPreference' => null,
                'defaultCallDirection' => 'outbound',
                'supportedCallDirections' => array('inbound', 'outbound'),
                'expectedCallDirection' => null,
            ),
            'setBeanDirectionFromEvent' => array(
                'calendarSource' => 'inbound-call-direction',
                'isCollection' => true,
                'isUser' => false,
                'beanCallDirection' => '',
                'callDirectionUserPreference' => null,
                'defaultCallDirection' => 'outbound',
                'supportedCallDirections' => array('inbound', 'outbound'),
                'expectedCallDirection' => 'inbound',
            ),
            'setBeanDirectionFromPreferencesIfNotInEvent' => array(
                'calendarSource' => 'no-call-direction',
                'isCollection' => true,
                'isUser' => true,
                'beanCallDirection' => '',
                'callDirectionUserPreference' => 'inbound',
                'defaultCallDirection' => 'outbound',
                'supportedCallDirections' => array('inbound', 'outbound'),
                'expectedCallDirection' => 'inbound',
            ),
            'setBeanDirectionFromDefaultIfNotInEvent' => array(
                'calendarSource' => 'no-call-direction',
                'isCollection' => true,
                'isUser' => false,
                'beanCallDirection' => '',
                'callDirectionUserPreference' => null,
                'defaultCallDirection' => 'outbound',
                'supportedCallDirections' => array('inbound', 'outbound'),
                'expectedCallDirection' => 'outbound',
            ),
            'setBeanDirectionFromPreferencesIfNotInSupportedCallDirections' => array(
                'calendarSource' => 'wrong-call-direction',
                'isCollection' => true,
                'isUser' => true,
                'beanCallDirection' => '',
                'callDirectionUserPreference' => 'inbound',
                'defaultCallDirection' => 'outbound',
                'supportedCallDirections' => array('inbound', 'outbound'),
                'expectedCallDirection' => 'inbound',
            ),
            'setBeanDirectionFromDefaultIfNotInSupportedCallDirections' => array(
                'calendarSource' => 'wrong-call-direction',
                'isCollection' => true,
                'isUser' => false,
                'beanCallDirection' => '',
                'callDirectionUserPreference' => null,
                'defaultCallDirection' => 'outbound',
                'supportedCallDirections' => array('inbound', 'outbound'),
                'expectedCallDirection' => 'outbound',
            ),
        );
    }

    /**
     * Covers correct setting bean direction if not exist.
     *
     * @dataProvider providerSetBeanProperties
     * @covers       CustomPropertiesAdapter::setBeanProperties
     *
     * @param string $calendarSource
     * @param bool $isCollection
     * @param bool $isUser
     * @param string $beanCallDirection
     * @param string $callDirectionUserPreference
     * @param string $defaultCallDirection
     * @param array $supportedCallDirections
     * @param string $expectedCallDirection
     */
    public function testSetBeanProperties(
        $calendarSource,
        $isCollection,
        $isUser,
        $beanCallDirection,
        $callDirectionUserPreference,
        $defaultCallDirection,
        $supportedCallDirections,
        $expectedCallDirection
    ) {
        $this->callMock->direction = $beanCallDirection;
        $this->adapterMock->method('getCallDirections')->willReturn($supportedCallDirections);

        $this->collectionMock->parent_type = 'Calls';
        $this->collectionMock->calendar_data = static::getSourceIcsFile($calendarSource);
        $collection = $isCollection ? $this->collectionMock : null;

        $this->sugarConfigMock->method('get')->willReturnMap(array(
            array('default_caldav_call_direction', null, $defaultCallDirection)
        ));

        $this->userMock->method('getPreference')->willReturnMap(array(
            array('caldav_call_direction', 'global', $callDirectionUserPreference)
        ));
        $user = $isUser ? $this->userMock : null;

        $this->adapterMock->setBeanProperties($this->callMock, $collection, $user);

        $this->assertEquals($expectedCallDirection, $this->callMock->direction);
    }
}
