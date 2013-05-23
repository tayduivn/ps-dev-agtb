<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
$layout = MetaDataManager::getLayout('SideBarLayout');

$layout->push('main', array('view' => 'list-headerpane'));

$layout->push('main', array('view' => 'info'));

$layout->push('main', array('layout' => 'list', 'context' => array('module' => 'ForecastManagerWorksheets')));
$layout->push('main', array('layout' => 'list', 'context' => array('module' => 'ForecastWorksheets')));

$layout->push('side', array('layout' => 'list-sidebar'));
$layout->push('dashboard', array('layout' => 'dashboard', 'context' => array(
    'forceNew' => true,
    'module' => 'Forecasts',
)));
$layout->push('preview', array('layout' => 'preview'));

$viewdefs['Forecasts']['base']['layout']['records'] = $layout->getLayout();
$viewdefs['Forecasts']['base']['layout']['records']['type'] = 'records';

/*
$viewdefs['Forecasts']['base']['layout']['records'] = array(
    'type' => 'records',
    'components' => array(
        array(
            'view' => 'forecastsTree',
        ),
        array(
            'view' => 'forecastsTitle',
        ),
        array(
            'layout' => 'info'
        ),
        array(
            'view' => 'forecastsChart',
        ),
        array(
            'view' => 'forecastsProgress',
        ),
        array(
            'view' => 'forecastsWorksheet'
        ),
        array(
            'view' => 'forecastsWorksheetTotals'
        ),
        array(
            'view' => 'forecastsWorksheetManager',
        ),
        array(
            'view' => 'forecastsWorksheetManagerTotals'
        ),
        array(
            'view' => 'forecastsCommitButtons',
        ),
        array(
            'layout' => array(
                'name' => 'inspector',
                'type' => 'ForecastsInspector',
            ),
        )
    ),
);
*/
