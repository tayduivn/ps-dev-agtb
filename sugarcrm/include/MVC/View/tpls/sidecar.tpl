{*
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */
*}

<!DOCTYPE HTML>
<html class="no-js">
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=8, IE=9, IE=10" >
        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">
        <title>SugarCRM</title>
        <link rel="icon" href="themes/default/images/sugar_icon.ico">
        <!-- CSS -->
        {foreach from=$css_url item=url}
            <link rel="stylesheet" href="{$url}"/>
        {/foreach}
        <!--[if lt IE 10]>
        <link rel="stylesheet" type="text/css" href="themes/default/css/ie.css">
        <![endif]-->
        {sugar_getscript file="include/javascript/modernizr.js"}
    </head>
    <body>
        <div id="sugarcrm">
            <div id="sidecar">
                <div id="alerts" class="alert-top">
                    <div class="alert alert-process">
                        <strong>{$LBL_LOADING}</strong>
                        <div class="loading">
                            <span class="l1"></span><span class="l2"></span><span class="l3"></span>
                        </div>
                    </div>
                </div>
                <div id="header"></div>
                <div id="content"></div>
                <div id="drawers"></div>
                <div id="footer"></div>
                <div id="tourguide"></div>
            </div>
        </div>
        <!-- App Scripts -->
        {if !empty($developerMode)}
            {sugar_getscript file="sidecar/minified/sidecar.js"}
        {else}
            {sugar_getscript file="sidecar/minified/sidecar.min.js"}
        {/if}
        <script src='{$sugarSidecarPath}'></script>
        <script src='{$SLFunctionsPath}'></script>
        <!-- <script src='sidecar/minified/sugar.min.js'></script> -->
        <script src='{$configFile}?hash={$configHash}'></script>
        {sugar_getscript file="include/javascript/jquery/jquery.dataTables.min.js"}

        {sugar_getscript file="include/javascript/sugar7.js"}
        {sugar_getscript file="include/javascript/sugar7/bwc.js"}
        {sugar_getscript file="include/javascript/sugar7/utils.js"}
        {sugar_getscript file="include/javascript/sugar7/field.js"}
        {sugar_getscript file="include/javascript/sugar7/hacks.js"}
        {sugar_getscript file="include/javascript/sugar7/alert.js"}
        {sugar_getscript file="include/javascript/sugar7/hbs-helpers.js"}
        {literal}
        <script language="javascript">
            if (parent.window != window && typeof(parent.SUGAR.App.router) != "undefined") {
                parent.SUGAR.App.router.navigate("#Home", {trigger:true});
            } else {
                var App;
                {/literal}{if $authorization}
                SUGAR.App.cache.set("{$appPrefix}AuthAccessToken", "{$authorization.access_token}")
                {if $authorization.refresh_token}
                SUGAR.App.cache.set("{$appPrefix}AuthRefreshToken", "{$authorization.refresh_token}")
                {/if}
                history.replaceState(null, 'SugarCRM', window.SUGAR.App.config.siteUrl+"/"+window.location.hash)
                {/if}{literal}
                App = SUGAR.App.init({
                    el: "#sidecar",
                    callback: function(app){
                        $('#alerts').empty();
                        app.start();
                    }
                });
                App.api.debug = App.config.debugSugarApi;
            }
        </script>
        {/literal}

        {if !empty($voodooFile)}
            <script src="{$voodooFile}"></script>
        {/if}
    </body>
</html>
