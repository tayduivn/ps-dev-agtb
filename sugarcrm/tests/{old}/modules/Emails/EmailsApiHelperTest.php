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

/**
 * @coversDefaultClass EmailsApiHelper
 */
class EmailsApiHelperTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $helper;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('current_user');
    }

    protected function setUp()
    {
        parent::setUp();
        $api = SugarTestRestUtilities::getRestServiceMock();
        $this->helper = new EmailsApiHelper($api);
    }

    /**
     * @covers ::formatForApi
     */
    public function testFormatForApi()
    {
        $bean = BeanFactory::newBean('Emails');
        $bean->new_with_id = false;
        $bean->id = Uuid::uuid1();
        $bean->name = 'Renewal notice';
        $bean->state = Email::STATE_DRAFT;
        // There is no outbound email account with that ID.
        $bean->outbound_email_id = Uuid::uuid1();

        $fieldList = [
            'id',
            'name',
            'state',
            'outbound_email_id',
        ];
        $data = $this->helper->formatForApi($bean, $fieldList);

        // Testing for these attributes is unnecessary.
        unset($data['_acl']);

        $expected = [
            'id' => $bean->id,
            'name' => $bean->name,
            'state' => $bean->state,
        ];
        $this->assertEquals($expected, $data);
    }

    public function populateFromApiCreateWithOutboundEmailIdProvider()
    {
        $outboundEmailId = Uuid::uuid1();

        return [
            // Creating a new draft. outbound_email_id can be set.
            [
                // The client submits state=Draft and an outbound_email_id.
                [
                    'state' => Email::STATE_DRAFT,
                    'outbound_email_id' => $outboundEmailId,
                ],
                // The submitted outbound_email_id is accepted.
                $outboundEmailId,
            ],
            // Creating a new archived email. outbound_email_id cannot be set.
            [
                // The client submits state=Archived and an outbound_email_id.
                [
                    'state' => Email::STATE_ARCHIVED,
                    'outbound_email_id' => $outboundEmailId,
                ],
                // The submitted outbound_email_id is ignored.
                null,
            ],
        ];
    }

    /**
     * @covers ::populateFromApi
     * @dataProvider populateFromApiCreateWithOutboundEmailIdProvider
     * @param array $submittedData The arguments provided over the API. This must include an ID for outbound_email_id.
     * @param null|string $expected The expected value for outbound_email_id after the bean is populated.
     */
    public function testPopulateFromApi_CreateWithOutboundEmailId(array $submittedData, $expected)
    {
        $oe = BeanFactory::newBean('OutboundEmail');
        $oe->id = $submittedData['outbound_email_id'];
        BeanFactory::registerBean($oe);

        // Start with an empty bean.
        $bean = BeanFactory::newBean('Emails');
        $bean->id = Uuid::uuid1();
        $bean->new_with_id = true;

        $result = $this->helper->populateFromApi($bean, $submittedData);

        $this->assertTrue($result);
        $this->assertEquals($expected, $bean->outbound_email_id, "outbound_email_id should be {$expected}");

        BeanFactory::unregisterBean($oe);
    }

    public function populateFromApiUpdateOutboundEmailIdProvider()
    {
        $outboundEmailId = Uuid::uuid1();

        return [
            // outbound_email_id can be cleared.
            [
                $outboundEmailId,
                // Patching the record does not require the state argument.
                // The client submits an empty string for outbound_email_id.
                [
                    'outbound_email_id' => '',
                ],
                // The submitted outbound_email_id is accepted.
                '',
            ],
            // outbound_email_id can be changed when the email's state remains unchanged.
            [
                Uuid::uuid1(),
                // The client explicitly sets the state, like in a PUT use case. This is typical of what sidecar does.
                // The client also submits a different outbound_email_id.
                [
                    'state' => Email::STATE_DRAFT,
                    'outbound_email_id' => $outboundEmailId,
                ],
                // The submitted outbound_email_id is accepted.
                $outboundEmailId,
            ],
            // outbound_email_id can be changed when the email's state remains unchanged.
            [
                Uuid::uuid1(),
                // Patching the record does not require the state argument.
                // The client only submits an outbound_email_id.
                [
                    'outbound_email_id' => $outboundEmailId,
                ],
                // The submitted outbound_email_id is accepted.
                $outboundEmailId,
            ],
        ];
    }

    /**
     * @covers ::populateFromApi
     * @dataProvider populateFromApiUpdateOutboundEmailIdProvider
     * @param string $outboundEmailId The initial value of the archived email's outbound_email_id property.
     * @param array $submittedData The arguments provided over the API.
     * @param array $expected The expected values for specified bean properties.
     */
    public function testPopulateFromApi_UpdateOutboundEmailId($outboundEmailId, array $submittedData, $expected)
    {
        $oe = BeanFactory::newBean('OutboundEmail');
        $oe->id = $submittedData['outbound_email_id'];
        BeanFactory::registerBean($oe);

        // Start with an existing draft that has an outbound_email_id.
        $bean = BeanFactory::newBean('Emails');
        $bean->id = Uuid::uuid1();
        $bean->new_with_id = false;
        $bean->state = Email::STATE_DRAFT;
        $bean->outbound_email_id = $outboundEmailId;

        $result = $this->helper->populateFromApi($bean, $submittedData);

        $this->assertTrue($result);
        $this->assertEquals($expected, $bean->outbound_email_id, "outbound_email_id should be {$expected}");

        BeanFactory::unregisterBean($oe);
    }

    public function populateFromApiOutboundEmailIdShouldNotChangeProvider()
    {
        $outboundEmailId = Uuid::uuid1();

        return [
            [
                // Start with an existing archived email that has an outbound_email_id.
                [
                    'new_with_id' => false,
                    'state' => Email::STATE_ARCHIVED,
                    'outbound_email_id' => $outboundEmailId,
                ],
                // Patching the record does not require the state argument.
                // The client only submits an outbound_email_id.
                [
                    'outbound_email_id' => Uuid::uuid1(),
                ],
                // The submitted outbound_email_id is ignored.
                $outboundEmailId,
            ],
            [
                // Start with an existing archived email that has an outbound_email_id.
                [
                    'new_with_id' => false,
                    'state' => Email::STATE_ARCHIVED,
                    'outbound_email_id' => $outboundEmailId,
                ],
                // Patching the record does not require the state argument.
                // The client submits a null outbound_email_id.
                [
                    'outbound_email_id' => null,
                ],
                // The submitted outbound_email_id is ignored.
                $outboundEmailId,
            ],
            [
                // Start with an existing archived email that has an outbound_email_id.
                [
                    'new_with_id' => false,
                    'state' => Email::STATE_ARCHIVED,
                    'outbound_email_id' => $outboundEmailId,
                ],
                // Patching the record does not require the state argument.
                // The client submits an empty string for outbound_email_id.
                [
                    'outbound_email_id' => '',
                ],
                // The submitted outbound_email_id is ignored.
                $outboundEmailId,
            ],
            [
                // Start with an existing archived email that does not have an outbound_email_id.
                [
                    'new_with_id' => false,
                    'state' => Email::STATE_ARCHIVED,
                    'outbound_email_id' => null,
                ],
                // Patching the record does not require the state argument.
                // The client only submits an outbound_email_id.
                [
                    'outbound_email_id' => Uuid::uuid1(),
                ],
                // The submitted outbound_email_id is ignored.
                null,
            ],
            [
                // Start with an existing draft that does not have an outbound_email_id.
                [
                    'new_with_id' => false,
                    'state' => Email::STATE_DRAFT,
                    'outbound_email_id' => null,
                ],
                // Patching the record does not require the state argument.
                // The client submits a null outbound_email_id.
                [
                    'outbound_email_id' => null,
                ],
                // The submitted outbound_email_id is ignored.
                null,
            ],
            [
                // Start with an existing draft that has an outbound_email_id.
                [
                    'new_with_id' => false,
                    'state' => Email::STATE_DRAFT,
                    'outbound_email_id' => $outboundEmailId,
                ],
                // The client explicitly sets the state, like in a PUT use case. This is typical of what sidecar does.
                // The client does not submit an outbound_email_id.
                [
                    'state' => Email::STATE_DRAFT,
                ],
                $outboundEmailId,
            ],
            [
                // Start with an existing draft that has an outbound_email_id.
                [
                    'new_with_id' => false,
                    'state' => Email::STATE_DRAFT,
                    'outbound_email_id' => $outboundEmailId,
                ],
                // Patching the record does not require the state argument.
                // Assume that the client submits some arguments, but the state and outbound_email_id arguments were not
                // among them.
                [],
                $outboundEmailId,
            ],
            [
                // Start with an existing draft that has an outbound_email_id.
                [
                    'new_with_id' => false,
                    'state' => Email::STATE_DRAFT,
                    'outbound_email_id' => $outboundEmailId,
                ],
                // Patching the record does not require the state argument.
                // The client submits a null outbound_email_id.
                [
                    'outbound_email_id' => null,
                ],
                $outboundEmailId,
            ],
        ];
    }

    /**
     * @covers ::populateFromApi
     * @dataProvider populateFromApiOutboundEmailIdShouldNotChangeProvider
     * @param array $beanData The bean is initialized with these properties.
     * @param array $submittedData The arguments provided over the API. This must include an ID for outbound_email_id.
     * @param null|string $expected The expected value for outbound_email_id after the bean is populated.
     */
    public function testPopulateFromApi_OutboundEmailIdShouldNotChange(array $beanData, array $submittedData, $expected)
    {
        $bean = BeanFactory::newBean('Emails');
        $bean->id = Uuid::uuid1();

        foreach ($beanData as $field => $value) {
            $bean->{$field} = $value;
        }

        $result = $this->helper->populateFromApi($bean, $submittedData);

        $this->assertTrue($result);
        $this->assertEquals($expected, $bean->outbound_email_id, 'outbound_email_id should not have changed');
    }

    /**
     * @covers ::populateFromApi
     * @expectedException SugarApiExceptionInvalidParameter
     */
    public function testPopulateFromApi_InvalidOutboundEmailIdIsSubmitted()
    {
        $bean = BeanFactory::newBean('Emails');
        $bean->id = Uuid::uuid1();
        $bean->state = Email::STATE_DRAFT;

        $submittedData = ['outbound_email_id' => Uuid::uuid1()];

        $this->helper->populateFromApi($bean, $submittedData);
    }
}
