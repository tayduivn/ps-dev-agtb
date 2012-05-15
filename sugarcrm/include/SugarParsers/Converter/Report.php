<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

require_once("include/SugarParsers/Converter/AbstractConverter.php");
class SugarParsers_Converter_Report extends SugarParsers_Converter_AbstractConverter
{

    /**
     * Storage of all the Filters
     * @var array
     */
    protected $_reportFilters = array();

    /**
     * Default Control Statement
     *
     * @var string
     */
    protected $controlStatement = "AND";

    /**
     * @var ReportBuilder
     */
    protected $reportBuilder;

    protected $table_key = "self";

    /**
     * Set the ReportBuilderObject
     *
     * @param ReportBuilder $reportBuilder
     */
    public function setReportBuilder(ReportBuilder $reportBuilder)
    {
        $this->reportBuilder = $reportBuilder;
    }

    /**
     * Convert the filter into a Report Engine Friendly Array
     *
     * @param mixed $value
     * @return array
     */
    public function convert($value)
    {
        foreach ($value as $key => $val) {

            /* @var $val SugarParsers_Filter_AbstractFilter */
            if ($val::isControlVariable()) {
                if (!($val instanceOf SugarParsers_Filter_Not)) {
                    $this->controlStatement = $val->getOperator(true, $this->is_not);
                } else if ($val instanceof SugarParsers_Filter_Not) {
                    $this->is_not = true;
                }

                $val = $val->getValue();
            }

            // check to see if the key is a link
            $linkName = $this->checkLinkExistsInReportBuilder($key);
            if ($linkName !== false) {
                $this->table_key = $linkName;
            }

            $this->_convert($key, $val);

            // reset it to the default value;
            $this->table_key = "self";
        }

        return array("Filter_1" => array_merge(array(
            'operator' => $this->controlStatement,
        ), $this->_reportFilters));
    }

    /**
     * Internal Method to Convert
     *
     * @param string|integer $key
     * @param SugarParsers_Filter_AbstractFilter $value
     */
    protected function _convert($key, $value)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $this->_convert($k, $v);
            }
        } else {
            if (is_integer($key)) {
                $key = $value->getKey();
            }
            // create a new filter
            $filter = $this->createFilter($key, $value->getOperator(true, $this->is_not), $value->getValue());
            if (!empty($filter)) {
                $this->_reportFilters[] = $filter;
            }
        }
    }

    /**
     * @param string $field_name        Name of the field we are modifying
     * @param string $operator          Operator to use.
     * @param string $value             Value for the field
     * @return array
     */
    protected function createFilter($field_name, $operator, $value)
    {
        if ($this->reportBuilder instanceof ReportBuilder) {
            // we need to check to see if the files exist

            /* @var $def_bean SugarBean */
            $def_bean = $this->reportBuilder->getBeanFromTableKey($this->table_key);
            if ($this->checkFieldExist($def_bean, $field_name)) {
                return array(
                    "name" => $field_name,
                    "table_key" => $this->reportBuilder->getKeyTable($def_bean->module_dir),
                    "qualifier_name" => $operator,
                    "input_name0" => $value
                );
            }

            return array();
        } else {
            return array(
                "name" => $field_name,
                "table_key" => $this->table_key,
                "qualifier_name" => $operator,
                "input_name0" => $value
            );
        }

    }

    /**
     * @param SugarBean $bean       Which bean are we checking
     * @param string $field         The field we are looking for
     * @return bool
     */
    protected function checkFieldExist($bean, $field)
    {
        if (isset($bean->field_defs[$field]) && $bean->field_defs[$field]['type'] != "link") {
            return true;
        }

        return false;
    }

    /**
     * @param string $link
     * @return bool|array
     */
    protected function checkLinkExistsInReportBuilder($link)
    {
        // make sure the link was added to the ReportBuilder
        if (!($this->reportBuilder instanceOf ReportBuilder)) return false;

        $rbLinkKey = $this->reportBuilder->getLinkTable($link);

        // if we got an array back, try adding it
        if (is_array($rbLinkKey)) {
            $this->reportBuilder->addLink($link);
            $rbLinkKey = $this->reportBuilder->getLinkTable($link);
        }

        // strange it still didn't add it, must be a bad link. return false.
        if (is_array($rbLinkKey)) {
            return false;
        }

        return $rbLinkKey;
    }
}