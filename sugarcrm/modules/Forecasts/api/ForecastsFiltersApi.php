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
            )
        );
        return $parentApi;
    }

    public function filters($api, $args) {
        // placeholder for filters
        // todo: really make this work
        global $app_list_strings, $current_language;
        $app_list_strings = return_app_list_strings_language($current_language);

        return array(
            'timeperiod_id' => array(
                'label' => 'Forecast Period:',
                'default' => TimePeriod::getCurrentId(),
                'options' => TimePeriod::get_not_fiscal_timeperiods_dom(),
            ),
            'category' => array(
                'label' => 'Forecast Category:',
                'default' => 'Committed',
                'options' => array(
                    'Committed' => 'Committed',
                    'Pipeline' => 'Pipeline',
                ),
            ),
        );
    }

    public function chartOptions($api, $args) {
        // placeholder for filters
        // todo: really make this work
        return array(
            'group_by' => array(
                'label' => 'Group By:',
                'default' => 'sales_stage',
                'options' => array(
                    'forecast' => 'Forecast Category',
                    'sales_stage' => 'Sales Stage',
                    'probability' => 'Probability'
                ),
            ),
            'dataset' => array(
                'label' => 'Data Set:',
                'default' => 'likely',
                'options' => array(
                    'likely' => 'Likely',
                    'best' => 'Best',
                    'worst' => 'Worst'
                ),
            )
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
        global $app_list_strings, $current_language, $current_user;
        $app_list_strings = return_app_list_strings_language($current_language);

        $id = clean_string($args['userId']);

        // Boolean do we want to return a Parent link with the result set
        $returnParent = false;

        $sql = $GLOBALS['db']->getRecursiveSelectSQL('users', 'id', 'reports_to_id','id, user_name, first_name, last_name, reports_to_id, _level',
            false, "id = '{$id}' AND status = 'Active' AND deleted = 0"
        );

        $result = $GLOBALS['db']->query($sql);

        // Final array to be returned
        $treeData = '';

        $flatUsers = array();
        while($row = $GLOBALS['db']->fetchByAssoc($result))
        {
            if(empty($users[$row['_level']]))  {
                $users[$row['_level']] = array();
            }

            $openClosed = ($row['_level'] == 1) ? 'open' : 'closed';

            $fullName = $this->getFullName($row['first_name'], $row['last_name']);

            $user = array(
                'data' => $fullName,
                'children' => array(),
                'metadata' => array(
                    "id" => $row['id'],
                    "full_name" => $fullName,
                    "first_name" => $row['first_name'],
                    "last_name" => $row['last_name'],
                    "reports_to_id" => $row['reports_to_id'],
                    "level" => $row['_level']
                ),
                'state' => $openClosed,
                'attr' => array(
                    // set all users to rep by default
                    'rel' => 'rep'
                )
            );

            // Set the main user id as the root for treeData
            if($user['metadata']['id'] == $current_user->id) {
                $user['attr']['rel'] = 'root';
                $treeData = $user;
            } else if($user['metadata']['id'] == $id) {
                // if this is the user requested in the URL,
                // but not the currently-logged-in user

                $user['attr']['rel'] = 'manager';
                $treeData = $user;

                // we want a parent node added to the return set
                $returnParent = true;
            } else {
                $flatUsers[] = $user;
            }
        }

        // TEMPORARY SOLUTION to lack of setting limits on recursive SQL function
        // Maximum depth of children to return
        // 2 = direct children of the parent are returned, children of those children are not
        $maxLevel = 2;

        // if this is empty, something is really wrong
        if(!empty($treeData))
            $treeData['children'] = $this->getChildren( $treeData['metadata']['id'], $flatUsers, $maxLevel );

        // Check to see if root user has children
        // if no children, the user tree will be hidden anyways, so don't bother getting Opportunities
        // if so, we want to grab any Opportunities the user might have
        if(!empty($treeData['children'])) {
            $result = $GLOBALS['db']->query("SELECT count(id) ct FROM opportunities WHERE assigned_user_id = '{$id}' ");
            $row = $GLOBALS['db']->fetchByAssoc($result);

            if($row['ct'] > 0) {
                global $current_language;
                //grab language defs
                $current_module_strings = return_module_language($current_language, 'Forecasts');

                $myOpp = array(
                    'data' => $current_module_strings['LBL_TREE_MY_OPPORTUNITIES'],
                    'children' => array(),
                    // Give myOpp the same metadata as the root Manager user
                    'metadata' => array(
                        "id" => $treeData['metadata']['id'],
                        "full_name" => $treeData['metadata']['full_name'],
                        "first_name" => $treeData['metadata']['first_name'],
                        "last_name" => $treeData['metadata']['last_name'],
                        "reports_to_id" => $treeData['metadata']['reports_to_id'],
                        "level" => "1"
                    ),
                    'state' => 'closed',
                    'attr' => array(
                        'rel' => 'my_opportunities'
                    )
                );
                // add myOpp to the beginning of children
                array_unshift($treeData['children'], $myOpp);
            }

            // Since user has children,
            // handle if user clicked a manager and we need to return a Parent link in the set
            if($returnParent)  {
                $parentUser = new User();
                $parentUser->retrieve($treeData['metadata']['reports_to_id']);

                if(!empty($parentUser->id)) {
                    $parentNode = array(
                        'data' => $current_module_strings['LBL_TREE_PARENT'],
                        'children' => array(),
                        // Give myOpp the same metadata as the root Manager user
                        'metadata' => array(
                            "id" => $parentUser->id,
                            "full_name" => $parentUser->full_name,
                            "first_name" => $parentUser->first_name,
                            "last_name" => $parentUser->last_name,
                            "reports_to_id" => $parentUser->reports_to_id,
                            "level" => "1"
                        ),
                        'state' => 'closed',
                        'attr' => array(
                            'rel' => 'parent_link'
                        )
                    );

                    // add parentNode to the beginning of treeData and put previous treeData
                    // as an element of the treeData array
                    $treeData = array($parentNode,$treeData);
                }
            }
        }

        return $treeData;
    }

    /***
     * Recursive function to get all children of a specific parent $id
     * given a list of $users
     * @param $id {int} ID value of the parent user
     * @param $users {Array} of users
     * @param $maxLevel {int} max level a user can be before not being included in tree data
     * @return array of child users
     */
    public function getChildren( $id, $users, $maxLevel ) {
        $retChildren = array();
        foreach( $users as $user ) {
            if( $user['metadata']['reports_to_id'] == $id ) {
                $user['children'] = $this->getChildren( $user['metadata']['id'] , $users, $maxLevel );

                // we want to set users as 'managers' if they have children
                if(!empty($user['children']))
                    $user['attr']['rel'] = 'manager';

                //but if their level is at/over our maxLevel, DO NOT WANT KIDS
                if($user['metadata']['level'] >= $maxLevel)
                    $user['children'] = array();

                $retChildren[] = $user;
            }
        }
        return $retChildren;
    }

    /***
     * Simple function that returns a full name based on first name and last name
     *
     * @param $first
     * @param $last
     * @return string
     */
    function getFullName($first, $last) {
        return (empty($last)) ? $first : $first . ' ' . $last;
    }

}
