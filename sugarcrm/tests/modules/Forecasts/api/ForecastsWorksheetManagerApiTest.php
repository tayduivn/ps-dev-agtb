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


require_once('tests/rest/RestTestBase.php');

/***
 * Used to test Forecast Module endpoints from ForecastModuleApi.php
 *
 * @group forecasts
 */
class ForecastsWorksheetManagerApiTest extends RestTestBase
{
    private $reportee;

    public function setUp()
    {
        parent::setUp();

        $this->reportee = SugarTestUserUtilities::createAnonymousUser();
        $this->reportee->reports_to_id = $GLOBALS['current_user']->id;
        $this->reportee->save();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /***
     * @group forecastapi
     */
    public function testForecastsWorksheetManagerApi()
    {
        global $current_user;

        //current user is manager
        $restReply = $this->_restCall("Forecasts/worksheetmanager/");

        $this->assertArrayHasKey($current_user->user_name, $restReply['reply'], "manager's user_name was not found in the Expected place in the rest reply" );
        $this->assertArrayHasKey($this->reportee->user_name, $restReply['reply'], "reportee's user_name was not found in the Expected place in the rest reply" );

        //user in filter is not manager - rest reply should be empty
        $restReply = $this->_restCall("Forecasts/worksheetmanager?user_id=" . $this->reportee->id);

        $this->assertEmpty($restReply['reply'], "rest reply is not empty");

        //current user is not manager - rest reply should be empty
        $this->reportee->reports_to_id = '';
        $this->reportee->save();

        $restReply = $this->_restCall("Forecasts/worksheetmanager/");

        $this->assertEmpty($restReply['reply'], "rest reply is not empty");
    }

}