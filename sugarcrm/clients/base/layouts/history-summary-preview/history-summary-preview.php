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
$viewdefs['base']['layout']['history-summary-preview'] = array(
    'components' =>
    array(
        array(
            'view' => 'history-summary-preview-header',
        ),
        array(
            'view' => 'history-summary-preview',
        ),
        array(
            'layout' => 'preview-activitystream',
            'context' =>
            array(
                'module' => 'Activities',
                'forceNew' => true,
            ),
        ),
    ),
    'type' => 'preview',
    'span' => 12,
);
