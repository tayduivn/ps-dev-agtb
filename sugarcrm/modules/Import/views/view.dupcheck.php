<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2007 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: view.step1.php 31561 2008-02-04 18:41:10Z jmertic $
 * Description: view handler for step 1 of the import process
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 ********************************************************************************/
require_once('modules/Import/views/ImportView.php');
require_once('modules/Import/ImportFile.php');
require_once('modules/Import/ImportFileSplitter.php');
require_once('modules/Import/ImportCacheFiles.php');
require_once('modules/Import/ImportDuplicateCheck.php');

require_once('include/upload_file.php');

class ImportViewDupcheck extends ImportView
{
    protected $pageTitleKey = 'LBL_STEP_DUP_TITLE';

 	/**
     * @see SugarView::display()
     */
 	public function display()
    {
        global $mod_strings, $app_strings, $current_user;
        global $sugar_config;

        $has_header = $_REQUEST['has_header'] == 'on' ? TRUE : FALSE;

        $this->ss->assign("MODULE_TITLE", $this->getModuleTitle());
        $this->ss->assign("DELETE_INLINE_PNG",  SugarThemeRegistry::current()->getImage('delete_inline','align="absmiddle" alt="'.$app_strings['LNK_DELETE'].'" border="0"'));
        $this->ss->assign("PUBLISH_INLINE_PNG",  SugarThemeRegistry::current()->getImage('publish_inline','align="absmiddle" alt="'.$mod_strings['LBL_PUBLISH'].'" border="0"'));
        $this->ss->assign("UNPUBLISH_INLINE_PNG",  SugarThemeRegistry::current()->getImage('unpublish_inline','align="absmiddle" alt="'.$mod_strings['LBL_UNPUBLISH'].'" border="0"'));
        $this->ss->assign("IMPORT_MODULE", $_REQUEST['import_module']);
        $this->ss->assign("JAVASCRIPT", $this->_getJS());

        //TAB CHOOSER
        require_once("include/templates/TemplateGroupChooser.php");

        $chooser_array = array();
        $chooser_array[0] = array();
        $idc = new ImportDuplicateCheck($this->bean);
        $chooser_array[1] = $idc->getDuplicateCheckIndexes();

        $field_map = $this->getImportMap();
        //check for saved entries from mapping
        foreach($chooser_array[1] as $ck=>$cv){
            if(isset($field_map['dupe_'.$ck])){
                //index is defined in mapping, so set this index as selected and remove from available list
                $chooser_array[0][$ck]=$cv;
                unset($chooser_array[1][$ck]);
            }
        }

        $chooser = new TemplateGroupChooser();
        $chooser->args['id'] = 'selected_indices';
        $chooser->args['values_array'] = $chooser_array;
        $chooser->args['left_name'] = 'choose_index';
        $chooser->args['right_name'] = 'ignore_index';
        $chooser->args['left_label'] =  $mod_strings['LBL_INDEX_USED'];
        $chooser->args['right_label'] =  $mod_strings['LBL_INDEX_NOT_USED'];
        $this->ss->assign("TAB_CHOOSER", $chooser->display());
        //END TAB CHOOSER

        // split file into parts
        $uploadFileName = $_REQUEST['tmp_file'];
        $splitter = new ImportFileSplitter($uploadFileName, $sugar_config['import_max_records_per_file']);
        $splitter->splitSourceFile( $_REQUEST['custom_delimiter'], html_entity_decode($_REQUEST['custom_enclosure'],ENT_QUOTES), $has_header);

        $this->ss->assign("FILECOUNT", $splitter->getFileCount() );
        $this->ss->assign("RECORDCOUNT", $splitter->getRecordCount() );
        $this->ss->assign("RECORDTHRESHOLD", $sugar_config['import_max_records_per_file']);

        $this->ss->display('modules/Import/tpls/dupcheck.tpl');
    }

    private function getImportMap()
    {
        if( !empty($_REQUEST['source_id']) )
        {
            $import_map_seed = new ImportMap();
            $import_map_seed->retrieve($_REQUEST['source_id'], false);

            return $import_map_seed->getMapping();
        }
        else
        {
            return array();
        }
    }

    private function getImportableModules()
    {
        global $beanList;
        $importableModules = array();
        foreach ($beanList as $moduleName => $beanName)
        {
            if( class_exists($beanName) )
            {
                $tmp = new $beanName();
                if( isset($tmp->importable) && $tmp->importable )
                    $importableModules[$moduleName] = $moduleName;
            }
        }

        asort($importableModules);
        return $importableModules;
    }

    private function getImportableExternalEAPMs()
    {
        require_once('include/externalAPI/ExternalAPIFactory.php');

        return ExternalAPIFactory::getModuleDropDown('Import', FALSE, FALSE, 'eapm_import_list');
    }
    /**
     * Returns JS used in this view
     */
    private function _getJS()
    {
        global $mod_strings;

        return <<<EOJAVASCRIPT
<script type="text/javascript">

document.getElementById('goback').onclick = function(){
    document.getElementById('importstepdup').action.value = 'step3';
    document.getElementById('importstepdup').to_pdf.value = '0';
    return true;
}

document.getElementById('importnow').onclick = function(){
    // get the list of indices chosen
    var chosen_indices = '';
    var selectedOptions = document.getElementById('choose_index_td').getElementsByTagName('select')[0].options.length;
    for (i = 0; i < selectedOptions; i++)
    {
        chosen_indices += document.getElementById('choose_index_td').getElementsByTagName('select')[0].options[i].value;
        if (i != (selectedOptions - 1))
        chosen_indices += "&";
    }
    document.getElementById('importstepdup').display_tabs_def.value = chosen_indices;
    var form = document.getElementById('importstepdup');
    // Move on to next step
    document.getElementById('importstepdup').action.value = 'Step4';
    ProcessImport.begin();
}



</script>

EOJAVASCRIPT;
    }
}

?>
