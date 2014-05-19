{*
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
*}

<!DOCTYPE HTML>
<html class="no-js">
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=8, IE=9, IE=10" >
        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">
        <title>SugarCRM</title>
        <link rel="shortcut icon" href="{sugar_getjspath file='themes/default/images/sugar_icon.ico'}">
        <!-- CSS -->
        {foreach from=$css_url item=url}
            <link rel="stylesheet" href="{sugar_getjspath file=$url}"/>
        {/foreach}
        <!--[if lt IE 10]>
        <link rel="stylesheet" type="text/css" href="{sugar_getjspath file='themes/default/css/ie.css'}">
        <![endif]-->
        {sugar_getscript file="include/javascript/modernizr.js"}
    </head>
    <body>
        <div id="sugarcrm">
            <div id="sidecar">
                <div id="alerts" class="alert-top">
                    <div class="loading gate">
                        <strong>{$LBL_LOADING}</strong>
                        <i class="l1 icon-circle"></i><i class="l2 icon-circle"></i><i class="l3 icon-circle"></i>
                    </div>
                </div>
                <div id="header"></div>
                <div id="content"></div>
                <div id="drawers"></div>
                <div id="footer"></div>
            </div>
        </div>
        <!-- App Scripts -->
        {if !empty($developerMode)}
            {sugar_getscript file="sidecar/minified/sidecar.js"}
        {else}
            {sugar_getscript file="sidecar/minified/sidecar.min.js"}
        {/if}
        <script src='{sugar_getjspath file=$sugarSidecarPath}'></script>
        <script src='{sugar_getjspath file=$SLFunctionsPath}'></script>
        <!-- <script src='{sugar_getjspath file='sidecar/minified/sugar.min.js'}'></script> -->
        <script src='{sugar_getjspath file=$configFile|cat:'?hash=$configHash'}'></script>
        {sugar_getscript file="cache/include/javascript/sugar_grp7.min.js"}
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
                if (window.SUGAR.App.config.siteUrl != '') {ldelim}
                    history.replaceState(null, 'SugarCRM', window.SUGAR.App.config.siteUrl+"/"+window.location.hash)
                {rdelim}
                {/if}{literal}
                App = SUGAR.App.init({
                    el: "#sidecar",
                    callback: function(app){
                        app.progress.set(0.6);
                        app.once("app:view:change", function(){
                            app.progress.done();
                        });
                        $('#alerts').empty();
                        app.start();
                    }
                });
                App.api.debug = App.config.debugSugarApi;
            }
        </script>
        {/literal}

        {if !empty($voodooFile)}
            <script src="{sugar_getjspath file=$voodooFile}"></script>
        {/if}
    </body>
</html>
