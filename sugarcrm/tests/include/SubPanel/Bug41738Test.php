<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
 
require_once('data/SugarBean.php');
require_once('modules/Contacts/Contact.php');
require_once('include/SubPanel/SubPanelDefinitions.php');

class Bug41738Test extends Sugar_PHPUnit_Framework_TestCase 
{   	
    protected $bean;

	public function setUp()
	{
	    global $moduleList, $beanList, $beanFiles;
        require('include/modules.php');
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['modListHeader'] = query_module_access_list($GLOBALS['current_user']);
        $GLOBALS['modules_exempt_from_availability_check']['Calls']='Calls';
        $GLOBALS['modules_exempt_from_availability_check']['Meetings']='Meetings';
        $this->bean = new Opportunity();
	}

	public function tearDown()
	{
	    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
	}

    public function testSubpanelCollectionWithSpecificQuery()
    {
        $subpanel = array(
			'order' => 20,
			'sort_order' => 'desc',
			'sort_by' => 'date_entered',
			'type' => 'collection',
			'subpanel_name' => 'history',   //this values is not associated with a physical file.
			'top_buttons' => array(),
			'collection_list' => array(
				'meetings' => array(
					'module' => 'Meetings',
					'subpanel_name' => 'ForHistory',
                    'get_subpanel_data' => 'function:subpanelCollectionWithSpecificQueryMeetings',
                    'generate_select'=>false,
                    'function_parameters' => array(
                        'bean_id'=>$this->bean->id,
                        'import_function_file' => __FILE__
                    ),
				),
				'tasks' => array(
					'module' => 'Tasks',
					'subpanel_name' => 'ForHistory',
                    'get_subpanel_data' => 'function:subpanelCollectionWithSpecificQueryTasks',
                    'generate_select'=>false,
                    'function_parameters' => array(
                        'bean_id'=>$this->bean->id,
                        'import_function_file' => __FILE__
                    ),
				),
			)
        );
        $subpanel_def = new aSubPanel("testpanel", $subpanel, $this->bean);
        $query = $this->bean->get_union_related_list($this->bean, "", '', "", 0, 5, -1, 0, $subpanel_def);
        $result = $this->bean->db->query($query["query"]);
        $this->assertTrue($result != false, "Bad query: {$query['query']}");
    }


}


function subpanelCollectionWithSpecificQueryMeetings($params)
{
		$query = "SELECT meetings.id , meetings.name , meetings.status , 0 reply_to_status , ' ' contact_name , ' ' contact_id , ' ' contact_name_owner , ' ' contact_name_mod , meetings.parent_id , meetings.parent_type , meetings.date_modified , jt1.user_name assigned_user_name , jt1.created_by assigned_user_name_owner , 'Users' assigned_user_name_mod, ' ' filename , meetings.assigned_user_id , 'meetings' panel_name 
			FROM meetings 
			LEFT JOIN users jt1 ON jt1.id= meetings.assigned_user_id AND jt1.deleted=0 AND jt1.deleted=0 
			WHERE ( meetings.parent_type = 'Opportunities'
				AND meetings.deleted=0 
				AND (meetings.status='Held' OR meetings.status='Not Held') 
				AND meetings.parent_id IN(
											SELECT o.id 
											FROM opportunities o 
											INNER JOIN opportunities_contacts oc on o.id = oc.opportunity_id 
											AND oc.contact_id = '".$params['bean_id']."')
							)";

		return $query ;
}

function subpanelCollectionWithSpecificQueryTasks($params)
{
		$query = "SELECT tasks.id , tasks.name , tasks.status , 0 reply_to_status , ' ' contact_name , ' ' contact_id , ' ' contact_name_owner , ' ' contact_name_mod , tasks.parent_id , tasks.parent_type , tasks.date_modified , jt1.user_name assigned_user_name , jt1.created_by assigned_user_name_owner , 'Users' assigned_user_name_mod, ' ' filename , tasks.assigned_user_id , 'tasks' panel_name 
			FROM tasks 
			LEFT JOIN users jt1 ON jt1.id= tasks.assigned_user_id AND jt1.deleted=0 AND jt1.deleted=0 
			WHERE ( tasks.parent_type = 'Opportunities'
				AND tasks.deleted=0 
				AND (tasks.status='Completed' OR tasks.status='Deferred') 
				AND tasks.parent_id IN(
											SELECT o.id 
											FROM opportunities o 
											INNER JOIN opportunities_contacts oc on o.id = oc.opportunity_id 
											AND oc.contact_id = '".$params['bean_id']."')
							)";

		return $query ;
}


