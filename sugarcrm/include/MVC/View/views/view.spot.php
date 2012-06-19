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
require_once('modules/Home/UnifiedSearchAdvanced.php');
require_once('include/SugarSearchEngine/SugarSearchEngineFactory.php');


class ViewSpot extends ViewAjax
{
    /**
     * @see SugarView::display()
     */
    public function display()
    {

		$offset = -1;
        $modules = array();

		if(!empty($_REQUEST['zoom']) )
        {
			$modules = array($_REQUEST['zoom']);
			if(isset($_REQUEST['offset'])){
				$offset = $_REQUEST['offset'];
			}
		}

        $limit = ( !empty($GLOBALS['sugar_config']['max_spotresults_initial']) ? $GLOBALS['sugar_config']['max_spotresults_initial'] : 5 );
        if($offset !== -1)
        {
            $limit = ( !empty($GLOBALS['sugar_config']['max_spotresults_more']) ? $GLOBALS['sugar_config']['max_spotresults_more'] : 20 );
        }

        $options = array('current_module' => $this->module, 'modules' => $modules);

        $searchEngine = SugarSearchEngineFactory::getInstance('', array(), true);

        $trimmed_query = trim($_REQUEST['q']);
        $rs = $searchEngine->search($trimmed_query, $offset, $limit, $options);
        $formattedResults = $this->formatSearchResultsToDisplay($rs, $offset,$trimmed_query);

        $query_encoded = urlencode($trimmed_query);
        $displayMoreForModule = $formattedResults['displayMoreForModule'];
        $displayResults = $formattedResults['displayResults'];

        $ss = new Sugar_Smarty();
        $ss->assign('displayResults', $displayResults);
        $ss->assign('displayMoreForModule', $displayMoreForModule);
        $ss->assign('appStrings', $GLOBALS['app_strings']);
        $ss->assign('appListStrings', $GLOBALS['app_list_strings']);
        $ss->assign('queryEncoded', $query_encoded);

        $template = 'include/SearchForm/tpls/SugarSpot.tpl';
        if(file_exists('custom/include/SearchForm/tpls/SugarSpot.tpl'))
        {
            $template = 'custom/include/SearchForm/tpls/SugarSpot.tpl';
        }
        echo $ss->fetch($template);
    }


    protected function formatSearchResultsToDisplay($results, $offset, $trimmedQuery)
    {
        $displayResults = array();
        $displayMoreForModule = array();
        //$actions=0;
        foreach($results as $m=>$data)
        {
            if(empty($data['data']))
            {
                continue;
            }

            $countRemaining = $data['pageData']['offsets']['total'] - count($data['data']);
            if($offset > 0)
            {
                $countRemaining -= $offset;
            }

            if($countRemaining > 0)
            {
                $displayMoreForModule[$m] = array('query'=>$trimmedQuery,
                    'offset'=>++$data['pageData']['offsets']['next'],
                    'countRemaining'=>$countRemaining);
            }

            foreach($data['data'] as $row)
            {
                $name = '';

                //Determine a name to use
                if(!empty($row['NAME']))
                {
                    $name = $row['NAME'];
                }
                else if(!empty($row['DOCUMENT_NAME']))
                {
                    $name = $row['DOCUMENT_NAME'];
                }
                else
                {
                    $foundName = '';
                    foreach($row as $k=>$v)
                    {
                        if(strpos($k, 'NAME') !== false)
                        {
                            if(!empty($row[$k]))
                            {
                                $name = $v;
                                break;
                            }
                            else if(empty($foundName))
                            {
                                $foundName = $v;
                            }
                        }
                    }

                    if(empty($name))
                    {
                        $name = $foundName;
                    }
                }

                $displayResults[$m][$row['ID']] = $name;
            }
        }

        return array('displayResults' => $displayResults, 'displayMoreForModule' => $displayMoreForModule);
    }
}

