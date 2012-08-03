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
        //BEGIN SUGARCRM flav=pro ONLY
         //Include fts engine name in etag so we don't cache searchbar.
        $etag .= SugarSearchEngineFactory::getFTSEngineNameFromConfig();
        $etag = md5($etag);
        //END SUGARCRM flav=pro ONLY
 		generateEtagHeader($etag);
        //Prevent double footers
        $GLOBALS['app']->headerDisplayed = false;
 	}
}
