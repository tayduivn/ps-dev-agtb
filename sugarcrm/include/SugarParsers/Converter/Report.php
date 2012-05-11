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

require_once("include/SugarParsers/Converter/AbstractDecorator.php");
class SugarParsers_Converter_Report extends SugarParsers_Converter_AbstractConverter
{

    /**
     * Storage of all the Filters
     * @var array
     */
    protected $_reportFilters = array();

    protected $controlStatement = "AND";

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

            $this->_convert($key, $val);
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
            $this->_reportFilters[] = $this->createFilter($key, $value->getOperator(true, $this->is_not), $value->getValue());
        }
    }

    protected function createFilter($field_name, $operator, $value)
    {
        return array(
            "name" => $field_name,
            "table_key" => "self",
            "qualifier_name" => $operator,
            "input_name0" => $value
        );
    }
}