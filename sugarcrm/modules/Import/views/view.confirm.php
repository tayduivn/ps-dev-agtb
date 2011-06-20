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
require_once('include/MVC/View/SugarView.php');
require_once('modules/Import/ImportFile.php');
require_once('modules/Import/ImportFileSplitter.php');

require_once('include/upload_file.php');

class ImportViewConfirm extends SugarView
{
    const SAMPLE_ROW_SIZE = 3;

    private $currentStep;

    public function __construct($bean = null, $view_object_map = array())
    {
        parent::__construct($bean, $view_object_map);
        $this->currentStep = isset($_REQUEST['current_step']) ? ($_REQUEST['current_step'] + 1) : 1;
    }
 	/**
     * @see SugarView::getMenu()
     */
    public function getMenu(
        $module = null
        )
    {
        global $mod_strings, $current_language;
        
        if ( empty($module) )
            $module = $_REQUEST['import_module'];
        
        $old_mod_strings = $mod_strings;
        $mod_strings = return_module_language($current_language, $module);
        $returnMenu = parent::getMenu($module);
        $mod_strings = $old_mod_strings;
        
        return $returnMenu;
    }
    
 	/**
     * @see SugarView::_getModuleTab()
     */
 	protected function _getModuleTab()
    {
        global $app_list_strings, $moduleTabMap;
        
 		// Need to figure out what tab this module belongs to, most modules have their own tabs, but there are exceptions.
        if ( !empty($_REQUEST['module_tab']) )
            return $_REQUEST['module_tab'];
        elseif ( isset($moduleTabMap[$_REQUEST['import_module']]) )
            return $moduleTabMap[$_REQUEST['import_module']];
        // Default anonymous pages to be under Home
        elseif ( !isset($app_list_strings['moduleList'][$_REQUEST['import_module']]) )
            return 'Home';
        else
            return $_REQUEST['import_module'];
 	}
 	
 	/**
	 * @see SugarView::_getModuleTitleParams()
	 */
	protected function _getModuleTitleParams($browserTitle = false)
	{
	    global $mod_strings, $app_list_strings;
	    
	    $iconPath = $this->getModuleTitleIconPath($this->module);
	    $returnArray = array();
	    if (!empty($iconPath) && !$browserTitle) {
	        $returnArray[] = "<a href='index.php?module={$_REQUEST['import_module']}&action=index'><img src='{$iconPath}' alt='{$app_list_strings['moduleList'][$_REQUEST['import_module']]}' title='{$app_list_strings['moduleList'][$_REQUEST['import_module']]}' align='absmiddle'></a>";
    	}
    	else {
    	    $returnArray[] = $app_list_strings['moduleList'][$_REQUEST['import_module']];
    	}
	    $returnArray[] = "<a href='index.php?module=Import&action=Step1&import_module={$_REQUEST['import_module']}'>".$mod_strings['LBL_MODULE_NAME']."</a>";
    	$returnArray[] = string_format($mod_strings['LBL_CONFIRM_TITLE'], array($this->currentStep));

	    return $returnArray;
    }
    
