<?php

require_once('include/MVC/View/views/view.detail.php');
require_once('modules/EAPM/EAPM.php');

class MeetingsViewDetail extends ViewDetail {

   function MeetingsViewDetail() {
      parent::ViewDetail();
   }

   function display() {
      if ($this->bean->type != 'SugarCRM') {
         $login_info = EAPM::getLoginInfo(strtolower($this->bean->type));
         if ($login_info['name'] == $this->bean->creator) {
            $this->bean->displayed_url = $this->bean->host_url;
         } else {
            $this->bean->displayed_url = $this->bean->join_url;
         }
      }
      parent::display();
   }
}
