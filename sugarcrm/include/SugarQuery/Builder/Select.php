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
 * SugarQueryBuilder_Select
 * @api
 */

class SugarQuery_Builder_Select
{

    /**
     * Array of Select fields/statements
     * @var array
     */
    protected $select = array();

    /**
     * Create Select Object
     * @param $columns
     */
    public function __construct($columns)
	{
		if(!is_array($columns)) {
			$columns = func_get_args();

		}
		$this->select = array_merge($this->select, $columns);	
	}

    /**
     * Select method
     * Add select elements
     * @param string $columns
     * @return object this
     */
	public function field($columns) {
		$columns = func_get_args();
		$this->select = array_merge($this->select, $columns);
		return $this;
	}


    /**
     * SelectReset method
     * clear out the objects select array
     * @return object this
     */
	public function selectReset() {
		$this->select = array();
		return $this;
	}

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
	{
		return $this->$name;
	}


}