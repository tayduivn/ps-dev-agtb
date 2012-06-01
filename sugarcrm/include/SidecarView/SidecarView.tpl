<script src="../sidecar/lib/jquery-ui/js/jquery-ui-1.8.18.custom.min.js"></script>
<script src="../sidecar/lib/backbone/underscore.js"></script>
<script src="../sidecar/lib/backbone/backbone.js"></script>
<script src="../sidecar/lib/handlebars/handlebars-1.0.0.beta.6.js"></script>
<script src="../sidecar/lib/stash/stash.js"></script>
<script src="../sidecar/lib/async/async.js"></script>
<script src="../sidecar/lib/jstree/jquery.jstree.js"></script>
<link rel="stylesheet" href="../sidecar/lib/chosen/chosen.css"/>
<script src="../sidecar/lib/chosen/chosen.jquery.js"></script>
<!-- App Scripts -->

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

<script src='../sidecar/src/view/alert.js'></script>
<script src='../sidecar/src/view/hbt-helpers.js'></script>
<script src='../sidecar/src/view/view-manager.js'></script>
<script src='../sidecar/src/view/component.js'></script>
<script src='../sidecar/src/view/view.js'></script>
<script src='../sidecar/src/view/field.js'></script>
<script src='../sidecar/src/view/layout.js'></script>

<script src='../sidecar/src/view/views/header-view.js'></script>
<script src='../sidecar/src/view/views/test-view.js'></script>
<script src='../sidecar/src/view/views/alert-view.js'></script>
<script src='../sidecar/src/view/layouts/columns-layout.js'></script>
<script src='../sidecar/src/view/layouts/fluid-layout.js'></script>

<script src='clients/base/layouts/forecasts/forecasts-layout.js'></script>
<script src='clients/base/views/grid/grid.js'></script>
<script src='clients/base/views/tree/tree.js'></script>
<script src='clients/base/models/filter/filter.js'></script>
<script src='clients/base/views/filter/filter.js'></script>
<script src='clients/base/views/progress/progress.js'></script>
<script src='clients/base/views/chart/chart.js'></script>

<script src='include/SidecarView/SidecarView.js'></script>

<script src="../sidecar/lib/sinon/sinon.js"></script>

<link rel="stylesheet" href="themes/default/css/bootstrap.css"/>
<link rel="stylesheet" href="../sidecar/lib/jquery-ui/css/smoothness/jquery-ui-1.8.18.custom.css"/>
<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600" rel="stylesheet" type="text/css">

<script src="../sidecar/lib/jeditable/jquery.jeditable.js"></script>
<script src="../sidecar/lib/datatables/media/js/jquery.dataTables.js"></script>

<script type="text/javascript" src="include/SugarCharts/Jit/js/Jit/jit.js"></script>
<script type="text/javascript" src="include/SugarCharts/Jit/js/sugarCharts.js"></script>

{literal}
<style type="text/css">
    .view {
        border:none;
    }
</style>
{/literal}
<div class="content"></div>
<div class="subnav">
    <div class="btn-toolbar pull-left">
        <h1>Forecast: Sabra Khan</h1>
    </div>
    <div class="btn-toolbar pull-right">
        <div class="dropdown">
            <a href="#" data-toggle="dropdown" class="dropdown-toggle btn btn-success">Actions <i class="icon caret"></i></a>
            <ul class="dropdown-menu menu">
                <li><a href="#">Duplicate</a></li>
                <li><a href="#">Save</a></li>
                <li class="divider"></li>
                <li><a href="#">Note</a></li>
                <li><a href="#">Email</a></li>
                <li><a href="#">PDF</a></li>
                <li><a href="#">CSV</a></li>
            </ul>
        </div>
    </div>
</div>
<div id="core-module">
    <div id="core" style="" >
        <div class="container-fluid">
            <div class="row-fluid">
                <div class="span2" id="drawer">
                    <a class="drawerTrig btn btn-mini pull-right"><i class="icon-chevron-left icon-sm"></i></a>
                    <div class="bordered">
                        <div class="view-filter"></div>
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
        return true;
    };

    App.api.debug = App.config.debugSugarApi;
    App.start();
</script>
{/literal}
