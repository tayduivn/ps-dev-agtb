<?php
//FILE SUGARCRM flav=ent ONLY

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
 * ChartAndWorksheetManagerTest.php
 *
 * This is a test for the ChartAndWorksheetManager class
 *
 */
require_once('modules/Forecasts/data/ChartAndWorksheetManager.php');

class ChartAndWorksheetManagerTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        global $beanFiles, $beanList, $current_user, $app_list_strings, $app_strings, $timedate;
        $timedate = TimeDate::getInstance();
        $app_list_strings = return_app_list_strings_language('en');
        $app_strings = return_application_language('en');

        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $current_user->user_name = 'employee0';
        $current_user->is_admin = 1;
        $current_user->save();

        $employee1 = SugarTestUserUtilities::createAnonymousUser();
        $employee1->reports_to_id = $current_user->id;
        $employee1->user_name = 'employee1';
        $employee1->save();

        $employee2 = SugarTestUserUtilities::createAnonymousUser();
        $employee2->reports_to_id = $current_user->id;
        $employee2->user_name = 'employee2';
        $employee2->save();

        $employee3 = SugarTestUserUtilities::createAnonymousUser();
        $employee3->reports_to_id = $employee2->id;
        $employee3->user_name = 'employee3';
        $employee3->save();

        $employee4 = SugarTestUserUtilities::createAnonymousUser();
        $employee4->reports_to_id = $employee3->id;
        $employee4->user_name = 'employee4';
        $employee4->save();

        $products = $current_user->build_related_list("SELECT id FROM products WHERE deleted = 0", new Product(), 0, 10);

        //This opp has not been marked to be forecasted, but it still will be since probability > 85
        $opp1 = SugarTestOpportunityUtilities::createOpportunity();
        $opp1->assigned_user_id = $current_user->id;
        $opp1->probability = '85';
        $opp1->forecast = -1;
        $opp1->best_case = 1300;
        $opp1->likely_case = 1200;
        $opp1->worst_case = 1100;
        $opp1->team_id = '1';
        $opp1->team_set_id = '1';
        $opp1->timeperiod_id = TimePeriod::getCurrentId();
        $opp1->save();

        $line_1 = SugarTestProductUtilities::createProduct();
        $line_1->name = $opp1->name;
        $line_1->opportunity_id = $opp1->id;
        $line_1->product_id = $products[array_rand($products)]->id;
        $line_1->team_set_id = '1';
        $line_1->team_id = '1';
        $line_1->best_case = 1300;
        $line_1->likely_case = 1200;
        $line_1->worst_case = 1100;
        $line_1->save();

        //This opp has been explicitly set to be forecasting on with forecast = 1
        $opp2 = SugarTestOpportunityUtilities::createOpportunity();
        $opp2->date_closed = $timedate->getNow()->asDbDate();
        $opp2->assigned_user_id = $employee1->id;
        $opp2->probability = '75';
        $opp2->forecast = 1;
        $opp2->best_case = 1300;
        $opp2->likely_case = 1200;
        $opp2->team_id = '1';
        $opp2->team_set_id = '1';
        $opp2->timeperiod_id = TimePeriod::getCurrentId();
        $opp2->save();

        $line_2 = SugarTestProductUtilities::createProduct();
        $line_2->name = $opp2->name;
        $line_2->opportunity_id = $opp2->id;
        $line_2->product_id = $products[array_rand($products)]->id;
        $line_2->team_set_id = '1';
        $line_2->team_id = '1';
        $line_2->best_case = 1300;
        $line_2->likely_case = 1200;
        $line_2->worst_case = 1100;
        $line_2->save();

        //This opp will be forecasted on since forecast=1 even though probability <= 70
        $opp3 = SugarTestOpportunityUtilities::createOpportunity();
        $opp3->assigned_user_id = $employee2->id;
        $opp3->probability = '60';
        $opp3->forecast = 1;
        $opp3->best_case = 1300;
        $opp3->likely_case = 1200;
        $opp3->team_id = '1';
        $opp3->team_set_id = '1';
        $opp3->timeperiod_id = TimePeriod::getCurrentId();
        $opp3->save();

        $line_3 = SugarTestProductUtilities::createProduct();
        $line_3->name = $opp3->name;
        $line_3->opportunity_id = $opp3->id;
        $line_3->product_id = $products[0]->id;
        $line_3->team_set_id = '1';
        $line_3->team_id = '1';
        $line_3->best_case = 1300;
        $line_3->likely_case = 1200;
        $line_3->worst_case = 1100;
        $line_3->save();

        //This won't be counted
        $opp4 = SugarTestOpportunityUtilities::createOpportunity();
        $opp4->assigned_user_id = $employee3->id;
        $opp4->probability = '60';
        $opp4->forecast = -1;
        $opp4->best_case = 1300;
        $opp4->likely_case = 1200;
        $opp4->worst_case = 1100;
        $opp4->team_id = '1';
        $opp4->team_set_id = '1';
        $opp4->timeperiod_id = TimePeriod::getCurrentId();
        $opp4->save();

        $line_4 = SugarTestProductUtilities::createProduct();
        $line_4->name = $opp4->name;
        $line_4->opportunity_id = $opp4->id;
        $line_4->product_id = $products[1]->id;
        $line_4->team_set_id = '1';
        $line_4->team_id = '1';
        $line_4->best_case = 1300;
        $line_4->likely_case = 1200;
        $line_4->worst_case = 1100;
        $line_4->save();

        //This one won't be counted
        $opp5 = SugarTestOpportunityUtilities::createOpportunity();
        $opp5->date_closed = $timedate->getNow()->modify('+4 month')->asDbDate();
        $opp5->assigned_user_id = $employee4->id;
        $opp5->probability = '90';
        $opp5->forecast = 0;
        $opp5->best_case = 1300;
        $opp5->likely_case = 1200;
        $opp5->team_id = '1';
        $opp5->team_set_id = '1';
        $opp5->timeperiod_id = TimePeriod::getCurrentId();
        $opp5->save();

        $line_5 = SugarTestProductUtilities::createProduct();
        $line_5->name = $opp5->name;
        $line_5->opportunity_id = $opp5->id;
        $line_5->product_id = $products[2]->id;
        $line_5->team_set_id = '1';
        $line_5->team_id = '1';
        $line_5->best_case = 1300;
        $line_5->likely_case = 1200;
        $line_5->worst_case = 1100;
        $line_5->save();
        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestProductUtilities::removeAllCreatedProducts();
        SugarTestOpportunityUtilities::removeAllCreatedOpps();
        parent::tearDownAfterClass();
    }

    /**
     * Test to see that the calls to retrieve manager and individual worksheet definitions return data
     */
    function testChartAndWorksheetManager()
    {
        $manager = new ChartAndWorksheetManager();
        $def = $manager->getWorksheetDefinition('manager', 'opportunities');
        $this->assertNotEmpty($def);

        $def = $manager->getWorksheetDefinition('individual', 'opportunities');
        $this->assertNotEmpty($def);
    }



}