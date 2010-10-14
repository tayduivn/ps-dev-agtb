<?php

require_once('include/MVC/View/views/view.list.php');
require_once('modules/EAPM/EAPM.php');
class MeetingsViewListbytype extends ViewList {
    var $options = array('show_header' => false, 'show_title' => false, 'show_subpanels' => false, 'show_search' => true, 'show_footer' => false, 'show_javascript' => false, 'view_print' => false,);

   function MeetingsViewListbytype() {
  	
      parent::ViewList();
        $this->params['orderBy'] = 'meetings.date_start DESC';
      $this->params['overrideOrder'] = true;
   }

   function listViewProcess(){
   	 	
   		$this->lv->show_action_dropdown = false;
   		$this->lv->multiSelect = false;
   		
   		

		unset($this->searchForm->searchdefs['layout']['advanced_search']);
	
		parent::listViewProcess();
		echo "</div>";
 	}
 	
 	function processSearchForm(){
 		$type = 'LotusLive';
 		$where =  " meetings.type = '$type' AND meetings.date_start > UTC_TIMESTAMP() - 7200 ";
 		parent::processSearchForm();
 		if(!empty($this->where)){
 			$this->where .= " AND $where ";
 		}else{
 			$this->where = $where;	
 		}
 		
 			
 	}

}
