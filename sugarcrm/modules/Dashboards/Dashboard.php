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

require_once('data/visibility/OwnerVisibility.php');

/**
 *  Dashboards is used to store dashboard configuration data.
 */
class Dashboard extends Basic 
{    
    public $table_name = "dashboards";
    public $module_name = 'Dashboards';
    public $module_dir = 'Dashboards';
    public $object_name = "Dashboard";
    
    public function __construct() 
    {
        parent::__construct();
        $this->addVisibilityStrategy("OwnerVisibility");
    }

    /**
     * This overrides the default retrieve function setting the default to encode to false
     */
    function retrieve($id='-1', $encode=false,$deleted=true)
    {
        return parent::retrieve($id, false, $deleted);
    }

    /**
     * This function fetches an array of dashboards for the current user
     */
    public function getDashboardsForUser( User $user, $options = array() )
    {
        $order = !empty($options['order_by']) ? $options['order_by'] : 'date_entered desc';
        $from = "assigned_user_id = '".$this->db->quote($user->id)."' and dashboard_module ='".$this->db->quote($options['dashboard_module'])."'";
        if (!empty($options['view'])) {
            $from .= " and view ='".$this->db->quote($options['view'])."'";
        }
        $offset = !empty($options['offset']) ? (int)$options['offset'] : 0;
        $limit = !empty($options['limit']) ? (int)$options['limit'] : -1;
        $result = $this->get_list($order,$from,$offset,$limit,-99,0);
        $nextOffset = (count($result['list']) > 0 && count($result['list']) ==  $limit) ? ($offset + $limit) : -1;
        return array('records'=>$result['list'], 'next_offset'=>$nextOffset);
    }

    /**
     * This overrides the default save function setting assigned_user_id
     * @see SugarBean::save()
     */
    function save($check_notify = FALSE)
    {
        $this->assigned_user_id = $GLOBALS['current_user']->id;
        return parent::save($check_notify);
    }
}
