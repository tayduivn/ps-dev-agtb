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
 		//hack to get around caching bug. We need to remove this and re-enable the cache once we
 		//can be sure we clear it on a per user basis.
 		header("cache-control:nocache");
 		/*header("cache-control: max-age=86400");
 		header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));
 		header("Pragma:");*/
        //Prevent double footers
        $GLOBALS['app']->headerDisplayed = false;
 	}
}
