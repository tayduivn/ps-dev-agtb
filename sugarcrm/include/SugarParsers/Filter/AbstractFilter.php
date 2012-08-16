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

require_once("include/SugarParsers/Filter/FilterInterface.php");
abstract class SugarParsers_Filter_AbstractFilter implements SugarParsers_Filter_FilterInterface
{
    /**
     * Default to false to have it ignore the class
     *
     * @var Mixed
     */
    protected $variables = false;

    /**
     * This is the filtered value,
     * It could be any number of things from Arrays to strings
     *
     * @var Mixed
     */
    protected $value;

    /**
     * This should be the key (field name) of each field
     *
     * @var String
     */
    protected $key;

    /**
     * Operator Eg: < > + - =
     *
     * @var null|string
     */
    protected $operator = null;

    /**
     * Not Operator Eg: !=
     *
     * @var null|string
     */
    protected $operator_not = null;

    /**
     * Text Version of the Operator
     *
     * @var null|string
     */
    protected $operator_text = null;

    /**
     * Text Version of the Operator when used with not
     *
     * @var null|string
     */
    protected $operator_not_text = null;

    /**
     * Just store the value so we can use it later
     *
     * @param String $value
     */
    public function filter($value)
    {
        $this->value = $value;
    }

    /**
     * Set the key (usually the field name);
     *
     * @param $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * Return the set key
     *
     * @return String
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Return the variables set for each class so we have a mapping
     *
     * @return bool|Mixed
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * Return the stored value
     *
     * @return Mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * This is a way to tell if the filter is a control method
     *
     * @static
     * @return bool
     */
    public static function isControlVariable()
    {
        return false;
    }

    /**
     * Return an operator
     *
     * @param bool $text        Return Text Version
     * @param bool $not         Return Not Version
     * @return null|string
     */
    public function getOperator($text = false, $not = false)
    {
        if ($text === true and $not === true) {
            return $this->operator_not_text;
        } elseif ($text === true && $not === false) {
            return $this->operator_text;
        } elseif ($text === false && $not === true) {
            return $this->operator_not;
        } else {
            return $this->operator;
        }
    }


    public function getValueInputs($field_name, $table_key, $operator)
    {
        return array(
            "name" => $field_name,
            "table_key" => $table_key,
            "qualifier_name" => $operator,
            "input_name0" => $this->value
        );
    }
}