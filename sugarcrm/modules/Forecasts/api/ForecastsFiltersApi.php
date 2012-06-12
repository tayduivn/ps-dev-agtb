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

require_once('include/api/ModuleApi.php');

class ForecastsFiltersApi extends ModuleApi {

    public function __construct()
    {

    }

    public function registerApiRest()
    {
        $parentApi = parent::registerApiRest();
        //Extend with test method
        $parentApi= array (
            'filters' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts','filters'),
                'pathVars' => array('',''),
                'method' => 'filters',
                'shortHelp' => 'forecast filters',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastFiltersApi.html#filters',
            ),
            'chartoptions' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts','chartoptions'),
                'pathVars' => array('',''),
                'method' => 'chartOptions',
                'shortHelp' => 'forecasting chart options',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastFiltersApi.html#chartOptions',
            ),
            'reportees' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts', 'reportees', '?'),
                'pathVars' => array('','','userId'),
                'method' => 'getReportees',
                'shortHelp' => 'Gets reportees to a user by id',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastFiltersApi.html#reportees',
            ),
        );
        return $parentApi;
    }

    public function filters($api, $args) {
        // placeholder for filters
        // todo: really make this work
        global $app_list_strings, $current_language;
        $app_list_strings = return_app_list_strings_language($current_language);

        return array(
            'timeperiods' => array(
                'label' => 'Forecast Period:',
                'default' => TimePeriod::getCurrentId(),
                'options' => TimePeriod::get_timeperiods_dom(),
            ),
            'stages' => array(
                'label' => 'Sales Stage:',
                'options' => $app_list_strings['sales_stage_dom'],
            ),
            'probabilities' => array(
                'label' => 'Probability (>=):',
                'options' => $app_list_strings['sales_probability_dom'],
            ),
        );
    }

    public function chartOptions($api, $args) {
        // placeholder for filters
        // todo: really make this work
        return array(
            'horizontal' => array(
                'label' => 'Horizontal (x):',
                'options' => array(
                    'x0' => 'Team Members',
                    'x1' => 'Account',
                    'x2' => 'Channel',
                    'x3' => 'Line Items',
                    'x4' => 'Month',
                ),
            ),
            'vertical' => array(
                'label' => 'Vertical (y):',
                'options' => array(
                    'y0' => 'Revenue',
                    'y1' => 'Number of Units',
                ),
            ),
            'groupby' => array(
                'label' => 'Group By:',
                'options' => array(
                    'y0' => 'Sales Stage',
                    'y1' => 'Revenue Type',
                ),
            ),
        );
    }

    /***
     * Returns a hierarchy of users reporting to the current user
     *
     * @param $api
     * @param $args
     * @return string
     */
    public function getReportees($api, $args) {
        // TEMPORARY SOLUTION to lack of setting limits on recursive SQL function
        $maxLevel = 2;

        $id = $args['userId'];

        $sql = $GLOBALS['db']->getRecursiveSelectSQL('users', 'id', 'reports_to_id','id, user_name, first_name, last_name, reports_to_id, _level',
            false, "id = '{$id}' AND status = 'Active' AND deleted = 0"
        );

        $result = $GLOBALS['db']->query($sql);

        // Final array to be returned
        $treeData = '';

        $flatUsers = array();
        while($row = $GLOBALS['db']->fetchByAssoc($result))
        {
            if( $row['_level'] <= $maxLevel )
            {
                if(empty($users[$row['_level']]))  {
                    $users[$row['_level']] = array();
                }

                $openClosed = ($row['_level'] == 1) ? 'open' : 'closed';

                $fullName = (empty($row['last_name'])) ? $row['first_name'] : $row['first_name'] . ' ' . $row['last_name'];

                $user = array(
                    'data' => $fullName,
                    'children' => array(),
                    'metadata' => array(
                        "id" => $row['id'],
                        "full_name" => $fullName,
                        "first_name" => $row['first_name'],
                        "last_name" => $row['last_name'],
                        "reports_to_id" => $row['reports_to_id']
                    ),
                    'state' => $openClosed
                );

                // Set the main user id as the root for treeData
                if($user['metadata']['id'] == $id)
                {
                    $treeData = $user;
                } else {
                    $flatUsers[] = $user;
                }
            }
        }

        // if this is empty, something is really wrong
        if(!empty($treeData))
            $treeData['children'] = $this->getChildren( $treeData['metadata']['id'], $flatUsers );

        return $treeData;
    }

    /***
     * Recursive function to get all children of a specific parent $id
     * given a list of $users
     * @param $id {int} ID value of the parent user
     * @param $users {Array} of users
     * @return array of child users
     */
    public function getChildren( $id, $users ) {
        $retChildren = array();
        foreach( $users as $user ) {
            if( $user['metadata']['reports_to_id'] == $id ) {
                $user['children'] = $this->getChildren( $user['metadata']['id'] , $users );
                $retChildren[] = $user;
            }
        }
        return $retChildren;
    }

}
