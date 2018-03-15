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

use Sugarcrm\Sugarcrm\DependencyInjection\Container;
use Sugarcrm\Sugarcrm\Security\Context;
use Sugarcrm\Sugarcrm\Security\Subject\User;
use Sugarcrm\Sugarcrm\Security\Subject\ApiClient\Rest as RestApiClient;

/**
 * @group api
 * @group calendar
 */
class CalendarApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $api, $calendarApi;
    private $dp;

    protected function setUp()
    {
        parent::setUp();
        $this->api = SugarTestRestUtilities::getRestServiceMock();
        $this->api->user = $GLOBALS['current_user']->getSystemUser();
        $GLOBALS['current_user'] = $this->api->user;

        $this->calendarApi = new CalendarApi();
        $this->dp = array();
    }

    protected function tearDown()
    {
        parent::tearDown();
        SugarTestContactUtilities::removeAllCreatedContacts();

        if (!empty($this->dp)) {
            $GLOBALS['db']->query('DELETE FROM data_privacy WHERE id IN (\'' . implode("', '", $this->dp) . '\')');
        }

        $this->dp = array();
    }

    public function testTransformInvitee_DPEnabled_NameIsErased()
    {
        $args = array(
            'q' => 'bar',
            'fields' => 'first_name,last_name',
            'search_fields' => 'first_name,last_name',
            'erased_fields' => true,
        );

        $contactValues = array(
            '_module' => 'Contacts',
            'first_name' => 'Foo',
            'last_name' => 'Bar',
        );
        $contact = SugarTestContactUtilities::createContact('', $contactValues);

        $searchResults = array(
            'result' => array(
                'list' => array(
                    array('bean' => $contact),
                ),
            ),
        );

        $this->createDpErasureRecord($contact, ['first_name', 'last_name']);
        $calendarApi = new \CalendarApi();

        $result = SugarTestReflection::callProtectedMethod(
            $calendarApi,
            'transformInvitees',
            array($this->api, $args, $searchResults)
        );

        $this->assertNotEmpty(1, $result['records'], 'Api Result Contains No Records');
        $records = $result['records'];
        $this->assertCount(1, $records, 'Expecting 1 Contact Record to be returned as Invitee');
        $this->assertNotEmpty($records[0]['_erased_fields'], 'Erased Fields expected, not returned');
        $this->assertCount(2, $records[0]['_erased_fields'], 'Expected 2 erased fields');
        $this->assertSame(
            ['first_name', 'last_name'],
            $records[0]['_erased_fields'],
            'Unexpected Erased Fields were returned'
        );
    }

    public function testBuildSearchParams_ConvertsRestArgsToLegacyParams()
    {
        $args = array(
            'q' => 'woo',
            'module_list' => 'Foo,Bar',
            'search_fields' => 'foo_search_field,bar_search_field',
            'fields' => 'foo_field,bar_field',
        );

        $expectedParams = array(
            array(
                'modules' => array('Foo', 'Bar'),
                'group' => 'or',
                'field_list' => array(
                    'foo_field',
                    'bar_field',
                    'foo_search_field',
                    'bar_search_field',
                ),
                'conditions' => array(
                    array(
                        'name' => 'foo_search_field',
                        'op' => 'starts_with',
                        'value' => 'woo',
                    ),
                    array(
                        'name' => 'bar_search_field',
                        'op' => 'starts_with',
                        'value' => 'woo',
                    ),
                ),
            ),
        );

        $this->assertEquals(
            $expectedParams,
            SugarTestReflection::callProtectedMethod(
                $this->calendarApi,
                'buildSearchParams',
                array($args)
            ),
            'Rest API args should be transformed correctly into legacy query params'
        );
    }

    public function testTransformInvitees_ConvertsLegacyResultsToUnifiedSearchForm()
    {
        $args = array(
            'q' => 'bar',
            'fields' => 'first_name,last_name,email,account_name',
            'search_fields' => 'first_name,last_name,email,account_name',
        );

        $bean = new SugarBean(); //dummy, mocking out formatBean anyway
        $formattedBean = array(
            '_module' => 'Contacts',
            'first_name' => 'Foo',
            'last_name' => 'Bar',
            'account_name' => 'Baz Inc',
            'email' => array(
                array('email_address' => 'foo@baz.com'),
                array('email_address' => 'bar@baz.com'),
            ),
        );

        $this->calendarApi = $this->createPartialMock(
            'CalendarApi',
            array('formatBean')
        );
        $this->calendarApi->expects($this->once())
            ->method('formatBean')
            ->will($this->returnValue($formattedBean));

        $searchResults = array(
            'result' => array(
                'list' => array(
                    array('bean' => $bean)
                ),
            ),
        );

        $expectedInvitee = array_merge($formattedBean, array(
            '_search' => array(
                'highlighted' => array(
                    'last_name' => array(
                        'text' => 'Bar',
                        'module' => 'Contacts',
                        'label' => 'LBL_LAST_NAME',
                    ),
                ),
            ),
        ));

        $expectedInvitees = array(
            'next_offset' => -1,
            'records' => array(
                $expectedInvitee
            ),
        );;

        $this->assertEquals(
            $expectedInvitees,
            SugarTestReflection::callProtectedMethod(
                $this->calendarApi,
                'transformInvitees',
                array($this->api, $args, $searchResults)
            ),
            'Legacy search results should be transformed correctly into unified search format'
        );
    }

    public function testGetMatchedFields_MatchesRegularFieldsCorrectly()
    {
        $args = array(
            'q' => 'foo',
            'search_fields' => 'first_name,last_name,email,account_name',
        );

        $record = array(
            '_module' => 'Contacts',
            'first_name' => 'Foo',
            'last_name' => 'Bar',
            'account_name' => 'Baz Inc',
            'email' => array(
                array('email_address' => 'woo@baz.com'),
                array('email_address' => 'bar@baz.com'),
            ),
        );

        $expectedMatchedFields = array(
            'first_name' => array(
                'text' => 'Foo',
                'module' => 'Contacts',
                'label' => 'LBL_FIRST_NAME',
            ),
        );

        $this->assertEquals(
            $expectedMatchedFields,
            SugarTestReflection::callProtectedMethod(
                $this->calendarApi,
                'getMatchedFields',
                array($args, $record, 1)
            ),
            'Should match search query to field containing search text'
        );
    }

    public function testGetMatchedFields_MatchesEmailFieldCorrectly()
    {
        $args = array(
            'q' => 'woo',
            'search_fields' => 'first_name,last_name,email,account_name',
        );

        $record = array(
            '_module' => 'Contacts',
            'first_name' => 'Foo',
            'last_name' => 'Bar',
            'account_name' => 'Baz Inc',
            'email' => array(
                array('email_address' => 'woo@baz.com'),
                array('email_address' => 'bar@baz.com'),
            ),
        );

        $expectedMatchedFields = array(
            'email' => array(
                'text' => 'woo@baz.com',
                'module' => 'Contacts',
                'label' => 'LBL_EMAIL_ADDRESS',
            ),
        );

        $this->assertEquals(
            $expectedMatchedFields,
            SugarTestReflection::callProtectedMethod(
                $this->calendarApi,
                'getMatchedFields',
                array($args, $record, 1)
            ),
            'Should match search query to field containing search text'
        );
    }

    private function createDpErasureRecord($contact, $fields)
    {
        $dp = BeanFactory::newBean('DataPrivacy');
        $dp->name = 'Data Privacy Test';
        $dp->type = 'Request to Erase Information';
        $dp->status = 'Open';
        $dp->priority = 'Low';
        $dp->assigned_user_id = $GLOBALS['current_user']->id;
        $dp->date_opened = $GLOBALS['timedate']->getDatePart($GLOBALS['timedate']->nowDb());
        $dp->date_due = $GLOBALS['timedate']->getDatePart($GLOBALS['timedate']->nowDb());
        $dp->save();

        $module = 'Contacts';
        $linkName = strtolower($module);
        $dp->load_relationship($linkName);
        $dp->$linkName->add(array($contact));

        $options = ['use_cache' => false, 'encode' => false];
        $dp = BeanFactory::retrieveBean('DataPrivacy', $dp->id, $options);
        $dp->status = 'Closed';

        $fieldInfo = implode('","', $fields);
        $dp->fields_to_erase = '{"' . strtolower($module) . '":{"' . $contact->id . '":["' . $fieldInfo . '"]}}';

        $context = Container::getInstance()->get(Context::class);
        $subject = new User($GLOBALS['current_user'], new RestApiClient());
        $context->activateSubject($subject);
        $context->setAttribute('platform', 'base');

        $dp->save();
        $this->dp[] = $dp->id;
        return $dp;
    }
}
