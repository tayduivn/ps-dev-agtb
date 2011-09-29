<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');


require_once('include/Dashlets/Dashlet.php');


class CalendarDashlet extends Dashlet {
    var $view = 'week';

    function CalendarDashlet($id, $def) {
        $this->loadLanguage('CalendarDashlet','modules/Calendar/Dashlets/');

		parent::Dashlet($id); 
         
		$this->isConfigurable = true; 
		$this->hasScript = true;  
                
		if(empty($def['title'])) 
			$this->title = $this->dashletStrings['LBL_TITLE'];
		else 
			$this->title = $def['title'];  
			
		if(!empty($def['view']))
			$this->view = $def['view'];			
             
    }

    function display(){
		ob_start();
		
		if(isset($GLOBALS['cal_strings']))
			return parent::display() . "Only one Calendar dashlet is allowed.";
		include("modules/Calendar/initialization.php");
		if(!ACLController::checkAccess('Calendar', 'list', true))
			ACLController::displayNoAccess(true);
						
		$args['view'] = $this->view;
		$args['cal'] = new Calendar($args['view']);
		$args['cal']->dashlet = true;
		$args['cal']->add_activities($GLOBALS['current_user']);
		$args['cal']->load_activities();
		$args['dashlet_id'] = $this->id;
		
		$ed = new CalendarDisplay($args);
		$ed->display_calendar_header(false);		
		$ed->display();
			
		$str = ob_get_contents();	
		ob_end_clean();
		
		return parent::display() . $str;
    }
    

    function displayOptions() {
        global $app_strings,$mod_strings;        
        $ss = new Sugar_Smarty();
        $ss->assign('MOD', $this->dashletStrings);        
        $ss->assign('title', $this->title);
        $ss->assign('view', $this->view);
        $ss->assign('id', $this->id);

        return parent::displayOptions() . $ss->fetch('modules/Calendar/Dashlets/CalendarDashlet/CalendarDashletOptions.tpl');
    }  

    function saveOptions($req) {
        global $sugar_config, $timedate, $current_user, $theme;
        $options = array();
        $options['title'] = $_REQUEST['title']; 
        $options['view'] = $_REQUEST['view'];       
         
        return $options;
    }

    function displayScript(){
	return "";
    }


}

?>
