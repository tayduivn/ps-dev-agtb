<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
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
require_once('include/api/UnifiedSearchApi.php');
class MeetingsApi extends UnifiedSearchApi {
    public function registerApiRest() {
        return array(
            'moduleSearch' => array(
                'reqType' => 'GET',
                'path' => array('Meetings'),
                'pathVars' => array(''),
                'method' => 'globalSearch',
                'shortHelp' => 'Search records in this module',
                'longHelp' => 'include/api/help/getListModule.html',
            ),

        );
    }


    public function __construct() {
    }
    
    /**
     * This function is the global search
     * @param $api ServiceBase The API class of the request
     * @param $args array The arguments array passed in from the API
     * @return array result set
     */
    public function globalSearch(ServiceBase $api, array $args) {
        require_once('include/SugarSearchEngine/SugarSearchEngineFactory.php');

        // This is required to keep the loadFromRow() function in the bean from making our day harder than it already is.
        $GLOBALS['disable_date_format'] = true;

        $options = $this->parseSearchOptions($api,$args);
        $options['moduleList'] = $options['moduleFilter'] = array('Meetings');

        // determine the correct serach engine, don't pass any configs and fallback to the default search engine if the determiend one is down
        $searchEngine = SugarSearchEngineFactory::getInstance($this->determineSugarSearchEngine($api, $args, $options), array(), false);
        if ( $searchEngine instanceOf SugarSearchEngine) {
            $options['custom_where'] = "meetings.date_start >= NOW()";
            $orderBy = 'date_start ASC, id DESC';
            $orderByData['date_start'] = false;
            $orderByData['id'] = false;
            $options['orderByArray'] = $orderByData;
            $options['orderBy'] = $orderBy;
            $options['resortResults'] = true;
            $recordSet = $this->globalSearchSpot($api,$args,$searchEngine,$options);
            $sortByDateModified = true;
        } else {
            // add an option for filtering out meetings that have already started
            $options['filter']['fieldname'] = 'date_start';
            $options['filter']['type'] = 'range';
            $options['filter']['range']['from'] = date('c');
            $options['filter']['range']['to'] = date('c',strtotime('+6 months'));
            $options['filter']['range']['include_lower'] = true;
            $options['filter']['range']['include_upper'] = true;
            $options['sort'][] = array('date_start' => array('order' => 'asc')); 

            $recordSet = $this->globalSearchFullText($api,$args,$searchEngine,$options);
            $sortByDateModified = false;
        }

        return $recordSet;

    }
}
