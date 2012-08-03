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


require_once 'include/SugarSearchEngine/Elastic/SugarSearchEngineElasticResultSet.php';
require_once 'include/SugarSearchEngine/Elastic/Elastica/ResultSet.php';
require_once 'include/SugarSearchEngine/Elastic/Elastica/Result.php';
require_once 'include/SugarSearchEngine/Elastic/Elastica/Response.php';

class SugarSearchEngineElasticResultSetTest extends Sugar_PHPUnit_Framework_TestCase
{

    private $_responseString = '{"took":8,"timed_out":false,"_shards":{"total":1,"successful":1,"failed":0},"hits":{"total":2,"max_score":1.0,"hits":[{"_index":"c5368b06edf5dabf62a27e146d35ab3f","_type":"Accounts","_id":"20575d83-63aa-2c15-a4e3-4f3ab8344249","_score":1.0, "_source" : {"name":"test account","module":"Accounts","team_set_id":"1"}},{"_index":"c5368b06edf5dabf62a27e146d35ab3f","_type":"Accounts","_id":"e7abbd8c-1daa-80cc-bdce-4f3ab8cf1cca","_score":1.0, "_source" : {"name":"test2 account","module":"Accounts","team_set_id":"1"}}]},"facets":{"_type":{"_type":"terms","missing":0,"total":2,"other":0,"terms":[{"term":"Accounts","count":2}]}}}';
    private $_sugarElsaticResultSet;

    public function setUp()
    {
        $response = new Elastica_Response($this->_responseString);
        $elasticResultSet = new Elastica_ResultSet($response);
        $this->_sugarElsaticResultSet = new SugarSeachEngineElasticResultSet($elasticResultSet);
    }

    public function testElasticResultSetTotalHits()
    {
        $totalHits = $this->_sugarElsaticResultSet->getTotalHits();

        $this->assertEquals(2, $totalHits, 'Incorrect total hits');
    }

    public function testElasticResultSetFacets()
    {
        $facets = $this->_sugarElsaticResultSet->getFacets();

        $this->assertEquals('terms', $facets['_type']['_type'], 'Incorrect type');
        $this->assertEquals(2, $facets['_type']['total'], 'Incorrect total');
        $this->assertEquals('Accounts', $facets['_type']['terms'][0]['term'], 'Incorrect term');
        $this->assertEquals('2', $facets['_type']['terms'][0]['count'], 'Incorrect count');
    }

    public function testElasticResultSetTotalTime()
    {
        $totalTime = $this->_sugarElsaticResultSet->getTotalTime();

        $this->assertEquals(8, $totalTime, 'Incorrect total time');
    }

    public function testElasticResultSetPosition()
    {
        $key = $this->_sugarElsaticResultSet->key();
        $this->assertEquals(0, $key, 'Incorrect key');

        $this->_sugarElsaticResultSet->next();
        $key = $this->_sugarElsaticResultSet->key();
        $this->assertEquals(1, $key, 'Incorrect key after next()');

        $this->_sugarElsaticResultSet->rewind();
        $key = $this->_sugarElsaticResultSet->key();
        $this->assertEquals(0, $key, 'Incorrect key after rewind()');
    }
}
