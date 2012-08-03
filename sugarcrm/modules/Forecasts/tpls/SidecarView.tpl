<div id="alert" class="alert-top"></div>
<div id="core-module">
    <div id="forecasts" style="" >
        <div class="row-fluid">
            <div class="view-forecastsSubnav subnav" id="headerbar"></div>
        </div>
        <div class="container-fluid">
            <div class="row-fluid">
                <div class="span8">
                    <div class="view-forecastsCommitted"></div>
                    <div class="view-filter"></div>
                    <div>
                        <div id="view-sales-rep" style="display:none">
                            <div class="view-forecastsWorksheet"></div>
                        </div>
                        <div id="view-manager" style="display:none">
                            <div class="view-forecastsWorksheetManager"></div>
                        </div>
                    </div>
                </div>
                <div class="span4 tab-content" id="folded">
                    <div class="tab-pane active" id="overview">
                        <div class="viz">
                            <div class="view-chart"></div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="block" id="guages">
                            <div class="view-progress"></div>
                        </div>
                    </div>
                </div>

                <div class="view-chartOptions"></div>
                <div class="view-tree"></div>
                <div class="view-timeframes"></div>


            {*<div class="span2" id="drawer">*}
            {*<a class="drawerTrig btn btn-mini pull-right"><i class="icon-chevron-left icon-sm"></i></a>*}
            {*<div class="bordered">*}
                    {*</div>*}
                {*</div>*}
                {*<div id="charts" class="span10">*}
                    {*<div class="row-fluid">*}
                        {*<div class="span12">*}
                            {*<hr/>*}
                        {*</div>*}
                    {*</div>*}
                    {*<div class="row-fluid">*}
                        {*<div class="topline thumbnail span12">*}
                          {**}
                          {*<hr>*}
                          {**}
                        {*</div>*}
                    {*</div>*}
                {*</div>*}
            </div>
        </div>
    </div>
</div>

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
<script src='clients/forecasts/config.js'></script>
<script src='clients/forecasts/helper/hbt-helpers.js'></script>
<script src='clients/forecasts/lib/ClickToEdit.js'></script>
<script src='clients/forecasts/lib/BucketGridEnum.js'></script>
<script src='clients/forecasts/layouts/forecasts/forecasts-layout.js'></script>
<script src='clients/forecasts/views/forecastsWorksheet/forecastsWorksheet.js'></script>
<script src='clients/forecasts/views/forecastSchedule/forecastSchedule.js'></script>
<script src='clients/forecasts/views/tree/tree.js'></script>
<script src='clients/forecasts/views/filter/filter.js'></script>
<script src='clients/forecasts/views/timeframes/timeframes.js'></script>
<script src='clients/forecasts/views/chartOptions/chartOptions.js'></script>
<script src='clients/forecasts/views/forecastsCommitted/forecastsCommitted.js'></script>
<script src='clients/forecasts/views/forecastsSubnav/forecastsSubnav.js'></script>
<script src='clients/forecasts/views/progress/progress.js'></script>
<script src='clients/forecasts/views/chart/chart.js'></script>
<script src='clients/forecasts/views/alert/alert-view.js'></script>
<script scr='clients/forecasts/fields/userLink/userLink.js'></script>
<script scr='clients/forecasts/fields/recordLink/recordLink.js'></script>
<script src='modules/Forecasts/tpls/SidecarView.js'></script>
{literal}
<script language="javascript">
    var syncResult, view, layout, html;

    SUGAR.App.sugarAuthStore.set('AuthAccessToken', {/literal}'{$token}'{literal});
    SUGAR.App.sugarAuthStore.set('AuthRefreshToken', {/literal}'{$token}'{literal});

    (function(app) {
         app.augment("forecasts", {
            initForecast: function(authAccessToken) {

                var forecastData = {/literal} {$initData} {literal};

                // get default selections for filter and category
                app.defaultSelections = forecastData.defaultSelections;
                app.initData = forecastData.initData;
                app.viewModule = {/literal}'{$module}';{literal}
                app.AUTH_ACCESS_TOKEN = authAccessToken;
                app.AUTH_REFRESH_TOKEN = authAccessToken;
                app.config.showBuckets = {/literal}'{$forecast_opportunity_buckets}' == '1'?true:false;{literal}
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
         });
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
