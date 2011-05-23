<?php
require_once('include/MVC/View/SugarView.php');

// START jvink - customization
// Use opportunity description instead of name in "last viewed"

// The row does not contain the full bean object, only item_summary from tracking table. We can override
// get_summary_text as suggested by Jostrow on Opportunity bean which is non-upgrade safe. This implementation
// is upgrade safe but requires an additional query to retrieve the description fields.

// also note --> the description field is not required, so it default to "no description" if null

class ViewModulelistmenu extends SugarView
{
 	public function __construct()
 	{
 		$this->options['show_title'] = false;
		$this->options['show_header'] = false;
		$this->options['show_footer'] = false; 	  
		$this->options['show_javascript'] = false; 
		$this->options['show_subpanels'] = false; 
		$this->options['show_search'] = false; 
 		parent::SugarView();
 	}	
 	
 	public function display()
 	{
 	    $tracker = new Tracker();
        $history = $tracker->get_recently_viewed($GLOBALS['current_user']->id,$this->module);

		// build query to retrieve description field
		$id_list = '';
		foreach( $history as $key => $row ) {
			$id_list .= '"'.$key.'",';
		}
		if($id_list) {
			$sql = 'SELECT id, IFNULL(description,"(no description)") AS description 
						FROM opportunities WHERE id IN ('.rtrim($id_list,',').') AND deleted = 0';
			$q_descr = $GLOBALS['db']->query($sql);
			while($descr = $GLOBALS['db']->fetchByAssoc($q_descr)) {
				$history_descr[$descr['id']] = $descr;
			}
		}
		
		// normal behaviour
        foreach ( $history as $key => $row ) {

			// pass in description
			if(array_key_exists($key, $history_descr)) {
				$descr = $history_descr[$key]['description'];
			} else {
				$descr = $row['item_summary'];
			}

            $history[$key]['item_summary_short'] = getTrackerSubstring($descr);
            $history[$key]['image'] = SugarThemeRegistry::current()
                ->getImage($row['module_name'],'border="0" align="absmiddle" alt="'.$row['item_summary'].'"');
        }

        $this->ss->assign('LAST_VIEWED',$history);
 	    
 		$this->ss->display('include/MVC/View/tpls/modulelistmenu.tpl');
 	}
}

// END jvink

?>
