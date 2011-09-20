<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
require_once('include/MVC/View/views/view.modulelistmenu.php');

class CampaignsViewModulelistmenu extends ViewModulelistmenu
{
 	public function display()
 	{
 	    $tracker = new Tracker();
        $history = $tracker->get_recently_viewed($GLOBALS['current_user']->id, array('Campaigns','ProspectLists','Prospects'));
        foreach ( $history as $key => $row ) {
            $history[$key]['item_summary_short'] = getTrackerSubstring($row['item_summary']);
            $history[$key]['image'] = SugarThemeRegistry::current()
                ->getImage($row['module_name'],'border="0" align="absmiddle"', null,null,'.gif',$row['item_summary']);

        }
        $this->ss->assign('LAST_VIEWED',$history);
 	    
 		$this->ss->display('include/MVC/View/tpls/modulelistmenu.tpl');
 	}
}
?>
