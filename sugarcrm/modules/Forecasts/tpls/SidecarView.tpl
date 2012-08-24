
<script src='include/javascript/sugarAuthStore.js'></script>
<script type='text/javascript' src='include/SugarCharts/Jit/js/Jit/jit.js'></script>
<script type='text/javascript' src='include/SugarCharts/Jit/js/sugarCharts.js'></script>

<div id="alert" class="alert-top"></div>
<div id="core-module">
    <div id="forecasts" style="" >
        <div class="row-fluid">
            <div class="view-forecastsSubnav subnav" id="headerbar"></div>
        </div>
        <div class="container-fluid">
            <div class="row-fluid">
                <div class="span8">
                    <div class="view-forecastsCommitButtons"></div>
                    <div class="view-forecastsTimeframes"></div>
                    <div class="view-forecastsCommitted"></div>
                    <div class="view-forecastsFilter"></div>
                    <div>
                        <div id="view-sales-rep" style="display:none">
                            <div class="view-forecastsWorksheet"></div>
                        </div>
                        <div id="view-manager" style="display:none">
                            <div class="view-forecastsWorksheetManager"></div>
                        </div>
                    </div>
                </div>

                <div class="span4 tab-content">{*<div class="span4 tab-content" id="folded">*}
                    <div class="tab-pane active" id="overview">
                        <div class="thumbnail viz">
                            <div class="view-forecastsChart"></div>
                            <div class="view-forecastsChartOptions"></div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="block" id="guages">
                            <div class="view-forecastsProgress"></div>
                        </div>
                    </div>
                </div>

                <div class="modal hide fade" id="forecastSubnavSettingsModal" style="display:block">
                    <div class="modal-header">
                        <a class="close" data-dismiss="modal">×</a>
                        <h3>Admin</h3>
                    </div>
                    <div class="modal-body">
                        <p>Body....................</p>
                    </div>
                    <div class="modal-footer">
                        <a href="#" class="btn">Cancel</a>
                        <a href="#" class="btn btn-primary">Save</a>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{*temporarily adding the footer here manually to aid in development.  This should get added automatically once the footer is updated for all of Sugar*}
<footer>
    <div class="row-fluid">
        <div class="span6">
            <a href="" class="logo">SugarCRM</a>
        </div>
        <div class="span6">
            <div class="btn-toolbar pull-right">
                <div class="btn-group">
                    <a data-toggle="modal" title="Activity View Tour" href="#systemTour" class="btn btn-invisible"><i class="icon-road"></i> Tour</a>
                    <a data-toggle="modal" title="Feedback" href="#systemFeedback" class="btn btn-invisible"><i class="icon-comment"></i> Feedback</a>
                    <a data-toggle="modal" title="Support" href="#systemSupport" class="btn btn-invisible"><i class="icon-question-sign"></i> Support</a>
                </div>
            </div>
        </div>
    </div>
</footer>

{literal}


<script id="included_template" type="text/x-handlebars-template">
    <th colspan='5' style='text-align: right;'>{{str "LBL_INCLUDED_TOTAL" "Forecasts"}}</th>
    <th>{{formatNumber includedAmount}}</th>
    <th>{{formatNumber includedBest}}</th>
    <th>{{formatNumber includedLikely}}</th>
</script>

<script id="overall_template" type="text/x-handlebars-template">
    <th colspan='5' style='text-align: right;'>{{str "LBL_OVERALL_TOTAL" "Forecasts"}}</th>
    <th>{{formatNumber overallAmount}}</th>
    <th>{{formatNumber overallBest}}</th>
    <th>{{formatNumber overallLikely}}</th>
</script>

<script id="overall_manager_template" type="text/x-handlebars-template">
    <tr>
        <td>{{str "LBL_OVERALL_TOTAL" "Forecasts"}}</td>
        <td>{{formatNumber amount}}</td>
        <td>{{formatNumber quota}}</td>
        <td>{{formatNumber best_case}}</td>
        <td>{{formatNumber best_adjusted}}</td>
        <td>{{formatNumber likely_case}}</td>
        <td>{{formatNumber likely_adjusted}}</td>
    </tr>
</script>
{/literal}

<div class="content"></div>
<script src='{$configFile}'></script>
<script src='modules/Forecasts/metadata/base/helper/hbt-helpers.js'></script>
<script src='modules/Forecasts/metadata/base/lib/ClickToEdit.js'></script>
<script src='modules/Forecasts/metadata/base/lib/BucketGridEnum.js'></script>
<script src='modules/Forecasts/metadata/base/lib/ForecastsUtils.js'></script>
<script src='modules/Forecasts/metadata/base/views/alert-view.js'></script>
<script src='modules/Forecasts/tpls/SidecarView.js'></script>
<script src='include/javascript/twitterbootstrap/js/bootstrap-tooltip.js'></script>
<script src='include/javascript/twitterbootstrap/js/bootstrap-popover.js'></script>
<script src='include/javascript/twitterbootstrap/js/bootstrapx-clickover.js'></script>
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

            var forecastData = {/literal} {$initData} {literal};

                // get default selections for filter and category
                app.defaultSelections = forecastData.defaultSelections;
                app.initData = forecastData.initData;
                    app.viewModule = {/literal}'{$module}';{literal}
                app.AUTH_ACCESS_TOKEN = authAccessToken;
                app.AUTH_REFRESH_TOKEN = authAccessToken;
                //app.config.show_buckets = {/literal}'{$forecast_opportunity_buckets}' == '1'?true:false;{literal}
                app.init({
                    el: "forecasts",
                    contentEl: ".content",
                    //keyValueStore: app.sugarAuthStore, //override the keyValueStore
                    callback: function(app) {
                        app.user.set(app.initData.selectedUser);
                        app.start();
                    }
                });
                return app;
            }
            }));
     })(SUGAR.App);

    //Call initForecast with the session id as token
    var App = SUGAR.App.forecasts.initForecast({/literal}'{$token}'{literal});

    // should already be logged in to sugar, don't need to log in to sidecar.
    // TODO: we will need to remove this when we get the OAuth stuff working...
    App.api.isAuthenticated = function() {
        return true;
    };

    App.api.debug = App.config.debugSugarApi;
</script>
{/literal}
