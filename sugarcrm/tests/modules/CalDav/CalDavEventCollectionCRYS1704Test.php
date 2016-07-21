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

namespace Sugarcrm\SugarcrmTests\modules\CalDav;

use CalDavEventCollection;
use Sugarcrm\Sugarcrm\Util\Uuid;
use User;
use Sugarcrm\Sugarcrm\Dav\Base\Helper\UserHelper;
use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Registry;
use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\CallsAdapter\Factory as CallsAdapterFactory;
use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\CallsAdapter\CustomPropertiesAdapter as CallsCustomPropertiesAdapter;
use SugarConfig;

/**
 * Class CalDavEventCollectionCRYS1704Test
 *
 * @package Sugarcrm\SugarcrmTests\modules\CalDav
 * @covers \CalDavEventCollection
 */
class CalDavEventCollectionCRYS1704Test extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var CalDavEventCollection|\PHPUnit_Framework_MockObject_MockObject */
    protected $collectionMock = null;

    /** @var UserHelper|\PHPUnit_Framework_MockObject_MockObject */
    protected $userHelperMock = null;

    /** @var Registry|\PHPUnit_Framework_MockObject_MockObject */
    protected $adapterRegistryMock = null;

    /** @var CallsAdapterFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $callsAdapterFactoryMock = null;

    /** @var CallsCustomPropertiesAdapter|\PHPUnit_Framework_MockObject_MockObject */
    protected $callsCustomPropertiesAdapterMock = null;

    /** @var SugarConfig|\PHPUnit_Framework_MockObject_MockObject */
    protected $sugarConfigMock = null;

    /** @var User */
    protected $originalGlobalUser;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        \SugarTestHelper::setUp('mock_db');
        \SugarTestHelper::setUp('app_list_strings');
        \BeanFactory::clearCache();
        \BeanFactory::setBeanClass('Users', 'Sugarcrm\SugarcrmTests\modules\CalDav\UserCRYS1704');

        $this->originalGlobalUser = $GLOBALS['current_user'];
        $GLOBALS['current_user'] = $this->getMock('User');

        $this->sugarConfigMock = $this->getMock('SugarConfig');

        $calendar = array(
            'id' => Uuid::uuid4(),
            'name' => 'Default',
            'date_entered' => '2016-02-25 12:25:49',
            'date_modified' => '2016-02-25 12:25:49',
            'modified_user_id' => '1',
            'created_by' => 1,
            'description' => '',
            'deleted' => 0,
            'uri' => 'default',
            'synctoken' => 0,
            'calendarorder' => 0,
            'calendarcolor' => '',
            'timezone' => static::getSourceIcsFile('timezone'),
            'components' => 'VEVENT,VTODO',
            'transparent' => 0,
            'assigned_user_id' => 1,
        );

        $this->userHelperMock = $this->getMock('Sugarcrm\Sugarcrm\Dav\Base\Helper\UserHelper');
        $this->userHelperMock->method('getCalendars')->willReturn(array($calendar));

        $this->callsCustomPropertiesAdapterMock = $this->getMock(
            'Sugarcrm\Sugarcrm\Dav\Cal\Adapter\CallsAdapter\CustomPropertiesAdapter',
            array(
                'getCallDirections',
                'getSugarConfig',
            )
        );
        $this->callsCustomPropertiesAdapterMock->method('getSugarConfig')->willReturn($this->sugarConfigMock);

        $this->callsAdapterFactoryMock = $this->getMock('Sugarcrm\Sugarcrm\Dav\Cal\Adapter\CallsAdapter\Factory');
        $this->callsAdapterFactoryMock->method('getPropertiesAdapter')
            ->willReturn($this->callsCustomPropertiesAdapterMock);

        $this->collectionMock = $this->getMock('CalDavEventCollection', array(
            'getUserHelper',
            'getAdapterRegistry',
            'getSugarConfig',
        ));

        $GLOBALS['current_user']->id = 'crys1704_created_by';
        $this->collectionMock->created_by = 'crys1704_created_by';

        $this->collectionMock->method('getUserHelper')->willReturn($this->userHelperMock);
        $this->collectionMock->method('getSugarConfig')->willReturn($this->sugarConfigMock);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $GLOBALS['current_user'] = $this->originalGlobalUser;
        \BeanFactory::setBeanClass('Users');
        \SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * Get ics source file.
     *
     * @param string $name
     * @return string
     */
    protected static function getSourceIcsFile($name)
    {
        return file_get_contents(__DIR__ . '/EventTemplates/SetParentTypeThroughSave.' . $name . '.ics');
    }

    /**
     * Data provider for testSetParentTypeThroughSave.
     *
     * @see CalDavEventCollectionCRYS1704Test::testSetParentTypeThroughSave
     * @return array
     */
    public static function providerSetParentTypeThroughSave()
    {
        return array(
            'setParentTypeFromEventModule' => array(
                'parentType' => null,
                'calendarEventTemplate' => 'meetings-x-sugar-module',
                'returnUser' => false,
                'caldavModuleUserPreference' => null,
                'defaultCalDavModule' => null,
                'supportedModules' => array('Calls', 'Meetings'),
                'expectedParentType' => 'Meetings',
                'expectedEventSugarModule' => 'Meetings',
            ),
            'doesNotChangeEventModuleIsSetBefore' => array(
                'parentType' => 'Calls',
                'calendarEventTemplate' => 'meetings-x-sugar-module',
                'returnUser' => false,
                'caldavModuleUserPreference' => null,
                'defaultCalDavModule' => null,
                'supportedModules' => array('Calls', 'Meetings'),
                'expectedParentType' => 'Calls',
                'expectedEventSugarModule' => 'Meetings',
            ),
            'setParentTypeFromPreferencesIfNoEventModule' => array(
                'parentType' => null,
                'calendarEventTemplate' => 'no-x-sugar-module',
                'returnUser' => true,
                'caldavModuleUserPreference' => 'Meetings',
                'defaultCalDavModule' => null,
                'supportedModules' => array('Calls', 'Meetings'),
                'expectedParentType' => 'Meetings',
                'expectedEventSugarModule' => 'Meetings',
            ),
            'setParentTypeFromPreferencesIfEventModuleNotInSupportedModules' => array(
                'parentType' => null,
                'calendarEventTemplate' => 'wrong-x-sugar-module',
                'returnUser' => true,
                'caldavModuleUserPreference' => 'Meetings',
                'defaultCalDavModule' => null,
                'supportedModules' => array('Calls', 'Meetings'),
                'expectedParentType' => 'Meetings',
                'expectedEventSugarModule' => 'Meetings',
            ),
            'doesNotSetParentTypeFromPreferencesIfNotInSupportedModules' => array(
                'parentType' => null,
                'calendarEventTemplate' => 'no-x-sugar-module',
                'returnUser' => true,
                'caldavModuleUserPreference' => 'Meetings',
                'defaultCalDavModule' => null,
                'supportedModules' => array('Calls'),
                'expectedParentType' => null,
                'expectedEventSugarModule' => null,
            ),
            'setParentTypeFromDefaultCalDavModuleIfNoEventModule' => array(
                'parentType' => null,
                'calendarEventTemplate' => 'no-x-sugar-module',
                'returnUser' => false,
                'caldavModuleUserPreference' => null,
                'defaultCalDavModule' => 'Meetings',
                'supportedModules' => array('Calls', 'Meetings'),
                'expectedParentType' => 'Meetings',
                'expectedEventSugarModule' => 'Meetings',
            ),
            'setParentTypeFromDefaultCalDavModuleIfEventModuleNotInSupportedModules' => array(
                'parentType' => null,
                'calendarEventTemplate' => 'wrong-x-sugar-module',
                'returnUser' => false,
                'caldavModuleUserPreference' => null,
                'defaultCalDavModule' => 'Meetings',
                'supportedModules' => array('Calls', 'Meetings'),
                'expectedParentType' => 'Meetings',
                'expectedEventSugarModule' => 'Meetings',
            ),
            'doesNotSetParentTypeFromDefaultCalDavModuleIfNotInSupportedModules' => array(
                'parentType' => null,
                'calendarEventTemplate' => 'no-x-sugar-module',
                'returnUser' => false,
                'caldavModuleUserPreference' => null,
                'defaultCalDavModule' => 'Meetings',
                'supportedModules' => array('Calls'),
                'expectedParentType' => null,
                'expectedEventSugarModule' => null,
            ),
        );
    }

    /**
     * Covers correct setting event parent type if not exist.
     *
     * @dataProvider providerSetParentTypeThroughSave
     * @covers       CalDavEventCollection::setParentType
     * @param string $parentType
     * @param string $calendarEventTemplate
     * @param bool $returnUser
     * @param string $caldavModuleUserPreference
     * @param string $defaultCalDavModule
     * @param array $supportedModules
     * @param string $expectedParentType
     * @param string $expectedEventSugarModule
     */
    public function testSetParentTypeThroughSave(
        $parentType,
        $calendarEventTemplate,
        $returnUser,
        $caldavModuleUserPreference,
        $defaultCalDavModule,
        $supportedModules,
        $expectedParentType,
        $expectedEventSugarModule
    ) {
        UserCRYS1704::$crys1704userMockSettings = array(
            'caldavModuleUserPreference' => $caldavModuleUserPreference,
            'callDirectionUserPreference' => null,
            'returnUser' => $returnUser,
        );

        $this->adapterRegistryMock = $this->getMock('Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Registry', array(
            'getSupportedModules'
        ));
        $this->adapterRegistryMock->method('getSupportedModules')->willReturn($supportedModules);

        $this->collectionMock->parent_type = $parentType;
        $this->collectionMock->calendar_data = static::getSourceIcsFile($calendarEventTemplate);
        $this->collectionMock->method('getAdapterRegistry')->willReturn($this->adapterRegistryMock);

        $this->sugarConfigMock->method('get')->willReturnMap(array(
            array('default_caldav_module', null, $defaultCalDavModule)
        ));

        $this->collectionMock->save(false);

        $this->assertEquals($expectedParentType, $this->collectionMock->parent_type);
        $this->assertEquals($expectedEventSugarModule, $this->collectionMock->getParent()->getSugarModule());

    }

    /**
     * Data provider for testSetCollectionPropertiesForCallsThroughSave.
     *
     * @see CalDavEventCollectionCRYS1704Test::testSetCollectionPropertiesForCallsThroughSave
     * @return array
     */
    public static function providerSetCollectionPropertiesForCallsThroughSave()
    {
        return array(
            'doesNotChangeCallDirectionIsSetBefore' => array(
                'calendarEventTemplate' => 'inbound-call-direction',
                'returnUser' => false,
                'callDirectionUserPreference' => null,
                'defaultCallDirection' => null,
                'supportedCallDirections' => array('inbound', 'outbound'),
                'expectedCallDirection' => 'inbound',
            ),
            'setCallDirectionFromPreferencesIfNoSetBefore' => array(
                'calendarEventTemplate' => 'no-call-direction',
                'returnUser' => true,
                'callDirectionUserPreference' => 'outbound',
                'defaultCallDirection' => null,
                'supportedCallDirections' => array('inbound', 'outbound'),
                'expectedCallDirection' => 'outbound',
            ),
            'setCallDirectionFromDefaultIfNoSetBefore' => array(
                'calendarEventTemplate' => 'no-call-direction',
                'returnUser' => false,
                'callDirectionUserPreference' => null,
                'defaultCallDirection' => 'outbound',
                'supportedCallDirections' => array('inbound', 'outbound'),
                'expectedCallDirection' => 'outbound',
            ),
            'setCallDirectionFromPreferencesIfNotInSupportedCallDirections' => array(
                'calendarEventTemplate' => 'wrong-call-direction',
                'returnUser' => true,
                'callDirectionUserPreference' => 'inbound',
                'defaultCallDirection' => null,
                'supportedCallDirections' => array('inbound', 'outbound'),
                'expectedCallDirection' => 'inbound',
            ),
            'setCallDirectionFromDefaultIfNotInSupportedCallDirections' => array(
                'calendarEventTemplate' => 'wrong-call-direction',
                'returnUser' => false,
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
     * @dataProvider providerSetCollectionPropertiesForCallsThroughSave
     * @covers       CalDavEventCollection::setParentType
     * @param string $calendarEventTemplate
     * @param bool $returnUser
     * @param string $callDirectionUserPreference
     * @param string $defaultCallDirection
     * @param array $supportedCallDirections
     * @param string $expectedCallDirection
     */
    public function testSetCollectionPropertiesForCallsThroughSave(
        $calendarEventTemplate,
        $returnUser,
        $callDirectionUserPreference,
        $defaultCallDirection,
        $supportedCallDirections,
        $expectedCallDirection
    ) {
        UserCRYS1704::$crys1704userMockSettings = array(
            'caldavModuleUserPreference' => 'Calls',
            'callDirectionUserPreference' => $callDirectionUserPreference,
            'returnUser' => $returnUser,
        );

        $this->callsCustomPropertiesAdapterMock->method('getCallDirections')
            ->willReturn($supportedCallDirections);

        $this->adapterRegistryMock = $this->getMock('Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Registry');
        $this->adapterRegistryMock->method('getFactory')->willReturnMap(array(
            array('Calls', $this->callsAdapterFactoryMock)
        ));
        $this->adapterRegistryMock->method('getSupportedModules')->willReturn(array(
            'Calls',
            'Meetings'
        ));

        $this->collectionMock->parent_type = 'Calls';
        $this->collectionMock->calendar_data = static::getSourceIcsFile($calendarEventTemplate);
        $this->collectionMock->method('getAdapterRegistry')->willReturn($this->adapterRegistryMock);

        $this->sugarConfigMock->method('get')->willReturnMap(array(
            array('default_caldav_module', null, 'Calls'),
            array('default_caldav_call_direction', null, $defaultCallDirection)
        ));

        $this->collectionMock->save(false);

        $this->assertEquals(
            $expectedCallDirection,
            $this->collectionMock->getParent()->getCustomProperty(
                CallsCustomPropertiesAdapter::CALL_DIRECTION_EVENT_PROPERTY_NAME
            )
        );
    }
}

class UserCRYS1704 extends User
{
    public static $crys1704userMockSettings = array();

    public function retrieve($id)
    {
        if ($id == 'crys1704_created_by') {
            if (static::$crys1704userMockSettings['returnUser']) {
                $this->id = $id;
                return $this;
            } else {
                return null;
            }
        } else {
            $this->id = $id;
            return $this;
        }
    }

    public function getPreference($name, $category = 'global')
    {
        if ($name == 'caldav_module' && $category == 'global') {
            return static::$crys1704userMockSettings['caldavModuleUserPreference'];
        } elseif ($name == 'caldav_call_direction' && $category == 'global') {
            return static::$crys1704userMockSettings['callDirectionUserPreference'];
        }

        return parent::getPreference($name, $category);
    }
}
