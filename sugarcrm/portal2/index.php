<?php
if(!defined('sugarEntry'))define('sugarEntry', true);
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
$rootDir = dirname(getcwd());// up one
set_include_path(get_include_path() . PATH_SEPARATOR . $rootDir);
chdir($rootDir);
//initialize the various globals we use this is needed so modules.php properly registers the modules globals, otherwise they end up defined in wrong scope
global $sugar_config, $db, $fileName, $current_user, $locale, $current_language, $beanFiles, $beanList, $objectList, $moduleList, $modInvisList;
require_once('include/entryPoint.php');
require_once('jssource/minify_utils.php');
$minifyUtils = new SugarMinifyUtils();
ensureCache($minifyUtils, $rootDir);
?>
<!DOCTYPE HTML>
<html>
    <head>
        <title>Customer Self-Service Portal - Powered by SugarCRM.</title>
        <meta http-equiv="X-UA-Compatible" content="IE=9, IE=10">
        <meta name="viewport" content="initial-scale=1.0">
        <link rel="SHORTCUT ICON" href="../themes/default/images/sugar_icon.ico">
        <? if(inDeveloperMode()): ?>
            <script type="text/javascript" src="../sidecar/minified/sidecar.js"></script>
        <? else: ?>
            <script type="text/javascript" src="../sidecar/minified/sidecar.min.js"></script>
        <? endif; ?>
        
        <!-- CSS -->
        <link rel="stylesheet" href="../sidecar/lib/jquery-ui/css/smoothness/jquery-ui-1.8.18.custom.css"/>
        <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600" rel="stylesheet" type="text/css">
        <link href="./assets/loading.css" rel="stylesheet" type="text/css">

        <!-- App Scripts -->
        <script src='../cache/include/javascript/sugar_sidecar.min.js'></script>
        <script language="javascript" src="../include/javascript/sugar7/bwc.js"></script>
        <script language="javascript" src="../include/javascript/sugar7/utils.js"></script>
        <script language="javascript" src="../include/javascript/sugar7/field.js"></script>
        <script language="javascript" src="../include/javascript/sugar7/hacks.js"></script>
        <script language="javascript" src="../include/javascript/sugar7/alert.js"></script>
        <script language="javascript" src="../include/javascript/sugar7/hbt-helpers.js"></script>
        <script language="javascript" src="../include/javascript/modernizr.js"></script>
        
        <!-- Portal specific JS -->
        <script src='portal.js'></script>
        <script src='config.js'></script>

    </head>
    <body>
        <div id="sidecar">
            <div id="portal">
                <div id="alerts" class="alert-top">
                </div>
                <div id="header">
                </div>
                <div id="content">
                    <div class="alert-top">
                        <div class="alert alert-process">
                            <strong>Loading</strong>
                            <div class="loading">
                                <span class="l1"></span><span class="l2"></span><span class="l3"></span>
                            </div>
                            <a class="close" data-dismiss="alert">x</a>
                        </div>
                    </div>
                </div>

                <div id="footer">

                </div>
                <div id="drawers"></div>
            </div>
        </div>
        <script language="javascript">
            var syncResult, view, layout, html;
            var App = SUGAR.App.init({
                el: "#sidecar",
                callback: function(app){
                    app.start();
                }
            });
            App.api.debug = App.config.debugSugarApi;
        </script>
    </body>
</html>
