<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * @ticket 56938
 */
class Bug56938Test extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var User */
    private $user, $duplicate;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->user = SugarTestUserUtilities::createAnonymousUser();
        SugarTestEmailAddressUtilities::addAddressToPerson(
            $this->user,
            'bug-56938-test@example.com'
        );
    }

    /**
     * Ensure that a new instance of EmailAddress is created during creating
     * User duplicate
     */
    public function testCreateDuplicate()
    {
        // retrieve created user from database in order to populate email addresses
        $original = new User();
        $original->retrieve($this->user->id);

        // simulate request parameters of "Duplicate" web form
        $address = $original->emailAddress->addresses[0];
        $_REQUEST = array(
            'Users_email_widget_id' => '1',
            'Users1emailAddress0'   => $address['email_address'],
            'Users1emailAddressId0' => $address['email_address_id'],
        );

        // create a duplicate and retrieve it from database as well
        $duplicate = $this->duplicate = new User();
        $duplicate->save();

        $retrieved = new User();
        $retrieved->retrieve($duplicate->id);

        // ensure that email address is created in duplicate
        $this->assertEquals(1, count($retrieved->emailAddress->addresses));

        // ensure that it's value is the same as original email address
        $this->assertEquals(
            $original->emailAddress->addresses[0]['email_address'],
            $retrieved->emailAddress->addresses[0]['email_address']
        );

        // ensure that new instance of EmailAddress is created instead of
        // sharing the same instance between users
        $this->assertNotEquals(
            $original->emailAddress->addresses[0]['email_address_id'],
            $retrieved->emailAddress->addresses[0]['email_address_id']
        );
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $_REQUEST = array();
        SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        if ($this->duplicate)
        {
            $this->duplicate->mark_deleted($this->duplicate->id);
        }
    }
}
