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

class Bug40911 extends TestCase
{
    protected function setUp() : void
    {
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->_user;
    }
    
    protected function tearDown() : void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestEmailUtilities::removeAllCreatedEmails();
        unset($GLOBALS['current_user']);
    }

    /**
     * Save a SugarFolder 
     */
    public function testSaveNewFolder()
    {
        global $app_strings;

        $data = array(
            'type' => 'out',
            'status' => 'sent',
            'state' => Email::STATE_ARCHIVED,
            'from_addr' => 'sender@domain.eu',
            'to_addrs' => 'to@domain.eu',
            'cc_addrs' => 'cc@domain.eu',
        );
        $email = SugarTestEmailUtilities::createEmail('', $data);

        $_REQUEST["emailUIAction"] = "getSingleMessageFromSugar";
        $_REQUEST["uid"] = $email->id;
        $_REQUEST["mbox"] = "";
        $_REQUEST['ieId'] = "";
        ob_start();
        require "modules/Emails/EmailUIAjax.php";
        $jsonOutput = ob_get_contents();
        ob_end_clean();
        $meta = json_decode($jsonOutput);

        $this->assertRegExp("/.*cc@domain.eu.*/", $meta->meta->email->cc);
    }
    
}
