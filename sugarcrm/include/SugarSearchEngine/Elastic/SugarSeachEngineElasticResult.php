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

require_once("include/SugarSearchEngine/SugarSearchEngineAbstractResult.php");
require_once("include/SugarSearchEngine/Elastic/SugarSearchEngineElasticHighlighter.php");

/**
 * Adapter class to Elastica Result
 */
class SugarSeachEngineElasticResult extends SugarSearchEngineAbstractResult
{
    /**
     * @var \Elastica_Result
     */
    private $elasticaResult;

    public function __construct(Elastica_Result $result)
    {
        $this->elasticaResult = $result;
        //No need to lazy load, will always want to load the bean to fill in the details
        $this->bean = BeanFactory::getBean($this->getModule(), $this->getId());
        if($this->bean === FALSE)
        {
            $GLOBALS['log']->fatal("Unable to load bean with id for FTS result set: {$this->getId()}");
        }
    }

    /**
     * Return the id of the
     *
     * @return string
     */
    public function getId()
    {
        return $this->elasticaResult->getId();
    }

    /**
     *
     * @return array
     */
    public function getModule()
    {
        return $this->elasticaResult->module;
    }

    /**
     * This function returns an array of highlighted key-value pairs.
     *
     * @param maxLen integer the maximum length of string in the returned array values
     * @param maxHits integer the maximum number of hits to be returned in the array elements
     * @param preTag string the tag to be used in front of the highlighted strings
     * @param postTag string the tag to be used after the highlighted strings
     *
     * @return array of key value pairs
     */
    public function getHighlightedHitText($maxLen=80, $maxHits=2, $preTag = '<em>', $postTag = '</em>')
    {
        $ret = array();
        $hit = $this->elasticaResult->getHit();

        // this is the word to be searched
        if (!isset($_REQUEST['q'])) {
            $GLOBALS['log']->error('Query string q not found.');
            return $ret;
        }
        // looks like this is encoded, so decode it first
        $q = html_entity_decode(trim($_REQUEST['q']), ENT_QUOTES);

        if (isset($hit['_source']) && is_array($hit['_source'])) {
            $highlighter = new SugarSearchEngineElasticHighlighter($maxLen, $maxHits, $preTag, $postTag);
            $highlighter->setModule($this->getModule());
            $ret = $highlighter->getHighlightedHitText($hit['_source'], $q);
        }

        return $ret;
    }

    /**
     * Return a string that matches the auto complete search.
     *
     * @return string
     */
    public function getAutoCompleteText()
    {
        $ret = '';
        $hit = $this->elasticaResult->getHit();

        if (isset($hit['_source']) && is_array($hit['_source'])) {
            $highlighter = new SugarSearchEngineElasticHighlighter();
            $ret = $highlighter->getAutoCompleteText($hit['_source']);
        }

        return $ret;
    }
}