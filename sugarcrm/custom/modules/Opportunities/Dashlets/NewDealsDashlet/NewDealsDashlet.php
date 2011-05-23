<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

// START jvink - My Key Deals Dashlet

require_once('custom/include/Dashlets/ibmBase/ibmBaseDashlet.php');

class NewDealsDashlet extends ibmBaseDashlet {

	function NewDealsDashlet($id, $def) {

    	$this->loadLanguage('NewDealsDashlet','custom/modules/Opportunities/Dashlets/');
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
    	
		/**
		 * It seems that every time an oppty is edited, the relationships are re-saved
		 * resulting in new entries (the old ones are marked as deleted). To determine
		 * the user assignement, we order our list ASC on date_modified per oppty.
		 * 
		 * After the query, we filter only pick the first oppty id occurence, and
		 * check against that date_modified date if the user is recently assigned.
		 * 
		 */
    	$sql = "SELECT 
    				opp.id AS id,
    				opp.name AS number, 
    				opp.description AS description,
    				opp.amount AS amount,
    				acc.id AS acc_id,
    				acc.name AS acc_name,
    				opp_usr.date_modified
				FROM opportunities_users opp_usr
				INNER JOIN opportunities opp
					ON opp_usr.opportunity_id = opp.id
					AND opp.deleted = 0
				INNER JOIN accounts_opportunities acc_opp
					ON acc_opp.opportunity_id = opp.id
					AND acc_opp.deleted = 0
				INNER JOIN accounts acc
					ON acc.id = acc_opp.account_id
					AND acc.deleted = 0
				WHERE opp_usr.user_id IN ('{$current_user->id}')
				ORDER BY opp.id ASC, opp_usr.date_modified ASC";
		
		$q_data = $GLOBALS['db']->query($sql);
    	$track_opp_id = array();

    	$date_threshold = time() - (7 * 24 * 60 * 60);   	
    	while($data = $GLOBALS['db']->fetchByAssoc($q_data)) {
    		// filter out duplicate oppties
    		if(! isset($track_opp_id[$data['id']])) {
    			// compare date modified against our threshold
    			$date_mod = strtotime($data['date_modified']);
  				if($date_mod >= $date_threshold) {
    				$this->add_row($data);
  				}
    		}
    		$track_opp_id[$data['id']] = true;
    	}
    }
}

?>