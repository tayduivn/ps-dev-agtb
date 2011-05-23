<?php
require_once('include/MVC/View/views/view.detail.php');

class ViewLivetextview extends ViewDetail{
	var $type = 'detail';

 	function ViewLivetextview() {
 		parent::SugarView();

 		$this->options['show_subpanels'] = false;
 		$this->options['show_title'] = false;
		$this->options['show_header'] = false;
		$this->options['show_footer'] = false; 
		$this->options['show_javascript'] = false; 
 	}
 	
 	function lookup($fields){

		echo '<style type="text/css">BODY { font-family: Arial, sans-serif; font-size: 12px; } TD { padding-top: 3px; padding-bottom: 3px; vertical-align: top; font-size: 12px; } .detail tr td[scope="row"] { font-size: 12px; color: #999999; }</style>';
 		
		if(isset($this->bean->custom_fields)) {
	        $custom_join = $this->bean->custom_fields->getJOIN();
		}
        else {
			$custom_join = false;
		}

 		if($custom_join)
        {
            $query = "SELECT {$this->bean->table_name}.*". $custom_join['select']. " FROM {$this->bean->table_name} " . $custom_join['join'];
        }
        else
        {
            $query = "SELECT {$this->bean->table_name}.* FROM {$this->bean->table_name} ";
        }

        $query .= " WHERE {$this->bean->table_name}.deleted=0 AND (";

        $first = TRUE;
        foreach($fields as $f=>$v){
        	$v = $this->bean->db->quote($v);
        	if(!$first)$query .= " OR ";

			if ($f == 'id') {
	        	$query .= " {$this->bean->table_name}.$f = '{$v}' ";
			}
			else {
	        	$query .= " {$this->bean->table_name}.$f LIKE '{$v}%' ";
			}

        	$first = false;
        }
        $query .= ')';

        $result = $this->bean->db->query($query);

		if (empty($result) || $this->bean->db->getRowCount($result) == 0) {
			echo "Sorry, no matches were found.";
			die();
		}

		$results = array();
		while ($row = $this->bean->db->fetchByAssoc($result, -1, FALSE)) {
			$results[] = $row;
		}

        if($this->bean->db->getRowCount($result) > 1)
        {
			echo "Multiple possible matches found.  Please choose the correct record.";
			echo "<br>";
			echo "<ul>";

			foreach ($results as $result) {
				echo "<li> <a href=\"index.php?module={$_REQUEST['module']}&action={$_REQUEST['action']}&match={$result['id']}&id_match=1\">";

				if(!empty($this->bean->field_defs['last_name'])) {
					echo $result['first_name'] . ' ' . $result['last_name'];
				}
				else {
					echo $result['name'];
				}

				echo "</a>\n";
 		 	}

			echo "</ul>";

			die();


        }

		// if we're here, that means we only found one result
		$row = $results[0];

        $this->bean->fetched_row = $row;
        $this->bean->fromArray($row);
        $this->bean->fill_in_additional_detail_fields();
		if(!empty($this->bean->last_name)){
			$this->bean->name = $this->bean->first_name . ' ' . $this->bean->last_name;	
			$this->bean->full_name = $this->bean->name;
		}
 		
 	}
 	
	function display(){
		if(empty($this->bean->id) &&  !empty($_REQUEST['match'])) {
			if (!empty($_REQUEST['id_match'])) {
				$id = str_replace(' ','+', $_REQUEST['match']);

				$this->lookup(array('id' => $id));
			}
			elseif(!empty($this->bean->field_defs['last_name'])){

				// if only one word was sent, search for a match against first_name OR last_name
				if (strpos($_REQUEST['match'], ' ') === FALSE) {
					$this->lookup(array('first_name'=>$_REQUEST['match'], 'last_name'=>$_REQUEST['match']));
				}
				else {
					// otherwise, grab the last word that was sent and search against last_name with it
					// all other words at the beginning of the string will be used to search against first_name

					$words = explode(' ', $_REQUEST['match']);

					$last_name_str = array_pop($words);
					$first_name_str = implode(' ', $words);

					$this->lookup(array('first_name' => $first_name_str, 'last_name' => $last_name_str));
				}

 			}
			else {
					$this->lookup(array('name'=>$_REQUEST['match']));
 			}
 		}

 		$this->dv->showVCRControl = false;
 		$this->dv->th->ss->assign('hideHeader', true);

 		if(empty($this->bean->id)){
			global $app_strings;
			sugar_die($app_strings['ERROR_NO_RECORD']);
		}			

		if (!file_exists("custom/modules/{$this->bean->module_dir}/metadata/livetextviewdefs.php")) {
			echo "Module not supported.";
			die();
		}

		require_once("custom/modules/{$this->bean->module_dir}/metadata/livetextviewdefs.php");
		$this->dv->defs = $viewdefs[$this->bean->module_dir]['Livetextview'];

		$this->dv->process();

		$html .= $this->dv->display(false, false);

		$html = preg_replace('#<table cellpadding="1" cellspacing="0" border="0" width="100%" class="actionsContainer">(.*)</table>#isU', '', $html);

		$html = preg_replace('#<script(.*)</script>#isU', '</form>', $html);

		echo $html;
 	}
}
