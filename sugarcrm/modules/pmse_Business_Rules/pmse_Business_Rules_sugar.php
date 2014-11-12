<?php

class pmse_Business_Rules_sugar extends Basic
{
    var $new_schema = true;
    var $module_name = 'pmse_Business_Rules';
    var $module_dir = 'pmse_Business_Rules';
    var $object_name = 'pmse_Business_Rules';
    var $table_name = 'pmse_business_rules';
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
    var $rst_uid;
    var $rst_type;
    var $rst_definition;
    var $rst_editable;
    var $rst_source;
    var $rst_source_definition;
    var $rst_module;
    var $rst_filename;
    var $rst_create_date;
    var $rst_update_date;
    var $disable_row_level_security = true;

    public function __construct()
    {
        parent::__construct();
    }

    public function bean_implements($interface)
    {
        switch($interface){
            case 'ACL': return true;
        }
        return false;
    }
}
