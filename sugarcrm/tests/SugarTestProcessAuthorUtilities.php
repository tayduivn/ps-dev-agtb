<?php
//FILE SUGARCRM flav=ent ONLY
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
defined('MOCK_CLASSES_PATH')
    || define('MOCK_CLASSES_PATH', realpath(dirname(__FILE__) . '/mockClasses/'));


function autoload_classes($class_name)
{
    $file = 'modules/pmse_Project/' . $class_name. '.php';
    if (file_exists($file)) {
        require_once($file);
    }
    
    $file = 'modules/pmse_Project/clients/base/api/wrappers/' . $class_name. '.php';
    if (file_exists($file)) {
        require_once($file);
    }
    
    $file = 'modules/pmse_Project/clients/base/api/wrappers/PMSEObservers/' . $class_name. '.php';
    if (file_exists($file)) {
        require_once($file);
    }

    $file = 'modules/pmse_Inbox/engine/' . $class_name. '.php';
    if (file_exists($file)) {
        require_once($file);
    }

    $file = 'modules/pmse_Inbox/engine/parser/' . $class_name. '.php';
    if (file_exists($file)) {
        require_once($file);
    }

    $file = 'modules/pmse_Inbox/engine/wrappers/' . $class_name. '.php';
    if (file_exists($file)) {
        require_once($file);
    }
    
    $file = 'modules/pmse_Inbox/engine/PMSEElements/' . $class_name. '.php';
    if (file_exists($file)) {
        require_once($file);
    }
    
    $file = 'modules/pmse_Inbox/engine/PMSEExceptions/' . $class_name. '.php';
    if (file_exists($file)) {
        require_once($file);
    }
    
    $file = 'modules/pmse_Inbox/engine/PMSEPreProcessor/' . $class_name. '.php';
    if (file_exists($file)) {
        require_once($file);
    }
    
    $file = 'modules/pmse_Inbox/engine/PMSEHandlers/' . $class_name. '.php';
    if (file_exists($file)) {
        require_once($file);
    }
}

function autoload_api_classes($class_name)
{

}

function autoload_mock_classes($class_name)
{
    $file = MOCK_CLASSES_PATH. '/' . $class_name. '.php';
    if (file_exists($file)) {
        require_once($file);
    }
}

spl_autoload_register('autoload_classes');
spl_autoload_register('autoload_api_classes');
spl_autoload_register('autoload_mock_classes');

defined('TEST_FILES_PATH')
    || define('TEST_FILES_PATH', realpath(dirname(__FILE__) . '/mockFiles'));
// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    TEST_FILES_PATH,
    get_include_path()
)));

//$nativeFunctionsFile = './mockFiles/modules/nativeFunctions.php';
//if (file_exists($nativeFunctionsFile)) {
//    require_once $nativeFunctionsFile;
//}

$nativeFunctionsFile = TEST_FILES_PATH.'/modules/nativeFunctions.php';
if (file_exists($nativeFunctionsFile)) {
    require_once $nativeFunctionsFile;
}
//require_once TEST_FILES_PATH.'modules/cryptoFunctions.php';
$path = get_include_path();

