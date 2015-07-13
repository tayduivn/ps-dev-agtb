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
     * {@inheritdoc}
     */
    public function addVisibilityFrom(&$query)
    {
        global $current_user;
        if ($this->getOption('where_condition') || !$this->isApplicable()) {
            return $query;
        }

        list($teamTableAlias, $tableAlias) = $this->getAliases();
        $query .= " INNER JOIN (
            SELECT tst.team_set_id
            FROM team_sets_teams tst
            INNER JOIN team_memberships {$teamTableAlias} ON tst.team_id = {$teamTableAlias}.team_id
                AND {$teamTableAlias}.user_id = '{$current_user->id}'
                AND {$teamTableAlias}.deleted=0
            GROUP BY tst.team_set_id
            ) {$tableAlias}_tba ON {$tableAlias}_tba.team_set_id = {$tableAlias}.team_set_selected_id";

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
     * {@inheritdoc}
     */
    public function addVisibilityWhere(&$query)
    {
        global $current_user;
        if (!$this->getOption('where_condition') || !$this->isApplicable()) {
            return $query;
        }

        list($teamTableAlias, $tableAlias) = $this->getAliases();
        $inClause = "SELECT tst.team_set_id
            FROM team_sets_teams tst
            INNER JOIN team_memberships {$teamTableAlias} ON tst.team_id = {$teamTableAlias}.team_id
                AND {$teamTableAlias}.user_id = '{$current_user->id}'
                AND {$teamTableAlias}.deleted = 0";

        $query .= " AND {$tableAlias}.team_set_selected_id IN ({$inClause}) ";
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
     * Verifies if Team Based ACL needs to be applied.
     * @return bool
     */
    protected function isApplicable()
    {
        global $current_user;

        if (empty($current_user) ||
            empty($this->bean->team_set_selected_id)
        ) {
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
            $filter->addMust(
                $provider->createFilter('TeamSet', array('user' => $user, 'field' => 'team_set_selected_id'))
            );
        }
    }
}
