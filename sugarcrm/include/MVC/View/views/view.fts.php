<?php
//FILE SUGARCRM flav=pro || flav=sales ONLY
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('include/MVC/View/SugarView.php');
require_once('include/SugarSearchEngine/SugarSearchEngineFactory.php');


class ViewFts extends SugarView
{
    private $fullView = FALSE;
    private $templateName = '';
    private $rsTemplateName = 'fts_full_rs.tpl';

    public function __construct()
    {
        $this->fullView = !empty($_REQUEST['full']) ? TRUE : FALSE;

        if($this->fullView)
        {
            $this->options = array('show_title'=> true,'show_header'=> true,'show_footer'=> true,'show_javascript'=> true,'show_subpanels'=> false,'show_search'=> false);
            $this->templateName = 'fts_full.tpl';
        }
        else
        {
            $this->options = array('show_title'=> false,'show_header'=> false,'show_footer'=> false,'show_javascript'=> false,'show_subpanels'=> false,'show_search'=> false);
            $this->templateName = 'fts_spot.tpl';
        }
        parent::__construct();

    }
    /**
     * @see SugarView::display()
     */
    public function display()
    {

        $offset = isset($_REQUEST['offset']) ? $_REQUEST['offset'] : 0;

        $limit = ( !empty($GLOBALS['sugar_config']['max_spotresults_initial']) ? $GLOBALS['sugar_config']['max_spotresults_initial'] : 5 );

        $moduleFilter = isset($_REQUEST['m']) ? $_REQUEST['m'] : FALSE;
        $options = array('current_module' => $this->module, 'moduleFilter' => $moduleFilter);;

        $searchEngine = SugarSearchEngineFactory::getInstance();
        $trimmed_query = trim($_REQUEST['q']);
        $rs = $searchEngine->search($trimmed_query, $offset, $limit, $options);
        $query_encoded = urlencode($trimmed_query);

        $resultSetOnly = !empty($_REQUEST['rs_only']) ? $_REQUEST['rs_only'] : FALSE;

        $this->ss->assign('queryEncoded', $query_encoded);
        $this->ss->assign('resultSet', $rs);
        $this->ss->assign('appListStrings', $GLOBALS['app_list_strings']);
        $template = "include/MVC/View/tpls/{$this->templateName}";
        $rsTemplate = "include/MVC/View/tpls/{$this->rsTemplateName}";
        if(file_exists("custom/$template"))
        {
            $template = "custom/$template";
        }
        if(file_exists("custom/$rsTemplate"))
        {
            $rsTemplate = "custom/$rsTemplate";
        }
        $this->ss->assign('rsTemplate', $rsTemplate);

        if( $this->fullView )
        {
            if($resultSetOnly)
            {
                echo $this->ss->fetch($rsTemplate);
                return;
            }
            $this->ss->assign('filterModules', $this->getFilterModules());

        }
        echo $this->ss->fetch($template);
    }

    /**
     * TODO: WIP
     * @return array
     */
    protected function getFilterModules()
    {
        require_once('modules/Home/UnifiedSearchAdvanced.php');
        $ufs = new UnifiedSearchAdvanced();
        $moduleList = $ufs->getUnifiedSearchModulesDisplay();
        $results = array();

        foreach($moduleList as $module=>$data)
        {
            if($data['visible'] && ACLController::checkAccess($module, 'list', true))
            {
                $moduleName  = isset($GLOBALS['app_list_strings']['moduleList'][$module] ) ? $GLOBALS['app_list_strings']['moduleList'][$module] : $module;
                $results[$module] = $moduleName;
            }
        }

        return $results;
    }
}

