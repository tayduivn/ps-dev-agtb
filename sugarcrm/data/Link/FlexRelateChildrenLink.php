<?php

if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

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

require_once 'data/Link2.php';

/**
 * Left-hand side link which aggregates related beans and the beans whose parent is current bean
 */
class FlexRelateChildrenLink extends Link2
{
    /**
     * {@inheritDoc}
     */
    public function getSide()
    {
        return REL_LHS;
    }

    /**
     * Reconstructs the query so that it fetches beans using both "related" and "parent" relationships
     *
     * {@inheritDoc}
     */
    public function buildJoinSugarQuery($sugar_query, $options = array())
    {
        parent::buildJoinSugarQuery($sugar_query, $options);

        $alias = $options['joinTableAlias'];

        /** @var SugarQuery_Builder_Join $join */
        $join = $sugar_query->join[$alias];
        $onContactId = array_shift($join->on()->conditions);

        $on = new SugarQuery_Builder_Orwhere($sugar_query);
        $on->add($onContactId);
        $on->queryAnd()
            ->equalsField('parent_id', $alias . '.id')
            ->equals('parent_type', $this->relationship->getLHSModule());

        array_unshift($join->on()->conditions, $on);
    }

    /**
     * Unlinks related beans and removes parent relation in case if it points to current bean
     *
     * {@inheritDoc}
     */
    public function delete($id, $related_id = '')
    {
        parent::delete($id, $related_id);

        if (!($related_id instanceof SugarBean)) {
            $related_id = $this->getRelatedBean($related_id);
        }

        /** @var SugarBean $relatedBean */
        if ($related_id
            && $related_id->parent_type == $this->relationship->getLHSModule()
            && $related_id->parent_id == $id) {
            $related_id->parent_type = '';
            $related_id->parent_id = '';
            $related_id->save();
        }
    }
}
