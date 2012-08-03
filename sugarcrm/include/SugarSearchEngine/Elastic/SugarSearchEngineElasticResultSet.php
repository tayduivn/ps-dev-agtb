<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
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
 *Portions created by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights
 *Reserved.
 ********************************************************************************/

require_once("include/SugarSearchEngine/Interface.php");
require_once('include/SugarSearchEngine/Elastic/SugarSeachEngineElasticResult.php');

/**
 * Adapter class to Elastica Result Set
 */
class SugarSeachEngineElasticResultSet implements SugarSearchEngineResultSet
{

    /**
     * @var \Elastica_ResultSet
     */
    private $elasticaResultSet;

    /**
     * @param Elastica_ResultSet $rs
     */
    public function __construct(Elastica_ResultSet $rs)
    {
        $this->elasticaResultSet = $rs;
    }

    /**
     * Return the total number of hits found from our search
     *
     * @return int
     */
    public function getTotalHits()
    {
        return $this->elasticaResultSet->getTotalHits();
    }

    /**
     * Return facets associated with this search.
     *
     * @return array
     */
    public function getFacets()
    {
        return $this->elasticaResultSet->getFacets();
    }

    /**
     * Return the facet results for the modules used in the search.
     *
     * @return array|bool
     */
    public function getModuleFacet()
    {
        $rs = $this->elasticaResultSet->getFacets();
        $results = array();
        if( !isset($rs['_type'] ) || !isset($rs['_type']['terms']) )
        {
            return FALSE;
        }
        else
        {
            foreach( $rs['_type']['terms'] as $entry)
            {
                $results[$entry['term']] = $entry['count'];
            }

            return $results;
        }
    }
    /**
     * Get the total amount of time the search took to complete.
     *
     * @return int
     */
    public function getTotalTime()
    {
        return $this->elasticaResultSet->getTotalTime();
    }

    public function current()
    {
        return new SugarSeachEngineElasticResult($this->elasticaResultSet->current());
    }

    public function key()
    {
        return $this->elasticaResultSet->key();
    }

    public function next()
    {
        $this->elasticaResultSet->next();
    }

    public function rewind()
    {
        $this->elasticaResultSet->rewind();
    }

    public function valid()
    {
        return $this->elasticaResultSet->valid();
    }

    /**
     * Return the count of hits returned, may not necessarily equal total hits.
     *
     * @return int
     */
    public function count()
    {
        return $this->elasticaResultSet->count();
    }
}