 	/** 
     * @see SugarView::display()
     */
 	public function display()
    {
        global $mod_strings, $app_strings, $current_user;
        global $sugar_config, $locale;

        //echo "<pre>";print_r($_REQUEST);die();
        $this->ss->assign("IMPORT_MODULE", $_REQUEST['import_module']);
        $this->ss->assign("TYPE",( !empty($_REQUEST['type']) ? $_REQUEST['type'] : "import" ));
        $this->ss->assign("SOURCE", $_REQUEST['source']);
        $this->ss->assign("SOURCE_ID", $_REQUEST['source_id']);
        $this->ss->assign("MODULE_TITLE", $this->getModuleTitle());
        $this->ss->assign("CURRENT_STEP", $this->currentStep);
        $sugar_config['import_max_records_per_file'] = ( empty($sugar_config['import_max_records_per_file']) ? 1000 : $sugar_config['import_max_records_per_file'] );
        $importSource = isset($_REQUEST['source']) ? $_REQUEST['source'] : 'csv' ;

        // Clear out this user's last import
        $seedUsersLastImport = new UsersLastImport();
        $seedUsersLastImport->mark_deleted_by_user_id($current_user->id);
        ImportCacheFiles::clearCacheFiles();

        // handle uploaded file
        $uploadFile = new UploadFile('userfile');
        if (isset($_FILES['userfile']) && $uploadFile->confirm_upload())
        {
            //file extension should be set to csv ONLY
            $uploadedFileExtension = pathinfo($_FILES['userfile']['name'], PATHINFO_EXTENSION);
            if(empty($uploadedFileExtension) || $uploadedFileExtension != 'csv' ){
                //if the extension is not .csv then return error message
                $this->_showImportError($mod_strings['LBL_IMPORT_ERROR_MIME_TYPE'],$_REQUEST['import_module'],'Step2');
                return;
            }

            $uploadFile->final_move('IMPORT_'.$this->bean->object_name.'_'.$current_user->id);
            $uploadFileName = $uploadFile->get_upload_path('IMPORT_'.$this->bean->object_name.'_'.$current_user->id);
        }
        else
        {
            $this->_showImportError($mod_strings['LBL_IMPORT_MODULE_ERROR_NO_UPLOAD'],$_REQUEST['import_module'],'Step2');
            return;
        }

        $this->ss->assign("FILE_NAME", $uploadFileName);

        // Now parse the file and look for errors
        $importFile = new ImportFile( $uploadFileName, $_REQUEST['custom_delimiter'], html_entity_decode($_REQUEST['custom_enclosure'],ENT_QUOTES), FALSE);

        if( $this->shouldAutoDetectProperties($importSource) )
        {
            $GLOBALS['log']->fatal("Auto detecing csv properties...");
            $importFile->autoDetectCSVProperties();
            $delimeter = $importFile->getFieldDelimeter();
            $enclosure = $importFile->getFieldEnclosure();
            $hasHeader = $importFile->hasHeaderRow();
        }
        else
        {
            $GLOBALS['log']->fatal("Using import map for import properties.");
            $import_map_seed = $this->getImportMap($importSource);
            $delimeter = $import_map_seed->delimiter;
            $enclosure = htmlentities($import_map_seed->enclosure);
            $hasHeader = $import_map_seed->has_header;
        }

        $this->ss->assign("CUSTOM_DELIMITER",  $delimeter);
        $this->ss->assign("CUSTOM_ENCLOSURE",  $enclosure);
        $hasHeaderFlag = $hasHeader ? " CHECKED" : "";
        $this->ss->assign("HAS_HEADER_CHECKED", $hasHeaderFlag);

        if ( !$importFile->fileExists() ) {
            $this->_showImportError($mod_strings['LBL_CANNOT_OPEN'],$_REQUEST['import_module'],'Step2');
            return;
        }

         //Check if we will exceed the maximum number of records allowed per import.
         $maxRecordsExceeded = FALSE;
         $maxRecordsWarningMessg = "";
         $lineCount = $importFile->getNumberOfLinesInfile();
         $maxLineCount = isset($sugar_config['import_max_records_total_limit'] ) ? $sugar_config['import_max_records_total_limit'] : 5000;
         if( !empty($maxLineCount) && ($lineCount > $maxLineCount) )
         {
             $maxRecordsExceeded = TRUE;
             $maxRecordsWarningMessg = string_format($mod_strings['LBL_IMPORT_ERROR_MAX_REC_LIMIT_REACHED'], array($lineCount, $maxLineCount) );
         }

        //Retrieve a sample set of data
        $rows = $this->getSampleSet($importFile);


        $this->ss->assign('getNumberJs', $locale->getNumberJs());
        $this->setImportFileCharacterSet($importFile);
        $this->setDateTimeProperties();
        $this->setCurrencyOptions();
        $this->setNumberFormatOptions();
        $this->setNameFormatProperties();
        
        $importMappingJS = $this->getImportMappingJS();
        
        $this->ss->assign("SAMPLE_ROWS",$rows);
        $this->ss->assign("JAVASCRIPT", $this->_getJS($maxRecordsExceeded, $maxRecordsWarningMessg, $importMappingJS ));
        $this->ss->display('modules/Import/tpls/confirm.tpl');
    }

