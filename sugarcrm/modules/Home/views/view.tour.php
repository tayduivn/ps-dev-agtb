<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
require_once('include/MVC/View/SugarView.php');

class HomeViewTour extends SugarView
{
    public function display()
    {
        global $sugar_flavor;
        global $current_user;
        $mod_strings = return_module_language($GLOBALS['current_language'], 'Home');
       $this->ss->assign('mod', $mod_strings);
        $this->ss->assign("sugarFlavor",$sugar_flavor);

       //check the upgrade history to see if this instance has been upgraded, if so then present the calendar url message
       //if no upgrade history exists then we can assume this is an install and we do not show the calendar message
       $uh = new UpgradeHistory();
       $upgrade = count($uh->getAll())>0 ? true : false;
       if($upgrade)
       {
            //create the url with the user id and scrolltocal flag.  This will be passed into language string
            $urlForString = $mod_strings['LBL_TOUR_CALENDAR_URL_1'];
            $urlForString .= '<br><a href="index.php?module=Users&action=EditView&record='.$current_user->id.'&scrollToCal=true" target="_blank">';
            $urlForString .= $mod_strings['LBL_TOUR_CALENDAR_URL_2'];
            $this->ss->assign('view_calendar_url', $urlForString );
       }
       $this->ss->display('modules/Home/tour.tpl');

    }

}
?>
