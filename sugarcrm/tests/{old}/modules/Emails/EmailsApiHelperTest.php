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

    /**
     * @covers ::populateFromApi
     * @expectedException SugarApiExceptionNotFound
     */
    public function testPopulateFromApi()
    {
        $bean = BeanFactory::newBean('Emails');
        $bean->id = Uuid::uuid1();

        $submittedData = [
            'state' => Email::STATE_DRAFT,
            'outbound_email_id' => Uuid::uuid1(),
        ];

        $this->helper->populateFromApi($bean, $submittedData);
    }
}
