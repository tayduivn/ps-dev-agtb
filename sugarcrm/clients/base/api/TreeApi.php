<?php
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
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

require_once 'clients/base/api/FilterApi.php';

class TreeApi extends FilterApi
{
    /**
     * Depth of the tree by default.
     *
     * @var integer
     */
    public $defaultTreeDepth = 5;

    public function registerApiRest()
    {
        return array(
            'filterModuleSubTree' => array(
                'reqType' => 'GET',
                'path' => array('<module>', '?', 'tree', '?'),
                'pathVars' => array('module', 'record', '', 'link_name'),
                'method' => 'filterSubTree',
            ),
            'filterModuleTree' => array(
                'reqType' => 'GET',
                'path' => array('<module>', 'tree', '?'),
                'pathVars' => array('module', '', 'link_name'),
                'method' => 'filterTree',
            ),
        );
    }

    protected function parseArguments(ServiceBase $api, array $args, SugarBean $seed = null)
    {
        $options = parent::parseArguments($api, $args, $seed);
        // Set up the defaults
        $options['depth'] = $this->defaultTreeDepth;

        if (!empty($args['depth'])) {
            $options['depth'] = (int)$args['depth'];
        }
        return $options;
    }

    protected function runQuery(ServiceBase $api, array $args, SugarQuery $q, array $options, SugarBean $seed)
    {
        $data = parent::runQuery($api, $args, $q, $options, $seed);

        if ($options['depth'] > 0) {
            $options['depth']--;
            foreach ($data['records'] as $i => $row) {
                $record = $seed->getCleanCopy();
                $record->loadFromRow($row, true);

                $q = self::getQueryObject($seed, $options);
                $q->joinSubpanel($record, $args['link_name'], array(
                    'joinType' => 'INNER',
                    'ignoreRole' => !empty($args['ignore_role'])
                ));
                self::addFilters($args['filter'], $q->where(), $q);

                $data['records'][$i][$args['link_name']] = $this->runQuery($api, $args, $q, $options, $seed);
            }
        }
        return $data;
    }


    public function filterSubTree($api, $args)
    {
        $this->requireArgs($args, array('module', 'record', 'link_name'));
        // Load the parent bean.
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
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: ' . $args['module']);
        }
        // Load the relationship.
        $linkName = $args['link_name'];
        if (!$record->load_relationship($linkName)) {
            // The relationship did not load.
            throw new SugarApiExceptionNotFound('Could not find a relationship named: ' . $args['link_name']);
        }
        $linkModuleName = $record->$linkName->getRelatedModuleName();
        if ($linkModuleName != $record->module_name) {
            throw new SugarApiExceptionNotFound('Could not find self referencing in relationship named: ' . $linkName);
        }
        $linkSeed = BeanFactory::getBean($linkModuleName);
        if (!$linkSeed->ACLAccess('list')) {
            throw new SugarApiExceptionNotAuthorized('No access to list records for module: ' . $linkModuleName);
        }
        $options = $this->parseArguments($api, $args, $linkSeed);

        // If they don't have fields selected we need to include any link fields
        // for this relationship
        if (empty($args['fields']) && is_array($linkSeed->field_defs)) {
            $relatedLinkName = $record->$linkName->getRelatedModuleLinkName();
            $options['linkDataFields'] = array();
            foreach ($linkSeed->field_defs as $field => $def) {
                if (empty($def['rname_link']) || empty($def['link'])) {
                    continue;
                }
                if ($def['link'] != $relatedLinkName) {
                    continue;
                }
                // It's a match
                $options['linkDataFields'][] = $field;
                $options['select'][] = $field;
            }
        }

        if (!isset($args['filter']) || !is_array($args['filter'])) {
            $args['filter'] = array();
        }

        $q = self::getQueryObject($linkSeed, $options);
        $q->joinSubpanel($record, $linkName, array(
            'joinType' => 'INNER',
            'ignoreRole' => !empty($args['ignore_role'])
        ));
        self::addFilters($args['filter'], $q->where(), $q);

        return $this->runQuery($api, $args, $q, $options, $linkSeed);
    }

    public function filterTree($api, $args)
    {
        $this->requireArgs($args, array('module', 'link_name'));
        // Load up a seed bean
        $seed = BeanFactory::getBean($args['module']);
        if (!$seed->ACLAccess('list')) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: ' . $args['module']);
        }
        // Load the relationship.
        $linkName = $args['link_name'];
        if (!$seed->load_relationship($linkName)) {
            // The relationship did not load.
            throw new SugarApiExceptionNotFound('Could not find a relationship named: ' . $linkName);
        }
        $linkModuleName = $seed->$linkName->getRelatedModuleName();
        if ($linkModuleName != $seed->module_name) {
            throw new SugarApiExceptionNotFound('Could not find self referencing in relationship named: ' . $linkName);
        }
        $options = $this->parseArguments($api, $args, $seed);

        // If they don't have fields selected we need to include any link fields
        // for this relationship
        if (empty($args['fields']) && is_array($seed->field_defs)) {
            $relatedLinkName = $seed->$linkName->getRelatedModuleLinkName();
            $options['linkDataFields'] = array();

            foreach ($seed->field_defs as $field => $def) {
                if (empty($def['rname_link']) || empty($def['link'])) {
                    continue;
                }
                if ($def['link'] != $relatedLinkName) {
                    continue;
                }
                // It's a match
                $options['linkDataFields'][] = $field;
                $options['select'][] = $field;
            }
        }
        if (!isset($args['filter']) || !is_array($args['filter'])) {
            $args['filter'] = array();
        }

        $q = self::getQueryObject($seed, $options);

        if ($seed->$linkName->getSide() == REL_LHS) {
            $q->where()->isNull($seed->$linkName->getRelationshipObject()->def['rhs_key'], $seed);
        } else {
            $q->where()->isNull($seed->$linkName->getRelationshipObject()->def['lhs_key'], $seed);
        }

        self::addFilters($args['filter'], $q->where(), $q);

        return $this->runQuery($api, $args, $q, $options, $seed);
    }
}
