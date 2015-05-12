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
 * Class KBVisibility
 * Addidional visibility check for KB.
 */
class KBVisibility extends SugarVisibility implements StrategyInterface
{
    /**
     * {@inheritDoc}
     * Need to check where it's used.
     */
    public function addVisibilityWhere(&$query)
    {
        return $query;
    }

    /**
     * {@inheritDoc}
     */
    public function addVisibilityWhereQuery(SugarQuery $query)
    {
        $currentUser = $GLOBALS['current_user'];
        $module = $this->bean->module_name;
        $db = DBManagerFactory::getInstance();
        if (!method_exists($this->bean, 'getPublishedStatuses') ||
            $currentUser->isAdminForModule($module) ||
            $currentUser->isDeveloperForModule($module)
        ) {
            return $query;
        } else {
            /**
             * It's better to use
             *             $query->orWhere()
             *   ->equals('created_by', $currentUser->id)
             *   ->in('status', $statuses);
             * but it doesn't work.
             */
            $statuses = $this->bean->getPublishedStatuses();
            foreach ($statuses as $_ => $status) {
                $statuses[$_] = $db->quoted($status);
            }
            $statuses = implode(',', $statuses);
            $ow = new OwnerVisibility($this->bean, $this->params);
            $addon = '';
            $ow->addVisibilityWhere($addon);

            $addon = "({$addon} OR {$this->bean->table_name}.status IN ($statuses))";
            $query->whereRaw($addon);
            return $query;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function elasticBuildAnalysis(AnalysisBuilder $analysisBuilder, Visibility $provider)
    {
        // no special analyzers needed
    }

    /**
     * {@inheritdoc}
     */
    public function elasticBuildMapping(Mapping $mapping, Visibility $provider)
    {
        $mapping->addNotAnalyzedField('status');
    }

    /**
     * {@inheritdoc}
     */
    public function elasticProcessDocumentPreIndex(Document $document, SugarBean $bean, Visibility $provider)
    {
        // nothing to do here
    }

    /**
     * {@inheritdoc}
     */
    public function elasticGetBeanIndexFields($module, Visibility $provider)
    {
        return array('status' => 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function elasticAddFilters(\User $user, \Elastica\Filter\Bool $filter, Visibility $provider)
    {
        $module = $this->bean->module_name;
        if ($user->isAdminForModule($module) || $user->isDeveloperForModule($module)) {
            return;
        }

        // create owner filter
        $options = array(
            'bean' => $this->bean,
            'user' => $user,
        );
        $ownerFilter = $provider->createFilter('Owner', $options);

        if ($statuses = $this->getPublishedStatuses()) {
            $combo = new \Elastica\Filter\Bool();
            $combo->addShould($provider->createFilter('KBStatus', array('published_statuses' => $statuses)));
            $combo->addShould($ownerFilter);
            $filter->addMust($combo);
        } else {
            $filter->addMust($ownerFilter);
        }
    }

    /**
     * Get published statuses
     * @return array
     */
    protected function getPublishedStatuses()
    {
        if (!method_exists($this->bean, 'getPublishedStatuses')) {
            return array();
        }
        return $this->bean->getPublishedStatuses();
    }
}
