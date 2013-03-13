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

    protected $query;

    /**
     * Create Select Object
     * @param $columns
     */
    public function __construct(SugarQuery $query, $columns)
	{
        if(!is_array($columns)) {
            $columns = array_slice(func_get_args(), 1);
        }
        $this->query = $query;
        $this->field($columns);
	}

    /**
     * Select method
     * Add select elements
     * @param string $columns
     * @return object this
     */
	public function field($columns) {
        if(!is_array($columns)) {
            $columns = array_slice(func_get_args(), 1);
        }
        if(!empty($this->select)) {
            $this->select = array_unique(array_merge($this->select, $columns));
        } else {
            $this->select = $columns;
        }
//         foreach($columns as $field)
//         {
//             $this->addFieldToQuery($this->query, $field);
//         }
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

    /**
     * Should be called when the from Bean is added/changed from the related query.
     */
    public function updateFrom()
    {
//         $oldSelect = $this->select;
//         $this->selectReset();
//         foreach($oldSelect as $field)
//         {
//             $this->addFieldToQuery($this->query, $field);
//         }
    }

    /**
     * Add bean field to the query
     * @param SugarQuery $query
     * @param string $field
     */
    protected function addFieldToQuery(SugarQuery $query, $field)
    {
        if (in_array($field, $this->select))
        {
            return;
        }

        $fieldName = is_array($field) ?  $field[0] : $field;
        $seed = !empty($query->from) && is_array($query->from) ? $query->from[0] : $query->from;
        if (!empty($seed))
        {
            if (isset($seed->field_defs[$fieldName]))
            {
                $def = $seed->field_defs[$fieldName];
                //Simple DB fields can be placed in the select normally
                if (!isset($def['source']) || $def['source'] == 'db')
                {
                    $this->select[] = $field;
                } else
                {
                    //Here is where we need to start implementing the harder code.
                    //Similar to what we have in create_new_list_query, we will need joins, additional alias's, ect
                    //I'm not sure how well we can do thins like track what tables are already joined in the query
                    //And determine if we need to join them a second time or re-use the existing join.
                }
            } else {
                $this->select[] = $field;
            }
        } else
        {
            $this->select[] = $field;
        }

    }


}