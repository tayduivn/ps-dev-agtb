<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/



/**
 * This is the base object for building SugarQueries Conditions
 * ************ WARNING**********************************************
 * THIS CLASS AND ALL RELATED CLASSES WILL BE FUNDAMENTALLY CHANGING
 * DO NOT USE THIS TO BUILD YOUR QUERIES.  
 * ******************************************************************
 * 
 */
class SugarQuery_Builder_Condition
{
    /**
     * @var string
     */
    public $operator;
    /**
     * @var string
     */
    public $field;
    /**
     * @var array
     */
    public $values = array();
    /**
     * @var bool|SugarBean
     */
    public $bean = false;
    /**
     * @var bool
     */
    public $isNull = false;
    /**
     * @var bool
     */
    public $notNull = false;

	public function __construct() {}

    /**
     * @param string $operator
     * @return SugarQuery_Builder_Condition
     */
    public function setOperator($operator) {
		$this->operator = $operator;
		return $this;
	}

    /**
     * @param array $values
     * @return SugarQuery_Builder_Condition
     */
    public function setValues($values) {
		$this->values = $values;
		return $this;
	}

    /**
     * @param string $field
     * @return SugarQuery_Builder_Condition
     */
    public function setField($field) {
		$this->field = $field;
		return $this;
	}

    /**
     * @param SugarBean $bean
     */
    public function setBean(SugarBean $bean) {
		$this->bean = $bean;
	}

    /**
     * @return SugarQuery_Builder_Condition
     */
    public function isNull() {
		$this->isNull = true;
		return $this;
	}

    /**
     * @return SugarQuery_Builder_Condition
     */
    public function notNull() {
		$this->notNull = true;
		return $this;
	}

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
	{
		return $this->$name;
	}

}