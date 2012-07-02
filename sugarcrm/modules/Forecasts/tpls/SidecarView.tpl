<link rel="stylesheet" href="../sidecar/lib/chosen/chosen.css"/>
<link rel="stylesheet" href="themes/default/css/bootstrap.css"/>
<link rel="stylesheet" href="../sidecar/lib/jquery-ui/css/smoothness/jquery-ui-1.8.18.custom.css"/>
<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600" rel="stylesheet" type="text/css">
{literal}
<style type="text/css">
	.view {
		border:none;
	}
</style>
{/literal}

<script src="../sidecar/lib/jquery-ui/js/jquery-ui-1.8.18.custom.min.js"></script>
<script src="../sidecar/lib/backbone/underscore.js"></script>
<script src="../sidecar/lib/backbone/backbone.js"></script>
<script src="../sidecar/lib/handlebars/handlebars-1.0.0.beta.6.js"></script>
<script src="../sidecar/lib/stash/stash.js"></script>
<script src="../sidecar/lib/async/async.js"></script>
<script src="../sidecar/lib/jstree/jquery.jstree.js"></script>
<script src="../sidecar/lib/chosen/chosen.jquery.js"></script>
<script src="../sidecar/lib/sinon/sinon.js"></script>
<script src="../sidecar/lib/jeditable/jquery.jeditable.js"></script>
<script src="../sidecar/lib/datatables/media/js/jquery.dataTables.js"></script>
<script src="../sidecar/lib/datatables/media/js/dataTables.fnMultiFilter.js"></script>

{* App Scripts *}
<script src='../sidecar/lib/sugarapi/sugarapi.js'></script>
<script src='../sidecar/src/app.js'></script>
<script src='../sidecar/src/utils/utils.js'></script>
<script src='../sidecar/src/core/cache.js'></script>
<script src="../sidecar/src/core/events.js"></script>
<script src='../sidecar/src/core/error.js'></script>
<script src='../sidecar/src/view/template.js'></script>
<script src='../sidecar/src/core/context.js'></script>
<script src='../sidecar/src/core/controller.js'></script>
<script src='../sidecar/src/core/router.js'></script>

<script src='../sidecar/src/core/language.js'></script>
<script src='../sidecar/src/core/metadata-manager.js'></script>
<script src='../sidecar/src/core/acl.js'></script>
<script src='../sidecar/src/core/user.js'></script>
<script src='../sidecar/src/utils/logger.js'></script>

<script src='clients/forecasts/config.js'></script>

<script src='../sidecar/src/data/bean.js'></script>
<script src='../sidecar/src/data/bean-collection.js'></script>
<script src='../sidecar/src/data/data-manager.js'></script>
<script src='../sidecar/src/data/validation.js'></script>

<script src='../sidecar/src/view/hbt-helpers.js'></script>
<script src='../sidecar/src/view/view-manager.js'></script>
<script src='../sidecar/src/view/component.js'></script>
<script src='../sidecar/src/view/view.js'></script>
<script src='../sidecar/src/view/field.js'></script>
<script src='../sidecar/src/view/layout.js'></script>
<script src='../sidecar/src/view/alert.js'></script>

<script src='../sidecar/extensions/modules/Forecasts/hbt-helpers.js'></script>

<script src='clients/forecasts/layouts/forecasts/forecasts-layout.js'></script>
<script src='clients/forecasts/views/forecastsWorksheet/forecastsWorksheet.js'></script>
<script src='clients/forecasts/views/tree/tree.js'></script>
<script src='clients/forecasts/views/chartOptions/chartOptions.js'></script>
<script src='clients/forecasts/views/forecastsCommitted/forecastsCommitted.js'></script>
<script src='clients/forecasts/views/forecastsSubnav/forecastsSubnav.js'></script>
<script src='clients/forecasts/views/progress/progress.js'></script>
<script src='clients/forecasts/views/chart/chart.js'></script>
<script src='clients/forecasts/views/alert/alert-view.js'></script>

<script src='modules/Forecasts/tpls/SidecarView.js'></script>
<script src='include/javascript/sugarAuthStore.js'></script>
<script type="text/javascript" src="include/SugarCharts/Jit/js/Jit/jit.js"></script>
<script type="text/javascript" src="include/SugarCharts/Jit/js/sugarCharts.js"></script>

<div class="view-forecastsSubnav subnav"></div>
<div id="alert" class="alert-top"></div>
<div id="core-module">
    <div id="forecasts" style="" >
        <div class="container-fluid">
            <div class="row-fluid">
                <div class="span2" id="drawer">
                    <a class="drawerTrig btn btn-mini pull-right"><i class="icon-chevron-left icon-sm"></i></a>
                    <div class="bordered">
                        <div class="view-forecastsFilter"></div>
                        <div class="view-chartOptions"></div>
                        <div class="view-tree"></div>
                    </div>
                </div>
                <div id="charts" class="span10">
                    <div class="row-fluid">
                        <div class="view-chart"></div>
                        <div class="span5">
                            <div class="tab-pane active" id="overview">
                                <div class="block" id="moduleTwitter">
                                    <div class="view-progress"></div>
                                </div>
                            </div>
                        </div>
                        <hr/>
                        <div class="topline thumbnail">
                          <div class="row-fluid">
                              <div class="view-forecastsCommitted"></div>
                          </div>
                          <hr>
                          <div>
                              <div id="view-sales-rep" style="display:none">
                                  <div class="view-forecastsWorksheet"></div>
                                  <div class="view-summary"></div>
                              </div>
                              <div id="view-manager" style="display:none">
                                  <div class="view-forecastsWorksheetManager"></div>
                                  <div class="view-summary"></div>
                              </div>
                          </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="content"></div>

{literal}
<script language="javascript">
    var syncResult, view, layout, html;

    SUGAR.App.sugarAuthStore.set('AuthAccessToken', {/literal}'{$token}'{literal});

    (function(app) {
         app.augment("forecasts", {
            initForecast: function(authAccessToken) {
                app.AUTH_ACCESS_TOKEN = authAccessToken;
                app.AUTH_REFRESH_TOKEN = authAccessToken;
                app.init({
                    el: "forecasts",
                    contentEl: ".content"
                    //keyValueStore: app.sugarAuthStore //override the keyValueStore
                });
                return app;
            }
         });
     })(SUGAR.App);

     //Call initForecast with the session id as token
     var App = SUGAR.App.forecasts.initForecast({/literal}'{$token}'{literal});


    App.viewModule = {/literal}'{$module}';{literal}

    // should already be logged in to sugar, don't need to log in to sidecar.
    App.api.isAuthenticated = function() {

        // Grab user data
        var userData = $.ajax(App.config.serverUrl + '/Forecasts/me', {
            dataType: "json"
        }).done(function(data){
            //  Set current User data
            App.user.set(data.current_user);
        });

        return true;
    };


    App.api.debug = App.config.debugSugarApi;
    App.start();
</script>
{/literal}
