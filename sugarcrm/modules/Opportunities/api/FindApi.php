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

// A simple example class
require_once('include/api/listApi.php');

class FindApi extends ListApi {
    public function registerApiRest() {
        return array(
            'find' => array(
                'reqType' => 'GET',
                'path' => array('find','<module>','?','?'),
                'pathVars' => array('','module','field','value'),
                'method' => 'findByField',
                'shortHelp' => 'Find All Records In A Module By A field',
                'longHelp' => 'include/api/html/find_base_help.html',
            ),
        );
    }

    public function findByField($api, $args) {
        $this->requireArgs($args,array('module','field','value'));

        $bean = $this->loadBean($api, $args, 'list');

        if($bean->getFieldDefinition($args['field']) === false) {
            throw new SugarApiExceptionNotFound('Could not find field: '.$args['field'].' in module: '.$args['module']);
        }

        $options = $this->parseArguments($api, $args, $bean);

        // set the file as the only where part

        $options['where'] = "{$bean->table_name}.{$args['field']}='{$args['value']}'";

        $listQueryParts = $bean->create_new_list_query($options['orderBy'], $options['where'], $options['userFields'], $options['params'], $options['deleted'], '', true, null, false, false);

        if ( $api->security->hasExtraSecurity($bean,'list') ) {
            $api->security->addExtraSecurityList($bean,$listQueryParts);
        }

        return $this->performQuery($api, $args, $bean, $listQueryParts, $options['limit'], $options['offset']);
    }

    /**
     * @param $api
     * @param $args
     * @param string $aclToCheck
     * @return SugarBean
     * @throws SugarApiExceptionNotFound
     * @throws SugarApiExceptionNotAuthorized
     */
    protected function loadBean($api, $args, $aclToCheck = 'read') {

        if(!isset($args['record']) || empty($args['record'])) {
            $args['record'] = null;
        }
        $bean = BeanFactory::getBean($args['module'],$args['record']);

        if ( $bean == FALSE ) {
            // Couldn't load the bean
            throw new SugarApiExceptionNotFound('Could not find record: '.$args['record'].' in module: '.$args['module']);
        }

        if (!$bean->ACLAccess($aclToCheck)) {
            throw new SugarApiExceptionNotAuthorized('No access to edit records for module: '.$args['module']);
        }

        return $bean;
    }
}