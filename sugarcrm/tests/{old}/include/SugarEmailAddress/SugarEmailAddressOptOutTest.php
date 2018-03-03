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

require_once 'include/SugarEmailAddress/SugarEmailAddress.php';

/**
 * @coversDefaultClass SugarEmailAddress
 */
class SugarEmailAddressOptOutTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $configOptoutBackUp;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('current_user');
    }

    protected function setup()
    {
        parent::setUp();

        if (isset($GLOBALS['sugar_config']['new_email_addresses_opted_out'])) {
            $this->configOptoutBackUp = $GLOBALS['sugar_config']['new_email_addresses_opted_out'];
        }
    }

    protected function tearDown()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();

        if (isset($this->configOptoutBackUp)) {
            $GLOBALS['sugar_config']['new_email_addresses_opted_out'] = $this->configOptoutBackUp;
        } else {
            unset($GLOBALS['sugar_config']['new_email_addresses_opted_out']);
        }

        parent::tearDown();
    }

    public function optoutDataProvider()
    {
        return array(
            [true],
            [false],
        );
    }

    /**
     * @covers ::save
     * @dataProvider optoutDataProvider
     */
    public function testCreateContactWithNewEmailAddress_ConfigureOptoutDefault_OptoutPropertySet(bool $defaultOptout)
    {
        $GLOBALS['sugar_config']['new_email_addresses_opted_out'] = $defaultOptout;
        $contact = SugarTestContactUtilities::createContact();

        $contact = BeanFactory::retrieveBean('Contacts', $contact->id);
        $actualOptout = (bool)$contact->emailAddress->addresses[0]['opt_out'];
        $this->assertSame(
            $defaultOptout,
            $actualOptout,
            'Expected New Contact Email to Match Configured Optout Default: ' . intval($defaultOptout)
        );
    }
}
