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
        }else {
        	//If file doesn't exist we create a best guess
        	if(!file_exists("modules/$this->module/metadata/detailviewdefs.php") &&
        	    file_exists("modules/$this->module/DetailView.html")) {
                global $dictionary;
        	    $htmlFile = "modules/" . $this->module . "/DetailView.html";
        	    $parser = new DetailViewMetaParser();
        	    if(!file_exists('modules/'.$this->module.'/metadata')) {
        	       sugar_mkdir('modules/'.$this->module.'/metadata');
        	    }
        	   	$fp = sugar_fopen('modules/'.$this->module.'/metadata/detailviewdefs.php', 'w');
        	    fwrite($fp, $parser->parse($htmlFile, $dictionary[$focus->object_name]['fields'], $this->module));
        	    fclose($fp);
        	}

        	//Flag an error... we couldn't create the best guess meta-data file
        	if(!file_exists("modules/$this->module/metadata/detailviewdefs.php")) {
        	   global $app_strings;
        	   $error = str_replace("[file]", "modules/$this->module/metadata/detailviewdefs.php", $app_strings['ERR_CANNOT_CREATE_METADATA_FILE']);
        	   $GLOBALS['log']->fatal($error);
        	   echo $error;
        	   die();
        	}
            require_once("modules/$this->module/metadata/detailviewdefs.php");
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