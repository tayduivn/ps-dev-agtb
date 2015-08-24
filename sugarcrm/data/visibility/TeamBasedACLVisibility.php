<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use Sugarcrm\Sugarcrm\Elasticsearch\Provider\Visibility\StrategyInterface;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\Visibility\Visibility;
use Sugarcrm\Sugarcrm\Elasticsearch\Analysis\AnalysisBuilder;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Document;

/**
 * Class TeamBasedACLVisibility
 * Grant access to users who belong to one of the Selected Teams.
 */
class TeamBasedACLVisibility extends SugarVisibility implements StrategyInterface
{
    /**
     * Apply TBA in from clause.
     * {@inheritdoc}
     */
    public function addVisibilityFrom(&$query)
    {
        if ($this->getOption('where_condition') || !$this->isApplicable()) {
            return $query;
        }
        list($teamTableAlias, $tableAlias) = $this->getAliases();
        // Inner join is not used because owner visibility implements a where part only.
        $where = $this->getWhereClause();
        $query .= " INNER JOIN (
                SELECT {$tableAlias}.id
                FROM {$tableAlias}
                WHERE deleted = 0 {$where}
            ) {$tableAlias}_agr ON {$tableAlias}_agr.id = {$tableAlias}.id";

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function addVisibilityFromQuery(SugarQuery $query)
    {
        $join = '';
        $this->addVisibilityFrom($join);
        if (!empty($join)) {
            $query->joinRaw($join);
        }
        return $query;
    }

    /**
     * Apply TBA in where clause.
     * {@inheritdoc}
     */
    public function addVisibilityWhere(&$query)
    {
        if (!$this->getOption('where_condition') || !$this->isApplicable()) {
            return $query;
        }
        $query .= $this->getWhereClause();
        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function addVisibilityWhereQuery(SugarQuery $query)
    {
        $condition = '';
        $this->addVisibilityWhere($condition);
        if (!empty($condition)) {
            $query->whereRaw($condition);
        }
        return $query;
    }

    /**
     * Get a TBA where clause.
     * @return string Where clause
     */
    protected function getWhereClause()
    {
        global $current_user;

        list($teamTableAlias, $tableAlias) = $this->getAliases();
        $inClause = "SELECT tst.team_set_id
            FROM team_sets_teams tst
            INNER JOIN team_memberships {$teamTableAlias} ON tst.team_id = {$teamTableAlias}.team_id
                AND {$teamTableAlias}.user_id = '{$current_user->id}'
                AND {$teamTableAlias}.deleted = 0";

        $ow = new OwnerVisibility($this->bean, $this->params);
        $ownerVisibilityRaw = '';
        $ow->addVisibilityWhere($ownerVisibilityRaw);

        return " AND ({$ownerVisibilityRaw} OR {$tableAlias}.team_set_selected_id IN ({$inClause})) ";
    }

    /**
     * Verifies if Team Based ACL needs to be applied.
     * @return bool
     */
    protected function isApplicable()
    {
        global $current_user;

        if (empty($current_user)) {
            return false;
        }
        return true;
    }

    /**
     * Get table aliases for raw queries.
     * @return array [Team Membership, Table]
     */
    protected function getAliases()
    {
        $teamTableAlias = 'team_memberships';
        $tableAlias = $this->getOption('table_alias');
        if (!empty($tableAlias)) {
            $teamTableAlias = $this->bean->db->getValidDBName($teamTableAlias . $tableAlias, true, 'table');
        } else {
            $tableAlias = $this->bean->table_name;
        }
        return array($teamTableAlias, $tableAlias);
    }

    /**
     * {@inheritdoc}
     */
    public function elasticBuildAnalysis(AnalysisBuilder $analysisBuilder, Visibility $provider)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function elasticBuildMapping(Mapping $mapping, Visibility $provider)
    {
        $mapping->addNotAnalyzedField('team_set_selected_id');
    }

    /**
     * {@inheritdoc}
     */
    public function elasticProcessDocumentPreIndex(Document $document, SugarBean $bean, Visibility $provider)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function elasticGetBeanIndexFields($module, Visibility $provider)
    {
        return array('team_set_selected_id' => 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function elasticAddFilters(\User $user, \Elastica\Filter\Bool $filter, Visibility $provider)
    {
        if ($this->isApplicable()) {
            $combo = new \Elastica\Filter\BoolOr();
            $combo->addFilter(
                $provider->createFilter('TeamSet', array('user' => $user, 'field' => 'team_set_selected_id'))
            );
            $combo->addFilter(
                $provider->createFilter('Owner', array('bean' => $this->bean, 'user' => $user))
            );
            $filter->addMust($combo);
        }
    }
}
