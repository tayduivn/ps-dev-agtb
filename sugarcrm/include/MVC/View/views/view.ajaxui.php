<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
require_once('include/MVC/View/SugarView.php');

class ViewAjaxUI extends SugarView
{
    /**
     * Constructor
     *
     * @see SugarView::SugarView()
     */
 	public function __construct()
 	{
 		$this->options['show_title'] = true;
		$this->options['show_header'] = true;
		$this->options['show_footer'] = true;
		$this->options['show_javascript'] = true;
		$this->options['show_subpanels'] = false; 
		$this->options['show_search'] = false;
		
 		parent::SugarView();
 	}

    public function display()
 	{
 		$user = $GLOBALS["current_user"];
 		$etag = $user->id . $user->getETagSeed("mainMenuETag");
 		header("cache-control:");
 		header('Expires: ');
 		header("ETag: " . $etag);
 		header("Pragma:");
 		if(isset($_SERVER["HTTP_IF_NONE_MATCH"])){
 			if($etag == $_SERVER["HTTP_IF_NONE_MATCH"]){
 				ob_clean();
 				header("Status: 304 Not Modified");
 				header("HTTP/1.0 304 Not Modified");
 				die();
 			}
 		}
        //Prevent double footers
        $GLOBALS['app']->headerDisplayed = false;
 	}
}
