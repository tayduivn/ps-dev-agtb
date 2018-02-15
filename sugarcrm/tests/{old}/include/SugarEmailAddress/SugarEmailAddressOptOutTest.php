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
    private $configOptoutBackUp = null;

    protected function setup()
    {
        if (is_null($this->configOptoutBackUp) && isset($GLOBALS['sugar_config']['new_email_addresses_opted_out'])) {
            $this->configOptoutBackUp = $GLOBALS['sugar_config']['new_email_addresses_opted_out'];
        }
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    protected function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
        if (!is_null($this->configOptoutBackUp)) {
            $GLOBALS['sugar_config']['new_email_addresses_opted_out'] = $this->configOptoutBackUp;
        }
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
        $this->setConfigOptout($defaultOptout);
        $contact = SugarTestContactUtilities::createContact();
        $contact->emailAddress->save($contact->id, $contact->module_dir);

        $contact = BeanFactory::getBean("Contacts", $contact->id);
        $actualOptout = (bool)$contact->emailAddress->addresses[0]['opt_out'];
        $this->assertEquals(
            $defaultOptout,
            $actualOptout,
            'Expected New Contact Email to Match Configured Optout Default: ' . intval($defaultOptout)
        );
    }

    private function setConfigOptout(bool $optOut)
    {
        $GLOBALS['sugar_config']['new_email_addresses_opted_out'] = "{$optOut}";
    }
}
