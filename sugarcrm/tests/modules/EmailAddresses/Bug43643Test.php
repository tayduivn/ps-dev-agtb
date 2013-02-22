<?php
//FILE SUGARCRM flav=pro ONLY
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

/**
 * @ticket 43643
 */
class Bug43643Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $current_user, $beanList, $beanFiles;
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        require('include/modules.php');
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }


    /**
     * Test EmailAddress::testReplyToAddress() method.
     */
    public function testReplyToAddress()
    {
        global $current_user;
        $current_user->load_relationship('email_addresses');

        $non_reply_to_value = 'non-reply-to@example.com';
        $non_reply_to_address = SugarTestEmailAddressUtilities::addAddressToPerson(
            $current_user,
            $non_reply_to_value
        );

        require_once 'modules/EmailAddresses/EmailAddress.php';
        $email_address = new EmailAddress();

        // ensure that the only email address value is returned
        $non_reply_to_result = $email_address->getReplyToAddress($current_user);
        $this->assertEquals($non_reply_to_value, $non_reply_to_result);

        // ensure that empty string is returned when there is no reply-to
        // address exists
        $reply_to_only_result1 = $email_address->getReplyToAddress($current_user, true);
        $this->assertEquals('', $reply_to_only_result1);

        // create reply-to address
        $reply_to_value = 'some-address-2@example.com';
        $reply_to_address = SugarTestEmailAddressUtilities::addAddressToPerson(
            $current_user,
            $reply_to_value,
            array(
                'reply_to_address' => true,
            )
        );

        // ensure that reply-to address is returned
        $reply_to_only_result2 = $email_address->getReplyToAddress($current_user, true);
        $this->assertEquals($reply_to_value, $reply_to_only_result2);

        // clean everything up
        $non_reply_to_address->mark_deleted($non_reply_to_address->id);
        $reply_to_address->mark_deleted($reply_to_address->id);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }
}
