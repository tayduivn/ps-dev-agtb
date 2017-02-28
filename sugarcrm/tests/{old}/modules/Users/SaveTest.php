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

/**
 * User profile Save tests
 *
 * @author mmarum
 */
class SaveTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $current_user;
    protected $tabs;
    protected $savedTabs;

    public function setUp()
    {
        parent::setUp();
        $this->current_user = SugarTestHelper::setUp('current_user', array(true, 1));
        $this->tabs = new TabController();
        $this->savedTabs = $this->tabs->get_user_tabs($this->current_user);
    }

    public function tearDown()
    {
        $this->tabs->set_user_tabs($this->savedTabs, $this->current_user, "display");
        unset($this->bean);
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * Home always needs to be first display tab
     */
    public function testAddHomeToDisplayTabsOnSave()
    {
        $current_user = $this->current_user;
        $_POST['record'] = $current_user->id;
        $_REQUEST['display_tabs_def'] = 'display_tabs[]=Leads';  //Save only included Leads
        include('modules/Users/Save.php');
        //Home was prepended
        $this->assertEquals(array('Home' => 'Home', 'Leads' => 'Leads'), $this->tabs->get_user_tabs($focus));
    }

    public function testSaveOfOutboundEmailSystemOverrideConfiguration()
    {
        $current_user = $this->current_user;
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
}
