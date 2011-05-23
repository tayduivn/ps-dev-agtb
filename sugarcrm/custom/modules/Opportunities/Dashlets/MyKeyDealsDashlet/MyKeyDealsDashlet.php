<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

// START jvink - My Key Deals Dashlet

require_once('custom/include/Dashlets/ibmBase/ibmBaseDashlet.php');

class MyKeyDealsDashlet extends ibmBaseDashlet {

	function MyKeyDealsDashlet($id, $def) {

    	$this->loadLanguage('MyKeyDealsDashlet','custom/modules/Opportunities/Dashlets/');
        $this->isConfigurable = false; // dashlet is configurable
        $this->hasScript = false;  // dashlet has javascript attached to it
                
        // setup our vardefs
        $this->vardefs = array(
			'id' => array(), 
    		'number' => array(), 
    		'description' => array(),
        	'amount' => array(),
    		'acc_id' => array(),
    		'acc_name' => array(),
        );
        
        // listview settings
        $this->listviewdefs = array(
        	'number' => array(
        		'label' => 'LBL_COL_OPPTY',
        		'type' => 'link',
        		'link_id' => 'id',
        		'link_type' => 'DetailView',
        		'link_module' => 'Opportunities',
        	),
        	'description' => array(
        		'label' => 'LBL_COL_DESCRIPTION',
        		'type' => 'varchar',
        	),
        	'acc_name' => array(
        		'label' => 'LBL_COL_ACCOUNT',
        		'type' => 'link',
        		'link_id' => 'acc_id',
        		'link_type' => 'DetailView',
        		'link_module' => 'Accounts',
        	),
        	'amount' => array(
        		'label' => 'LBL_COL_AMOUNT',
        		'type' => 'currency',
        	),
        	'icon' => array(
        		'label' => '',
        		'type' => 'icon',
        		'icon' => 'themes/Sugar/images/edit_inline.png',
        		'link_id' => 'id',
        		'link_type' => 'EditView',
        		'link_module' => 'Opportunities',
        	),
        );
        
        return parent::ibmBaseDashlet($id, $def);
    }

    function display() {

    	// load dataset
    	$this->get_data();
	
    	// return listview
    	return $this->generate_list_view();
    }
    
    function get_data() {
    	
    	global $current_user;
		$timeperiod = IBMHelper::date_to_timeperiod(date('Y-m-d'));

		// display columns
    	$sql_select = "opp.id AS id,
	    				opp.name AS number, 
	    				opp.description AS description,
	    				opp.amount AS amount,
	    				acc.id AS acc_id,
	    				acc.name AS acc_name";
		
		
		// hierarchy helper
    	require_once('modules/Forecasts/Common.php');
    	$hh = new Common();
    	$hh->set_current_user($current_user->id);
    	$hh->setup();
		$hh->retrieve_downline($current_user->id);
		
		// add current user to downline
		$hh->my_downline[] = $current_user->id;
    	
		// setup query
		$union = array();
		foreach($hh->my_downline as $user_id) {
    		
	    	// determine roadmap column to join & set key deal field
	    	$user = new User();
	    	$user->retrieve($user_id);
	    	
	    	$rm_join_table = false;
	    	switch($user->employee_department) {
	    		case 'SND': 
	    			$rm_join_table = 'ibm_roadmapsd';
	    			$rm_key_deal_field = 'imt_brand_exec_key_deal';
	    			break; 
	    		case 'Services': 
	    			$rm_join_table = 'ibm_roadmapservices';
	    			$rm_key_deal_field = 'imt_brand_exec_key_deal'; 
	    			break;
	    		case 'SWG': 
	    			$rm_join_table = 'ibm_roadmapswg';
	    			$rm_key_deal_field = 'key_deal';
	    			break;
	    		case 'STG': 
	    			$rm_join_table = 'ibm_roadmapstg';
	    			$rm_key_deal_field = 'imt_brand_exec_key_deal'; 
	    			break;
	    	}
    	
	    	// special case for SND --> assigned user = SND 
	    	// filter in this case on opportunity team
	    	if($user->employee_department == 'SND') {
	    		$assigned_user_id = 'SND';
	    		$snd_additional_sql = "INNER JOIN opportunities_users opp_usr
	    			ON opp_usr.opportunity_id = opp.id
	    			AND opp_usr.user_id = '{$user->id}'
	    			AND opp_usr.deleted = 0";
	    	} else {
	    		$assigned_user_id = $user_id;
	    		$snd_additional_sql = '';
	    	}
	    	
	    	if($rm_join_table) {

	    		$union[] = "SELECT $sql_select
	    			FROM opportunities opp    				
					INNER JOIN accounts_opportunities acc_opp
						ON acc_opp.opportunity_id = opp.id
						AND acc_opp.deleted = 0
					INNER JOIN accounts acc
						ON acc.id = acc_opp.account_id
						AND acc.deleted = 0
					INNER JOIN {$rm_join_table} rm
						ON rm.opportunity_id_c = opp.id
						AND rm.deleted = 0
						AND rm.{$rm_key_deal_field} = 1
						AND rm.assigned_user_id = '{$assigned_user_id}'
					{$snd_additional_sql}
	    			WHERE opp.deleted = 0
	    				AND opp.date_closed >= '{$timeperiod['start_date']}'
	    				AND opp.date_closed <= '{$timeperiod['end_date']}'";
	    	}
		}

		// build union query
		$sql_union = '';
		foreach($union as $sql) {
			if($sql_union) {
				$sql_union .= ' UNION ';
			}
			$sql_union .= '('.$sql.')';
		}
		
		// run query
		$q_data = $GLOBALS['db']->query($sql_union);
    	$track_opp_id = array();
    	while($data = $GLOBALS['db']->fetchByAssoc($q_data)) {
    		// filter out duplicate oppties
    		if(! isset($track_opp_id[$data['id']])) {
    			$this->add_row($data);
    		}
    		$track_opp_id[$data['id']] = true;
    	}
    }
}

?>