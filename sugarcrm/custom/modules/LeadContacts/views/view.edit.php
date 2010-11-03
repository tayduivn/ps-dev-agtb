<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/views/view.edit.php');
require_once('modules/Prospects/Prospect.php');

class LeadContactsViewEdit extends ViewEdit 
{
 	/**
     * Constructor
     */
 	public function __construct()
    {
 		parent::ViewEdit();
 	}
 	
 	/** 
     * @see SugarView::process()
     */
 	public function process()
    {
        if (isset($_REQUEST['prospect_id']) && !empty($_REQUEST['prospect_id'])) 
        {
            $prospect = new Prospect;
            $prospect->retrieve($_REQUEST['prospect_id']);
            
            foreach ( $prospect->field_defs as $key => $value ) {
                //exceptions.
                if ($key == 'id' or $key=='deleted' )
                    continue;
                if (isset($this->bean->field_defs[$key]))
                    $this->bean->$key = $prospect->$key;
            }
            
            //additional assignments
            $this->bean->team_name = get_assigned_team_name($prospect->team_id);
            $this->bean->assigned_user_name = get_assigned_user_name($prospect->assigned_user_id);
            $this->bean->leadaccount_id = $prospect->id;
            $this->bean->leadaccount_name = $prospect->account_name;
        }
        parent::process();
    }
	
	// BEGIN SUGARINTERNAL CUSTOMIZATION - REMOVE ACCESS TO lead_pass_c IF YOU AREN'T IN LEADS ADMIN ROLE
	public function display(){
		if($GLOBALS['current_user']->check_role_membership('Sales - Only Convert Leads')){
			echo "Error 4712: You do not have access to edit Lead records. If you feel this is in error, please file an IT Request, assigned to internalsystems, with the error number";
			return;
		}
		
		$this->ev->th->clearCache($this->module, 'EditView.tpl');

		// Load up all the references to the panels based on the labels
		$e = $this->ev->defs['panels'];
		$panelArray = array();
		if(isset($this->bean->lead_pass_c) && $this->bean->lead_pass_c == 1 && !$GLOBALS['current_user']->check_role_membership('Leads Admin Role')){
			foreach ($e as $panel_label => $panel_data) {
				foreach($panel_data as $row_index => $row_array){
					foreach($row_array as $col_index => $field_array){
						if(isset($field_array['name']) && $field_array['name'] == 'lead_pass_c'){
							$this->ev->defs['panels'][$panel_label][$row_index][$col_index]['displayParams']['field']['disabled'] = "true";
						}
					}
				}
			}
		}
		
		//echo "<PRE>"; print_r($this->ev->defs); die();
		
		parent::display();
	}
	// END SUGARINTERNAL CUSTOMIZATION - REMOVE ACCESS TO lead_pass_c IF YOU AREN'T IN LEADS ADMIN ROLE
}
?>
