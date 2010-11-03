<?php
define('WP_DEBUG', false);
// BEGIN SADEK: CUSTOM CODE TO ALLOW ACCESS ONLY FROM INTERNAL
$current_dir = getcwd();
$sugarinternal_dir = '';
$parts = explode('/', dirname(__FILE__));
$found = false;
while (!$found) {
    $part = array_pop($parts);
    if ('salesportal' == $part) {
	#$parts[] = $part;
    }
    $found = true;
}
$sugarinternal_dir = join('/', $parts) . '/';
if (!$found) {
    trigger_error('Could not find salesportal in current path: ' . $current_dir, E_USER_NOTICE);
    die('There has been a serious error, please contact internalsystems@sugarcrm.com');
} else {
    chdir($sugarinternal_dir);
}

$nonRootDir = false;
if (!strpos($_SERVER['REQUEST_URI'], 'wp-admin/')){
    define('sugarEntry', true);
    include_once('./include/entryPoint.php');
    include_once('./modules/Users/authentication/AuthenticationController.php');
    $authController = new AuthenticationController((!empty($sugar_config['authenticationClass'])? $sugar_config['authenticationClass'] : 'SugarAuthenticate'));
    session_start();
    if (!$authController->sessionAuthenticate()) {
	die("You are not currently logged in to Sugar Internal. You cannot access this page.");
    }
} else {
    $nonRootDir = $sugarinternal_dir . 'salesportal/wp-admin';
    chdir($current_dir);
}
require_once('/var/www/sugarinternal/sugarinternal.sugarondemand.com/config.php');
global $sugar_config;
// NOTE: The user in sugar config has to have access to the 'salesportal' database.
$dbh = $sugar_config['dbconfig']['db_host_name'];
$dbu = $sugar_config['dbconfig']['db_user_name'];
$dbp = $sugar_config['dbconfig']['db_password'];
$dbn = 'salesportal';

chdir($sugarinternal_dir . "salesportal");
if ($nonRootDir != false) {
    chdir($nonRootDir);
}
unset($current_user);
// END SADEK: CUSTOM CODE TO ALLOW ACCESS ONLY FROM INTERNAL
// ** MySQL settings ** //

define('DB_NAME', $dbn);    // The name of the database
define('DB_USER', $dbu);     // Your MySQL username
define('DB_PASSWORD', $dbp); // ...and password
define('DB_HOST', $dbh);    // 99% chance you won't need to change this value
// END SADEK CUSTOM CODE TO ALLOW ONLY FROM INTERNAL

// You can have multiple installations in one database if you give each a unique prefix
$table_prefix  = 'wp_';   // Only numbers, letters, and underscores please!

// Change this to localize WordPress.  A corresponding MO file for the
// chosen language must be installed to wp-includes/languages.
// For example, install de.mo to wp-includes/languages and set WPLANG to 'de'
// to enable German language support.
define ('WPLANG', '');

/* That's all, stop editing! Happy blogging. */

//echo getcwd();
#echo ABSPATH;
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__).'/');
}
require_once(ABSPATH.'wp-settings.php');

?>
