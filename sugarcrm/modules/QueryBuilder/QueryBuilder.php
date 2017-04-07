<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */


// ProductTemplate is used to store customer information.
class QueryBuilder extends SugarBean {
    // Stored fields
    var $id;
    var $deleted;
    var $date_entered;
    var $date_modified;
    var $modified_user_id;
    var $created_by;
    var $created_by_name;
    var $modified_by_name;

    var $name;
    var $description;
    var $query_type;
    var $query_locked;
    var $base_module;


    var $column_options=null;


    var $table_name = "query_builder";
    var $module_dir = "QueryBuilder";
    var $object_name = "QueryBuilder";
    var $disable_custom_fields = true;

    var $rel_column_table = 	"query_columns";
    var $rel_filter_table = 	"query_filters";
    var $rel_groupby_table = 	"query_groupbys";
    var $rel_orderby_table = 	"query_orderbys";
    var $rel_calc_table = 		"query_calcs";

    var $new_schema = true;

    // This is used to retrieve related fields from form posts.
    var $additional_column_fields = Array();

    public function __construct() {
        parent::__construct();

        $this->disable_row_level_security =true;

    }



    function get_summary_text()
    {
        return "$this->name";
    }

    public function create_export_query($order_by, $where)
    {
    }

    public function save_relationship_changes($is_update, $exclude = array())
    {
    }


    function mark_relationships_deleted($id)
    {
    }

    function fill_in_additional_list_fields()
    {

    }

    function fill_in_additional_detail_fields()
    {

        //Some sort of call to the various component tables


        //$this->get_custom_query();
        //$this->get_parent_dataset();
        //$this->get_report_name();
        //$this->get_child_dataset();
    }


    function get_custom_query(){

        $query = "SELECT cq.name from $this->rel_custom_queries cq, $this->table_name p1 where cq.id = p1.query_id and p1.id = '$this->id' and p1.deleted=0 and cq.deleted=0";
        $result = $this->db->query($query,true," Error filling in additional custom query detail fields: ");

        // Get the id and the name.
        $row = $this->db->fetchByAssoc($result);

        if($row != null)
        {
            $this->query_name = $row['name'];
        }
        else
        {
            $this->query_name = '';
        }
    }

    function get_parent_dataset(){
        $query = "SELECT $this->table_name.name from $this->table_name where $this->table_name.id = '$this->parent_id' AND $this->table_name.deleted=0 ";
        $result = $this->db->query($query,true," Error filling in additional parent detail fields: ");

        // Get the id and the name.
        $row = $this->db->fetchByAssoc($result);

        if($row != null)
        {
            $this->parent_name = $row['name'];
        }
        else
        {
            $this->parent_name = '';
        }
    }

    function get_child_dataset(){
        $query = "SELECT $this->table_name.name from $this->table_name where $this->table_name.parent_id = '$this->id' AND $this->table_name.deleted=0 ";
        $result = $this->db->query($query,true," Error filling in additional child detail fields: ");

        // Get the id and the name.
        $row = $this->db->fetchByAssoc($result);

        if($row != null)
        {
            $this->child_name = $row['name'];
        }
        else
        {
            $this->child_name = 'None';
        }
    }


    function get_report_name(){
        $query = "SELECT $this->report_table.name from $this->table_name
                    LEFT JOIN $this->report_table ON $this->report_table.id = '$this->report_id'
                    WHERE $this->table_name.deleted=0 AND $this->report_table.deleted=0";
        $result = $this->db->query($query,true," Error filling in report name information: ");

        // Get the id and the name.
        $row = $this->db->fetchByAssoc($result);

        if($row != null)
        {
            $this->report_name = $row['name'];
        }
        else
        {
            $this->report_name = '';
        }
    }


    function get_list_view_data(){
        global $app_strings, $mod_strings;
        global $app_list_strings;

        global $current_user;

        if(empty($this->query_locked)) $this->visible="off";

        $temp_array = parent::get_list_view_data();
        $temp_array['NAME'] = (($this->name == "") ? "<em>blank</em>" : $this->name);
        $temp_array['QUERY_TYPE'] = $app_list_strings['query_type_dom'][$this->query_type];
        $temp_array['BASE_MODULE'] = $this->base_module;
        $temp_array['QUERY_LOCKED'] = $this->query_locked;

        return $temp_array;
    }
    /**
        builds a generic search based on the query string using or
        do not include any $this-> because this is called on without having the class instantiated
    */
    function build_generic_where_clause ($the_query_string) {
    $where_clauses = Array();
    $the_query_string = $GLOBALS['db']->quote($the_query_string);
    array_push($where_clauses, "name like '$the_query_string%'");

    $the_where = "";
    foreach($where_clauses as $clause)
    {
        if($the_where != "") $the_where .= " or ";
        $the_where .= $clause;
    }


    return $the_where;
}



///////////////////////////////////////////////////Aquiring Query Components Area////////////

