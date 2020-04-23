<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use Elastica\Response;
use PHPUnit\Framework\TestCase;

class SugarSearchEngineElasticResultTest extends TestCase
{
    private $responseString = '{"took":4,"timed_out":false,"_shards":{"total":1,"successful":1,"failed":0},"hits":{"total":1,"max_score":1.0,"hits":[{"_index":"c5368b06edf5dabf62a27e146d35ab3f","_type":"Accounts","_id":"e7abbd8c-1daa-80cc-bdce-4f3ab8cf1cca","_score":1.0, "_source":{"module":"Accounts","name":"test account"}, "highlight" : {"name":["<span class=\"highlight\">test</span>2 account"]}}]},"facets":{"_type":{"_type":"terms","missing":0,"total":1,"other":0,"terms":[{"term":"Accounts","count":1}]}}}';
    private $elasticResult;

    protected function setUp() : void
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        $response = new Response($this->responseString);
        $data = $response->getData();
        $result = new \Elastica\Result($data['hits']['hits'][0]);
        $this->elasticResult = new SugarSeachEngineElasticResult($result);
    }

    protected function tearDown() : void
    {
        SugarTestHelper::tearDown();
    }

    public function testElasticResultGetId()
    {
        $id = $this->elasticResult->getId();
        $this->assertEquals('e7abbd8c-1daa-80cc-bdce-4f3ab8cf1cca', $id, 'Incorrect Id');
    }

    public function testElasticResultGetModule()
    {
        $module = $this->elasticResult->getModule();
        $this->assertEquals('Accounts', $module, 'Incorrect module');
    }

    public function testElasticResultGetHighlightedHitText()
    {
        $_REQUEST['q'] = 'test';
        $text = $this->elasticResult->getHighlightedHitText(80, 1);
        $this->assertEquals('<span class="highlight">test</span>2 account', $text['name']['text'], 'Incorrect highlighted text');
    }

    public function testGetSource()
    {
        $source = $this->elasticResult->getSource();
        $msg = 'Expected getSource() to return an array, returned ' . var_export($source, true);
        $this->assertTrue(is_array($source), $msg);
    }
}