    private function shouldAutoDetectProperties($importSource)
    {
        if($importSource == 'csv')
            return TRUE;
        else
            return FALSE;
    }

    private function getImportMap($importSource)
    {
        if ( strncasecmp("custom:",$importSource,7) == 0)
        {
            $id = substr($importSource,7);
            $import_map_seed = new ImportMap();
            $import_map_seed->retrieve($id, false);

            $this->ss->assign("SOURCE_ID", $import_map_seed->id);
            $this->ss->assign("SOURCE_NAME", $import_map_seed->name);
            $this->ss->assign("SOURCE", $import_map_seed->source);
        }
        else
        {
            $classname = 'ImportMap' . ucfirst($importSource);
            if ( file_exists("modules/Import/{$classname}.php") )
                require_once("modules/Import/{$classname}.php");
            elseif ( file_exists("custom/modules/Import/{$classname}.php") )
                require_once("custom/modules/Import/{$classname}.php");
            else
            {
                require_once("custom/modules/Import/ImportMapOther.php");
                $classname = 'ImportMapOther';
                $importSource = 'other';
            }
            if ( class_exists($classname) )
            {
                $import_map_seed = new $classname;
                $this->ss->assign("SOURCE", $importSource);
            }
        }

        return $import_map_seed;
    }

    private function setNameFormatProperties($field_map = array())
    {
        global $locale, $current_user;
        
        $localized_name_format = isset($field_map['importlocale_default_locale_name_format'])? $field_map['importlocale_default_locale_name_format'] : $locale->getLocaleFormatMacro($current_user);
        $this->ss->assign('default_locale_name_format', $localized_name_format);
        $this->ss->assign('getNameJs', $locale->getNameJs());

    }

    private function setNumberFormatOptions($field_map = array())
    {
        global $locale, $current_user, $sugar_config;

        $num_grp_sep = isset($field_map['importlocale_num_grp_sep'])? $field_map['importlocale_num_grp_sep'] : $current_user->getPreference('num_grp_sep');
        $dec_sep = isset($field_map['importlocale_dec_sep'])? $field_map['importlocale_dec_sep'] : $current_user->getPreference('dec_sep');

        $this->ss->assign("NUM_GRP_SEP",( empty($num_grp_sep) ? $sugar_config['default_number_grouping_seperator'] : $num_grp_sep ));
        $this->ss->assign("DEC_SEP",( empty($dec_sep)? $sugar_config['default_decimal_seperator'] : $dec_sep ));


        $significantDigits = isset($field_map['importlocale_default_currency_significant_digits']) ? $field_map['importlocale_default_currency_significant_digits']
                                :  $locale->getPrecedentPreference('default_currency_significant_digits', $current_user);

        $sigDigits = '';
        for($i=0; $i<=6; $i++)
        {
            if($significantDigits == $i)
            {
                $sigDigits .= '<option value="'.$i.'" selected="true">'.$i.'</option>';
            } else
            {
                $sigDigits .= '<option value="'.$i.'">'.$i.'</option>';
            }
        }

        $this->ss->assign('sigDigits', $sigDigits);
    }


