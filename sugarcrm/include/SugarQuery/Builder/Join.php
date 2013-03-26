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
 * This is the base object for building SugarQueries Joins
 * ************ WARNING**********************************************
 * THIS CLASS AND ALL RELATED CLASSES WILL BE FUNDAMENTALLY CHANGING
 * DO NOT USE THIS TO BUILD YOUR QUERIES.
 * ******************************************************************
 *
 */

require_once('include/SugarQuery/Builder/Where.php');
require_once('include/SugarQuery/Builder/Andwhere.php');
require_once('include/SugarQuery/Builder/Orwhere.php');

class SugarQuery_Builder_Join {

    /**
     * @var array
     */
    protected $options = array();

    /**
     * @var null|string
     */
    protected $table;

    /**
     * @var array
     */
    protected $on = array();

    /**
     * @var bool|string
     */
    public $raw = false;

    /**
     * @var bool|string
     */
    public $linkName = false;
	/**
	 * Create the JOIN Object
	 * @param string $table
	 * @param string $type
	 */
	public function __construct($table = null, array $options = array())
	{
		// Set the table to JOIN on
		$this->table = $table;
		$this->options = $options;
	}

	/**
	 * Set the ON criteria
	 * @param string $c1
	 * @param string $op
	 * @param string $c2
	 * @return object this
	 */
	public function on()
	{
        if(!isset($this->on['and'])) {
            $this->on['and'] = new SugarQuery_Builder_Andwhere();
        }

		return $this->on['and'];
	}

    /**
     * Set the ON criteria
     * @param string $c1
     * @param string $op
     * @param string $c2
     * @return object this
     */
    public function onOr()
    {
        if(!isset($this->on['or'])) {
            $this->on['or'] = new SugarQuery_Builder_Orwhere();
        }

        return $this->on['or'];
    }

	/**
	 * Add a string of Raw SQL
	 * @param string $sql
	 * @return SugarQuery_Builder_Join
	 */
	public function addRaw($sql) {
		$this->raw = $sql;
		return $this;
	}

	/**
	 * Add a string that is a link name from vardefs
	 * @param string $linkName
	 * @return SugarQuery_Builder_Join
	 */
	public function addLinkName($linkName) {
		$this->linkName = $linkName;
		return $this;
	}

	/**
	 * Return name of the join table
	 * @return string
	 */
	public function joinName()
	{
	    if(!empty($this->options['alias'])) {
	        return $this->options['alias'];
	    }
	    return $this->table;
	}


	public function __get($name)
	{
		return $this->$name;
	}

}