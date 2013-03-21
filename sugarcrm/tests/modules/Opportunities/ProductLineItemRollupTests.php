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

class ProductLineItemRollupTests extends Sugar_PHPUnit_Framework_TestCase
{
    private static $isSetup;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp("current_user");
        SugarTestHelper::setUp("app_strings");
        SugarTestHelper::setUp("beanFiles");
        SugarTestHelper::setUp("beanList");
        $admin = BeanFactory::getBean("Administration");
        $settings = $admin->getConfigForModule("Forecasts");
        self::$isSetup = $settings["is_setup"];
        $admin->saveSetting("Forecasts", "is_setup", "1", "base");
    }

    public function tearDown()
    {   
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestProductUtilities::removeAllCreatedProducts();                
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestWorksheetUtilities::removeAllCreatedWorksheets();
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
    }

    public static function tearDownAfterClass()
    {
        $admin = BeanFactory::getBean("Administration");
        $admin->saveSetting("Forecasts", "is_setup", self::$isSetup, "base");       
        parent::tearDownAfterClass();
    }

    
   /**
    * @group forecasts
    */
    public function testOpportunitiesWithOneProduct()
    {
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $oppId = $opp->id;
        
        $product = SugarTestProductUtilities::createProduct();
        $product->opportunity_id = $oppId;        
        $product->likely_case = 1000;
        $product->best_case = 1000;
        $product->worst_case = 1000;
        $product->date_closed = "2013-03-01";
        $product->date_closed_timestamp = strtotime("2013-03-01");
        $product->save();
        
        $opp->retrieve($oppId);
        $this->assertEquals(1000, $opp->amount, "Amount not equal.");
        $this->assertEquals(1000, $opp->best_case, "Best_case not equal");
        $this->assertEquals(1000, $opp->worst_case, "Worst_case not equal");
    }
   
   /**
    * @group forecasts
    */
    public function testOpportunitiesWithOneProductSavedTwice()
    {
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $oppId = $opp->id;
        
        $product = SugarTestProductUtilities::createProduct();
        $product->opportunity_id = $oppId;        
        $product->likely_case = 1000;
        $product->best_case = 1000;
        $product->worst_case = 1000;
        $product->date_closed = "2013-03-01";
        $product->date_closed_timestamp = strtotime("2013-03-01");
        $product->save();
        
        $opp->retrieve($oppId);
        $this->assertEquals(1000, $opp->amount, "Amount not equal.");
        $this->assertEquals(1000, $opp->best_case, "Best_case not equal");
        $this->assertEquals(1000, $opp->worst_case, "Worst_case not equal");
        
        //change the product and save again
        $product->likely_case = 5000;
        $product->best_case = 5000;
        $product->worst_case = 5000;
        $product->save();
        
        $opp->retrieve($oppId);
        $this->assertEquals(5000, $opp->amount, "Amount not equal.");
        $this->assertEquals(5000, $opp->best_case, "Best_case not equal");
        $this->assertEquals(5000, $opp->worst_case, "Worst_case not equal");
    }
   
   /**
    * @group forecasts
    */
    public function testOpportunitiesWithTwoProducts()
    {
        $products = 2;
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $oppId = $opp->id;
        
        for ($index = 0; $index < $products; $index++) {        
            $product = SugarTestProductUtilities::createProduct();
            $product->opportunity_id = $oppId;        
            $product->likely_case = 10000;
            $product->best_case = 10000;
            $product->worst_case = 10000;
            $product->date_closed = "2013-03-01";
            $product->date_closed_timestamp = strtotime("2013-03-01");
            $product->save();
        }      
        
        $opp->retrieve($oppId);               
        $this->assertEquals(20000, $opp->amount, "Amount not equal.");
        $this->assertEquals(20000, $opp->best_case, "Best_case not equal");
        $this->assertEquals(20000, $opp->worst_case, "Worst_case not equal");
    }
    
    /**
    * @group forecasts
    */
    public function testOpportunitiesWithTwoProducts_deleteOne()
    {
        $products = 2;
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $oppId = $opp->id;
        
        for ($index = 0; $index < $products; $index++) {        
            $product = SugarTestProductUtilities::createProduct();
            $product->opportunity_id = $oppId;        
            $product->likely_case = 10000;
            $product->best_case = 10000;
            $product->worst_case = 10000;
            $product->date_closed = "2013-03-01";
            $product->date_closed_timestamp = strtotime("2013-03-01");
            $product->save();
        }      
        
        //make sure the opp has both products
        $opp->retrieve($oppId);               
        $this->assertEquals(20000, $opp->amount, "Amount not equal.");
        $this->assertEquals(20000, $opp->best_case, "Best_case not equal");
        $this->assertEquals(20000, $opp->worst_case, "Worst_case not equal");
        
        //delete one, and make sure it removed the value of that opp.
        $product->mark_deleted($product->id);
        $opp->retrieve($oppId);
        $this->assertEquals(10000, $opp->amount, "Amount not equal.");
        $this->assertEquals(10000, $opp->best_case, "Best_case not equal");
        $this->assertEquals(10000, $opp->worst_case, "Worst_case not equal");
    }
   
   /**
    * @group forecasts
    */
    public function testOpportunitiesWithTwoProductsSavedTwice()
    {
        $products = 2;
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $oppId = $opp->id;
        
        for ($index = 0; $index < $products; $index++) {        
            $product = SugarTestProductUtilities::createProduct();
            $product->opportunity_id = $oppId;        
            $product->likely_case = 10000;
            $product->best_case = 10000;
            $product->worst_case = 10000;
            $product->date_closed = "2013-03-01";
            $product->date_closed_timestamp = strtotime("2013-03-01");
            $product->save();
        }
        
        $opp->retrieve($oppId); 
        $this->assertEquals(20000, $opp->amount, "Amount not equal.");
        $this->assertEquals(20000, $opp->best_case, "Best_case not equal");
        $this->assertEquals(20000, $opp->worst_case, "Worst_case not equal");
        
        //change the product and save again
        $product->likely_case = 40000;
        $product->best_case = 40000;
        $product->worst_case = 40000;
        $product->save();
        
        $opp->retrieve($oppId);
        $this->assertEquals(50000, $opp->amount, "Amount not equal.");
        $this->assertEquals(50000, $opp->best_case, "Best_case not equal");
        $this->assertEquals(50000, $opp->worst_case, "Worst_case not equal");
    }
   
   /**
    * @group forecasts
    */
    public function testOpportunitiesWithThreeProducts()
    { 
        $products = 3;
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $oppId = $opp->id;
        
        for ($index = 0; $index < $products; $index++) {        
            $product = SugarTestProductUtilities::createProduct();
            $product->opportunity_id = $oppId;        
            $product->likely_case = 10000;
            $product->best_case = 10000;
            $product->worst_case = 10000;
            $product->date_closed = "2013-03-01";
            $product->date_closed_timestamp = strtotime("2013-03-01");
            $product->save();
        }
        
        $opp->retrieve($oppId);
        $this->assertEquals(30000, $opp->amount, "Amount not equal.");
        $this->assertEquals(30000, $opp->best_case, "Best_case not equal");
        $this->assertEquals(30000, $opp->worst_case, "Worst_case not equal");
    }
   
   /**
    * @group forecasts
    */
    public function testOpportunitiesWithThreeProductsSavedTwice()
    {
        $products = 3;
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $oppId = $opp->id;
        
        for ($index = 0; $index < $products; $index++) {        
            $product[] = SugarTestProductUtilities::createProduct();
            $product[$index]->opportunity_id = $oppId;        
            $product[$index]->likely_case = 10000;
            $product[$index]->best_case = 10000;
            $product[$index]->worst_case = 10000;
            $product[$index]->date_closed = "2013-03-01";
            $product[$index]->date_closed_timestamp = strtotime("2013-03-01");
            $product[$index]->save();
        }
        
        $opp->retrieve($oppId);      
        $this->assertEquals(30000, $opp->amount, "Amount not equal.");
        $this->assertEquals(30000, $opp->best_case, "Best_case not equal");
        $this->assertEquals(30000, $opp->worst_case, "Worst_case not equal");
        
        for ($index = 0; $index < $products; $index++) { 
            $product[$index]->opportunity_id = $oppId;        
            $product[$index]->likely_case = 20000;
            $product[$index]->best_case = 20000;
            $product[$index]->worst_case = 20000;
            $product[$index]->save();
        }
        
        $opp->retrieve($oppId);
        $this->assertEquals(60000, $opp->amount, "Amount not equal.");
        $this->assertEquals(60000, $opp->best_case, "Best_case not equal");
        $this->assertEquals(60000, $opp->worst_case, "Worst_case not equal");
    }
   
   /**
    * @group forecasts
    */
    public function testOpportunitiesWithTwoProducts_dateClosed()
    {
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $oppId = $opp->id;
        
        //grab default product
        $product = SugarTestProductUtilities::createProduct();
        $product->opportunity_id = $oppId;  
        $product->likely_case = 1000;
        $product->best_case = 1000;
        $product->worst_case = 1000;
        $product->date_closed = "2013-02-01";
        $product->date_closed_timestamp = strtotime($product->date_closed);
        $product->save();
        
        //create an additional product to go with the $10,000 default product that is created
        $product2 = SugarTestProductUtilities::createProduct();
        $product2->opportunity_id = $oppId;        
        $product2->likely_case = 10000;
        $product2->best_case = 10000;
        $product2->worst_case = 10000;
        $product2->date_closed = "2013-01-01";
        $product2->date_closed_timestamp = strtotime($product2->date_closed);
        $product2->save();
                       
        $this->assertEquals($product->date_closed, $opp->date_closed, "Dates not equal.");
        $this->assertEquals($product->date_closed_timestamp, $opp->date_closed_timestamp, "Timestamps not equal");
    }   
   
   /**
    * @group forecasts
    */
    public function testOpportunitiesWithNoProductsDefaultDateClosed()
    {    
        $timedate = TimeDate::getInstance();
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $oppId = $opp->id;
        //get the date from the sugarTestOpportunity helper used to create this.
        $now = $timedate->getNow()->asDbDate();     
                       
        //check to make sure the date was unchanged by sugarlogic               
        $this->assertEquals($now, $opp->date_closed, "Date not blank.");
    }  
}

