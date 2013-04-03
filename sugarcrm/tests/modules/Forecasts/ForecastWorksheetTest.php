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

class ForecastWorksheetTest extends Sugar_PHPUnit_Framework_TestCase
{

    protected static $settings = array();

    /**
     * @var TimePeriod
     */
    protected static $timeperiod;

    /**
     * @var User
     */
    protected static $user;

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');
        self::$settings = $admin->getConfigForModule('Forecasts');
        $admin->saveSetting('Forecasts', 'is_setup', 1, 'base');
        $admin->saveSetting('Forecasts', 'forecast_by', 'products', 'base');

        self::$timeperiod = SugarTestTimePeriodUtilities::createTimePeriod('2009-01-01', '2009-03-31');

        self::$user = SugarTestUserUtilities::createAnonymousUser();

        // setup 10 products
        for ($x = 0; $x < 10; $x++) {
            $product = SugarTestProductUtilities::createProduct();
            $product->date_closed = '2009-02-01';
            $product->assigned_user_id = self::$user->id;
            $product->save();
        }
    }

    public function tearDown()
    {
        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');
        $admin->saveSetting('Forecasts', 'is_setup', self::$settings['is_setup'], 'base');
        $admin->saveSetting('Forecasts', 'forecast_by', self::$settings['forecast_by'], 'base');

        SugarTestWorksheetUtilities::removeAllWorksheetsForParentIds(SugarTestProductUtilities::getCreatedProductIds());
        SugarTestWorksheetUtilities::removeAllWorksheetsForParentIds(SugarTestOpportunityUtilities::getCreatedOpportunityIds());
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestProductUtilities::removeAllCreatedProducts();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestJobQueueUtilities::removeAllCreatedJobs();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();

        parent::tearDown();
    }

    /**
     * @group forecasts
     */
    public function testCommitWorksheetDoesNotCreateAnyJobsAndHasTenCommittedRecords()
    {
        /* @var $bean ForecastWorksheet */
        $bean = BeanFactory::getBean('ForecastWorksheets');
        $bean->commitWorksheet(self::$user->id, self::$timeperiod->id);

        // query for jobs based on name
        $sq = new SugarQuery();
        $sq->select(array('id'));
        $sq->from(BeanFactory::getBean('SchedulersJobs'))->where()
            ->equals('target', 'class::SugarJobUpdateForecastWorksheets');
        $results = $sq->execute();

        foreach($results as $result) {
            SugarTestJobQueueUtilities::setCreatedJobs(array($result['id']));
        }

        $this->assertEquals(0, count($results));

        $worksheets = SugarTestWorksheetUtilities::loadWorksheetForBeans(
            'Products',
            SugarTestProductUtilities::getCreatedProductIds(),
            true
        );
        $this->assertEquals(10, count($worksheets));
    }

    /**
     * @group forecasts
     */
    public function testCommitWorksheetsCreatesOneJobAndHasFiveCommittedRecords()
    {
        /* @var $bean ForecastWorksheet */
        $bean = BeanFactory::getBean('ForecastWorksheets');
        $bean->commitWorksheet(self::$user->id, self::$timeperiod->id, 5);

        // query for jobs based on name
        $sq = new SugarQuery();
        $sq->select(array('id'));
        $sq->from(BeanFactory::getBean('SchedulersJobs'))->where()
            ->equals('target', 'class::SugarJobUpdateForecastWorksheets');
        $results = $sq->execute();

        foreach($results as $result) {
            SugarTestJobQueueUtilities::setCreatedJobs(array($result['id']));
        }

        // we should have 1 job created
        $this->assertEquals(1, count($results));

        // make sure we have 5 worksheets committed (the other 5 are in the job)
        $worksheets = SugarTestWorksheetUtilities::loadWorksheetForBeans(
            'Products',
            SugarTestProductUtilities::getCreatedProductIds(),
            true
        );
        $this->assertEquals(5, count($worksheets));
    }

    /**
     * Make sure closed_date is being updated on worksheet save
     *
     * @ticket SFA-704
     * @group forecasts
     * @group products
     * @group opportunities
     */
    public function testExpectedCloseDateRollupWorks()
    {
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $product = SugarTestProductUtilities::createProduct();

        $product->date_closed = '2009-02-01';
        $product->opportunity_id = $opp->id;
        $product->likely_case = '1000.00';

        $product->save();

        // test to make sure opp was updated
        /* @var $oppBean Opportunity */
        $oppBean = BeanFactory::getBean($opp->module_name);
        $oppBean->retrieve($opp->id);

        // test the db formatted version
        $this->assertEquals($product->date_closed, $oppBean->fetched_row['date_closed']);

        // find the product worksheet
        $worksheet = SugarTestWorksheetUtilities::loadWorksheetForBean($product);

        // make sure a worksheet is actually returned
        $this->assertInstanceOf('ForecastWorksheet', $worksheet);
        // make sure it has the correct date closed on it
        $this->assertEquals($product->date_closed, $worksheet->date_closed);

        // update the worksheet date_closed
        $worksheet->date_closed = '2009-03-16';
        $worksheet->saveWorksheet();

        // get the product and make sure it was updated
        /* @var $prodBean Product */
        $prodBean = BeanFactory::getBean($product->module_name);
        $prodBean->retrieve($product->id);

        // test the db formatted version
        $this->assertEquals($worksheet->date_closed, $prodBean->fetched_row['date_closed']);

        // test the the opp to make sure it's updated as well
        unset($oppBean);
        /* @var $oppBean Opportunity */
        $oppBean = BeanFactory::getBean($opp->module_name);
        $oppBean->retrieve($opp->id);

        // test the db formatted version
        $this->assertEquals($worksheet->date_closed, $oppBean->fetched_row['date_closed']);
    }

    /**
     * @dataProvider dataProviderSaveWorksheetUpdatesBeanValues
     * @group forecasts
     * @param $field
     * @param $start_value
     * @param $updated_value
     */
    public function testSaveWorksheetUpdatesBeanValues($field, $start_value, $updated_value)
    {
        $product = SugarTestProductUtilities::createProduct();
        $product->$field = $start_value;
        $product->save();

        $worksheet = SugarTestWorksheetUtilities::loadWorksheetForBean($product);

        $worksheet->$field = $updated_value;
        $worksheet->saveWorksheet();

        //load the product and test to see if it's the updated value
        $prodBean = BeanFactory::getBean($product->module_name);
        $prodBean->retrieve($product->id);

        $this->assertEquals($updated_value, $prodBean->fetched_row[$field]);
    }

    public function dataProviderSaveWorksheetUpdatesBeanValues()
    {
        return array(
            array('likely_case', '1000', '1500'),
            array('best_case', '1000', '1500'),
            array('worst_case', '1000', '1500'),
            array('probability', '50', '80'),
            array('date_closed', '2009-02-01', '2009-03-01'),
            array('commit_stage', 'include', 'exclude'),
            array('sales_stage', 'test1', 'test2')
        );
    }
    
    public function dataProviderTimePeriodsHasMigrated() {
        return array(
            array(null, null, false),                   // invalid dates
            array('2013-02-01', null, false),           // one invalid date
            array(null, '2013-02-01', false),           // one invalid date
            array('2013-02-01', '2013-02-01', false),    // equal dates
            array('2013-02-01', '2013-05-01', true)    // equal dates
        );
    }
    
    /**
     * @group forecasts
     * @dataProvider dataProviderTimePeriodsHasMigrated
     */
    public function testTimeperiodHasMigrated($date1, $date2, $expectedReturn)
    {
        $tp1 = SugarTestTimePeriodUtilities::createTimePeriod('2013-01-01', '2013-03-31');
        $tp2 = SugarTestTimePeriodUtilities::createTimePeriod('2013-04-01', '2013-06-30');
        $worksheet = BeanFactory::getBean("ForecastWorksheets");                    
        
        $hasMigrated = SugarTestReflection::callProtectedMethod($worksheet, 'timeperiodHasMigrated', array($date1, $date2));
        $this->assertEquals($expectedReturn, $hasMigrated);        
    }   

    /**
     * @group forecasts
     */
    public function testTimeperiodMigratedDeleteCommittedOpp()
    {
        $tp1 = SugarTestTimePeriodUtilities::createTimePeriod('2013-01-01', '2013-03-31');
        $tp2 = SugarTestTimePeriodUtilities::createTimePeriod('2013-04-01', '2013-06-30');
        
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->date_closed = '2013-01-01';
        $opp->save();
        $opp->retrieve($opp->id);
        
        $product = SugarTestProductUtilities::createProduct();
        $product->opportunity_id = $opp->id;
        $product->date_closed = '2013-01-01';
        $product->save();
        
        $worksheet = SugarTestWorksheetUtilities::loadWorksheetForBean($opp);
        $worksheet2 = SugarTestWorksheetUtilities::loadWorksheetForBean($opp);
        $worksheet2->id = "";
        $worksheet2->draft = 0;
        $worksheet2->save();
                
        $worksheet->date_closed = '2013-05-01';
        $worksheet->save();
        
        $worksheet->saveRelatedOpportunity($opp);
        $worksheet2 = BeanFactory::getBean("ForecastWorksheets", $worksheet2->id);
                
        $this->assertEquals(1, $worksheet2->deleted);        
    }
    
    /**
     * @group forecasts
     */
    public function testTimeperiodMigratedDeleteCommittedProduct()
    {
        $tp1 = SugarTestTimePeriodUtilities::createTimePeriod('2013-01-01', '2013-03-31');
        $tp2 = SugarTestTimePeriodUtilities::createTimePeriod('2013-04-01', '2013-06-30');
        
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->date_closed = '2013-01-01';
        $opp->save();
        
        $product = SugarTestProductUtilities::createProduct();
        $product->opportunity_id = $opp->id;
        $product->date_closed = '2013-01-01';
        $product->save();
        $product->retrieve($product->id);
        
        $worksheet = SugarTestWorksheetUtilities::loadWorksheetForBean($product);
        $worksheet2 = SugarTestWorksheetUtilities::loadWorksheetForBean($product);
        $worksheet2->id = "";
        $worksheet2->draft = 0;
        $worksheet2->save();
               
        $worksheet->date_closed = '2013-05-01';
        $worksheet->save();
        
        $worksheet->saveRelatedProduct($product);
        $worksheet2 = BeanFactory::getBean("ForecastWorksheets", $worksheet2->id);
        
        $this->assertEquals(1, $worksheet2->deleted);        
    }

    /**
     * @group forecasts
     */
    public function testDeleteProductMarksDraftWorksheetRecordAsDeleted()
    {
        SugarTestTimePeriodUtilities::createTimePeriod('2013-01-01', '2013-03-31');

        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->date_closed = '2013-01-01';
        $opp->save();

        $product = SugarTestProductUtilities::createProduct();
        $product->opportunity_id = $opp->id;
        $product->date_closed = '2013-01-01';
        $product->save();

        $worksheet = SugarTestWorksheetUtilities::loadWorksheetForBean($product);

        // assert that worksheet is not deleted
        $this->assertEquals(0, $worksheet->deleted);

        // delete the product
        $product->deleted = 1;
        $product->save();

        $this->assertEquals(1, $product->deleted);

        // fetch the worksheet again
        unset($worksheet);
        $worksheet = SugarTestWorksheetUtilities::loadWorksheetForBean($product, false, true);
        $this->assertEquals(1, $worksheet->deleted);
    }
}