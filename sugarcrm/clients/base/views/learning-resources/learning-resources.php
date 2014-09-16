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
        'discover' => array(
            'color' => 'blue',
            'icon' => 'icon-youtube-play',
            'url' => 'https://www.youtube.com/user/DiscoverSugarCRM',
            'link' => 'LBL_LEARNING_RESOURCES_DISCOVER_LINK',
        ),
        'sugar_university' => array(
            'color' => 'blue',
            'icon' => 'icon-book',
            'url' => 'http://university.sugarcrm.com/',
            'link' => 'LBL_LEARNING_RESOURCES_SUGAR_UNIVERSITY_LINK',
        ),
        'start' => array(
            'color' => 'blue',
            'icon' => 'icon-compass',
            'url' => 'http://support.sugarcrm.com/01_Get_Started/',
            'link' => 'LBL_LEARNING_RESOURCES_START_LINK',
        ),
        'training' => array(
            'color' => 'blue',
            'icon' => 'icon-microphone',
            'url' => 'http://support.sugarcrm.com/03_Training/Webinars/',
            'link' => 'LBL_LEARNING_RESOURCES_TRAINING_LINK',
        ),
        'webinars' => array(
            'color' => 'blue',
            'icon' => 'icon-desktop',
            'url' => 'http://www.sugarcrm.com/university_classes/list',
            'link' => 'LBL_LEARNING_RESOURCES_WEBINARS_LINK',
        ),
        'videos' => array(
            'color' => 'blue',
            'icon' => 'icon-play-sign',
            'url' => 'http://support.sugarcrm.com/03_Training/02_Videos/02_Videos_Sugar_v7/',
            'link' => 'LBL_LEARNING_RESOURCES_VIDEOS_LINK',
        ),
    ),
);
