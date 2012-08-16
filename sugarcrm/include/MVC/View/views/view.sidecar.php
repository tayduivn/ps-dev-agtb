<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/

require_once('include/MVC/View/SidecarView.php');

class ViewSidecar extends SidecarView
{
    /**
     * Constructor
     *
     * @see SidecarView::SidecarView()
     */
 	public function __construct($bean = null, $view_object_map = array())
 	{
        $this->options['show_title'] = false;
        $this->options['show_header'] = false;
        $this->options['show_footer'] = false;
        $this->options['show_javascript'] = false;
        $this->options['show_subpanels'] = false;
        $this->options['show_search'] = false;
 		parent::__construct($bean = null, $view_object_map = array());
 	}

}
