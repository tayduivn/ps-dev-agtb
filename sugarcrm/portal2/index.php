<?php
if(!defined('sugarEntry'))define('sugarEntry', true);
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
$rootDir = dirname(getcwd());// up one
set_include_path(get_include_path() . PATH_SEPARATOR . $rootDir);
chdir($rootDir);
//initialize the various globals we use this is needed so modules.php properly registers the modules globals, otherwise they end up defined in wrong scope
global $sugar_config, $db, $fileName, $current_user, $locale, $current_language, $beanFiles, $beanList, $objectList, $moduleList, $modInvisList;
require_once('include/entryPoint.php');
// Make sure the cache files exist
ensureJSCacheFilesExist();
?>
<!DOCTYPE HTML>
<html>
    <head>
        <title>Customer Self-Service Portal - Powered by SugarCRM.</title>
        <meta name="viewport" content="initial-scale=1.0">
        <link rel="SHORTCUT ICON" href="../themes/default/images/sugar_icon.ico">
        <!-- CSS -->
        <link rel="stylesheet" href="../styleguide/assets/css/loading.css" type="text/css">
        <link rel="stylesheet" href="../sidecar/lib/jquery-ui/css/smoothness/jquery-ui-1.8.18.custom.css" type="text/css"/>
        <script src="../include/javascript/modernizr.js"></script>

        <? if(inDeveloperMode()): ?>
            <script src="../sidecar/minified/sidecar.js"></script>
        <? else: ?>
            <script src="../sidecar/minified/sidecar.min.js"></script>
        <? endif; ?>

        <!-- Sidecar Scripts -->
        <script src="../cache/include/javascript/sugar_sidecar.min.js"></script>

        <!-- Portal specific JS -->
        <script src="../cache/portal2/portal.min.js"></script>
        <script src="config.js"></script>

        <!-- App Scripts -->
        <script src="../cache/portal2/sugar_portal.min.js"></script>
    </head>
    <body>
        <div id="sidecar">
            <div id="portal">
                <div id="alerts" class="alert-top">
                    <div class="alert-wrapper">
                        <div class="alert alert-process">
                            <strong>Loading</strong>
                            <div class="loading">
                                <i class="l1">&period;</i><i class="l2">&period;</i><i class="l3">&period;</i>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="header"></div>
                <div id="content"></div>
                <div id="footer"></div>
                <div id="drawers"></div>
            </div>
        </div>
        <script>
            var syncResult, view, layout, html;
            var App = SUGAR.App.init({
                el: '#sidecar',
                callback: function(app){
                    $('#alerts').empty();
                    app.start();
                }
            });
            App.api.debug = App.config.debugSugarApi;
        </script>
    </body>
</html>