    private function setCurrencyOptions($field_map = array() )
    {
        global $locale, $current_user;
        $cur_id = isset($field_map['importlocale_currency'])? $field_map['importlocale_currency'] : $locale->getPrecedentPreference('currency', $current_user);
        // get currency preference
        require_once('modules/Currencies/ListCurrency.php');
        $currency = new ListCurrency();
        if($cur_id)
            $selectCurrency = $currency->getSelectOptions($cur_id);
        else
            $selectCurrency = $currency->getSelectOptions();

        $this->ss->assign("CURRENCY", $selectCurrency);

        $currenciesVars = "";
        $i=0;
        foreach($locale->currencies as $id => $arrVal)
        {
            $currenciesVars .= "currencies[{$i}] = '{$arrVal['symbol']}';\n";
            $i++;
        }
        $currencySymbolsJs = <<<eoq
var currencies = new Object;
{$currenciesVars}
function setSymbolValue(id) {
    document.getElementById('symbol').value = currencies[id];
}
eoq;
        $this->ss->assign('currencySymbolJs', $currencySymbolsJs);

    }


    private function setDateTimeProperties( $field_map = array() )
    {
        global $current_user, $sugar_config;

        $timeFormat = $current_user->getUserDateTimePreferences();
        $timeOptions = isset($field_map['importlocale_timeformat'])? $field_map['importlocale_timeformat'] : get_select_options_with_id($sugar_config['time_formats'], $timeFormat['time']);
        $dateOptions = isset($field_map['importlocale_dateformat'])? $field_map['importlocale_dateformat'] : get_select_options_with_id($sugar_config['date_formats'], $timeFormat['date']);

        // get list of valid timezones
        $userTZ = isset($field_map['importlocale_timezone'])? $field_map['importlocale_timezone'] : $current_user->getPreference('timezone');
        if(empty($userTZ))
            $userTZ = TimeDate::userTimezone();

        $this->ss->assign('TIMEZONE_CURRENT', $userTZ);
        $this->ss->assign('TIMEOPTIONS', $timeOptions);
        $this->ss->assign('DATEOPTIONS', $dateOptions);
        $this->ss->assign('TIMEZONEOPTIONS', TimeDate::getTimezoneList());
    }

    private function setImportFileCharacterSet($importFile)
    {
        global $locale;
        $charset_for_import = $importFile->autoDetectCharacterSet();
        $charsetOptions = get_select_options_with_id( $locale->getCharsetSelect(), $charset_for_import);//wdong,  bug 25927, here we should use the charset testing results from above.
        $this->ss->assign('CHARSETOPTIONS', $charsetOptions);
    }

    protected function getImportMappingJS()
    {
        $results = array();
        $importMappings = array('ImportMapSalesforce', 'ImportMapOutlook');
        foreach($importMappings as $importMap)
        {
            $tmpFile = "modules/Import/$importMap.php";
            if( file_exists($tmpFile) )
            {
                require_once($tmpFile);
                $t = new $importMap();
                $results[$t->name] = array('delim' => $t->delimiter, 'enclos' => $t->enclosure, 'has_header' => $t->has_header);
            }
        }
        return $results;
    }


    public function getSampleSet($importFile)
    {
        $rows = array();
        for($i=0; $i < self::SAMPLE_ROW_SIZE; $i++)
        {
            $rows[] = $importFile->getNextRow();
        }

        if( ! $importFile->hasHeaderRow() )
        {
            array_unshift($rows, array_fill(0, count($rows[0]),'') );
        }

        return $rows;
    }

