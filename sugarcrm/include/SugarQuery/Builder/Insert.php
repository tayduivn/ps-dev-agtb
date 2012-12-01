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
 * This is the base object for building SugarQueries Insert
 * ************ WARNING**********************************************
 * THIS CLASS AND ALL RELATED CLASSES WILL BE FUNDAMENTALLY CHANGING
 * DO NOT USE THIS TO BUILD YOUR QUERIES.  
 * ******************************************************************
 * 
 */
class SugarQuery_Builder_Insert {
	/**
	 * Table to do the insert on
	 */
	protected $table;

	/**
	 * Columns array for the inserts
	 */
	protected $columns = array();

	/**
	 * Values of the insert
	 */
	protected $values = array();

	/**
	 * Constructor, sets up the insert
	 * @param string $table 
	 * @param array $columns 
	 * @return object this
	 */
	public function __construct($table = NULL, array $columns = NULL)
	{
		if ($table)
		{
			// Set the inital table name
			$this->table = $table;
		}

		if ($columns)
		{
			// Set the column names
			$this->columns = $columns;
		}
	}

	/**
	 * Set the Table for the insert
	 * @param string $table 
	 * @return object this
	 */
	public function table($table)
	{
		$this->table = $table;

		return $this;
	}

	/**
	 * Set the columns for the insert
	 * @param array $columns 
	 * @return object this
	 */
	public function columns(array $columns)
	{
		$this->columns = $columns;

		return $this;
	}

	/**
	 * Set the values for the insert
	 * @param array $values 
	 * @return object this
	 */
	public function values(array $values)
	{
		// Get all of the passed values
		$values = func_get_args();

		$this->values = array_merge($this->values, $values);

		return $this;
	}

	public function __get($name)
	{
		return $this->$name;
	}

}