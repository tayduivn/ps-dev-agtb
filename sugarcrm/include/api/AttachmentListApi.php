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

require_once('include/api/ListApi.php');

class AttachmentListApi extends ListApi {
    public function registerApiRest() {
        return array(
            'listAttachments' => array(
                'reqType' => 'GET',
                'path' => array('<module>','?', 'link','attachments'),
                'pathVars' => array('module','record','', ''),
                'method' => 'listAttachments',
                'shortHelp' => 'List attachments related to this module',
                'longHelp' => 'include/api/html/module_attach_help.html',
            ),
        );
    }

    public function __construct() {
        $this->defaultLimit = $GLOBALS['sugar_config']['list_max_entries_per_subpanel'];
    }
    
    public function listAttachments($api, $args) {
        // Load up the bean
        $record = BeanFactory::getBean($args['module'], $args['record']);

        if ( empty($record) ) {
            throw new SugarApiExceptionNotFound('Could not find parent record '.$args['record'].' in module '.$args['module']);
        }
        if ( ! $record->ACLAccess('view') ) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: '.$args['module']);
        }
        // Load up the relationship
        if ( ! $record->load_relationship('notes') ) {
            // The relationship did not load, I'm guessing it doesn't exist
            throw new SugarApiExceptionNotFound('Could not find a relationship name notes');
        }
        // Figure out what is on the other side of this relationship, check permissions
        $linkModuleName = $record->notes->getRelatedModuleName();
        $linkSeed = BeanFactory::newBean($linkModuleName);
        if ( ! $linkSeed->ACLAccess('view') ) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: '.$linkModuleName);
        }

        $options = $this->parseArguments($api, $args, $linkSeed);

        $notes = $record->notes->query(array('where'=>array('lhs_field'=>'filename','operator'=>'<>','rhs_value'=>"''")));
        $rowCount = 1;

        $data['records'] = array();
        foreach ( $notes['rows'] as $noteId => $ignore ) {
            $rowCount++;
            $note = BeanFactory::getBean('Notes',$noteId);
            $data['records'][] = $this->formatBean($api,$args,$note);
            if ( $rowCount == $options['limit'] ) {
                // We have hit our limit.
                break;
            }
        }
        return $data['records'];
    }
}
