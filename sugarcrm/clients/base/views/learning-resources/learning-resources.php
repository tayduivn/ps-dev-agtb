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

$viewdefs['base']['view']['learning-resources'] = array(
    'dashlets' => array(
        array(
            'label' => 'LBL_LEARNING_RESOURCES_TITLE',
            'description' => 'LBL_LEARNING_RESOURCES_DESC',
            'config' => array(),
            'preview' => array(),
            'filter' => array(),
        ),
    ),
    'resources' => array(
        'sugar_university' => array(
            'color' => 'blue',
            'icon' => 'icon-book',
            'url' => 'http://university.sugarcrm.com/',
            'link' => 'LBL_LEARNING_RESOURCES_SUGAR_UNIVERSITY_LINK',
            'teaser' => 'LBL_LEARNING_RESOURCES_SUGAR_UNIVERSITY_TEASER',
        ),
        'community' => array(
            'color' => 'green',
            'icon' => 'icon-comments-alt',
            'url' => 'https://community.sugarcrm.com/',
            'link' => 'LBL_LEARNING_RESOURCES_COMMUNITY_LINK',
            'teaser' => 'LBL_LEARNING_RESOURCES_COMMUNITY_TEASER',
        ),
        'support' => array(
            'color' => 'red',
            'icon' => 'icon-question-sign',
            'url' => 'http://support.sugarcrm.com/',
            'link' => 'LBL_LEARNING_RESOURCES_SUPPORT_LINK',
            'teaser' => 'LBL_LEARNING_RESOURCES_SUPPORT_TEASER',
        ),
    ),
);
