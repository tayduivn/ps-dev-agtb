<?php

//error_reporting( E_ALL | E_STRICT );
//ini_set('display_startup_errors', 1);
//ini_set('display_errors', 1);

// Define path to application directory
defined('MODULES_PATH')
    || define('MODULES_PATH', realpath(dirname(__FILE__) . '/../package/SugarModules/'));

defined('MOCK_CLASSES_PATH')
    || define('MOCK_CLASSES_PATH', realpath(dirname(__FILE__) . '/package/mockClasses/'));

defined('BEAN_GENERATOR_CLASSES_PATH')
    || define('BEAN_GENERATOR_CLASSES_PATH', realpath(dirname(__FILE__).'/../beangenerator/'));

function autoload_classes($class_name)
{
    $file = MODULES_PATH. '/modules/pmse_Project/' . $class_name. '.php';
    if (file_exists($file)) {
        require_once($file);
    }
    
    $file = MODULES_PATH. '/modules/pmse_Project/clients/base/api/wrappers/' . $class_name. '.php';
    if (file_exists($file)) {
        require_once($file);
    }
    
    $file = MODULES_PATH. '/modules/pmse_Project/clients/base/api/wrappers/PMSEObservers/' . $class_name. '.php';
    if (file_exists($file)) {
        require_once($file);
    }

    $file = MODULES_PATH. '/modules/pmse_Inbox/engine/' . $class_name. '.php';
    if (file_exists($file)) {
        require_once($file);
    }

    $file = MODULES_PATH. '/modules/pmse_Inbox/engine/parser/' . $class_name. '.php';
    if (file_exists($file)) {
        require_once($file);
    }

    $file = MODULES_PATH. '/modules/pmse_Inbox/engine/wrappers/' . $class_name. '.php';
    if (file_exists($file)) {
        require_once($file);
    }
    
    $file = MODULES_PATH. '/modules/pmse_Inbox/engine/PMSEElements/' . $class_name. '.php';
    if (file_exists($file)) {
        require_once($file);
    }
    
    $file = MODULES_PATH. '/modules/pmse_Inbox/engine/PMSEExceptions/' . $class_name. '.php';
    if (file_exists($file)) {
        require_once($file);
    }
    
    $file = MODULES_PATH. '/modules/pmse_Inbox/engine/PMSEPreProcessor/' . $class_name. '.php';
    if (file_exists($file)) {
        require_once($file);
    }
    
    $file = MODULES_PATH. '/modules/pmse_Inbox/engine/PMSEHandlers/' . $class_name. '.php';
    if (file_exists($file)) {
        require_once($file);
    }

    $file = MODULES_PATH. '/modules/pmse_Business_Rules/' . $class_name. '.php';
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

function autoload_beangenerator_classes($class_name)
{
    $file = BEAN_GENERATOR_CLASSES_PATH. '/' . $class_name. '.php';
    if (file_exists($file)) {
        require_once($file);
    }
}

spl_autoload_register('autoload_classes');
spl_autoload_register('autoload_api_classes');
spl_autoload_register('autoload_mock_classes');
spl_autoload_register('autoload_beangenerator_classes');

defined('TEST_FILES_PATH')
    || define('TEST_FILES_PATH', realpath(dirname(__FILE__) . '/package/mockFiles/'));
// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    TEST_FILES_PATH,
    get_include_path(),
    MODULES_PATH,
    BEAN_GENERATOR_CLASSES_PATH,
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

