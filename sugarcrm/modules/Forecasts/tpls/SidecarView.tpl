<link rel="stylesheet" type="text/css" href="{$css_url}" />
<div class="content" id="forecasts">
    <div class="alert-top">
        <div class="alert alert-process">
            <strong>Loading</strong>
            <div class="loading">
                <span class="l1"></span><span class="l2"></span><span class="l3"></span>
            </div>
        </div>
    </div>
</div>
<footer>
    <div class="row-fluid">
        <div class="span6">
            <a href="" class="logo">SugarCRM</a>
        </div>
        <div class="span6">
            <div class="btn-toolbar pull-right">
                <div class="btn-group">
                    <a data-toggle="modal" title="Activity View Tour" href="#systemTour" class="btn btn-invisible"><i class="icon-road"></i> Tour</a>
                    <a data-toggle="modal" title="Support" href="#systemSupport" class="btn btn-invisible"><i class="icon-question-sign"></i> Support</a>
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
                app.viewModule = {/literal}'{$module}';{literal}
                app.AUTH_ACCESS_TOKEN = authAccessToken;
                app.AUTH_REFRESH_TOKEN = authAccessToken;
                app.init({
                    el: "forecasts",
                    contentEl: ".content",
                    //keyValueStore: app.sugarAuthStore, //override the keyValueStore
                    callback: function(app) {
                        var url = app.api.buildURL("Forecasts/init");
                        app.api.call('GET', url, null, {success: function(forecastData) {
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
                        }});
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
