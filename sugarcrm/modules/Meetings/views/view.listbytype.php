<?php

require_once('include/MVC/View/views/view.list.php');
require_once('modules/EAPM/EAPM.php');
class MeetingsViewListbytype extends ViewList {
    var $options = array('show_header' => false, 'show_title' => false, 'show_subpanels' => false, 'show_search' => true, 'show_footer' => false, 'show_javascript' => false, 'view_print' => false,);

   function MeetingsViewListbytype() {
  
      parent::ViewList();
   }

   function listViewProcess(){
   	 	
   		$this->lv->show_action_dropdown = false;
   		$this->lv->multiSelect = false;
   		$type = 'lotus';
   		$this->where = " meetings.type = '$type' ";

		unset($this->searchForm->searchdefs['layout']['advanced_search']);
	
		parent::listViewProcess();
		echo "</div>";
 	}

}
