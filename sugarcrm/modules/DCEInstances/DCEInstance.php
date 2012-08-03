<?PHP
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/**
 * THIS CLASS IS FOR DEVELOPERS TO MAKE CUSTOMIZATIONS IN
 */
require_once('modules/DCEInstances/DCEInstance_sugar.php');



class DCEInstance extends DCEInstance_sugar {

	function DCEInstance(){
		parent::DCEInstance_sugar();
	}

	function fill_in_relationship_fields(){
	    $this->dcetemplate_name = '';
	    parent::fill_in_relationship_fields();
	}

	function save_relationship_changes($is_update){
		parent::save_relationship_changes($is_update);

		if(!$is_update){

			$cronSchedule = new DCECronSchedule();
			$cronSchedule->instance_id = $this->id;
			$cronSchedule->save();
		}
	    if (!empty($this->contact_id)) {
            $this->set_dceinstance_contact_relationship($this->contact_id);
        }
	    if (!empty($this->user_id)) {
            $this->set_dceinstance_user_relationship($this->user_id);
        }
	}

   /** Returns a list of the associated contacts
     * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
     * All Rights Reserved..
     * Contributor(s): ______________________________________..
    */
    function get_contacts()
    {
        $this->load_relationship('contacts');
        $query_array=$this->contacts->getQuery(true);

        //update the select clause in the returned query.
        $query_array['select']="SELECT contacts.id, contacts.first_name, contacts.last_name, contacts.title, contacts.phone_work, dceinstances_contacts.contact_role as dceinstance_role, dceinstances_contacts.id as dceinstance_rel_id ";

        $query='';
        foreach ($query_array as $qstring) {
            $query.=' '.$qstring;
        }
        $temp = Array('id', 'first_name', 'last_name', 'title', 'phone_work', 'dceinstance_role', 'dceinstance_rel_id');
        return $this->build_related_list2($query, new Contact(), $temp);
    }
   /** Returns a list of the associated users
     * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
     * All Rights Reserved..
     * Contributor(s): ______________________________________..
    */
    function get_users()
    {
        $this->load_relationship('users');
        $query_array=$this->users->getQuery(true);

        //update the select clause in the returned query.
        $query_array['select']="SELECT users.id, users.first_name, users.last_name, users.title users.phone_work, dceinstances_users.user_role as dceinstance_role, dceinstances_users.id as dceinstance_rel_id ";

        $query='';
        foreach ($query_array as $qstring) {
            $query.=' '.$qstring;
        }
        $temp = Array('id', 'first_name', 'last_name', 'title', 'phone_work', 'dceinstance_role', 'dceinstance_rel_id');
        return $this->build_related_list2($query, new user(), $temp);
    }

    function set_dceinstance_contact_relationship($contact_id)
    {
        global $app_list_strings;
        $default = $app_list_strings['dceinstance_contact_relationship_type_default_key'];
        $this->load_relationship('contacts');
        $this->contacts->add($contact_id,array('contact_role'=>$default));
    }

    function set_dceinstance_user_relationship($user_id)
    {
        global $app_list_strings;
        $default = $app_list_strings['dceinstance_user_relationship_type_default_key'];
        $this->load_relationship('users');
        $this->users->add($user_id,array('user_role'=>$default));
    }
    /*
     * Create the DCE Action record that will be picked up by DN processing
     */
    function create_action($record, $actionType, $startDate='',$priority='',$upgradeVars='',$dbCloned=false){
        global $timedate;
        if( (isset($record) && !empty($record))
        && (isset($actionType) && !empty($actionType) )){
            // Retrieve Instance.

            $inst = new DCEInstance();
            $inst->retrieve($record);

            //set the priority (default to medium)
            if(empty($priority)){
                $priority = '1';

                //if toggle support user, default to high, and set the support flag on
                if(strpos($actionType,'toggle')!==false){
                    $priority = '2';
                    if($inst->support_user){
                     //support user was already created once, so this action is requesting immediate clean up
                     //change priority settings and resave
                        $actionType = 'toggle_off';

                    }else{
                        $actionType = 'toggle_on';
                    }
                }
            }

            //create dce action

            $action = new DCEAction();
            $action->name = $inst->name.' '.$actionType.' action';
            $action->instance_id = $inst->id;
            $action->cluster_id = $inst->dcecluster_id;
            $action->template_id = $inst->dcetemplate_id;
            $action->type = $actionType;//'create';
            $action->status = 'queued';
            $action->start_date = $timedate->now();
            $action->priority = $priority;
            $action->action_parms .= ', previous_status:'.$inst->status;

            //reset start time if set.
            if(!empty($startDate)){
                $action->start_date = $startDate;
            }

            //change the instance status to pending if this is not a toggle or license action
            if(strpos($actionType,'toggle') === false && strpos($actionType,'key') === false){
                $inst->status = 'in_progress';
            }

            //if action is convert, then modify instance and set action to deleted
            if($actionType == 'convert'){
                $inst->status = 'live';
                $inst->type = 'production';
                $action->status = 'completed';
            }

            //if action is create and parent_id is set, then this is a clone,
            //check to see if db should be cloned
            if($actionType=='create'){
                global $sugar_config;
                if(!empty($sugar_config['unique_key'])){
                    $action->action_parms .= ',unique_key:'.$sugar_config['unique_key'].' ' ;
                }

                if(!empty($inst->dce_parent_id) &&$dbCloned){
                                $action->action_parms .= ',clone_db:true ' ;
                }
            }

            //if this is an upgrade, then create the upgrade variables to be
            //used for processing the action on the dn side
            if(strpos($actionType, 'upgrade')!==false && !empty($upgradeVars)){
                if(is_array($upgradeVars)){
                    foreach($upgradeVars as $k => $v){
                        $action->action_parms .= ", $k:$v ";
                    }
                }
            }


            //save action
            $action->save();


            //if action is toggle, then create a toggle_on and toggle_off action
            if(strpos($actionType,'toggle')!==false && !$inst->support_user){

                 //create second action to disable
                    $action2 = new DCEAction();
                    $action2->name = $inst->name.' '.$actionType.' off action';
                    $action2->instance_id = $inst->id;
                    $action2->cluster_id = $inst->dcecluster_id;
                    $action2->template_id = $inst->dcetemplate_id;
                    $action2->priority = '1';
                    $action2->type = 'toggle_off';
                    $action2->status = 'queued';

                  //add start date in future
                   //retrieve num of hours for expiration of user from settings
                    $adm = new Administration();
                    $adm->retrieveSettings();
                    $exp_hours =$adm->settings['dce_support_user_time_limit'];
                    if(empty($exp_hours)) $exp_hours = 5;

                    $action2->start_date = $timedate->fromUser($action->start_date)->modify("+$exp_hours hours")->asUser();
                    $action2->save();

                    //support user is not already set, then create new action to disable and set support user
                    $inst->support_user = 1;
            }
            //save unless instance has been deleted.  We do not save because
            //deletion requires to know what the instance status is for instance location on DN side,
            //and we do not want to accidentally change it.
            if($actionType!='delete') $inst->save();
        }else{
         //could not create action.

        }

    }

    function returnExpirationDate($lic_start,$lic_duration){
        global $timedate;
        if(empty($lic_start) || empty($lic_duration)){
            return false;
        }
        // license start date plus $duration days
        return TimeDate::fromDbFormat($lic_start, $timedate->get_db_date_format())->modify("+{$lic_duration} days")->asDbDate();
    }

}
