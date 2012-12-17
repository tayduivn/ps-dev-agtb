<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=8, IE=9, IE=10" >
    </head>
    <body>
        <div id="sugarcrm">
            <div id="sidecar">
                <div id="alerts" class="alert-top"></div>
                <div id="header"></div>
                <div id="content">
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
            </div>
        </div>

        <script src='{$configFile}'></script>

        {literal}
        <script language="javascript">
            var syncResult, view, layout, html;

            (function(app) {
                if(!_.has(app, 'forecasts')) {
                    app.forecasts = {}
                }
                app.augment("forecasts", _.extend(app.forecasts, {
                    initForecast: function() {
                        app.viewModule = {/literal}'{$module}';{literal}
                        app.init({
                            el: "forecasts",
                            contentEl: ".content",
                            callback: function(app) {
                                app.start();
                            }
                        });
                        return app;
                    }
                }));
             })(SUGAR.App);

            //Call initForecast with the session id as token
            var App = SUGAR.App.forecasts.initForecast();

            App.api.debug = App.config.debugSugarApi;
        </script>
        {/literal}
    </body>
</html>