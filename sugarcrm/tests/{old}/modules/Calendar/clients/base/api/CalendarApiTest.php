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

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;
use Sugarcrm\Sugarcrm\Security\Context;
use Sugarcrm\Sugarcrm\Security\Subject\User;
use Sugarcrm\Sugarcrm\Security\Subject\ApiClient\Rest as RestApiClient;

/**
 * @group api
 * @group calendar
 */
class CalendarApiTest extends TestCase
{
    private $api;
    private $calendarApi;
    private $dp;

    protected function setUp() : void
    {
        $this->api = SugarTestRestUtilities::getRestServiceMock();
        $this->api->user = $GLOBALS['current_user']->getSystemUser();
        $GLOBALS['current_user'] = $this->api->user;

        $this->calendarApi = new CalendarApi();
        $this->dp = [];
    }

    protected function tearDown() : void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();

        if (!empty($this->dp)) {
            $GLOBALS['db']->query('DELETE FROM data_privacy WHERE id IN (\'' . implode("', '", $this->dp) . '\')');
        }

        $this->dp = [];
    }

    public function testTransformInvitee_DPEnabled_NameIsErased()
    {
        $args = [
            'q' => 'bar',
            'fields' => 'first_name,last_name',
            'search_fields' => 'first_name,last_name',
            'erased_fields' => true,
        ];

        $contactValues = [
            '_module' => 'Contacts',
            'first_name' => 'Foo',
            'last_name' => 'Bar',
        ];
        $contact = SugarTestContactUtilities::createContact('', $contactValues);

        $searchResults = [
            'result' => [
                'list' => [
                    ['bean' => $contact],
                ],
            ],
        ];

        $this->createDpErasureRecord($contact, ['first_name', 'last_name']);
        $calendarApi = new \CalendarApi();

        $result = SugarTestReflection::callProtectedMethod(
            $calendarApi,
            'transformInvitees',
            [$this->api, $args, $searchResults]
        );

        $this->assertNotEmpty($result['records'], 'Api Result Contains No Records');
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
        $args = [
            'q' => 'woo',
            'module_list' => 'Foo,Bar',
            'search_fields' => 'foo_search_field,bar_search_field',
            'fields' => 'foo_field,bar_field',
        ];

        $expectedParams = [
            [
                'modules' => ['Foo', 'Bar'],
                'group' => 'or',
                'field_list' => [
                    'foo_field',
                    'bar_field',
                    'foo_search_field',
                    'bar_search_field',
                ],
                'conditions' => [
                    [
                        'name' => 'foo_search_field',
                        'op' => 'starts_with',
                        'value' => 'woo',
                    ],
                    [
                        'name' => 'bar_search_field',
                        'op' => 'starts_with',
                        'value' => 'woo',
                    ],
                ],
            ],
        ];

        $this->assertEquals(
            $expectedParams,
            SugarTestReflection::callProtectedMethod(
                $this->calendarApi,
                'buildSearchParams',
                [$args]
            ),
            'Rest API args should be transformed correctly into legacy query params'
        );
    }

    public function testTransformInvitees_ConvertsLegacyResultsToUnifiedSearchForm()
    {
        $args = [
            'q' => 'bar',
            'fields' => 'first_name,last_name,email,account_name',
            'search_fields' => 'first_name,last_name,email,account_name',
        ];

        $bean = new SugarBean(); //dummy, mocking out formatBean anyway
        $formattedBean = [
            '_module' => 'Contacts',
            'first_name' => 'Foo',
            'last_name' => 'Bar',
            'account_name' => 'Baz Inc',
            'email' => [
                ['email_address' => 'foo@baz.com'],
                ['email_address' => 'bar@baz.com'],
            ],
        ];

        $this->calendarApi = $this->createPartialMock(
            'CalendarApi',
            ['formatBean']
        );
        $this->calendarApi->expects($this->once())
            ->method('formatBean')
            ->will($this->returnValue($formattedBean));

        $searchResults = [
            'result' => [
                'list' => [
                    ['bean' => $bean],
                ],
            ],
        ];

        $expectedInvitee = array_merge($formattedBean, [
            '_search' => [
                'highlighted' => [
                    'last_name' => [
                        'text' => 'Bar',
                        'module' => 'Contacts',
                        'label' => 'LBL_LAST_NAME',
                    ],
                ],
            ],
        ]);

        $expectedInvitees = [
            'next_offset' => -1,
            'records' => [
                $expectedInvitee,
            ],
        ];
        ;

        $this->assertEquals(
            $expectedInvitees,
            SugarTestReflection::callProtectedMethod(
                $this->calendarApi,
                'transformInvitees',
                [$this->api, $args, $searchResults]
            ),
            'Legacy search results should be transformed correctly into unified search format'
        );
    }

    public function testGetMatchedFields_MatchesRegularFieldsCorrectly()
    {
        $args = [
            'q' => 'foo',
            'search_fields' => 'first_name,last_name,email,account_name',
        ];

        $record = [
            '_module' => 'Contacts',
            'first_name' => 'Foo',
            'last_name' => 'Bar',
            'account_name' => 'Baz Inc',
            'email' => [
                ['email_address' => 'woo@baz.com'],
                ['email_address' => 'bar@baz.com'],
            ],
        ];

        $expectedMatchedFields = [
            'first_name' => [
                'text' => 'Foo',
                'module' => 'Contacts',
                'label' => 'LBL_FIRST_NAME',
            ],
        ];

        $this->assertEquals(
            $expectedMatchedFields,
            SugarTestReflection::callProtectedMethod(
                $this->calendarApi,
                'getMatchedFields',
                [$args, $record, 1]
            ),
            'Should match search query to field containing search text'
        );
    }

    public function testGetMatchedFields_MatchesEmailFieldCorrectly()
    {
        $args = [
            'q' => 'woo',
            'search_fields' => 'first_name,last_name,email,account_name',
        ];

        $record = [
            '_module' => 'Contacts',
            'first_name' => 'Foo',
            'last_name' => 'Bar',
            'account_name' => 'Baz Inc',
            'email' => [
                ['email_address' => 'woo@baz.com'],
                ['email_address' => 'bar@baz.com'],
            ],
        ];

        $expectedMatchedFields = [
            'email' => [
                'text' => 'woo@baz.com',
                'module' => 'Contacts',
                'label' => 'LBL_EMAIL_ADDRESS',
            ],
        ];

        $this->assertEquals(
            $expectedMatchedFields,
            SugarTestReflection::callProtectedMethod(
                $this->calendarApi,
                'getMatchedFields',
                [$args, $record, 1]
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
        $dp->$linkName->add([$contact]);

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