    /**
     * Returns JS used in this view
     */
    private function _getJS($maxRecordsExceeded, $maxRecordsWarningMessg, $importMappingJS)
    {
        global $mod_strings;
        $maxRecordsExceededJS = $maxRecordsExceeded?"true":"false";
        $importMappingJS = json_encode($importMappingJS);
        return <<<EOJAVASCRIPT
<script type="text/javascript">

var import_mapping_js = $importMappingJS;
document.getElementById('goback').onclick = function(){
    document.getElementById('importconfirm').action.value = 'Step2';
    return true;
}

document.getElementById('gonext').onclick = function(){
    document.getElementById('importconfirm').action.value = 'Step3';
    return true;
}

document.getElementById('custom_enclosure').onchange = function()
{
    document.getElementById('importconfirm').custom_enclosure_other.style.display = ( this.value == 'other' ? '' : 'none' );
}

YAHOO.util.Event.onDOMReady(function(){
    if($maxRecordsExceededJS)
    {
        var contImport = confirm('$maxRecordsWarningMessg');
        if(!contImport)
        {
            var module = document.getElementById('importconfirm').import_module.value;
            var source = document.getElementById('importconfirm').source.value;
            var returnUrl = "index.php?module=Import&action=Step2&import_module=" + module + "&source=" + source;
            document.location.href = returnUrl;
        }
    }

    function refreshDataTable(e)
    {
        var callback = {
          success: function(o) {
            document.getElementById('confirm_table').innerHTML = o.responseText;
          },
          failure: function(o) {},
        };

        var importFile = document.getElementById('importconfirm').file_name.value;
        var fieldDelimeter = document.getElementById('custom_delimiter').value;
        var fieldQualifier = document.getElementById('custom_enclosure').value;
        var hasHeader = document.getElementById('importconfirm').has_header.checked ? 'true' : '';

        if(fieldQualifier == 'other' && this.id == 'custom_enclosure')
        {
            return;
        }
        else if( fieldQualifier == 'other' )
        {
            fieldQualifier = document.getElementById('custom_enclosure_other').value;
        }

        var url = 'index.php?action=RefreshMapping&module=Import&importFile=' + importFile
                    + '&delim=' + fieldDelimeter + '&qualif=' + fieldQualifier + "&header=" + hasHeader;

        YAHOO.util.Connect.asyncRequest('GET', url, callback);
    }
    var subscribers = ["custom_delimiter", "custom_enclosure", "custom_enclosure_other", "has_header", "importlocale_charset"];
    YAHOO.util.Event.addListener(subscribers, "change", refreshDataTable);

    function setMappingProperties(el)
    {
       var sourceEl = document.getElementById('source');
       if(sourceEl.value != '' && sourceEl.value != 'csv')
       {
           if( !confirm(SUGAR.language.get('Import','LBL_CONFIRM_MAP_OVERRIDE')) )
           {
                deSelectExternalSources();
                return;
           }
        }
        var selectedMap = this.value;
        if( typeof(import_mapping_js[selectedMap]) == 'undefined')
            return;

        sourceEl.value = selectedMap;
        document.getElementById('custom_delimiter').value = import_mapping_js[selectedMap].delim;
        document.getElementById('custom_enclosure').value = import_mapping_js[selectedMap].enclos;
        document.getElementById('has_header').checked = import_mapping_js[selectedMap].has_header;
        
        refreshDataTable();
    }

    function deSelectExternalSources()
    {
        var els = document.getElementsByName('external_source');
        for(i=0;i<els.length;i++)
        {
            els[i].checked = false;
        }
    }
    YAHOO.util.Event.addListener(['sf_map', 'outlook_map'], "click", setMappingProperties);
});
</script>

EOJAVASCRIPT;
    }

    /**
     * Displays the Smarty template for an error
     *
     * @param string $message error message to show
     * @param string $module what module we were importing into
     * @param string $action what page we should go back to
     */
    protected function _showImportError(
        $message,
        $module,
        $action = 'Step1'
        )
    {
        $ss = new Sugar_Smarty();

        $ss->assign("MESSAGE",$message);
        $ss->assign("ACTION",$action);
        $ss->assign("IMPORT_MODULE",$module);
        $ss->assign("MOD", $GLOBALS['mod_strings']);
        $ss->assign("SOURCE","");
        if ( isset($_REQUEST['source']) )
            $ss->assign("SOURCE", $_REQUEST['source']);

        echo $ss->fetch('modules/Import/tpls/error.tpl');
    }
}

?>
