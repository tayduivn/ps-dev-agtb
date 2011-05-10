<?php

require_once('include/Sugar_Smarty.php');
require_once('include/ListView/ListViewSmarty.php');
require_once('include/ListView/ListViewDataSubpanel.php');

class SubpanelPortal extends ListViewSmarty {
    var $ss;
    var $tpl;
    var $result;
    var $parentModule;
    var $id;
    
    /**
     * Creates a new subpanel for portal
     *    
     * @param module string Module the subpanel is for
     * @param parentModule string parent module the subpanel is related to
     * @param id string record id of the parent module
     * @param fields array fields to retrieves
     * @param tpl string tpl file to use
     * 
     */ 
    function SubpanelPortal($module, $parentModule, $id, $fields, $tpl = 'include/ListView/ListViewGeneric.tpl') {
        require_once('modules/' . $module . '/metadata/subpanelDefs.php');
        
        $this->displayColumns = $viewdefs[$module]['subpanel'];
        $this->ss = new Sugar_Smarty();
        $this->tpl = $tpl;
        
        $this->lvd = new ListViewDataSubpanel($parentModule);
        $this->lvd->module = $module;
        $this->lvd->parentModule = $parentModule;
        $this->lvd->parentRecordId = $id;
        $this->lvd->selectFields = $fields;
    }
    
    function process($file, $data, $htmlVar) {
        parent::process($file, $data, $htmlVar);
        
        $this->ss->assign('returnModule', $this->lvd->parentModule);
        $this->ss->assign('returnAction', 'DetailView');
        $this->ss->assign('returnId', $this->lvd->parentRecordId);
    }
    
    function display($title) {
        $str = '<p>' . get_form_header($title, '', false)  . '</p>';

        if(empty($this->data)) {
            global $app_strings;
            return $str . '<h3>' . $app_strings['LBL_NO_RECORDS_FOUND'] . '</h3>';
        }
        else {
            return $str . parent::display();
        }
    } 
}
?>