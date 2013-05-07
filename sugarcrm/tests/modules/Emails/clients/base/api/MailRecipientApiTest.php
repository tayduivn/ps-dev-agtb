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

require_once('tests/rest/RestTestBase.php');

/***
 * Used to test Mail Recipient API in Emails Module endpoints from MailRecipientApi.php.
 */
class MailRecipientApiTest extends RestTestBase
{
    private $recipients = array();
    private $bean_ids = array();

    public function setUp()
    {
        parent::setUp();

        $recipients = array(
            array("type" => "accounts", "name" => "This Account",   "email" => "this_account@yahoo.com"),
            array("type" => "accounts", "name" => "That Account ",  "email" => "that_account@yahoo.com"),

            array("type" => "contacts", "first_name" => "John",    "last_name" => "Doe",        "email" => "john_doe@yahoo.com"),
            array("type" => "contacts", "first_name" => "Sam",     "last_name" => "The Sham",   "email" => "sam_the_sham@yahoo.com"),
            array("type" => "contacts", "first_name" => "Jiminy",  "last_name" => "Crickett",   "email" => "jiminy_crickett@gmail.com"),

            array("type" => "leads",    "first_name" => "Davey",   "last_name" => "Crockett",   "email" => "davey_crockett@alamo.com"),
            array("type" => "leads",    "first_name" => "Jim",     "last_name" => "Bowie",      "email" => "jim_bowie@alamo.com"),
            array("type" => "leads",    "first_name" => "Sam",     "last_name" => "Houston",    "email" => "sam_houston@alamo.com"),
        );

        foreach ($recipients AS $recipient) {
            switch ($recipient['type']) {
                case 'accounts': {
                    $bean = SugarTestAccountUtilities::createAccount(null, $recipient);
                    $this->recipients[] = $bean;
                    $this->bean_ids[$bean->id] = true;
                    break;
                }
                case 'contacts': {
                    $bean = SugarTestContactUtilities::createContact(null, $recipient);
                    $this->recipients[] = $bean;
                    $this->bean_ids[$bean->id] = true;
                    break;
                }
                case 'leads': {
                    $bean = SugarTestLeadUtilities::createLead(null, $recipient);
                    $this->recipients[] = $bean;
                    $this->bean_ids[$bean->id] = true;
                    break;
                }
                default: {
                    break;
                }
            }
        }
    }

    public function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * @group mailrecipientapi
     */
    public function testListRecipients_SearchAllModules_ExpectTwoMatches()
    {
        $this->markTestIncomplete('Migrate this to SOAP UI');
        $max_num     = 500;
        $module_list = 'all';
        $filter      = 'sam_';

        $args = "?q={$filter}";
        $args .= "&module_list={$module_list}";
        $args .= "&max_num={$max_num}";

        $response = $this->_restCall("MailRecipient/{$args}", '', "GET");
        $this->assertHttpStatus($response);

        $reply = $this->getFormattedReply($response['reply']);
        $this->assertEquals(2, count($reply['records']), "Expecting Two Matches");
    }

    /**
     * @group mailrecipientapi
     */
    public function testListRecipients_SearchOnlyAccountsMatchingAnEmail()
    {
        $this->markTestIncomplete('Migrate this to SOAP UI');
        $max_num     = 500;
        $module_list = 'accounts';
        $filter      = 'this_account@';

        $args = "?q={$filter}";
        $args .= "&module_list={$module_list}";
        $args .= "&max_num={$max_num}";

        $response = $this->_restCall("MailRecipient/{$args}", '', "GET");
        $this->assertHttpStatus($response);

        $reply = $this->getFormattedReply($response['reply']);
        $this->assertEquals(1, count($reply['records']), "Expecting One Match on Email Filter");
        $this->assertEquals("this_account@yahoo.com", $reply['records'][0]['email'], "Unexpected Match");
    }

    /**
     * @group mailrecipientapi
     */
    public function testListRecipients_SearchOnlyContacts_OrderByEmailDesc()
    {
        $this->markTestIncomplete('Migrate this to SOAP UI');
        $max_num     = 500;
        $order_by    = 'email:desc';
        $module_list = 'contacts';
        $filter      = '';

        $args = "?q={$filter}";
        $args .= "&module_list={$module_list}";
        $args .= "&order_by={$order_by}";
        $args .= "&max_num={$max_num}";

        $response = $this->_restCall("MailRecipient/{$args}", '', "GET");
        $this->assertHttpStatus($response);

        $reply = $this->getFormattedReply($response['reply']);
        $this->assertEquals(3, count($reply['records']), "Expecting Three Matches");

        $this->assertEquals("sam_the_sham@yahoo.com", $reply['records'][0]['email'], "Unexpected Sort Order");
    }

    /**
     * @group mailrecipientapi
     */
    public function testListRecipients_SearchOnlyLeads_ExpectOneMatch()
    {
        $this->markTestIncomplete('Migrate this to SOAP UI');
        $max_num     = 500;
        $module_list = 'leads';
        $filter      = 'sam_';

        $args = "?q={$filter}";
        $args .= "&module_list={$module_list}";
        $args .= "&max_num={$max_num}";

        $response = $this->_restCall("MailRecipient/{$args}", '', "GET");
        $this->assertHttpStatus($response);

        $reply = $this->getFormattedReply($response['reply']);
        $this->assertEquals(1, count($reply['records']), "Expecting One Match");

        $this->assertEquals("Leads", $reply['records'][0]['module'], "Unexpected Match - Module");
        $this->assertEquals("sam_houston@alamo.com", $reply['records'][0]['email'], "Unexpected Match - Email");
    }

    /**
     * @group mailrecipientapi
     * @note This is a dangerous test because it could end up in an infinite loop. Although, that's highly unlikely.
     * More likely is that it just takes some time to complete. Using a test database instead of the development
     * database would improve test performance and make all tests in this suite less brittle.
     */
    public function testListRecipients_SearchOnlyLeads_NextOffsetShouldBecomeNegativeOneWhenThereAreNoMoreRecords()
    {
        $this->markTestIncomplete('Migrate this to SOAP UI');
        $max_num     = 500; // the database might really have a large number, so set this high to limit the iterations of the for loop
        $module_list = "leads";
        $offset      = 0;

        // mimic the behavior of pagination
        while ($offset > -1) {
            $args = "?module_list={$module_list}";
            $args .= "&max_num={$max_num}";
            $args .= "&offset={$offset}";

            $response = $this->_restCall("MailRecipient/{$args}", "", "GET");
            $this->assertHttpStatus($response);

            $reply = $this->getFormattedReply($response["reply"]);
            $offset = $reply["next_offset"];
        }

        $this->assertEquals(-1, $offset, "The offset should be -1 as there are no more records");
    }

    private function getFormattedReply($reply)
    {
        $records = array();

        foreach ($reply['records'] as $record) {
            if (isset($this->bean_ids[$record['id']])) {
                $records[] = $record;
            }
        }

        $formattedReply                = array();
        $formattedReply['next_offset'] = $reply['next_offset'];
        $formattedReply['records']     = $records;

        return $formattedReply;
    }
}
