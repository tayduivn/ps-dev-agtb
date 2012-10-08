<script src='include/javascript/sugarAuthStore.js'></script>
<script type='text/javascript' src='include/SugarCharts/Jit/js/Jit/jit.js'></script>
<script type='text/javascript' src='include/SugarCharts/Jit/js/sugarCharts.js'></script>

<div class="content"></div>
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
<script src='{$configFile}'></script>
<script src='modules/Forecasts/clients/base/helper/hbt-helpers.js'></script>
<script src='modules/Forecasts/clients/base/lib/ClickToEdit.js'></script>
<script src='modules/Forecasts/clients/base/lib/BucketGridEnum.js'></script>
<script src='modules/Forecasts/clients/base/lib/ForecastsUtils.js'></script>
<script src='modules/Forecasts/clients/base/views/alert-view/alert-view.js'></script>
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

    App.api.debug = App.config.debugSugarApi;
</script>
{/literal}
