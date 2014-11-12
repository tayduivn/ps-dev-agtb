<?php
/**
 * THIS CLASS IS GENERATED BY MODULE BUILDER
 * PLEASE DO NOT CHANGE THIS CLASS
 * PLACE ANY CUSTOMIZATIONS IN pmse_BpmnEvent
 */
class pmse_BpmnEvent_sugar extends Basic {
	var $new_schema = true;
	var $module_dir = 'pmse_Project/pmse_BpmnEvent';
	var $object_name = 'pmse_BpmnEvent';
	var $table_name = 'pmse_bpmn_event';
	var $importable = false;
        var $id;
		var $name;
		var $date_entered;
		var $date_modified;
		var $modified_user_id;
		var $modified_by_name;
		var $created_by;
		var $created_by_name;
		var $description;
		var $deleted;
		var $created_by_link;
		var $modified_user_link;
		var $activities;
		var $assigned_user_id;
		var $assigned_user_name;
		var $assigned_user_link;
    var $evn_uid;
    var $prj_id;
    var $pro_id;
    var $evn_type;
    var $evn_marker;
    var $evn_is_interrupting;
    var $evn_attached_to;
    var $evn_cancel_activity;
    var $evn_activity_ref;
    var $evn_wait_for_completion;
    var $evn_error_name;
    var $evn_error_code;
    var $evn_escalation_name;
    var $evn_escalation_code;
    var $evn_condition;
    var $evn_message;
    var $evn_operation_name;
    var $evn_operation_implementation;
    var $evn_time_date;
    var $evn_time_cycle;
    var $evn_time_duration;
    var $evn_behavior;

	/**
	 * This is a depreciated method, please start using __construct() as this method will be removed in a future version
     *
     * @see __construct
     * @depreciated
	 */
	function pmse_BpmnEvent_sugar(){
		self::__construct();
	}

	public function __construct(){
		parent::__construct();
	}

	public function bean_implements($interface){
		switch($interface){
			case 'ACL': return true;
		}
		return false;
}

}
?>