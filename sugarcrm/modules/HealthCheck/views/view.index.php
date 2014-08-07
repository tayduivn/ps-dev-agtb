<?php

/**
 *
 * Index main view
 *
 */
class ViewIndex extends SugarView
{
    /**
     *
     * Constructor
     *
     * @see SugarView::SugarView()
     */
    public function __construct($bean = null, $view_object_map = array())
    {
        $this->suppressDisplayErrors = true;

        $this->options['show_title'] = false;
        $this->options['show_header'] = false;
        $this->options['show_footer'] = false;
        $this->options['show_javascript'] = false;
        $this->options['show_subpanels'] = false;
        $this->options['show_search'] = false;

        parent::SugarView($bean, $view_object_map);
    }

    /**
     *
     * @see SugarView::display()
     */
    public function display()
    {
        $this->ss->display('modules/HealthCheck/tpls/index.tpl');
    }
}
