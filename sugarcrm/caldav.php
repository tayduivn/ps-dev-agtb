<?php
if (!defined('sugarEntry')) {
    define('sugarEntry', true);
}
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

define('ENTRY_POINT_TYPE', 'api');
require_once 'include/entryPoint.php';

if (empty($current_language)) {
    $current_language = $sugar_config['default_language'];
}
$app_list_strings = return_app_list_strings_language($current_language);
$app_strings = return_application_language($current_language);

$serverHelper = new \Sugarcrm\Sugarcrm\Dav\Base\Helper\ServerHelper();
$server = $serverHelper->setUp();

$server->exec();
