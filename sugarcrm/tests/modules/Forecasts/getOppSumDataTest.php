<?php
//FILE SUGARCRM flav=pro ONLY
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

require_once 'modules/Forecasts/ForecastUtils.php';

class GetOppSumDataTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $manager;
    private $sales_rep;

    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        $this->manager = SugarTestUserUtilities::createAnonymousUser();

        $this->sales_rep = SugarTestUserUtilities::createAnonymousUser();
        $this->sales_rep->reports_to_id = $this->manager->id;
        $this->sales_rep->save();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        SugarTestProductUtilities::removeAllCreatedProducts();
        SugarTestOpportunityUtilities::removeAllCreatedOpps();
    }

    public function testGetOppSumDataTest()
    {
        //case #1: get summation data by sales rep. Opp_line b/l/w cases are NULL. Opp b/l/w cases are NULL
        $opp_1 = SugarTestOpportunityUtilities::createOpportunity();
        $opp_1->assigned_user_id = $this->sales_rep->id;
        $opp_1->probability = '10';
        $opp_1->save();

        $product_1 = SugarTestProductUtilities::createProduct();

        $result = getOppSummationData($this->sales_rep->id, '', 'Direct');

        $this->assertTrue($result['direct'][$this->sales_rep->id][0]['opp_id'] == $opp_1->id);
        $this->assertTrue($result['direct'][$this->sales_rep->id][0]['best_case'] == '1000');
        $this->assertTrue($result['direct'][$this->sales_rep->id][0]['likely_case'] == '1000');
        $this->assertTrue($result['direct'][$this->sales_rep->id][0]['worst_case'] == '1000');

        //case #2: get summation data by sales rep. Opp_line b/l/w cases are NULL. Opp b/l/w cases are not NULL
        $opp_1->best_case = '1300';
        $opp_1->likely_case = '1200';
        $opp_1->worst_case = '1100';
        $opp_1->save();

        $result = getOppSummationData($this->sales_rep->id, '', 'Direct');

        $this->assertTrue($result['direct'][$this->sales_rep->id][0]['opp_id'] == $opp_1->id);
        $this->assertTrue($result['direct'][$this->sales_rep->id][0]['best_case'] == $opp_1->best_case);
        $this->assertTrue($result['direct'][$this->sales_rep->id][0]['likely_case'] == $opp_1->likely_case);
        $this->assertTrue($result['direct'][$this->sales_rep->id][0]['worst_case'] == $opp_1->worst_case);

        //case #3: get summation data by sales rep. Opp_line b/l/w cases are not NULL
        
        $product_1->opportunity_id = $opp_1->id;
        $product_1->save();

        $result = getOppSummationData($this->sales_rep->id, '', 'Direct');

        $this->assertTrue($result['direct'][$this->sales_rep->id][0]['opp_id'] == $opp_1->id);
        $this->assertTrue($result['direct'][$this->sales_rep->id][0]['best_case'] == $product_1->best_case);
        $this->assertTrue($result['direct'][$this->sales_rep->id][0]['likely_case'] == $product_1->likely_case);
        $this->assertTrue($result['direct'][$this->sales_rep->id][0]['worst_case'] == $product_1->worst_case);

        //case #4: get rollup summation data by manager
        $opp_2 = SugarTestOpportunityUtilities::createOpportunity();
        $opp_2->assigned_user_id = $this->manager->id;
        $opp_2->probability = '10';
        $opp_2->save();

        $product_2 = SugarTestProductUtilities::createProduct();
        $product_2->opportunity_id = $opp_2->id;
        $product_2->save();

        $result = getOppSummationData($this->manager->id, '', 'Rollup');

        $this->assertTrue($result['rollup'][0]['user_id'] == $this->manager->id);
        $this->assertTrue($result['rollup'][0]['best_case'] == $product_2->best_case);
        $this->assertTrue($result['rollup'][0]['likely_case'] == $product_2->likely_case);
        $this->assertTrue($result['rollup'][0]['worst_case'] == $product_2->worst_case);
        $this->assertTrue($result['rollup'][1]['user_id'] == $this->sales_rep->id);
        $this->assertTrue($result['rollup'][1]['best_case'] == $product_1->best_case);
        $this->assertTrue($result['rollup'][1]['likely_case'] == $product_1->likely_case);
        $this->assertTrue($result['rollup'][1]['worst_case'] == $product_1->worst_case);
    }
}