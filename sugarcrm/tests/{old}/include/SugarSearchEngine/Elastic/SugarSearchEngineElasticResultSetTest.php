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

use Elastica\Query;
use PHPUnit\Framework\TestCase;
use Elastica\Response;
use Elastica\ResultSet;
use Elastica\Result;


class SugarSearchEngineElasticResultSetTest extends TestCase
{
    private $responseString = '{"took":8,"timed_out":false,"_shards":{"total":1,"successful":1,"failed":0},"hits":{"total":2,"max_score":1.0,"hits":[{"_index":"c5368b06edf5dabf62a27e146d35ab3f","_type":"Accounts","_id":"20575d83-63aa-2c15-a4e3-4f3ab8344249","_score":1.0, "_source" : {"name":"test account","module":"Accounts","team_set_id":"1"}},{"_index":"c5368b06edf5dabf62a27e146d35ab3f","_type":"Accounts","_id":"e7abbd8c-1daa-80cc-bdce-4f3ab8cf1cca","_score":1.0, "_source" : {"name":"test2 account","module":"Accounts","team_set_id":"1"}}]},"aggregations":{"_type":{"_type":"terms","missing":0,"total":2,"other":0,"terms":[{"term":"Accounts","count":2}]}}}';
    private $sugarElsaticResultSet;

    protected function setUp() : void
    {
        $response = new Response($this->responseString);
        $query = new Query();
        $resultSet = new ResultSet($response, $query, array());
        $this->sugarElsaticResultSet = new SugarSeachEngineElasticResultSet($resultSet);
    }

    public function testElasticResultSetTotalHits()
    {
        $totalHits = $this->sugarElsaticResultSet->getTotalHits();

        $this->assertEquals(2, $totalHits, 'Incorrect total hits');
    }

    public function testElasticResultSetFacets()
    {
        $facets = $this->sugarElsaticResultSet->getFacets();

        $this->assertEquals('terms', $facets['_type']['_type'], 'Incorrect type');
        $this->assertEquals(2, $facets['_type']['total'], 'Incorrect total');
        $this->assertEquals('Accounts', $facets['_type']['terms'][0]['term'], 'Incorrect term');
        $this->assertEquals('2', $facets['_type']['terms'][0]['count'], 'Incorrect count');
    }

    public function testElasticResultSetTotalTime()
    {
        $totalTime = $this->sugarElsaticResultSet->getTotalTime();

        $this->assertEquals(8, $totalTime, 'Incorrect total time');
    }

    public function testElasticResultSetPosition()
    {
        $key = $this->sugarElsaticResultSet->key();
        $this->assertEquals(0, $key, 'Incorrect key');

        $this->sugarElsaticResultSet->next();
        $key = $this->sugarElsaticResultSet->key();
        $this->assertEquals(1, $key, 'Incorrect key after next()');

        $this->sugarElsaticResultSet->rewind();
        $key = $this->sugarElsaticResultSet->key();
        $this->assertEquals(0, $key, 'Incorrect key after rewind()');
    }
}
