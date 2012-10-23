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
 * Bug 46605
 * false-positive DEPRECATED messages
 *
 * @ticket 46605
 */
class Bug46605DatesInMeetingTest extends Sugar_PHPUnit_Framework_TestCase
{
    const DB_DATE_TIME_REGEX = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';

    const DB_TIME_REGEX = '/^\d{2}:\d{2}:\d{2}$/';

    protected function setUp()
    {
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Ensure that Meeting::fill_in_additional_detail_fields() initializes
     * date and time fields with values in database format.
     *
     * @group 46605
     */
    public function testFillInAdditionalDetailFieldsSettsNonLocalizedDates()
    {
        $meeting = new Meeting();
        $meeting->fill_in_additional_detail_fields();
        $this->assertRegExp(self::DB_DATE_TIME_REGEX, $meeting->date_start);
        $this->assertRegExp(self::DB_DATE_TIME_REGEX, $meeting->date_end);
        $this->assertRegExp(self::DB_TIME_REGEX, $meeting->time_start);
    }
}