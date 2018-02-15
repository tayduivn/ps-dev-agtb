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
 * @coversDefaultClass ContactsApi
 */
class ContactsApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $api;
    private $contactsApi;
    private $configOptoutBackUp;

    private $contactIds = array();
    private $emailAddressIds = array();

    protected function setUp()
    {
        SugarTestHelper::setUp('current_user');
        parent::setUp();
        if (is_null($this->configOptoutBackUp) && isset($GLOBALS['sugar_config']['new_email_addresses_opted_out'])) {
            $this->configOptoutBackUp = $GLOBALS['sugar_config']['new_email_addresses_opted_out'];
        }
        $this->contactIds = array();

        $this->api = SugarTestRestUtilities::getRestServiceMock();
        $this->api->user = $GLOBALS['current_user'];
        $GLOBALS['current_user'] = $this->api->user;
        $this->contactsApi = new ContactsApi();
    }

    protected function tearDown()
    {
        BeanFactory::setBeanClass('Contacts');
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
        if (!empty($this->contactIds)) {
            $ids = implode("','", $this->contactIds);
            $GLOBALS['db']->query("DELETE FROM contacts WHERE id IN ('" . $ids . "')");
            $this->contactIds = array();
        }
        if (!empty($this->emailAddressIds)) {
            $ids = implode("','", $this->emailAddressIds);
            $GLOBALS['db']->query("DELETE FROM email_addresses WHERE id IN ('" . $ids . "')");
            $this->emailAddressIds = array();
        }
        parent::tearDown();
        if (!is_null($this->configOptoutBackUp)) {
            $GLOBALS['sugar_config']['new_email_addresses_opted_out'] = $this->configOptoutBackUp;
        }
    }

    public function contactDataProvider()
    {
        $uuid = Uuid::uuid1();
        return array(
            array(
                "foo_{$uuid}@bar.biz",
                true,
                false,
                false,
            ),
            array(
                "foo_{$uuid}@bar.biz",
                true,
                true,
                false,
            ),
            array(
                "foo_{$uuid}@bar.biz",
                true,
                false,
                true,
            ),
            array(
                "foo_{$uuid}@bar.biz",
                true,
                true,
                true,
            ),
            array(
                "foo_{$uuid}@bar.biz",
                false,
                false,
                false,
            ),
            array(
                "foo_{$uuid}@bar.biz",
                false,
                true,
                false,
            ),
            array(
                "foo_{$uuid}@bar.biz",
                false,
                false,
                true,
            ),
            array(
                "foo_{$uuid}@bar.biz",
                false,
                true,
                true,
            ),
        );
    }

    /**
     * Create Contact Record with supplied Email properties
     * @dataProvider contactDataProvider
     * @covers ::createRecord
     * @param string $email
     * @param boolean $primary
     * @param boolean $optOut
     * @param boolean $invalidEmail
     */
    public function testCreateContact_EmailPropertiesSupplied($email, $primary, $optOut, $invalidEmail)
    {
        $args = array(
            'module' => 'Contacts',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => array(
                array(
                    'email_address' => $email,
                    'primary_address' => $primary,
                    'opt_out' => $optOut,
                    'invalid_email' => $invalidEmail,
                ),
            ),
        );

        $result = $this->contactsApi->createRecord($this->api, $args);
        $this->assertNotEmpty($result['email'][0]['email_address_id'], 'Email Address Id should not be empty');
        $this->emailAddressIds[] = $result['email'][0]['email_address_id'];

        $this->assertSame($email, $result['email'][0]['email_address'], 'email_address should match');
        $this->assertSame($primary, $result['email'][0]['primary_address'], 'primary property should match');
        $this->assertSame($optOut, $result['email'][0]['opt_out'], 'opt_out property should match');
        $this->assertSame($invalidEmail, $result['email'][0]['invalid_email'], 'invalid_email property should match');
    }

    public function optoutDataProvider()
    {
        return array(
            [true],
            [false],
        );
    }

    /**
     * Create Contact Record with Email Address - Optout value is defaulted based on Configuration
     *
     * @covers ::createRecord
     * @dataProvider optoutDataProvider
     */
    public function testCreateContactWithEmail_OptoutPropertyShouldDefaultToOptedIn(bool $defaultOptout)
    {
        $this->setConfigOptout($defaultOptout);

        $email =  'email_' . Uuid::uuid1() . '@bar.biz';
        $args = array(
            'module' => 'Contacts',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => array(
                array(
                    'email_address' =>  $email,
                ),
            ),
        );
        $result = $this->contactsApi->createRecord($this->api, $args);

        $this->assertNotEmpty($result['email'][0]['email_address_id'], 'Email Address Id should not be empty');
        $this->emailAddressIds[] = $result['email'][0]['email_address_id'];

        $this->assertSame($email, $result['email'][0]['email_address'], 'email_address should match');
        $this->assertSame($defaultOptout, $result['email'][0]['opt_out'], 'Email opt_out value does not match config');
    }

    /**
     * Set Email Optout Default Configuration
     */
    private function setConfigOptout(bool $optOut)
    {
        $GLOBALS['sugar_config']['new_email_addresses_opted_out'] = "{$optOut}";
    }
}
