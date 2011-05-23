<?php

// sadek
// to test:
// * replace the ids in all the bean retrieves with valid ids
// * replace the first parameter in the getUserAlerts() call with a user that should have an alert inserted on the previous call
//     to handleAlert

define('sugarEntry', true);

require('include/entryPoint.php');

$GLOBALS['current_user'] = new User();
$GLOBALS['current_user']->retrieve('1');

require_once('custom/include/SugarAlerts/SugarAlerts.php');
$sa = new SugarAlerts();

// Test case 1
require_once("modules/Opportunities/Opportunity.php");
require_once("custom/include/SugarAlerts/Alerts/SugarAlertsOppKeyDealFlagChanged.php");
$bean = new Opportunity();
$bean->retrieve('46e9f14c-455e-4ec8-36fc-4d8966fe18d6');

$alert = new SugarAlertsOppKeyDealFlagChanged();
$alert->handleAlert($bean);
var_dump($sa->getUserAlerts($GLOBALS['current_user'], 'dashlet'));

// Test case 2
require_once("modules/Forecasts/Forecast.php");
require_once("custom/include/SugarAlerts/Alerts/SugarAlertsForecastDirectReportCommit.php");
$bean = new Forecast();
$bean->retrieve('616dc6d0-525f-0da9-9076-4d894b88c8ac');

$alert = new SugarAlertsForecastDirectReportCommit();
$alert->handleAlert($bean);
var_dump($sa->getUserAlerts('seed_sarah_id', 'dashlet'));

// Test case 3
require_once("modules/ibm_revenueLineItems/ibm_revenueLineItems.php");
require_once("custom/include/SugarAlerts/Alerts/SugarAlertsNewRevLineItem.php");
$bean = new ibm_revenueLineItems();
$bean->retrieve('5db093c4-d612-17e8-30a9-4d895683931c');

$alert = new SugarAlertsNewRevLineItem();
$alert->handleAlert($bean);
var_dump($sa->getUserAlerts('1', 'dashlet'));

// Test case 4
require_once("modules/Opportunities/Opportunity.php");
require_once("custom/include/SugarAlerts/Alerts/SugarAlertsNewOpportunity.php");
$bean = new Opportunity();
$bean->retrieve('46e9f14c-455e-4ec8-36fc-4d8966fe18d6');

$alert = new SugarAlertsNewOpportunity();
$alert->handleAlert($bean);
var_dump($sa->getUserAlerts($GLOBALS['current_user'], 'dashlet'));

