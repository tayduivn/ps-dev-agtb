<?php
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

if(!defined('sugarEntry')) define('sugarEntry', true);

if (basename(getcwd()) === 'tests/unit-php' || !is_file('include/entryPoint.php')) {
    $path = str_replace('\\', '/', realpath(dirname(__FILE__) . '/../..'));
} else {
    $path = str_replace('\\', '/', realpath(dirname(__FILE__)));
}

define('SUGAR_BASE_DIR', $path);
chdir(SUGAR_BASE_DIR);

set_include_path(
    dirname(__FILE__) . PATH_SEPARATOR .
    dirname(__FILE__) . '/..' . PATH_SEPARATOR .
    dirname(__FILE__) . '/../..' . PATH_SEPARATOR .
    get_include_path()
);

require_once 'include/utils/autoloader.php';

// we need to pass in false since we don't have have an installed instance
SugarAutoLoader::init(false);

// Disables sending email.
define('DISABLE_EMAIL_SEND', true);
