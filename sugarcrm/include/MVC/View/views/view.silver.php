<?php
//FILE SUGARCRM flav=int ONLY
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
/*
 * Created on Apr 13, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once('include/DetailView/DetailView2.php');
require_once ('include/SubPanel/SubPanelBronze.php');

class ViewSilver extends SugarView{
	var $type ='detail';
	var $dv;
	
 	function ViewSilver(){
 		$this->options['show_subpanels'] = true;
 		parent::SugarView();
 	}

 	function preDisplay(){
 		$metadataFile = null;
 		$foundViewDefs = false;
 		if(file_exists('custom/modules/' . $this->module . '/metadata/detailviewdefs.php')){
 			$metadataFile = 'custom/modules/' . $this->module . '/metadata/detailviewdefs.php';
 			$foundViewDefs = true;
 		}else{
	 		if(file_exists('custom/modules/'.$this->module.'/metadata/metafiles.php')){
				require_once('custom/modules/'.$this->module.'/metadata/metafiles.php');
				if(!empty($metafiles[$this->module]['detailviewdefs'])){
					$metadataFile = $metafiles[$this->module]['detailviewdefs'];
					$foundViewDefs = true;
				}
			}elseif(file_exists('modules/'.$this->module.'/metadata/metafiles.php')){
				require_once('modules/'.$this->module.'/metadata/metafiles.php');
				if(!empty($metafiles[$this->module]['detailviewdefs'])){
					$metadataFile = $metafiles[$this->module]['detailviewdefs'];
					$foundViewDefs = true;
				}
			}
 		}
 		$GLOBALS['log']->debug("metadatafile=". $metadataFile);
		if(!$foundViewDefs && file_exists('modules/'.$this->module.'/metadata/detailviewdefs.php')){
				$metadataFile = 'modules/'.$this->module.'/metadata/detailviewdefs.php';
 		}

		$this->dv = new DetailView2();
		$this->dv->ss =&  $this->ss;
		$this->dv->setup($this->module, $this->bean, $metadataFile, 'include/DetailView/DetailView.tpl'); 		
 	} 	
 	
 	function display(){
		if(empty($this->bean->id)){
			global $app_strings;
			sugar_die($app_strings['ERROR_NO_RECORD']);
		}
		$this->subpanel = new SubPanelBronze($this->bean, $this->module);			
		$this->dv->process();
		$this->subpanel->createTabs('container', array('Detail'=>$this->dv->display()));
 	}

 	/**
     * @see SugarView::_displaySubPanels()
     */
    protected function _displaySubPanels()
    {
        if (isset($this->bean) && !empty($this->bean->id) && (file_exists('modules/' . $this->module . '/metadata/subpaneldefs.php') || file_exists('custom/modules/' . $this->module . '/metadata/subpaneldefs.php') || file_exists('custom/modules/' . $this->module . '/Ext/Layoutdefs/layoutdefs.ext.php'))) {
            $GLOBALS['focus'] = $this->bean;
            
            echo $this->subpanel->display();
        }
    }
}
