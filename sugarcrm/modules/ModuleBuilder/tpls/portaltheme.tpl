{*
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
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
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
//FILE SUGARCRM flav=ent ONLY
*}
<!-- Sidecar Config -->
<script type="text/javascript" src="cache/config.js"></script>
<!-- CSS -->
{foreach from=$css_url item=url}
    <link rel="stylesheet" href="{$url}"/>
{/foreach}
<style>
    h2{literal}{line-height: 100%;}{/literal}
    body{literal}{padding-top: 0px;}{/literal}
</style>
<div id="portal_themeroller" style="">
    <div id="alerts" class="alert-top">
        <div class="alert alert-process">
            <strong>{$LBL_LOADING}</strong>
            <div class="loading">
                <span class="l1"></span><span class="l2"></span><span class="l3"></span>
            </div>
        </div>
    </div>
    <div class="content">
    </div>
</div>




{literal}

<script language="javascript">
SUGAR.App.config.platform = 'portal';

// set our auth Token
SUGAR.App.sugarAuthStore.set('AuthAccessToken', {/literal}'{$token}'{literal});

// bootstrap token
(function (app) {
    app.augment("theme", {
        initTheme:function (authAccessToken) {
            app.AUTH_ACCESS_TOKEN = authAccessToken;
            app.AUTH_REFRESH_TOKEN = authAccessToken;
            app.init({
                el:"#portal_themeroller",
                contentEl:".content"
            });
            return app;
        }
    });
})(SUGAR.App);
// Reset app if it already exists
if (App){
    App.destroy();
}
// Call initTheme with the session id as token
var App = SUGAR.App.theme.initTheme({/literal}'{$token}'{literal});

// should already be logged in to sugar, don't need to log in to sidecar.
App.api.isAuthenticated = function () {
    return true;
};

// Disabling the app sync complete event which starts sidecars competing router
App.events.off("app:sync:complete");
//force app sync and load the appropriate view on success
App.sync(
        {
            callback:function (data) {
                $('#alerts').empty();
                App.controller.loadView({
                    layout:'themeroller',
                    create:true
                });
            },
            err:function (data) {
                console.log("app sync error");
            }
        }
);

</script>
{/literal}
