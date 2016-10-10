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

require_once 'modules/Emails/EmailsApiHelper.php';

/**
 * @coversDefaultClass EmailsApiHelper
 */
class EmailsApiHelperTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $helper;

    protected function setUp()
    {
        parent::setUp();
        $api = SugarTestRestUtilities::getRestServiceMock();
        $this->helper = new EmailsApiHelper($api);
    }

    public function populateFromApiProvider()
    {
        $outboundEmailId = create_guid();

        return array(
            // Creating a new draft. outbound_email_id can be set.
            array(
                // Start with an empty bean.
                array(
                    'new_with_id' => true,
                ),
                // The client submits state=Draft and an outbound_email_id.
                array(
                    'state' => Email::EMAIL_STATE_DRAFT,
                    'outbound_email_id' => $outboundEmailId,
                ),
                // The submitted outbound_email_id is accepted.
                array(
                    'outbound_email_id' => $outboundEmailId,
                ),
            ),
            // Creating a new archived email. outbound_email_id cannot be set.
            array(
                // Start with an empty bean.
                array(
                    'new_with_id' => true,
                ),
                // The client submits state=Archived and an outbound_email_id.
                array(
                    'state' => Email::EMAIL_STATE_ARCHIVED,
                    'outbound_email_id' => $outboundEmailId,
                ),
                // The submitted outbound_email_id is ignored.
                array(
                    'outbound_email_id' => null,
                ),
            ),
            // Updating a draft. outbound_email_id can be changed when the email's state remains unchanged.
            array(
                // Start with an existing draft that has an outbound_email_id.
                array(
                    'new_with_id' => false,
                    'state' => Email::EMAIL_STATE_DRAFT,
                    'outbound_email_id' => create_guid(),
                ),
                // The client explicitly sets the state, like in a PUT use case. This is typical of what sidecar does.
                // The client also submits a different outbound_email_id.
                array(
                    'state' => Email::EMAIL_STATE_DRAFT,
                    'outbound_email_id' => $outboundEmailId,
                ),
                // The submitted outbound_email_id is accepted.
                array(
                    'outbound_email_id' => $outboundEmailId,
                ),
            ),
            // Updating a draft. outbound_email_id can be changed when the email's state remains unchanged.
            array(
                // Start with an existing draft that has an outbound_email_id.
                array(
                    'new_with_id' => false,
                    'state' => Email::EMAIL_STATE_DRAFT,
                    'outbound_email_id' => create_guid(),
                ),
                // Patching the record does not require the state argument.
                // The client only submits an outbound_email_id.
                array(
                    'outbound_email_id' => $outboundEmailId,
                ),
                // The submitted outbound_email_id is accepted.
                array(
                    'outbound_email_id' => $outboundEmailId,
                ),
            ),
            // Updating a draft. outbound_email_id can be cleared.
            array(
                // Start with an existing draft that has an outbound_email_id.
                array(
                    'new_with_id' => false,
                    'state' => Email::EMAIL_STATE_DRAFT,
                    'outbound_email_id' => $outboundEmailId,
                ),
                // Patching the record does not require the state argument.
                // The client submits an empty string for outbound_email_id.
                array(
                    'outbound_email_id' => '',
                ),
                // The submitted outbound_email_id is accepted.
                array(
                    'outbound_email_id' => '',
                ),
            ),
            // Updating a draft. outbound_email_id is not changed unless the argument is provided.
            array(
                // Start with an existing draft that has an outbound_email_id.
                array(
                    'new_with_id' => false,
                    'state' => Email::EMAIL_STATE_DRAFT,
                    'outbound_email_id' => $outboundEmailId,
                ),
                // The client explicitly sets the state, like in a PUT use case. This is typical of what sidecar does.
                // The client does not submit an outbound_email_id.
                array(
                    'state' => Email::EMAIL_STATE_DRAFT,
                ),
                // The outbound_email_id remains unchanged.
                array(
                    'outbound_email_id' => $outboundEmailId,
                ),
            ),
            // Updating a draft. outbound_email_id is not changed unless the argument is provided.
            array(
                // Start with an existing draft that has an outbound_email_id.
                array(
                    'new_with_id' => false,
                    'state' => Email::EMAIL_STATE_DRAFT,
                    'outbound_email_id' => $outboundEmailId,
                ),
                // Patching the record does not require the state argument.
                // Assume that the client submits some arguments, but the state and outbound_email_id arguments were not
                // among them.
                array(),
                // The outbound_email_id remains unchanged.
                array(
                    'outbound_email_id' => $outboundEmailId,
                ),
            ),
            // Updating a draft. outbound_email_id is not changed unless the argument is provided.
            array(
                // Start with an existing draft that does not have an outbound_email_id.
                array(
                    'new_with_id' => false,
                    'state' => Email::EMAIL_STATE_DRAFT,
                ),
                // Patching the record does not require the state argument.
                // The client submits a null outbound_email_id.
                array(
                    'outbound_email_id' => null,
                ),
                // The outbound_email_id remains unchanged.
                array(
                    'outbound_email_id' => null,
                ),
            ),
            // Updating a draft. outbound_email_id is not changed unless the argument is provided.
            array(
                // Start with an existing draft that has an outbound_email_id.
                array(
                    'new_with_id' => false,
                    'state' => Email::EMAIL_STATE_DRAFT,
                    'outbound_email_id' => $outboundEmailId,
                ),
                // Patching the record does not require the state argument.
                // The client submits a null outbound_email_id.
                array(
                    'outbound_email_id' => null,
                ),
                // The outbound_email_id remains unchanged.
                array(
                    'outbound_email_id' => $outboundEmailId,
                ),
            ),
            // Updating an archived email. outbound_email_id cannot be changed.
            array(
                // Start with an existing archived email that has an outbound_email_id.
                array(
                    'new_with_id' => false,
                    'state' => Email::EMAIL_STATE_ARCHIVED,
                    'outbound_email_id' => $outboundEmailId,
                ),
                // Patching the record does not require the state argument.
                // The client only submits an outbound_email_id.
                array(
                    'outbound_email_id' => create_guid(),
                ),
                // The submitted outbound_email_id is ignored.
                array(
                    'outbound_email_id' => $outboundEmailId,
                ),
            ),
            // Updating an archived email. outbound_email_id cannot be changed.
            array(
                // Start with an existing archived email that has an outbound_email_id.
                array(
                    'new_with_id' => false,
                    'state' => Email::EMAIL_STATE_ARCHIVED,
                    'outbound_email_id' => $outboundEmailId,
                ),
                // Patching the record does not require the state argument.
                // The client submits a null outbound_email_id.
                array(
                    'outbound_email_id' => null,
                ),
                // The submitted outbound_email_id is ignored.
                array(
                    'outbound_email_id' => $outboundEmailId,
                ),
            ),
            // Updating an archived email. outbound_email_id cannot be changed.
            array(
                // Start with an existing archived email that has an outbound_email_id.
                array(
                    'new_with_id' => false,
                    'state' => Email::EMAIL_STATE_ARCHIVED,
                    'outbound_email_id' => $outboundEmailId,
                ),
                // Patching the record does not require the state argument.
                // The client submits an empty string for outbound_email_id.
                array(
                    'outbound_email_id' => '',
                ),
                // The submitted outbound_email_id is ignored.
                array(
                    'outbound_email_id' => $outboundEmailId,
                ),
            ),
            // Updating an archived email. outbound_email_id cannot be changed.
            array(
                // Start with an existing archived email that does not have an outbound_email_id.
                array(
                    'new_with_id' => false,
                    'state' => Email::EMAIL_STATE_ARCHIVED,
                ),
                // Patching the record does not require the state argument.
                // The client only submits an outbound_email_id.
                array(
                    'outbound_email_id' => create_guid(),
                ),
                // The submitted outbound_email_id is ignored.
                array(
                    'outbound_email_id' => null,
                ),
            ),
        );
    }

    /**
     * @covers ::populateFromApi
     * @dataProvider populateFromApiProvider
     * @param array $beanData The bean is initialized with these properties.
     * @param array $submittedData The arguments provided over the API.
     * @param array $expected The expected values for specified bean properties.
     */
    public function testPopulateFromApi(array $beanData, array $submittedData, array $expected)
    {
        $bean = BeanFactory::newBean('Emails');
        $bean->id = create_guid();

        foreach ($beanData as $field => $value) {
            $bean->{$field} = $value;
        }

        $result = $this->helper->populateFromApi($bean, $submittedData);
        $this->assertTrue($result);

        foreach ($expected as $field => $value) {
            $actual = $bean->{$field};
            $this->assertEquals($value, $actual, "{$field} should be {$value}, not {$actual}");
        }
    }
}
