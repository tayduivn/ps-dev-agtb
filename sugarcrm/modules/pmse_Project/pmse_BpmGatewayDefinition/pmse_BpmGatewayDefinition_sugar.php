<?php
/**
 * THIS CLASS IS GENERATED BY MODULE BUILDER
 * PLEASE DO NOT CHANGE THIS CLASS
 * PLACE ANY CUSTOMIZATIONS IN pmse_BpmGatewayDefinition
 */
class pmse_BpmGatewayDefinition_sugar extends Basic {
	var $new_schema = true;
	var $module_dir = 'pmse_Project/pmse_BpmGatewayDefinition';
	var $object_name = 'pmse_BpmGatewayDefinition';
	var $table_name = 'pmse_bpm_gateway_definition';
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
    var $prj_id;
    var $pro_id;
    var $execution_mode;

	/**
	 * This is a depreciated method, please start using __construct() as this method will be removed in a future version
     *
     * @see __construct
     * @depreciated
	 */
	function pmse_BpmGatewayDefinition_sugar(){
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