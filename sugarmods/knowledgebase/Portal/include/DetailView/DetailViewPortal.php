<?php

require_once('include/Sugar_Smarty.php');
require_once('include/TemplateHandler/TemplateHandler.php');

class DetailViewPortal {
    
    var $th;
    var $tpl;
    var $result;
    var $notes;
    var $modStrings;
    var $id;
    var $metadataFile;
    var $headerTpl;
    var $footerTpl;
    var $returnAction;
    var $returnModule;
    var $returnId;
    var $editable;
    
    /**
     * Creates a new detailview portal object
     *    
     * @param module string Module this detail view is for
     * @param id string record id to retrieve
     * @param fields array fields to retrieves
     * @param tpl string tpl file to use
     * @param translate bool translate the drop down values
     * 
     */ 
    function DetailViewPortal($module, $id, $metadataFile = null, $tpl = 'include/DetailView/DetailView.tpl', $translate = true) {
        global $portal;
        
        $this->th = new TemplateHandler();
        
        $this->id = $id;
        $this->tpl = $tpl;  
        $this->module = $module;
        $this->modStrings = array();
        $this->metadataFile = $metadataFile;
        $this->editable = true;
        
        if(isset($this->metadataFile)) {
            require_once($this->metadataFile);
        }
        elseif(is_file('custom/portal/modules/' . $this->module . '/metadata/detailviewdefs.php')) {
               require_once('custom/portal/modules/' . $this->module . '/metadata/detailviewdefs.php');
        }
        else {
            require_once('modules/' . $this->module . '/metadata/detailviewdefs.php');
        }
        
        $this->defs = $viewdefs[$this->module]['detailview'];
        
        // figure out which fields to pull based off of metadata
        $fields = array();
        foreach($this->defs['data'] as $row) {
            foreach($row as $col => $def) {
                if(is_array($def)) 
                    $fields[$def['field']] = true;
                else 
                    $fields[$def] = true;
            }
        }
        if(!empty($this->defs['templateMeta']['extraFields'])) {
            foreach($this->defs['templateMeta']['extraFields'] as $field) {
                $fields[$field] = true;
            }
        }
        $fields['id'] = true; // always get id
        
        // TODO: use version of getEntry that doesn't return fields 
        $this->result = $portal->getEntry($module, $id, array_keys($fields));
        foreach($this->result['data'] as $name => $value) {
            $this->result['fields'][$name]['value'] = $value;
        }

        foreach($this->result['fields'] as $name => $def) {
            $this->modStrings[$name] = empty($def['label']) ? '' : $def['label'];
            if($translate) {
                if(!empty($def['options'])) { // convert drop down values
                    foreach($def['options'] as $n => $v) {
                        if(!empty($this->result['data'][$name]) && $n == $this->result['data'][$name]) {
                            $this->result['data'][$name] = $v;
                            break;
                        } 
                    }
                }
            }
        }
    } 

    function process() {
        global $mod_strings, $sugar_config, $app_strings;
        
        if(empty($this->modStrings)){
            $this->modStrings = $mod_strings;  
        }
        
        $this->th->ss->assign('app', $app_strings);
        $this->th->ss->assign('mod', $this->modStrings);
        $this->th->ss->assign('fields', $this->result['fields']);
        $this->th->ss->assign('data', $this->result['data']);
        $this->th->ss->assign('siteUrl', $sugar_config['site_url']);

        $totalWidth = 0;
        foreach($this->defs['templateMeta']['widths'] as $col => $def) {
            foreach($def as $k => $value) $totalWidth += $value;
        }
        // calculate widths
        foreach($this->defs['templateMeta']['widths'] as $col => $def) {
            foreach($def as $k => $value) 
                $this->defs['templateMeta']['widths'][$col][$k] = round($value / ($totalWidth / 100), 2);
        }
        
        foreach($this->defs['data'] as $row => $rowDef) {
            $columnsInRows = count($rowDef);
            $columnsUsed = 0;
            foreach($rowDef as $col => $colDef) {
                // change just simple fieldnames to metadata arrays
                if(!is_array($this->defs['data'][$row][$col]) && $this->defs['data'][$row][$col] != '') {
                    $this->defs['data'][$row][$col] = array('field' => $this->defs['data'][$row][$col]);
                }
                if($columnsInRows < $this->defs['templateMeta']['maxColumns']) { // calculate colspans
                    if($col == $columnsInRows - 1) {
                        $this->defs['data'][$row][$col]['colspan'] = 2 * $this->defs['templateMeta']['maxColumns'] - $columnsUsed;
                    }
                    else {
                        $this->defs['data'][$row][$col]['colspan'] = floor(($this->defs['templateMeta']['maxColumns'] * 2 - $columnsInRows) / $columnsInRows);
                        $columnsUsed = $this->defs['data'][$row][$col]['colspan'];
                    }
                }
            }
        }
        
        $this->th->ss->assign('returnModule', $this->returnModule);
        $this->th->ss->assign('returnAction', $this->returnAction);
        $this->th->ss->assign('returnId', $this->returnId);
        $this->th->ss->assign('def', $this->defs);
        $this->th->ss->assign('module', $this->module);
        $this->th->ss->assign('editable', $this->editable);
        $this->th->ss->assign('headerTpl', isset($this->headerTpl) ? $this->headerTpl : 'include/DetailView/header.tpl');
        $this->th->ss->assign('footerTpl', isset($this->footerTpl) ? $this->footerTpl : 'include/DetailView/footer.tpl');
    }
 
    function display($showTitle = true) {
        global $mod_strings;
        
        if($showTitle) {
            $title = $mod_strings['LBL_MODULE_NAME'] . (empty($this->result['data']['name']) ? '' : (': ' . $this->result['data']['name']));
            $str = '<p>' . get_module_title($mod_strings['LBL_MODULE_NAME'], $title, false)  . '</p>';
        }
        else {
            $str = '';
        }

        $str .= $this->th->displayTemplate($this->module, 'DetailView', $this->tpl);
        
        return $str;       
    }
    
    function insertJavascript($javascript){
        $this->ss->assign('javascript', $javascript);   
    }
}
?>