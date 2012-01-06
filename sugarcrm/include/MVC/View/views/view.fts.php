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

require_once('include/MVC/View/views/view.ajax.php');
require_once('include/SugarSearchEngine/SugarSearchEngineFactory.php');


class ViewFts extends ViewAjax
{
    /**
     * @see SugarView::display()
     */
    public function display()
    {

		$offset = -1;
        $offset = isset($_REQUEST['offset']) ? $_REQUEST['offset'] : -1;

        $limit = ( !empty($GLOBALS['sugar_config']['max_spotresults_initial']) ? $GLOBALS['sugar_config']['max_spotresults_initial'] : 5 );

        $options = array('current_module' => $this->module);

        $searchEngine = SugarSearchEngineFactory::getInstance();
        $trimmed_query = trim($_REQUEST['q']);
        $rs = $searchEngine->search($trimmed_query, $offset, $limit, $options);

        $query_encoded = urlencode($trimmed_query);
        $ss = new Sugar_Smarty();
        //$ss->assign('displayResults', $displayResults);
        //$ss->assign('displayMoreForModule', $displayMoreForModule);
        //$ss->assign('appStrings', $GLOBALS['app_strings']);
        //$ss->assign('appListStrings', $GLOBALS['app_list_strings']);
        $ss->assign('queryEncoded', $query_encoded);
        $ss->assign('resultSet', $rs);
        $template = 'include/MVC/View/tpls/fts_spot.tpl';
        if(file_exists('custom/include/MVC/View/tpls/fts_spot.tpl'))
        {
            $template = 'custom/include/MVC/View/tpls/fts_spot.tpl';
        }
        echo $ss->fetch($template);
    }
}

