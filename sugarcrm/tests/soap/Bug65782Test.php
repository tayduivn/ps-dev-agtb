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

require_once 'tests/service/SOAPTestCase.php';


/**
 * Bug #65782
 * SOAP API (v1) - get_entry_list retuning duplicates
 *
 * @author mgusev@sugarcrm.com
 * @ticked 65782
 */
class Bug65782Test extends SOAPTestCase
{
    /** @var Contact */
    protected $contact = null;

    /** @var Meeting */
    protected $meeting1 = null;

    /** @var Meeting */
    protected $meeting2 = null;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, true));

        $this->contact = SugarTestContactUtilities::createContact();
        $this->meeting1 = SugarTestMeetingUtilities::createMeeting();
        $this->meeting2 = SugarTestMeetingUtilities::createMeeting();
        SugarTestMeetingUtilities::addMeetingContactRelation($this->meeting1->id, $this->contact->id);
        SugarTestMeetingUtilities::addMeetingContactRelation($this->meeting2->id, $this->contact->id);

        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/soap.php';

        parent::setUp();

        $this->user = self::$_user = $GLOBALS['current_user'];
        $this->_login();
    }

    public function tearDown()
    {
        SugarTestMeetingUtilities::removeMeetingContacts();
        SugarTestMeetingUtilities::removeMeetingUsers();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
    }

    public function testGetEntryList()
    {
        $client = array(
            'session'       => $this->_sessionId,
            'module_name'   => 'Contacts',
            'query'         => 'contacts.id=' . $GLOBALS['db']->quoted($this->contact->id),
            'order_by'      => '',
            'offset'        => 0,
            'select_fields' => array(),
            'max_results'   => 20,
            'deleted'       => -1,
        );

        $result = $this->_soapClient->call('get_entry_list', $client);
        $data = array();
        foreach ($result['entry_list'] as $v) {
            $this->assertNotContains($v['id'], $data, 'Duplicates were found');
            $data[] = $v['id'];
        }

        $this->assertNotEmpty($data, 'Records are not found');
    }
}
