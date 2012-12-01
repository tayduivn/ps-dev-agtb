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
 * This is the base object for building SugarQueries Update
 * ************ WARNING**********************************************
 * THIS CLASS AND ALL RELATED CLASSES WILL BE FUNDAMENTALLY CHANGING
 * DO NOT USE THIS TO BUILD YOUR QUERIES.  
 * ******************************************************************
 * 
 */
class SugarQuery_Builder_Update {
	/**
	 * Table for the update
	 */
	protected $table;

	/**
	 * SET Array for the updates
	 */
	protected $set = array();

	protected $order_by = array();

	protected $limit = NULL;

	/**
	 * Set up the UPDATE with the initial table
	 * @param string $table 
	 */
	public function __construct($table = NULL)
	{
		if ($table)
		{
			// Set the inital table name
			$this->table = $table;
		}
	}

	/**
	 * Set a Table to user
	 * @param string $table 
	 * @return object this
	 */
	public function table($table)
	{
		$this->table = $table;

		return $this;
	}

	/**
	 * Set the SET Paramaters
	 * @param array $pairs 
	 * @return object this
	 */
	public function set(array $pairs)
	{
		foreach ($pairs as $column => $value)
		{
			$this->set[] = array($column, $value);
		}

		return $this;
	}

	/**
	 * Set the Column, Value 
	 * @param string $column
	 * @param string $value 
	 * @return object this
	 */
	public function value($column, $value)
	{
		$this->set[] = array($column, $value);

		return $this;
	}

	public function __get($name)
	{
		return $this->$name;
	}


	/**
	 * Set an Order By Close
	 * @param string $column 
	 * @param string $direction 
	 * @return object this
	 */
	public function orderBy($column, $direction = NULL)
	{
		$this->order_by[] = array($column, $direction);

		return $this;
	}

	/**
	 * Set a LIMIT clause
	 * @param int $number 
	 * @return object this
	 */
	public function limit($number)
	{
		$this->limit = $number;

		return $this;
	}	

}