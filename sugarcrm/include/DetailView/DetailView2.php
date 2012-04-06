<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
require_once('include/TemplateHandler/TemplateHandler.php');
require_once('include/EditView/EditView2.php');

/**
 * DetailView - display single record
 * New implementation
 * @api
 */
class DetailView2 extends EditView
{
    var $view = 'DetailView';

    /**
     * DetailView constructor
     * This is the DetailView constructor responsible for processing the new
     * Meta-Data framework
     *
     * @param $module String value of module this detail view is for
     * @param $focus An empty sugarbean object of module
     * @param $id The record id to retrieve and populate data for
     * @param $metadataFile String value of file location to use in overriding default metadata file
     * @param tpl String value of file location to use in overriding default Smarty template
     */
    function setup(
        $module,
        $focus,
        $metadataFile = null,
        $tpl = 'include/DetailView/DetailView.tpl'
        )
    {
        $this->th = new TemplateHandler();
        $this->th->ss = $this->ss;
        $this->focus = $focus;
        $this->tpl = $tpl;
        $this->module = $module;
        $this->metadataFile = $metadataFile;
        if(isset($GLOBALS['sugar_config']['disable_vcr'])) {
           $this->showVCRControl = !$GLOBALS['sugar_config']['disable_vcr'];
        }
        if(!empty($this->metadataFile) && file_exists($this->metadataFile)){
        	require_once($this->metadataFile);
        }

        $this->defs = $viewdefs[$this->module][$this->view];
    }

	//BEGIN SUGARCRM flav=inlineEdit ONLY
	function display(
	    $showTitle = true,
	    $ajaxSave = false
	    )
	{
	 	require_once('include/EditView/InlineEdit.php');

	 	$str = parent::display($showTitle, $ajaxSave);
		$ie = new InlineEdit();
		$str .=  $ie->getEditInPlaceJS($this->defs['panels'], $this->focus);

		return $str;
	}
	//END SUGARCRM flav=inlineEdit ONLY
}
?>