    function get_relationship_modules($column_module=""){

        //convert this to something dynamic based on the new relationship structure - jgreen

        $column_select_array = array(

    'Contacts' => 'Contacts',
    'Accounts' => 'Accounts',
    'Opportunities' => 'Opportunities',

    );

        return $column_select_array;

    //end function get_relationship_modules
    }


    function get_column_data($orderBy="")
    {
        // First, get the list of IDs.
        $query = 	"SELECT $this->rel_dataset.id from $this->rel_dataset
                    where $this->rel_dataset.report_id='$this->id'
                    AND $this->rel_dataset.deleted=0 ".$orderBy;

        return $this->build_related_list($query, BeanFactory::newBean('DataSets'));
    }


    function get_column_select($drop_down_module="")
    {
        $this->column_options = array();
        if(!empty($drop_down_module)){
            $column_module = $drop_down_module;
        } else {
            $column_module = $this->base_module;
        }
        //Get dictionary data for base bean and all connected beans

        //Get dictionary and focus data for base bean
        $temp_focus = BeanFactory::newBean($column_module);
        if(!SugarAutoLoader::fileExists('modules/'. $column_module . '/vardefs.php') || empty($temp_focus)){
            return;
        }

        $this->add_to_column_select($temp_focus, $column_module);
        return $this->column_options;
    }


    function add_to_column_select($temp_focus, $module_name){
        global $dictionary;
        global $current_language;
        global $app_strings;

        $temp_module_strings = return_module_language($current_language, $temp_focus->module_dir);

        $base_array = $dictionary[$temp_focus->object_name]['fields'];




    foreach($base_array as $key => $value){

        $label_name = $value['vname'];
        if(!empty($temp_module_strings[$label_name])){
            $label_name = $temp_module_strings[$label_name];
        } else {
            if(!empty($app_strings[$label_name])){
            $label_name = $app_strings[$label_name];
            }
        }
        if(!empty($value['table'])){
            //Custom Field
            $column_table = $value['table'];
        } else {
            //Non-Custom Field
            $column_table = $temp_focus->table_name;
        }

        $index = $key;
        $value = "(".$value['name'].")".$label_name;

        $this->column_options[$index] = $value;

    //end foreach
    }


    //end function add_to_column_select
    }


    /**
     * Get bean by module name
     * @deprecated use BeanFactory::getBean
     * @param string $module_name
     * @return SugarBean|null
     */
    function get_module_info($module_name)
    {
        return BeanFactory::newBean($module_name);
    }

    function get_field_table($module, $field){

        $seed_object = BeanFactory::newBean($module);
        $field_table = $this->determine_field_type($seed_object, $field);

        return $field_table;

    //end function get_module_table
    }


    function determine_field_type($seed_object, $field){

    global $dictionary;
        if(!empty($dictionary[$seed_object->object_name]['fields'][$field]['custom_type'])){
        //field is present in the module's custom table.  Retrieve this table and use as query
            $field_select = $seed_object->table_name."_cstm.".$field;

        } else {
            //field is not custom and present in module table
            $field_select = $seed_object->table_name.".".$field;
        }

            return $field_select;
    //end function determine_field_type
    }


////////LABEL DISPLAY FUNCTION



    //only call this after the bean has been made and the vardef file exists
    function display_label($focus, $field){

        global $dictionary, $current_language;

        if(!SugarAutoLoader::fileExists('modules/'. $focus->module_dir . '/'.$focus->object_name.'.php')){
            return $field;
        }

        $var_name = $dictionary[$focus->object_name]['fields'][$field]['vname'];
        $current_module_strings = return_module_language($current_language, $focus->object_name);

        if(!empty($current_module_strings[$var_name])){

            return $current_module_strings[$var_name];

        } else {
            return $field;
        }


    //end function display_label
    }



    /////////////////RUN QUERY FUNCTIONS//////////



    function run_query(){

        //Create the glue object
        $query_glue = new QueryGlue($this);

        $query_glue->build_select();

        return $query_glue->glue_select(true);

    //end function run_query
    }

//end class
}
