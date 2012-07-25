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


require_once 'data/SugarVisibility.php';

/**
 * Bean visibility manager
 * @api
 */
class BeanVisibility
{
    /**
     * List of strategies to apply to this bean
     * @var array
     */
    protected $strategies = array();
    /**
     * Parent bean
     * @var SugarBean
     */
    protected $bean;

    /**
     * @param SugarBean $bean
     * @param array $metadata
     */
    public function __construct($bean, $metadata)
    {
        $this->bean = $bean;
        foreach($metadata as $visclass => $data) {
            if($data === false) continue;
            $this->strategies[] = new $visclass($bean, $data);
        }
    }

    /**
     * Add the strategy to the list
     * @param string $strategy Strategy class name
     * @param mixed $data Strategy params
     */
    public function addStrategy($strategy, $data = null)
    {
        $this->strategies[] = new $strategy($this->bean, $data);
    }

    /**
     * Add visibility clauses to the FROM part of the query
     * @param string $query
     * @param array $options
     * @return string Modified query
     */
    public function addVisibilityFrom(&$query, $options = array())
    {
        foreach($this->strategies as $strategy) {
            $strategy->setOptions($options)->addVisibilityFrom($query);
        }
        return $query;
    }

    /**
     * Add visibility clauses to the WHERE part of the query
     * @param string $query
     * @param array $options
     * @return string Modified query
     */
    public function addVisibilityWhere(&$query, $options = array())
    {
        foreach($this->strategies as $strategy) {
            $strategy->setOptions($options)->addVisibilityWhere($query);
        }
        return $query;
    }
}
