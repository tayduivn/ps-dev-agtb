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
require_once('include/api/SugarListApi.php');

class ListApi extends SugarListApi {
    public function registerApiRest() {
        return array(
        );
    }

    public function __construct() {
        $this->defaultLimit = $GLOBALS['sugar_config']['list_max_entries_per_page'];
    }

    public function parseArguments($api, $args, $seed) {

        $parsed = parent::parseArguments($api, $args, $seed);

        $deleted = false;
        if ( isset($args['deleted']) && ( strtolower($args['deleted']) == 'true' || $args['deleted'] == '1' ) ) {
            $deleted = true;
        }

        $whereParts = array();
        // TODO: Upgrade this to use the full-text search for basic searches
        if ( isset($args['q']) ) {
            $args['q'] = trim($args['q']);
            $tableName = $seed->table_name;
            $basicSearch = $GLOBALS['db']->quote($args['q']);
            if ( is_a($seed,'Person') ) {
                // Search by first_name, last_name
                if ( strpos($args['q'],' ') !== false ) {
                    // There is a space in there, search by first name and last name
                    list($leftPart,$rightPart) = explode(' ',$args['q']);
                    $leftPart = $GLOBALS['db']->quote($leftPart);
                    $rightPart = $GLOBALS['db']->quote($rightPart);
                    
                    $whereParts[] = "( {$tableName}.first_name LIKE '{$leftPart}%' AND {$tableName}.last_name LIKE '{$rightPart}%' ) OR ( {$tableName}.last_name LIKE '{$leftPart}%' AND {$tableName}.first_name LIKE '{$right_part}%' )";
                } else {
                    // No space, search by first name or last name
                    $whereParts[] = "{$tableName}.first_name LIKE '{$basicSearch}%' OR {$tableName}.last_name LIKE '{$basicSearch}%' ";
                }
            } else {
                // Search by name
                $whereParts[] = "{$tableName}.name LIKE '{$basicSearch}%' ";
            }
        }
        $params = array();
        if ( isset($args['favorites']) && $args['favorites'] ) {
            $params['favorites'] = true;
        }

        if ( count($whereParts) > 0 ) {
            $where = '('.implode(") AND (",$whereParts).')';
        } else {
            $where = '';
        }


        return array('deleted'=>$deleted,
                     'limit'=>$parsed['limit'],
                     'offset'=>$parsed['offset'],
                     'userFields'=>$parsed['fields'],
                     'orderBy'=>$this->convertOrderByToSql($parsed['orderBy']),
                     'params'=>$params,
                     'whereParts'=>$whereParts,
                     'where'=>$where,
        );
                     
        
    }

    public function listModule($api, $args) {
        $this->requireArgs($args,array('module'));

        // Load up a seed bean
        $seed = BeanFactory::getBean($args['module']);
        if ( ! $seed->ACLAccess('list') ) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: '.$args['module']);
        }
        
        $options = $this->parseArguments($api, $args, $seed);

        $listQueryParts = $seed->create_new_list_query($options['orderBy'], $options['where'], $options['userFields'], $options['params'], $options['deleted'], '', true, null, false, false);
        
        return $this->performQuery($api, $args, $seed, $listQueryParts, $options['limit'], $options['offset']);
    }


    protected function performQuery($api, $args, $seed, $queryParts, $limit, $offset) {
        $query = $queryParts['select'] . $queryParts['from'] . $queryParts['where'] . $queryParts['order_by'];
        $countQuery = 'SELECT COUNT(*) c ' . $queryParts['from'] . $queryParts['where'] . $queryParts['order_by'];

        // If we want the last page, here is the magic to get there.
        if($offset === 'end'){
            $ret = $GLOBALS['db']->query($countQuery);
            if ( $row = $GLOBALS['db']->fetchByAssoc($ret) ) {
                $totalCount = $row['c'];
            } else {
                $totalCount = 0;
            }
            $offset = (floor(($totalCount -1) / $limit)) * $limit;
        }
        
        $ret = $GLOBALS['db']->limitQuery($query, $offset, $limit + 1);
        
        $records = array();
        $count = 0;

        while($row = $GLOBALS['db']->fetchByAssoc($ret)) {
            if ( $count < $limit ) {
                $records[$row['id']] = $seed->convertRow($row);
            }
            $count++;
        }

        if ( $count == 0 ) {
            // Empty query
            return array('next_offset' => -1, 'records' => array());
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

        if ( $count > $limit ) {
            $nextOffset = $offset + $limit;
        } else {
            $nextOffset = -1;
        }
        
        $response = array();
        $response["next_offset"] = $nextOffset;
        $response['args'] = $args;
        $response['records'] = array();
        // Format the records to match every other record result out there
        $api->action = 'list';
        foreach ( $records as $row ) {
            $rowBean = clone $seed;
            $rowBean->populateFromRow($row);
            $response['records'][] = $this->formatBean($api,$args,$rowBean);
        }

        return $response;
    }

    protected function performSearch(ServiceBase $api, $args, SugarBean $seed, $searchTerm, $options) {
        require_once('include/SugarSearchEngine/SugarSearchEngineFactory.php');
        $searchEngine = SugarSearchEngineFactory::getInstance();
        //Default db search will be handled by the spot view, everything else by fts.
        if($searchEngine instanceOf SugarSearchEngine) {
        }
        
    }
}
