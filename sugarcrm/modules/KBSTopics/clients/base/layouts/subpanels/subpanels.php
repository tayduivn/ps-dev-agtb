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
 * Copyright  2004-2014 SugarCRM Inc.  All rights reserved.
 */

$viewdefs['KBSTopics']['base']['layout']['subpanels'] = array(
    'components' => array(
        array(
            'layout' => 'subpanel',
            'label' => 'LBL_TOPICS_SUBPANEL_TITLE',
            'context' => array(
                'link' => 'subnodes',
            ),
        ),
        array(
            'layout' => 'subpanel',
            'label' => 'LBL_CONTENTS_SUBPANEL_TITLE',
            'context' => array(
                'link' => 'contents',
            ),
        ),
    ),
    'type' => 'subpanels',
    'span' => 12,
);
