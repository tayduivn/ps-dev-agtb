<?php
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
require_once "modules/Products/Product.php";
require_once "modules/Opportunities/Opportunity.php";
require_once "include/SugarForecasting/Hooks/StatusAutoAdjustHook.php";

class SugarForecasting_StatusAutoAdjustHookTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected static $hook;
    
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', array('Products'));
        self::$hook = new StatusAutoAdjustHook();
    }

    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        SugarTestProductUtilities::removeAllCreatedProducts();
        SugarTestWorksheetUtilities::removeAllCreatedWorksheets();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        parent::tearDown();
    }

    public static function tearDownAfterClass()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }
    
    /**
     * Data Provider
     * 
     * @return array
     */
     public static function dataProviderStageData()
     {
        //use new bean, quote_id, Status, stage, likely_case, resulting Status
     return array(
            array(true, "", "", "", 1500, Opportunity::STATUS_NEW),
            array(false, "", "", "", 1500, Opportunity::STATUS_IN_PROGRESS),
            array(false, "", "Some Status", Opportunity::STAGE_CLOSED_WON, "", "Some Status"),
            array(false, "", "Some Status", Opportunity::STAGE_CLOSED_LOST, "", "Some Status"),
            array(false, "", "", Opportunity::STAGE_CLOSED_WON, "", Opportunity::STAGE_CLOSED_WON),
            array(false, "", "", Opportunity::STAGE_CLOSED_LOST, "", Opportunity::STAGE_CLOSED_LOST)
            //BEGIN SUGARCRM flav=ent ONLY
            ,array(false, "12345", "", Opportunity::STAGE_CLOSED_WON, "", Opportunity::STAGE_CLOSED_WON),
            array(false, "12345", "", Opportunity::STAGE_CLOSED_LOST, "", Opportunity::STAGE_CLOSED_LOST),
            array(false, "12345", "", "Some Stage", "", Product::STATUS_CONVERTED_TO_QUOTE),
            array(false, "12345", "", "", "", Product::STATUS_CONVERTED_TO_QUOTE)
            //END SUGARCRM flav=ent ONLY
        );
     }
     //BEGIN SUGARCRM flav=pro && flav!=ent ONLY
     /**
      * @group forecasts
      * @group products
      * @dataProvider dataProviderStageData
      */
     public function testOppStatusAdjust($bNew, $quote_id, $status, $stage, $likely_case, $rStatus)
     {
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $id = $opp->id;
        if ($bNew) {
            $opp->id = '';
        } else {
            $opp = $opp->retrieve($opp->id);
            $opp->sales_stage = "New"; //needed to fix random sales stages from the helper file
        }
        
        if (!empty($status)) {
            $opp->sales_status = $status;
        }
        
        if (!empty($stage)) {
            $opp->sales_stage = $stage;
        }
        
        if (!empty($likely_case)) {
            $opp->likely_case = $likely_case;
        }
                
        self::$hook->adjustStatus($opp, "", "");
        $opp->id = $id;
        $this->assertEquals($rStatus, $opp->sales_status);
     }
     //END SUGARCRM flav=pro && flav!=ent ONLY
     
     //BEGIN SUGARCRM flav=ent ONLY   
     /**
      * @group forecasts
      * @group products
      * @dataProvider dataProviderStageData
      */
     public function testRLIStatusAdjust($bNew, $quote_id, $status, $stage, $likely_case, $rStatus)
     {
        $product = SugarTestProductUtilities::createProduct();
        $id = $product->id;
        if ($bNew) {
        $product->id = '';
        } else {
            $product = $product->retrieve($product->id);
        }
        
        if (!empty($status)) {
        $product->sales_status = $status;
        }
        
        if (!empty($stage)) {
            $product->sales_stage = $stage;
        }
        
        if (!empty($likely_case)) {
            $product->likely_case = $likely_case;
        }
        
        if (!empty($quote_id)) {
            $product->quote_id = $quote_id;
        }
                
        self::$hook->adjustStatus($product, "", "");
        $product->id = $id;
        $this->assertEquals($rStatus, $product->sales_status);
     }
     //END SUGARCRM flav=ent ONLY
}
