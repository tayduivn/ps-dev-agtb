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
 * Bug #53516
 * Workflow on related module stops meetings from saving
 *
 * @author vromanenko@sugarcrm.com
 * @ticket 53516
 */
class Bug53516Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Contact
     */
    public $contact;

    protected function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, 1));
        $this->contact = SugarTestContactUtilities::createContact();
    }

    protected function tearDown()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestContactUtilities::removeCreatedContactsEmailAddresses();
        SugarTestContactUtilities::removeCreatedContactsUsersRelationships();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestMeetingUtilities::removeMeetingContacts();
        SugarTestMeetingUtilities::removeMeetingUsers();
        SugarTestHelper::tearDown();
    }

    /**
     * Ensure that saving relationship changes do not fail with fatal error and works fine
     * when saving relation field with type id
     *
     * @group 53516
     */
    public function testSaveRelationOnRelateFieldWithIdType()
    {
        $meeting = SugarTestMeetingUtilities::createMeeting();
        $meeting->in_workflow = true;
        $meeting->not_use_rel_in_req = true;
        $meeting->contact_id = $meeting->new_rel_id = $this->contact->id;
        $meeting->new_rel_relname = 'contact_id';
        $meeting->save_relationship_changes(false);
    }
}
