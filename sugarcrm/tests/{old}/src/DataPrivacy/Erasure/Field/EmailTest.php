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

namespace Sugarcrm\SugarcrmTests\DataPrivacy\Erasure\Field;

use Sugarcrm\Sugarcrm\DataPrivacy\Erasure\Field\Email;
use SugarTestContactUtilities;
use SugarTestEmailAddressUtilities;
use SugarTestLeadUtilities;
use SugarTestHelper;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\DataPrivacy\Erasure\Field\Email
 */
class EmailTest extends \Sugar_PHPUnit_Framework_TestCase
{
    private static $emailAddress;
    private static $contact;
    private static $lead;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('current_user', [true, true]);

        self::$emailAddress = SugarTestEmailAddressUtilities::createEmailAddress();
        self::$contact = SugarTestContactUtilities::createContact();
        SugarTestEmailAddressUtilities::addAddressToPerson(self::$contact, self::$emailAddress);
        self::$lead = SugarTestLeadUtilities::createLead();
        SugarTestEmailAddressUtilities::addAddressToPerson(self::$lead, self::$emailAddress);
    }

    public static function tearDownAfterClass()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
        parent::tearDownAfterClass();
    }

    /**
     * @test
     * @covers ::__construct()
     * @covers ::erase()
     * @covers ::getAllRelatedBeans
     */
    public function erase()
    {
        $email = new Email(self::$emailAddress->id);

        $count = count(\SugarTestReflection::callProtectedMethod($email, 'getAllRelatedBeans', [self::$emailAddress]));
        $this->assertEquals(2, $count);

        $email->erase(\BeanFactory::newBean('Contacts'));

        $count = count(\SugarTestReflection::callProtectedMethod($email, 'getAllRelatedBeans', [self::$emailAddress]));
        $this->assertEquals(0, $count);
    }
}
