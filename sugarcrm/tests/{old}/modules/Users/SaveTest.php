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
use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * User profile Save tests
 */
class SaveTest extends TestCase
{
    protected $tabs;
    protected $savedTabs;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('current_user', array(true, 1));
    }

    public static function tearDownAfterClass()
    {
        SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
        parent::tearDownAfterClass();
    }

    protected function setUp()
    {
        parent::setUp();
        $this->tabs = new TabController();
        $this->savedTabs = $this->tabs->get_user_tabs($GLOBALS['current_user']);
    }

    protected function tearDown()
    {
        $this->tabs->set_user_tabs($this->savedTabs, $GLOBALS['current_user'], 'display');
        parent::tearDown();
    }

    /**
     * Home always needs to be first display tab
     */
    public function testAddHomeToDisplayTabsOnSave()
    {
        $current_user = $GLOBALS['current_user'];
        $_POST['record'] = $current_user->id;
        $_REQUEST['display_tabs_def'] = 'display_tabs[]=Leads';  //Save only included Leads
        include 'modules/Users/Save.php';
        //Home was prepended
        $this->assertEquals(array('Home' => 'Home', 'Leads' => 'Leads'), $this->tabs->get_user_tabs($focus));
    }

    /**
     * @dataProvider saveLicenseTypeProvider
     */
    public function testSaveLicenseType(string $licenseType, bool $isAdmin, string $expected)
    {
        $this->current_user = SugarTestHelper::setUp('current_user', array(true, $isAdmin));

        $current_user = $this->current_user;
        $_POST['record'] = $current_user->id;
        $_POST['LicenseType'] = $licenseType;
        include 'modules/Users/Save.php';

        $record = BeanFactory::getBean('Users', $current_user->id);

        $this->assertEquals($expected, $record->license_type);
    }

    public function saveLicenseTypeProvider()
    {
        return [
            ['CURRENT', true, 'CURRENT'],
            ['SERVICE_CLOUD', true, 'SERVICE_CLOUD'],
            ['CURRENT', false, 'CURRENT'],
            ['SERVICE_CLOUD', false, 'CURRENT'],
        ];
    }

    public function testSaveOfOutboundEmailSystemOverrideConfiguration()
    {
        $current_user = $GLOBALS['current_user'];
        OutboundEmailConfigurationTestHelper::setUp();
        OutboundEmailConfigurationTestHelper::setAllowDefaultOutbound(0);
        OutboundEmailConfigurationTestHelper::createSystemOverrideOutboundEmailConfiguration($current_user->id);

        $_POST['record'] = $current_user->id;
        $_POST['first_name'] = 'Julia';
        $_POST['last_name'] = 'Watkins';
        $_POST['mail_smtpuser'] = $_REQUEST['mail_smtpuser'] = 'julia';
        $_POST['mail_smtppass'] = $_REQUEST['mail_smtppass'] = 'B5rz71Kg';

        include 'modules/Users/Save.php';

        unset($_POST['record']);
        unset($_POST['mail_smtpuser']);
        unset($_REQUEST['mail_smtpuser']);
        unset($_POST['mail_smtppass']);
        unset($_REQUEST['mail_smtppass']);

        $current_user->retrieve($current_user->id);
        $userData = $current_user->getUsersNameAndEmail();
        $emailAddressId = $current_user->emailAddress->getGuid($userData['email']);
        $oe = BeanFactory::newBean('OutboundEmail');
        $override = $oe->getUsersMailerForSystemOverride($current_user->id);

        $this->assertSame($userData['name'], $override->name, 'The names should match');
        $this->assertSame($current_user->id, $override->user_id, 'The current user should be the owner');
        $this->assertSame($userData['email'], $override->email_address, 'The email addresses should match');
        $this->assertSame($emailAddressId, $override->email_address_id, 'The email address IDs should match');
        $this->assertSame('julia', $override->mail_smtpuser, 'The usernames should match');
        $this->assertSame('B5rz71Kg', $override->mail_smtppass, 'The passwords should not match');

        OutboundEmailConfigurationTestHelper::tearDown();
    }

    /**
     * @covers User::save
     * @covers Person::save
     * @covers SugarEmailAddress::handleLegacySave
     * @covers SugarEmailAddress::populateAddresses
     * @covers SugarEmailAddress::addAddress
     * @covers SugarEmailAddress::getEmailGUID
     * @covers SugarEmailAddress::getGuid
     */
    public function testSaveReplacesTheEmailAddressForTheCurrentUserWithoutAffectingTheOtherUser()
    {
        $address1 = Uuid::uuid4() . '@example.com';
        $ea = SugarTestEmailAddressUtilities::createEmailAddress($address1);

        $address2 = Uuid::uuid4() . '@example.com';

        $current_user = $GLOBALS['current_user'];
        SugarTestEmailAddressUtilities::addAddressToPerson($current_user, $ea);

        $user2 = SugarTestUserUtilities::createAnonymousUser();
        SugarTestEmailAddressUtilities::addAddressToPerson($user2, $ea);

        $_POST['record'] = $current_user->id;
        $_POST['Users_email_widget_id'] = $_REQUEST['Users_email_widget_id'] = 0;
        $_POST['emailAddressWidget'] = $_REQUEST['emailAddressWidget'] = 1;
        $_POST['useEmailWidget'] = $_REQUEST['useEmailWidget'] = true;

        // Save the current user's primary email address so that it isn't removed.
        $_POST['Users0emailAddress0'] = $_REQUEST['Users0emailAddress0'] = $current_user->email1;
        $_POST['Users0emailAddressId0'] = $_REQUEST['Users0emailAddressId0'] = $ea->getGuid($current_user->email1);
        $_POST['Users0emailAddressVerifiedFlag0'] = $_REQUEST['Users0emailAddressVerifiedFlag0'] = true;
        $_POST['Users0emailAddressVerifiedValue0'] = $_REQUEST['Users0emailAddressVerifiedValue0'] = $current_user->email1;

        // Change the current user's secondary email address.
        // The ID and email address are not in sync. The address is different. The ID is still passed but not used when
        // saving the changes for the current user.
        $_POST['Users0emailAddress1'] = $_REQUEST['Users0emailAddress1'] = $address2;
        $_POST['Users0emailAddressId1'] = $_REQUEST['Users0emailAddressId1'] = $ea->id;
        // Mark the new email address invalid.
        $_POST['Users0emailAddressInvalidFlag'] = $_REQUEST['Users0emailAddressInvalidFlag'] = ['Users0emailAddress1'];
        $_POST['Users0emailAddressVerifiedFlag1'] = $_REQUEST['Users0emailAddressVerifiedFlag1'] = true;
        $_POST['Users0emailAddressVerifiedValue1'] = $_REQUEST['Users0emailAddressVerifiedValue1'] = $address2;

        include 'modules/Users/Save.php';

        unset($_POST['record']);
        unset($_POST['Users_email_widget_id']);
        unset($_REQUEST['Users_email_widget_id']);
        unset($_POST['emailAddressWidget']);
        unset($_REQUEST['emailAddressWidget']);
        unset($_POST['useEmailWidget']);
        unset($_REQUEST['useEmailWidget']);
        unset($_POST['Users0emailAddress0']);
        unset($_REQUEST['Users0emailAddress0']);
        unset($_POST['Users0emailAddressId0']);
        unset($_REQUEST['Users0emailAddressId0']);
        unset($_POST['Users0emailAddressVerifiedFlag0']);
        unset($_REQUEST['Users0emailAddressVerifiedFlag0']);
        unset($_POST['Users0emailAddressVerifiedValue0']);
        unset($_REQUEST['Users0emailAddressVerifiedValue0']);
        unset($_POST['Users0emailAddress1']);
        unset($_REQUEST['Users0emailAddress1']);
        unset($_POST['Users0emailAddressId1']);
        unset($_REQUEST['Users0emailAddressId1']);
        unset($_POST['Users0emailAddressInvalidFlag']);
        unset($_REQUEST['Users0emailAddressInvalidFlag']);
        unset($_POST['Users0emailAddressVerifiedFlag1']);
        unset($_REQUEST['Users0emailAddressVerifiedFlag1']);
        unset($_POST['Users0emailAddressVerifiedValue1']);
        unset($_REQUEST['Users0emailAddressVerifiedValue1']);

        // Make sure we can clean up the new email address.
        SugarTestEmailAddressUtilities::setCreatedEmailAddressByAddress($address2);

        $current_user->retrieve($current_user->id);
        $user2->retrieve($user2->id);

        $currentUserIndex = ($ea->id == $current_user->emailAddress->addresses[0]['email_address_id']) ? 0 : 1;
        $user2Index = ($ea->id == $user2->emailAddress->addresses[0]['email_address_id']) ? 0 : 1;

        $this->assertCount(
            2,
            $current_user->emailAddress->addresses,
            'The current user should have two email addresses'
        );
        $this->assertEquals(
            1,
            $current_user->emailAddress->addresses[$currentUserIndex]['invalid_email'],
            'The email address should be have been marked invalid'
        );

        // None of the current user's email addresses should be address1.
        foreach ($current_user->emailAddress->addresses as $address) {
            $this->assertNotEquals(
                $ea->id,
                $address['email_address_id'],
                'The current user should not be linked to the same email address as user2'
            );
            $this->assertNotEquals(
                $address1,
                $address['email_address'],
                'The current user should not have the same email address as user2'
            );
        }

        $this->assertCount(
            2,
            $user2->emailAddress->addresses,
            'user2 should have two email addresses'
        );
        $this->assertEquals(
            0,
            $user2->emailAddress->addresses[$user2Index]['invalid_email'],
            'The email address should not have been changed'
        );
        $this->assertEquals(
            $ea->id,
            $user2->emailAddress->addresses[$user2Index]['email_address_id'],
            'user2 should still be linked to address1'
        );
        $this->assertEquals(
            $address1,
            $user2->emailAddress->addresses[$user2Index]['email_address'],
            'user2 should still have address1'
        );

        // None of user2's email addresses should be address2.
        foreach ($user2->emailAddress->addresses as $address) {
            $this->assertNotEquals(
                $current_user->emailAddress->addresses[$user2Index]['email_address_id'],
                $address['email_address_id'],
                "user2 should not be linked to {$address2}"
            );
            $this->assertNotEquals(
                $address2,
                $address['email_address'],
                "user2 should not have {$address2}"
            );
        }
    }

    /**
     * @covers User::save
     * @covers Person::save
     * @covers SugarEmailAddress::handleLegacySave
     * @covers SugarEmailAddress::populateAddresses
     * @covers SugarEmailAddress::addAddress
     * @covers SugarEmailAddress::getEmailGUID
     * @covers SugarEmailAddress::getGuid
     */
    public function testSaveUpdatesTheEmailAddressForBothUsers()
    {
        $address = Uuid::uuid4() . '@example.com';
        $ea = SugarTestEmailAddressUtilities::createEmailAddress($address);

        $current_user = $GLOBALS['current_user'];
        SugarTestEmailAddressUtilities::addAddressToPerson($current_user, $ea);

        $user2 = SugarTestUserUtilities::createAnonymousUser();
        SugarTestEmailAddressUtilities::addAddressToPerson($user2, $ea);

        $_POST['record'] = $current_user->id;
        $_POST['Users_email_widget_id'] = $_REQUEST['Users_email_widget_id'] = 0;
        $_POST['emailAddressWidget'] = $_REQUEST['emailAddressWidget'] = 1;
        $_POST['useEmailWidget'] = $_REQUEST['useEmailWidget'] = true;

        // Save the current user's primary email address so that it isn't removed.
        $_POST['Users0emailAddress0'] = $_REQUEST['Users0emailAddress0'] = $current_user->email1;
        $_POST['Users0emailAddressId0'] = $_REQUEST['Users0emailAddressId0'] = $ea->getGuid($current_user->email1);
        $_POST['Users0emailAddressVerifiedFlag0'] = $_REQUEST['Users0emailAddressVerifiedFlag0'] = true;
        $_POST['Users0emailAddressVerifiedValue0'] = $_REQUEST['Users0emailAddressVerifiedValue0'] = $current_user->email1;

        // Change the email address without creating a new one.
        $_POST['Users0emailAddress1'] = $_REQUEST['Users0emailAddress1'] = $address;
        $_POST['Users0emailAddressId1'] = $_REQUEST['Users0emailAddressId1'] = $ea->id;
        // Mark the email address invalid.
        $_POST['Users0emailAddressInvalidFlag'] = $_REQUEST['Users0emailAddressInvalidFlag'] = ['Users0emailAddress1'];
        $_POST['Users0emailAddressVerifiedFlag1'] = $_REQUEST['Users0emailAddressVerifiedFlag1'] = true;
        $_POST['Users0emailAddressVerifiedValue1'] = $_REQUEST['Users0emailAddressVerifiedValue1'] = $address;

        include 'modules/Users/Save.php';

        unset($_POST['record']);
        unset($_POST['Users_email_widget_id']);
        unset($_REQUEST['Users_email_widget_id']);
        unset($_POST['emailAddressWidget']);
        unset($_REQUEST['emailAddressWidget']);
        unset($_POST['useEmailWidget']);
        unset($_REQUEST['useEmailWidget']);
        unset($_POST['Users0emailAddress0']);
        unset($_REQUEST['Users0emailAddress0']);
        unset($_POST['Users0emailAddressId0']);
        unset($_REQUEST['Users0emailAddressId0']);
        unset($_POST['Users0emailAddressVerifiedFlag0']);
        unset($_REQUEST['Users0emailAddressVerifiedFlag0']);
        unset($_POST['Users0emailAddressVerifiedValue0']);
        unset($_REQUEST['Users0emailAddressVerifiedValue0']);
        unset($_POST['Users0emailAddress1']);
        unset($_REQUEST['Users0emailAddress1']);
        unset($_POST['Users0emailAddressId1']);
        unset($_REQUEST['Users0emailAddressId1']);
        unset($_POST['Users0emailAddressInvalidFlag']);
        unset($_REQUEST['Users0emailAddressInvalidFlag']);
        unset($_POST['Users0emailAddressVerifiedFlag1']);
        unset($_REQUEST['Users0emailAddressVerifiedFlag1']);
        unset($_POST['Users0emailAddressVerifiedValue1']);
        unset($_REQUEST['Users0emailAddressVerifiedValue1']);

        $current_user->retrieve($current_user->id);
        $user2->retrieve($user2->id);

        $currentUserIndex = ($ea->id == $current_user->emailAddress->addresses[0]['email_address_id']) ? 0 : 1;
        $user2Index = ($ea->id == $user2->emailAddress->addresses[0]['email_address_id']) ? 0 : 1;

        $this->assertCount(
            2,
            $current_user->emailAddress->addresses,
            'The current user should have two email addresses'
        );
        $this->assertEquals(
            $ea->id,
            $current_user->emailAddress->addresses[$currentUserIndex]['email_address_id'],
            'The current user should still be linked to address'
        );
        $this->assertEquals(
            $address,
            $current_user->emailAddress->addresses[$currentUserIndex]['email_address'],
            'The current user should still have address'
        );
        $this->assertEquals(
            1,
            $current_user->emailAddress->addresses[$currentUserIndex]['invalid_email'],
            'The email address should be have been marked invalid for the current user'
        );

        $this->assertCount(
            2,
            $user2->emailAddress->addresses,
            'user2 should have two email addresses'
        );
        $this->assertEquals(
            $ea->id,
            $user2->emailAddress->addresses[$user2Index]['email_address_id'],
            'user2 should still be linked to address'
        );
        $this->assertEquals(
            $address,
            $user2->emailAddress->addresses[$user2Index]['email_address'],
            'user2 should still have address'
        );
        $this->assertEquals(
            1,
            $user2->emailAddress->addresses[$user2Index]['invalid_email'],
            'The email address should be have been marked invalid for user2'
        );
    }
}
