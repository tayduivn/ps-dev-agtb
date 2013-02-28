{*
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
*}
<link rel="stylesheet" type="text/css" href="{$css_url}" />
<div class="content" id="forecasts">
    <div id="alerts" class="alert-top">
        <div class="alert alert-process">
            <strong>Loading</strong>
            <div class="loading">
                <span class="l1"></span><span class="l2"></span><span class="l3"></span>
            </div>
        </div>
    </div>
</div>
<div id="arrow" title="Show" class="up"><i class="icon-chevron-down"></i></div>
<footer id="footer">
    <div class="row-fluid">
        <div class="span5">
            <span class="logo" id="logo" title="&#169; 2004-{$copyYear} SugarCRM Inc. All Rights Reserved. {$STATISTICS}">SugarCRM</span>
        </div>
        <div class="span2">
            <a href="http://www.sugarcrm.com" target="_blank" class="copyright">&copy; {$copyYear} SugarCRM Inc.</a>
            <script>
                var logoStats = "&#169; 2004-{$copyYear} SugarCRM Inc. All Rights Reserved. {$STATISTICS|addslashes}";
            </script>
        </div>
        <div class="span5">
            <div class="btn-toolbar pull-right">
                <div class="btn-group">
                    <a data-toggle="modal" title="Activity View Tour" id="productTour" href="javascript: void(0);"  class="btn btn-invisible"><i class="icon-road"></i> {$app_strings.LBL_TOUR}</a>
                    <a title="Support" href="{$HELP_URL}" class="btn btn-invisible"><i class="icon-question-sign"></i> {$MODULE_NAME} {$app_strings.LNK_HELP}</a>
                </div>
            </div>
        </div>
    </div>
</footer>
<script src='{$configFile}'></script>
{literal}
<script language="javascript">
    var syncResult, view, layout, html;

    SUGAR.App.sugarAuthStore.set('AuthAccessToken', {/literal}'{$token}'{literal});
    SUGAR.App.sugarAuthStore.set('AuthRefreshToken', {/literal}'{$token}'{literal});

    (function(app) {
        if(!_.has(app, 'forecasts')) {
            app.forecasts = {}
        }
        app.augment("forecasts", _.extend(app.forecasts, {
            initForecast: function(authAccessToken) {
                app.viewModule = 'Forecasts';
                app.AUTH_ACCESS_TOKEN = authAccessToken;
                app.AUTH_REFRESH_TOKEN = authAccessToken;
                app.init({
                    el: "forecasts",
                    contentEl: ".content",
                    //keyValueStore: app.sugarAuthStore, //override the keyValueStore
                    callback: function(app) {
                        var url = app.api.buildURL("Forecasts/init");
                        app.api.call('GET', url, null, {
                            success: function(forecastData) {
                                // get default selections for filter and ranges
                                app.defaultSelections = forecastData.defaultSelections;
                                app.initData = forecastData.initData;
                                app.user.set(app.initData.selectedUser);

                                if(forecastData.initData.forecasts_setup == 0) {
                                    window.location.hash = "#config";
                                }
                                // resize the top menu after the layout has been initialized
                                SUGAR.themes.resizeMenu();
                                app.start();
                            },
                            error:  app.error.handleForecastAPIError
                        });
                    }
                });
                return app;
            }
            }));
     })(SUGAR.App);

//Call initForecast with the session id as token
var App = SUGAR.App.forecasts.initForecast({/literal}'{$token}'{literal});
App.api.debug = App.config.debugSugarApi;

$("#productTour").click(function(){
    if($('#tour').length > 0){
        $('#tour').modal("show");
    }  else {
        SUGAR.tour.init({
            id: 'tour',
            modals: modals,
            modalUrl: "index.php?module=Home&action=tour&to_pdf=1",
            prefUrl: "index.php?module=Users&action=UpdateTourStatus&to_pdf=true&viewed=true",
            className: 'whatsnew',
            onTourFinish: function() {}
        });
    }
});


</script>
{/literal}
