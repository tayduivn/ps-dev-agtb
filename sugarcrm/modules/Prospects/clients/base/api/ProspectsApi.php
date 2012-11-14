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

require_once('clients/base/api/ModuleApi.php');

class ProspectsApi extends ModuleApi {
    public function registerApiRest() {
        return array(
            'interactions' => array(
                'reqType' => 'GET',
                'path' => array('Prospects','?', 'interactions'),
                'pathVars' => array('module', 'record'),
                'method' => 'interactions',
                'shortHelp' => '',
                'longHelp' => '',
            ),
        );
    }

    public function interactions($api, $args)
    {
        $record = $this->getBean($api, $args);
        $data = array('calls' => array(),'meetings' => array(),'emails' => array());

        // Limit here so that we still get the full count for interactions.
        $limit = 5;

        $calls = $this->getBeanRelationship($api, $args, $record, 'calls', null);
        $meetings = $this->getBeanRelationship($api, $args, $record, 'meetings', null);

        $data['calls'] = array('count' => count($calls), 'data' => array());
        $i = 0;
        while($i < $limit && isset($calls[$i])) {
            $data['calls']['data'][] = $calls[$i];
            $i++;
        }

        $data['meetings'] = array('count' => count($meetings), 'data' => array());
        $i = 0;
        while($i < $limit && isset($meetings[$i])) {
            $data['meetings']['data'][] = $meetings[$i];
            $i++;
        }

        return $data;
    }

    protected function getBeanRelationship($api, $args, $bean, $relationship, $limit = 5, $query = array())
    {
        // Load up the relationship
        if (!$bean->load_relationship($relationship)) {
            // The relationship did not load, I'm guessing it doesn't exist
            throw new SugarApiExceptionNotFound('Could not find a relationship name ' . $relationship);
        }
        // Figure out what is on the other side of this relationship, check permissions
        $linkModuleName = $bean->$relationship->getRelatedModuleName();
        $linkSeed = BeanFactory::newBean($linkModuleName);
        if (!$linkSeed->ACLAccess('view')) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: '.$linkModuleName);
        }

        $relationshipData = $bean->$relationship->query($query);
        $rowCount = 1;

        $data = array();
        foreach ($relationshipData['rows'] as $id => $value) {
            $rowCount++;
            $bean = BeanFactory::getBean(ucfirst($relationship), $id);
            $data[] = $this->formatBean($api, $args, $bean);
            if (!is_null($limit) && $rowCount == $limit) {
                // We have hit our limit.
                break;
            }
        }
        return $data;
    }

}
