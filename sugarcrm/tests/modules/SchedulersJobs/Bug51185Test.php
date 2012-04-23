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

require_once 'modules/SchedulersJobs/SchedulersJob.php';

/**
 * Bug51185Test.php
 * @author Collin Lee
 *
 * This unit tests checks to ensure the value returned from handleDateFormat in SchedulersJob.php is properly returned
 * depending on the arguments passed in.  By default, the handleDateFormat call should return a database formatted date
 * time value.
 *
 */
class Bug51185Test extends Sugar_PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {        
        global $current_user;
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        //Change the datef value in user preference so that it is not the default db format
        $current_user->setPreference('datef','d/m/Y', 0, 'global');
        $current_user->save();
    }

    public static function tearDownAfterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * testSchedulersJobHandleDateFormatWithNow
     *
     */
    public function testSchedulersJobHandleDateFormatWithNow()
    {
        global $current_user;
        $job = new SchedulersJob(false);
        $job->user = $current_user;
        $this->assertRegExp('/^\d{4}\-\d{2}\-\d{2}\s\d{1,2}\:\d{2}\:\d{2}$/', $job->handleDateFormat('now'));
        $this->assertRegExp('/^\d{4}\-\d{2}\-\d{2}\s\d{1,2}\:\d{2}\:\d{2}$/', $job->handleDateFormat('now'), $current_user, false);
        $this->assertRegExp('/^\d{1,2}\/\d{1,2}\/\d{4}\s\d{1,2}\:\d{2}$/', $job->handleDateFormat('now', $current_user, true));
    }

    /**
     * testSchedulersJobHandleDateFormatWithoutNow
     *
     */
    public function testSchedulersJobHandleDateFormatWithoutNow()
    {
        global $current_user;
        $job = new SchedulersJob(false);
        $job->user = $current_user;
        $this->assertRegExp('/^\d{4}\-\d{2}\-\d{2}\s\d{1,2}\:\d{2}\:\d{2}$/', $job->handleDateFormat());
    }

    /**
     * testSchedulersJobHandleDateFormatWithOtherTime
     *
     */
    public function testSchedulersJobHandleDateFormatWithOtherTime()
    {
        global $current_user;
        $job = new SchedulersJob(false);
        $job->user = $current_user;
        $this->assertRegExp('/^\d{4}\-\d{2}\-\d{2}\s\d{1,2}\:\d{2}\:\d{2}$/', $job->handleDateFormat('+7 days'));
        $this->assertRegExp('/^\d{4}\-\d{2}\-\d{2}\s\d{1,2}\:\d{2}\:\d{2}$/', $job->handleDateFormat('+7 days', $current_user, false));
        $this->assertRegExp('/^\d{1,2}\/\d{1,2}\/\d{4}\s\d{1,2}\:\d{2}$/', $job->handleDateFormat('+7 days', $current_user, true));
    }

}
