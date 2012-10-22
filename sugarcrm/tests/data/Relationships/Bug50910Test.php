<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

class Bug50910Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $emailAddress;

    public function setUp()
    {
        global $beanFiles, $beanList, $current_user;
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['db']->commit();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        if(!empty($this->emailAddress))
        {
            $GLOBALS['db']->query("DELETE FROM emails WHERE id='{$this->emailAddress->id}'");
            $GLOBALS['db']->query("DELETE FROM emails_beans WHERE email_id='{$this->emailAddress->id}'");
            $GLOBALS['db']->query("DELETE FROM emails_email_addr_rel WHERE email_id='{$this->emailAddress->id}'");
        }
    }

    public function testSugarRelationshipsAddRow()
    {
        global $current_user;
        // create email address instance
        $this->emailAddress = new EmailAddress();
        $this->emailAddress->email_address = 'Bug59010Test@test.com';
        $this->emailAddress->save();

        // create relation between user and email address with empty additional data to test if the addRow function
        // properly handles empty values with not generating incorrect SQL
        $current_user->load_relationship('email_addresses');
        $current_user->email_addresses->add(array($this->emailAddress), array());
        $this->assertNotEmpty($current_user->email_addresses);

    }
}
