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
 *
 * @author rbacon
 * Date: 2012.10.30
 * Time: 12:08
 ********************************************************************************/

require_once 'include/SugarEmailAddress/SugarEmailAddress.php';
require_once 'SugarTestSugarEmailAddressUtilities.php';

class SugarEmailAddressAddChangeTest extends Sugar_PHPUnit_Framework_TestCase
{

    protected $email;
    protected $old_email = 'test@sugar.example.com';
    protected $old_uuid;

    protected function setUp()
    {$this->markTestIncomplete();
        $this->email = SugarTestSugarEmailAddressUtilities::createEmailAddress($this->old_email);
        $this->old_uuid = SugarTestSugarEmailAddressUtilities::fetchEmailIdByAddress($this->old_email);

    }

    protected function tearDown()
    {$this->markTestIncomplete();
        SugarTestSugarEmailAddressUtilities::removeCreatedContactAndRelationships();
        SugarTestSugarEmailAddressUtilities::removeAllCreatedEmailAddresses();
    }

    /**
     * @group bug57426
     */
    public function testEmailAddressesBrandNew()
    {$this->markTestIncomplete();
        // now change the email, keeping track of bean UUIDs
        $old_uuid = $this->old_uuid;
        $uuid = $this->email->AddUpdateEmailAddress('george@sugar.example.com');

        $this->assertNotNull($uuid, 'Failed to enter the new email in the database!');
        $this->assertNotNull($old_uuid, 'Not seeing the old email in the database!');
        $this->assertNotEquals($uuid, $old_uuid, 'Same Email Address Bean used for different Email Addresses!');
    }

    public function testEmailAddressesChangeCaps()
    {
        $this->markTestIncomplete();
        $new_address = 'TEST@SUGAR.example.COM';
        // change the email with caps
        $old_uuid = $this->old_uuid;
        $uuid = $this->email->AddUpdateEmailAddress($new_address);

        $this->assertNotNull($uuid, 'Failed to enter the new email in the database!');
        $this->assertNotNull($old_uuid, 'Not seeing the old email in the database!');
        $this->assertEquals($uuid, $old_uuid, 'Different Email Address Bean used for same Email Address!');

        $new_sea = new SugarEmailAddress();
        $new_sea->retrieve($uuid);
        $this->assertNotNull($new_sea, 'Email Address not found in DB!');
        $this->assertEquals($new_address, $new_sea->email_address, 'Email Address in DB was not updated.');
    }

    public function testEmailSimulatedInvalidFlagWorkflow()
    {$this->markTestIncomplete();

        $workflow_email = 'testworkflow@sugar.example.com';
        $new_email = 'afreshnewemail@sugar.example.com';

        // simulate a before workflow: invalid is set to true
        $email_old_invalid = SugarTestSugarEmailAddressUtilities::createEmailAddress($workflow_email,'',array('invalid' => true));
        $old_uuid = SugarTestSugarEmailAddressUtilities::fetchEmailIdByAddress($workflow_email);
        $contact = SugarTestSugarEmailAddressUtilities::getContact();

        $email_old_invalid->stash($contact->id, $contact->module_dir);
        $email_old_invalid->AddUpdateEmailAddress($workflow_email,0); // 'workflow'

        $uuid = $email_old_invalid->AddUpdateEmailAddress($new_email,1,0,$old_uuid); // workflow is processed

        $this->assertNotNull($old_uuid, 'where is the old email address?');
        $this->assertNotNull($uuid, 'where is our new email address?');
        $this->assertNotEquals($old_uuid, $uuid, 'we used the same Email Address Bean for different Email Addresses!');

        // need a way to see our new work
        $fresh_sea = new SugarEmailAddress();
        $fresh_sea->retrieve($uuid);

        $this->assertNotNull($fresh_sea, 'Email Address not found in DB!');
        $this->assertEquals($new_email, $fresh_sea->email_address, 'Email Address in DB is not the same as expected.');
        $this->assertNotEquals(1, intval($fresh_sea->invalid_email), 'Workflow changes to Email not protected');
    }
}
