<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/

require_once('include/EditView/EditView2.php');
class ViewEdit extends SugarView
{
 	var $ev;
 	var $type ='edit';
 	var $useForSubpanel = false;  //boolean variable to determine whether view can be used for subpanel creates
 	var $useModuleQuickCreateTemplate = false; //boolean variable to determine whether or not SubpanelQuickCreate has a separate display function
 	var $showTitle = true;

    /**
     * Constructor
     *
     * @see SugarView::SugarView()
     */
    public function __construct($bean = null, $view_object_map = array()) {
        parent::__construct($bean, $view_object_map);
    }

    /**
     * Constructor
     * @deprecated Use the PHP 5.x style __construct instead
     * @see SugarView::SugarView()
     */
    public function ViewEdit($bean = null, $view_object_map = array()) {
        parent::SugarView($bean, $view_object_map);
    }

    /**
     * @see SugarView::preDisplay()
     */
    public function preDisplay()
    {
        $metadataFile = $this->getMetaDataFile();
        $this->ev = $this->getEditView();
        $this->ev->ss = $this->ss;
        $this->ev->setup($this->module, $this->bean, $metadataFile, SugarAutoLoader::existingCustomOne('include/EditView/EditView.tpl'));
    }

 	function display(){
		$this->ev->process();
		echo $this->ev->display($this->showTitle);
 	}

    /**
     * Get EditView object
     * @return EditView
     */
    protected function getEditView()
    {
        return new EditView();
    }
}

