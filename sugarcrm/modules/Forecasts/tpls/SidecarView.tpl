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

<script src='config.js'></script>

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

<script src='../sidecar/src/view/layouts/fluid-layout.js'></script>

<script src='clients/base/layouts/forecasts/forecasts-layout.js'></script>
<script src='clients/base/views/grid/grid.js'></script>
<script src='clients/base/models/grid/grid.js'></script>
<script src='clients/base/views/tree/tree.js'></script>
<script src='clients/base/views/forecastsFilter/forecastsFilter.js'></script>
<script src='clients/base/models/chartOptions/chartOptions.js'></script>
<script src='clients/base/views/chartOptions/chartOptions.js'></script>
<script src='clients/base/views/forecastsSubnav/forecastsSubnav.js'></script>
<script src='clients/base/views/progress/progress.js'></script>
<script src='clients/base/views/chart/chart.js'></script>

<script src='modules/Forecasts/tpls/SidecarView.js'></script>

<script type="text/javascript" src="include/SugarCharts/Jit/js/Jit/jit.js"></script>
<script type="text/javascript" src="include/SugarCharts/Jit/js/sugarCharts.js"></script>

<div class="view-forecastsSubnav subnav"></div>
<div id="core-module">
    <div id="core" style="" >
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
                                    <div class="view-changeLog"></div>
                                </div>
                            </div>
                        </div>
                        <hr />
                        <div class="view-grid"></div>
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
    var App = SUGAR.App.init({
        el: "#core",
        contentEl: ".content"
    });


    App.viewModule = "{/literal}{$module}{literal}";

    // should already be logged in to sugar, don't need to log in to sidecar.
    App.api.isAuthenticated = function() {

/*****
 * BEGIN - TEMPORARY CODE FIX TO GET USER DATA TO THE VIEW TEMPLATE
 ****/
        // Grab user data from smarty assigned values
        var userData = { "id" : "{/literal}{$userData_id}{literal}",
                         "full_name" : "{/literal}{$userData_full_name}{literal}",
                         "user_name" : "{/literal}{$userData_user_name}{literal}",
                         "timezone" : "{/literal}{$userData_timezone}{literal}",
                         "datef" : "{/literal}{$userData_datef}{literal}",
                         "timef" : "{/literal}{$userData_timef}{literal}"
        };

        //  Set current User data
        App.user.set(userData);
/*****
* END - TEMPORARY CODE FIX TO GET USER DATA TO THE VIEW TEMPLATE
****/
        return true;
    };

    App.api.debug = App.config.debugSugarApi;
    App.start();
</script>
{/literal}
