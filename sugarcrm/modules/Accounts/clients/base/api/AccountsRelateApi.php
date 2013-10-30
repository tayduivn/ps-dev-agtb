<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once 'clients/base/api/RelateApi.php';

class AccountsRelateApi extends RelateApi
{
    public function registerApiRest() {
        return array(
            'filterRelatedRecords' => array(
                'reqType' => 'GET',
                'path' => array('Accounts', '?', 'link', '?', 'filter'),
                'pathVars' => array('module', 'record', '', 'link_name', ''),
                'jsonParams' => array('filter'),
                'method' => 'filterRelated',
                'shortHelp' => 'Lists related filtered records.',
                'longHelp' => 'include/api/help/module_record_link_link_name_filter_get_help.html',
            )
        );
    }

    public function filterRelated(ServiceBase $api, array $args)
    {
        if (empty($args['include_child_items']) || !in_array($args['link_name'], array('calls', 'meetings'))) {
            return parent::filterRelated($api, $args);
        }

        $api->action = 'list';

        $record = BeanFactory::retrieveBean($args['module'], $args['record']);

        if (empty($record)) {
            throw new SugarApiExceptionNotFound(
                sprintf(
                    'Could not find parent record %s in module: %s',
                    $args['record'],
                    $args['module']
                )
            );
        }

        if (!$record->ACLAccess('view')) {
            throw new SugarApiExceptionNotAuthorized(
                sprintf(
                    'No access to view records for module: %s',
                    $args['module']
                )
            );
        }

        $linkName = $args['link_name'];
        if (!$record->load_relationship($linkName)) {
            throw new SugarApiExceptionNotFound(
                sprintf(
                    'Could not find a relationship named: %s',
                    $args['link_name']
                )
            );
        }

        $linkModuleName = $record->$linkName->getRelatedModuleName();
        $linkSeed = BeanFactory::getBean($linkModuleName);
        if (!$linkSeed->ACLAccess('list')) {
            throw new SugarApiExceptionNotAuthorized(
                sprintf(
                    'No access to list records for module: %s',
                    $linkModuleName
                )
            );
        }

        $options = $this->parseArguments($api, $args, $linkSeed);
        $q = self::getQueryObject($linkSeed, $options);
        if (!isset($args['filter']) || !is_array($args['filter'])) {
            $args['filter'] = array();
        }

        self::addFilters($args['filter'], $q->where(), $q);

        $q->joinTable('accounts')
            ->on()
            ->equals('accounts.id', $record->id)
            ->equals('accounts.deleted', 0);

        // FIXME: there should be the ability to specify from which related module
        // the child items should be loaded
        $q->joinTable('accounts_contacts', array('alias' => 'ac', 'joinType' => 'LEFT'))
            ->on()
            ->equalsField('ac.account_id', 'accounts.id')
            ->equals('ac.deleted', 0);

        // FIXME: this informations should be dynamically retrieved
        if ($linkModuleName === 'Meetings') {
            $childModuleTable = 'meetings';
            $childRelationshipTable = 'meetings_contacts';
            $childRelationshipAlias = 'mc';
            $childLhsColumn = $childModuleTable . '.id';
            $childRhsColumn = $childRelationshipAlias . '.meeting_id';

        } else {
            $childModuleTable = 'calls';
            $childRelationshipTable = 'calls_contacts';
            $childRelationshipAlias = 'cc';
            $childLhsColumn = $childModuleTable . '.id';
            $childRhsColumn = $childRelationshipAlias . '.call_id';
        }

        $q->joinTable($childRelationshipTable, array('alias' => $childRelationshipAlias, 'joinType' => 'LEFT'))
            ->on()
            ->equalsField($childRhsColumn, $childLhsColumn)
            ->equals($childRelationshipAlias . '.deleted', 0);

        $where = $q->where()->queryOr();
        $where->queryAnd()->equals($childModuleTable . '.parent_type', 'Contacts')->equalsField($childModuleTable . '.parent_id', 'ac.contact_id');
        $where->queryAnd()->equals($childModuleTable . '.parent_type', 'Contacts')->equalsField($childModuleTable . '.parent_id', $childRelationshipAlias . '.contact_id');
        $where->queryAnd()->equals($childModuleTable . '.parent_type', 'Accounts')->equalsField($childModuleTable . '.parent_id', 'accounts.id');

        return $this->runQuery($api, $args, $q, $options, $linkSeed);
    }
}
