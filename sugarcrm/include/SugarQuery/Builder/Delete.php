<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

class SugarQuery_Builder_Delete {

	/**
	 * Table to delete from
	 */
	protected $table;
	protected $order_by = array();
	protected $limit = null;

	/**
	 * Set up the DELETE
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
	 * Set Delete Table
	 * @param string $table 
	 * @return object this
	 */
	public function table($table)
	{
		$this->table = $table;

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