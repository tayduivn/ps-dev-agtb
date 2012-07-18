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
 * This is a test for ForecastWorksheet endpoints
 *
 * @group forecasts
 */
class ForecastsWorksheetApiTest extends RestTestBase
{
    var $opp1;
    var $opp2;

    public function setUp()
    {

        global $beanFiles, $beanList, $current_user, $app_list_strings, $current_language;
        $app_list_strings = return_app_list_strings_language($current_language);
        parent::setUp();

        $this->opp1 = SugarTestOpportunityUtilities::createOpportunity();
        $this->opp1->assigned_user_id = $current_user->id;
        $this->opp1->probability = '85';
        $this->opp1->forecast = -1;
        $this->opp1->amount = 150.55;
        $this->opp1->best_case = 0;
        $this->opp1->likely_case = 0;
        $this->opp1->worst_case = 0;
        $this->opp1->team_id = '1';
        $this->opp1->team_set_id = '1';
        $this->opp1->timeperiod_id = TimePeriod::getCurrentId();
        $this->opp1->save();

        $this->opp2 = SugarTestOpportunityUtilities::createOpportunity();
        $this->opp2->assigned_user_id = $current_user->id;
        $this->opp2->probability = '85';
        $this->opp2->forecast = -1;
        $this->opp2->amount = 50;
        $this->opp2->best_case = 100;
        $this->opp2->likely_case = 100;
        $this->opp2->worst_case = 100;
        $this->opp2->team_id = '1';
        $this->opp2->team_set_id = '1';
        $this->opp2->timeperiod_id = TimePeriod::getCurrentId();
        $this->opp2->save();
    }

    public function tearDown()
    {
        SugarTestOpportunityUtilities::removeAllCreatedOpps();
        parent::tearDown();
    }

    /***
     * This is a test to ensure that the best, likely and worst case values receive the opportunity amount value
     * when they are zero.
     *
     * @group forecastapi
     */
    public function testGridDataSetsDefaultValues()
    {
        global $current_user;
        $result = $this->_restCall("ForecastWorksheets?timeperiod_id=" . TimePeriod::getCurrentId() . '&user_id=' . $current_user->id);
        $this->assertNotEmpty($result['reply'], 'Assert we have a valid reply');

        $opp = $result['reply'][0];
        $this->assertEquals($this->opp1->amount, $opp['best_case'], 'Assert best_case is not zero');
        $this->assertEquals($this->opp1->amount, $opp['likely_case'], 'Assert likely_case is not zero');
        $this->assertEquals($this->opp1->amount, $opp['worst_case'], 'Assert worst_case is not zero');

        $opp = $result['reply'][1];
        $this->assertEquals($this->opp2->best_case, $opp['best_case'], 'Assert best_case is not zero');
        $this->assertEquals($this->opp2->likely_case, $opp['likely_case'], 'Assert likely_case is not zero');
        $this->assertEquals($this->opp2->worst_case, $opp['worst_case'], 'Assert worst_case is not zero');
    }

}