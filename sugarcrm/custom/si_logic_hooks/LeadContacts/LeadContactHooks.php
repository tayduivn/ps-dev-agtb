<?php
class LeadContactHooks{
    // This function is for IT REQUEST 11258
	function updateTouchpointAssignedFields($focus, $event, $arguments){
        if($event == "after_save"){
            $query = "update touchpoints set touchpoints.assigned_user_id = '".$focus->assigned_user_id."' ".
                        "where touchpoints.new_leadcontact_id = '".$focus->id."' and touchpoints.assigned_user_id != '".$focus->assigned_user_id."' and deleted = 0";
            $res = $GLOBALS['db']->query($query);
        }
	}
}
