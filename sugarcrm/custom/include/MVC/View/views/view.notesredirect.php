<?php
// jostrow

require_once('include/MVC/View/views/view.detail.php');

class ViewNotesredirect extends ViewDetail{
	var $type = 'detail';

	private $supported_modules = array(
		'Opportunities',
		'Accounts',
		'Contacts',
	);

 	function ViewNotesredirect() {
 		parent::SugarView();

 		$this->options['show_subpanels'] = false;
 		$this->options['show_title'] = false;
		$this->options['show_header'] = false;
		$this->options['show_footer'] = false; 
		$this->options['show_javascript'] = false; 
 	}
 	
 	function lookup($module, $match_str) {

		// for now, just sending them to a DetailView or ListView... no logic behind performing the search yet

		if ($module == 'Opportunities') {

			// START jvink - search for oppty
			if($match_str) {
				$sql = "SELECT opp.id
						FROM opportunities opp
						WHERE opp.name LIKE '%" . $GLOBALS['db']->quote($match_str) . "%'
						AND opp.deleted = 0";
				$q_opp = $GLOBALS['db']->query($sql);
				if($GLOBALS['db']->getRowCount($q_opp) == 1) {
					$opp = $GLOBALS['db']->fetchByAssoc($q_opp);
					SugarApplication::redirect("index.php?module={$module}&action=DetailView&record={$opp['id']}");
				} 	
			}
		
			SugarApplication::redirect("index.php?module={$module}&action=index&name_basic={$match_str}&current_user_only_basic=&favorites_only_basic=&account_name_basic=&account_client_id_basic=&tags_search=&searchFormTab=basic_search&query=true");
			// END jvink

		}
		elseif ($module == 'Accounts') {

			// hardcoding this value for now, so the Notes guys can test
			if ($match_str == 'found') {
				SugarApplication::redirect("index.php?module={$module}&action=DetailView&record=12KX-6WW-1479");
			}

			SugarApplication::redirect("index.php?module={$module}&action=index&name_basic={$match_str}&query=true");

		}
		elseif ($module == 'Contacts') {

			// hardcoding this value for now, so the Notes guys can test
			if ($match_str == 'found') {
				SugarApplication::redirect("index.php?module={$module}&action=DetailView&record=8f5203fa-7584-435e-f9be-4d686af532f1");
			}

			SugarApplication::redirect("index.php?module={$module}&action=index&search_name_basic={$match_str}&query=true");

		}


 	}
 	
	function display() {

		if (!isset($_REQUEST['match']) || $_REQUEST['match'] == '') {
			echo "ERROR: No match criteria specified";
			die();
		}

		if (!in_array($this->bean->module_dir, $this->supported_modules)) {
			echo "ERROR: Module not supported";
			die();
		}

		$this->lookup($this->bean->module_dir, $_REQUEST['match']);

 	}
}
