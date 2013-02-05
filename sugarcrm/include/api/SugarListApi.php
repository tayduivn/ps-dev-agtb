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

require_once('include/api/SugarApi.php');

abstract class SugarListApi extends SugarApi {
    protected $defaultLimit = 20;
    protected $allowOffsetEnd = true;
    protected $defaultOrderBy = array('date_modified'=>'DESC');
    protected $addDefaultFields = array('id','date_modified');
    protected $checkAcls = true;

    /**
     * This function will parse arguments and hand them back in an array
     * The defaults are set as part of the class ($defaultLimit, $allowOffsetEnd, $defaultOrderBy, $addDefaultFields, $checkAcls)
     * 
     * @param ServiceBase $api The API class (typically RestService)
     * @param array $args The argument array as passed in to the API call, currently checked variables are
     *        max_num, offset, fields, order_by
     * @param SugarBean|null $seed This is the seed bean that feeds the list, if you pass in a null seed then the fields are not validated
     * @return array An array with the options limit, offset, fields and order_by set
     */
    public function parseArguments(ServiceBase $api, array $args, $seed = null) {
        $limit = $this->defaultLimit;
        if ( isset($args['max_num']) ) {
            $limit = (int)$args['max_num'];
        }

        $offset = 0;
        if ( isset($args['offset']) ) {
            if ( $args['offset'] === 'end' ) {
                if ( $this->allowOffsetEnd ) {
                    $offset = 'end';
                } else {
                    $offset = 0;
                }
            } else {
                //Do not allow negative offsets
                $offset = max(0, (int)$args['offset']);
            }
        }

        $userFields = array();
        if (!empty($args['fields'])) {
            $userFields = explode(",", $args["fields"]);
        }
        foreach ( $this->addDefaultFields as $defaultField ) {
            if ( !in_array($defaultField,$userFields) ) {
                $userFields[] = $defaultField;
            }
        }
                    
        $orderBy = '';
        if ( isset($args['order_by']) ) {
            if ( strpos($args['order_by'],',') !== 0 ) {
                // There is a comma, we are ordering by more than one thing
                $orderBys = explode(',',$args['order_by']);
            } else {
                $orderBys = array($args['order_by']);
            }
            $orderByArray = array();
            foreach ( $orderBys as $order ) {
                if ( strpos($order,':') ) {
                    // It has a :, it's specifying ASC / DESC
                    list($column,$direction) = explode(':',$order);
                    if ( strtolower($direction) == 'desc' ) {
                        $direction = 'DESC';
                    } else {
                        $direction = 'ASC';
                    }
                } else {
                    // No direction specified, let's let it fly free
                    $column = $order;
                    $direction = 'ASC';
                }
                if ( $seed != null ) {
                    //BEGIN SUGARCRM flav=pro ONLY
                    if ( $this->checkAcls && !$seed->ACLFieldAccess($column,'list') ) {
                        throw new SugarApiExceptionNotAuthorized('No access to view field: '.$column.' in module: '.$seed->module_dir);
                    }
                    //END SUGARCRM flav=pro ONLY
                    if ( !isset($seed->field_defs[$column]) ) {
                        throw new SugarApiExceptionNotAuthorized('No access to view field: '.$column.' in module: '.$seed->module_dir);
                    }
                }
                
                $orderByArray[$column] = $direction;
            }
            
        } else {
            $orderByArray = $this->defaultOrderBy;
        }

        return array('limit' => $limit,
                     'offset' => $offset,
                     'fields' => $userFields,
                     'orderBy' => $orderByArray,
        );
        
    }

    /**
     * This function will convert an order by array returned by parseArguments into a SQL string
     * @param array $orderByArray an array of $column => $direction
     * @return string A SQL string of the order by.
     */
    public function convertOrderByToSql(array $orderByArray) {
        $sqlArray = array();
        foreach ( $orderByArray as $column => $direction ) {
            $sqlArray[] = $column." ".$direction;
        }
        
        return implode(',',$sqlArray);
    }
}