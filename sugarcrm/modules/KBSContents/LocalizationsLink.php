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
require_once("data/Link2.php");

class LocalizationsLink extends Link2
{
    /**
     * {@inheritdoc}
     */
    function buildJoinSugarQuery($sugar_query, $options = array())
    {
        $sugar_query->where()
            ->notEquals('id', $this->focus->id)
            ->notEquals('kbsarticle_id', $this->focus->kbsarticle_id)
            ->equals('active_rev', 1);
        $sugar_query->distinct('kbscontents.id');
        
        return $this->relationship->buildJoinSugarQuery($this, $sugar_query, $options);
    }
}
