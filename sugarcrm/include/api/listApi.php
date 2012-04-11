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

require_once('data/BeanFactory.php');

class listApi extends SugarApi {
    public function registerApiRest() {
        return array(
            'listModules' => array(
                'reqType' => 'GET',
                'path' => array('<module>'),
                'pathVars' => array('module'),
                'method' => 'listModule',
                'shortHelp' => 'List records in this module',
                'longHelp' => 'include/api/html/module_list_help.html',
            ),
            'searchModules' => array(
                'reqType' => 'GET',
                'path' => array('<module>','search','?'),
                'pathVars' => array('module','','basicSearch'),
                'method' => 'listModule',
                'shortHelp' => 'Searches records in this module',
                'longHelp' => 'include/api/html/module_list_search_help.html',
            ),
        );
    }

    public function listModule($api, $args) {
        global $current_user;

        $this->requireArgs($args,array('module'));

        // Load up a seed bean
        $seed = BeanFactory::getBean($args['module']);
        if ( ! $api->security->canAccessModule($seed,'view') ) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: '.$args['module']);
        }

        $deleted = false;
        if ( isset($args['deleted']) && ( strtolower($args['deleted']) == 'true' || $args['deleted'] == '1' ) ) {
            $deleted = true;
        }

        $maxResults = $GLOBALS['sugar_config']['list_max_entries_per_page'];
        if ( isset($args['maxResult']) ) {
            $maxResults = (int)$args['maxResult'];
        }

        $offset = 0;
        if ( isset($args['offset']) ) {
            if ( $args['offset'] === 'end' ) {
                $offset = 'end';
            } else {
                $offset = (int)$args['offset'];
            }
        }
        
        $userFields = null;
        if ( isset($args['fields'])) {
            $userFields = explode(",", $args["fields"]);
            
            foreach ( $userFields as $field ) {
                if ( !$api->security->canAccessField($seed,$field,'list') || !isset($seed->field_defs[$column]) ) {
                    throw new SugarApiExceptionNotAuthorized('No access to view field: '.$column.' in module: '.$args['module']);
                }
            }
            
            if ( ! in_array('date_modified',$userFields) ) {
                $userFields[] = 'date_modified';
            }

        }


        $orderBy = '';
        if ( isset($args['orderBy']) ) {
            if ( strpos($args['orderBy'],',') !== 0 ) {
                // There is a comma, we are ordering by more than one thing
                $orderBys = explode(',',$args['orderBy']);
            } else {
                $orderBys = array($args['orderBy']);
            }
            $orderByArray = array();
            foreach ( $orderBys as $order ) {
                if ( strpos($order,':') ) {
                    // It has a :, it's specifying ASC / DESC
                    list($column,$direction) = explode(':',$order);
                } else {
                    // No direction specified, let's let it fly free
                    $column = $order;
                    $direction = 'ASC';
                }
                if ( !$api->security->canAccessField($seed,$column,'list') || !isset($seed->field_defs[$column]) ) {
                    throw new SugarApiExceptionNotAuthorized('No access to view field: '.$column.' in module: '.$args['module']);
                }
                
                $orderByArray[] = $column.' '.$direction;
            }
            
            $orderBy = implode(',',$orderByArray);
        }

        $where = '';
        // TODO: Upgrade this to use the full-text search for basic searches
        if ( isset($args['basicSearch']) ) {
            $tableName = $seed->table_name;
            $basicSearch = $GLOBALS['db']->quote($args['basicSearch']);
            if ( is_a($seed,'Person') ) {
                // Search by first_name, last_name
                if ( strpos($args['basicSearch'],' ') !== false ) {
                    // There is a space in there, search by first name and last name
                    list($leftPart,$rightPart) = explode(' ',$args['basicSearch']);
                    $leftPart = $GLOBALS['db']->quote($leftPart);
                    $rightPart = $GLOBALS['db']->quote($rightPart);
                    
                    $where = "( {$tableName}.first_name LIKE '{$leftPart}%' AND {$tableName}.last_name LIKE '{$rightPart}%' ) OR ( {$tableName}.last_name LIKE '{$leftPart}%' AND {$tableName}.first_name LIKE '{$right_part}%' )";
                } else {
                    // No space, search by first name or last name
                    $where = "{$tableName}.first_name LIKE '{$basicSearch}%' OR {$tableName}.last_name LIKE '{$basicSearch}%' ";
                }
            } else {
                // Search by name
                $where = "{$tableName}.name LIKE '{$basicSearch}%' ";
            }
        }

        $params = array();
        if ( isset($args['favorites']) && $args['favorites'] ) {
            $params['favorites'] = true;
        }
        
        $listQueryParts = $seed->create_new_list_query($orderBy, $where, $userFields, $params, $deleted, '', true, null, false, false);

        if ( $api->security->hasExtraSecurity($seed,'list') ) {
            $api->security->addExtraSecurityList($seed,$listQueryParts);
        }
        
        $reply = $this->performQuery($api, $args, $seed, $listQueryParts, $maxResults, $offset);
        if ( $reply['count'] > $maxResults ) {
            $nextOffset = $offset + $maxResults;
        } else {
            $nextOffset = 0;
        }
        
        $response = array();
        $response["next_offset"] = $nextOffset;
        $response["result_count"] = $reply['count'];
        $response["records"] = $reply['records'];

        return $response;
    }


    protected function performQuery($api, $args, $seed, $queryParts, $maxResults, $offset) {
        $query = $queryParts['select'] . $queryParts['from'] . $queryParts['where'] . $queryParts['order_by'];

        // If we want the last page, here is the magic to get there.
        if($offset === 'end'){
            $countQuery = $seed->create_list_count_query($query);
            $ret = $GLOBALS['db']->query($countQuery);
            if ( $row = $GLOBALS['db']->fetchByAssoc($ret) ) {
                $totalCount = $row['c'];
            } else {
                $totalCount = 0;
            }
            $offset = (floor(($totalCount -1) / $limit)) * $limit;
        }
        
        $ret = $GLOBALS['db']->limitQuery($query, $offset, $maxResults + 1);
        
        $records = array();
        $count = 0;

        while($row = $GLOBALS['db']->fetchByAssoc($ret)) {
            if ( $count < $maxResults ) {
                $records[$row['id']] = $seed->convertRow($row);
            }
            $count++;
        }

        if ( $count == 0 ) {
            // Empty query
            return array('count' => 0, 'records' => array());
        }

        if ( !empty($queryParts['secondary_select']) ) {
            // There are some secondary selects we need to run to get the whole dataset
            $idList = "('".implode("','",array_keys($records))."')";
            
            $secondaryQuery = $queryParts['secondary_select'] . $queryParts['secondary_from'] . ' WHERE '.$seed->table_name.'.id IN ' .$idList;
            
            $ret = $GLOBALS['db']->query($secondaryQuery);
            while ( $row = $GLOBALS['db']->fetchByAssoc($ret) ) {
                foreach( $row as $name => $value ) {
                    if ( $name == 'ref_id' ) {
                        // It's the record id, we already have that bit.
                        continue;
                    }
                    $records[$row['ref_id']][$name] = $value;
                    if ( isset($records[$row['ref_id']]['secondary_select_count']) ) {
                        $records[$row['ref_id']]['secondary_select_count']++;
                    } else {
                        $records[$row['ref_id']]['secondary_select_count'] = 1;
                    }
                }
            }
        }
        
        return array('count' => $count, 'records' => $records );
    }
}