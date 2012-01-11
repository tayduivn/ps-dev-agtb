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

/**
 * Adapter class to Elastica Result
 */
class SugarSeachEngineElasticResult implements SugarSearchEngineResult
{
    /**
     * @var \Elastica_Result
     */
    private $elasticaResult;

    /**
     * @var SugarBean
     */
    private $bean;

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
     * TODO: We may store the module by type rather than as a field within the document.
     * @return array
     */
    public function getModule()
    {
        return $this->elasticaResult->module;
    }

    public function getModuleName()
    {
        $moduleName = $this->getModule();
        if( isset($GLOBALS['app_list_strings']['moduleList'][$moduleName]) )
            return $GLOBALS['app_list_strings']['moduleList'][$moduleName];
        else
            return $moduleName;
    }

    public function getSummaryText()
    {
        if($this->bean !== FALSE)
            return $this->bean->get_summary_text();
    }

    function highlight_callback($matches) {
        // escape user input before display to avoid XSS
        return $this->preTag . htmlspecialchars($matches[0]) . $this->postTag;
    }

    public function getHighlightedHitText($preTag = '<em>', $postTag = '</em>')
    {
        $ret = array();

        // this is the word to be searched
        if (!isset($_REQUEST['q'])) {
            return $ret;
        }
        $q = $_REQUEST['q'];

        $this->preTag = $preTag;
        $this->postTag = $postTag;

        $pattern = '/' . str_replace('*', '.*?', $q) . '/i';

        $hit = $this->elasticaResult->getHit();
        if (isset($hit['_source']) && is_array($hit['_source'])) {

            foreach ($hit['_source'] as $field=>$value) {
                $tmp = preg_replace_callback($pattern, array($this, 'highlight_callback'), $value, -1, $count);
                if ($count > 0) {
                    $ret[$field] = $tmp;
                }
            }
        }

        // TODO: should return an array $ret instead of a string, returning a string to test for now
        return implode('<br>', $ret);
        //return $ret;
    }

    public function __toString()
    {
        return __CLASS__ . " " . $this->getModule() . ": " . $this->getSummaryText() . " " . $this->getId();
    }

}