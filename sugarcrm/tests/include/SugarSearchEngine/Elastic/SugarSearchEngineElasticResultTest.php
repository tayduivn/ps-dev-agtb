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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


require_once 'include/SugarSearchEngine/Elastic/SugarSeachEngineElasticResult.php';
require_once 'include/SugarSearchEngine/Elastic/Elastica/ResultSet.php';
require_once 'include/SugarSearchEngine/Elastic/Elastica/Result.php';
require_once 'include/SugarSearchEngine/Elastic/Elastica/Response.php';

class SugarSearchEngineElasticResultTest extends Sugar_PHPUnit_Framework_TestCase
{

    private $_responseString = '{"took":4,"timed_out":false,"_shards":{"total":1,"successful":1,"failed":0},"hits":{"total":1,"max_score":1.0,"hits":[{"_index":"c5368b06edf5dabf62a27e146d35ab3f","_type":"Accounts","_id":"e7abbd8c-1daa-80cc-bdce-4f3ab8cf1cca","_score":1.0, "_source":{"module":"Accounts","name":"test account"}, "highlight" : {"name":["<span class=\"highlight\">test</span>2 account"]}}]},"facets":{"_type":{"_type":"terms","missing":0,"total":1,"other":0,"terms":[{"term":"Accounts","count":1}]}}}';
    private $_elasticResult;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        $response = new Elastica_Response($this->_responseString);
        $elasticResultSet = new Elastica_ResultSet($response);
        $results = $elasticResultSet->getResults();
        $this->_elasticResult = new SugarSeachEngineElasticResult($results[0]);
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    public function testElasticResultGetId()
    {
        $id = $this->_elasticResult->getId();
        $this->assertEquals('e7abbd8c-1daa-80cc-bdce-4f3ab8cf1cca', $id, 'Incorrect Id');
    }

    public function testElasticResultGetModule()
    {
        $module = $this->_elasticResult->getModule();
        $this->assertEquals('Accounts', $module, 'Incorrect module');
    }

    public function testElasticResultGetHighlightedHitText()
    {
        $_REQUEST['q'] = 'test';
        $text = $this->_elasticResult->getHighlightedHitText(80, 1);
        $this->assertEquals('<span class="highlight">test</span>2 account', $text['Name'], 'Incorrect highlighted text');

    }

}
