<script language="javascript" src="../sidecar/lib/jquery-ui/js/jquery-ui-1.8.18.custom.min.js"></script>
<script language="javascript" src="../sidecar/lib/backbone/underscore.js"></script>
<script language="javascript" src="../sidecar/lib/backbone/backbone.js"></script>
<script language="javascript" src="../sidecar/lib/handlebars/handlebars-1.0.0.beta.6.js"></script>
<script language="javascript" src="../sidecar/lib/stash/stash.js"></script>
<script language="javascript" src="../sidecar/lib/async/async.js"></script>
<script language="javascript" src="../sidecar/lib/jstree/jquery.jstree.js"></script>
<link rel="stylesheet" href="../sidecar/lib/chosen/chosen.css"/>
<script language="javascript" src="../sidecar/lib/chosen/chosen.jquery.js"></script>
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
<script src='../sidecar/src/view/views/list-view.js'></script>
<script src='../sidecar/src/view/views/detail-view.js'></script>
<script src='../sidecar/src/view/views/activity-view.js'></script>
<script src='../sidecar/src/view/views/header-view.js'></script>
<script src='../sidecar/src/view/views/test-view.js'></script>
<script src='../sidecar/src/view/views/alert-view.js'></script>
<script src='../sidecar/src/view/layouts/columns-layout.js'></script>
<script src='../sidecar/src/view/layouts/fluid-layout.js'></script>

<script src='clients/base/views/grid/grid-view.js'></script>
<script src='clients/base/views/tree/tree-view.js'></script>
<script src='clients/base/views/tree/filter-view.js'></script>

<script src='include/SidecarView/SidecarView.js'></script>

<script language="javascript" src="../sidecar/lib/sinon/sinon.js"></script>
<link rel="stylesheet" href="../sidecar/lib/twitterbootstrap/bootstrap/css/bootstrap.css"/>
<link rel="stylesheet" href="../sidecar/lib/jquery-ui/css/smoothness/jquery-ui-1.8.18.custom.css"/>
<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600" rel="stylesheet" type="text/css">

<link rel="stylesheet" href="css/bootstrap-core.css"/>

<script src="../sidecar/lib/twitterbootstrap/bootstrap/js/bootstrap-tooltip.js"></script>
<script src="../sidecar/lib/twitterbootstrap/bootstrap/js/bootstrap-popover.js"></script>
<script src="../sidecar/lib/twitterbootstrap/bootstrap/js/bootstrap-dropdown.js"></script>
<script src="../sidecar/lib/twitterbootstrap/bootstrap/js/bootstrap-modal.js"></script>
<script src="../sidecar/lib/twitterbootstrap/bootstrap/js/bootstrap-alert.js"></script>
<script src="../sidecar/lib/twitterbootstrap/bootstrap/js/application.js"></script>
<script language="javascript" src="../sidecar/lib/jeditable/jquery.jeditable.mini.js"></script>
<script language="javascript" src="../sidecar/lib/datatables/media/js/jquery.dataTables.js"></script>

<div id="core-module">
    <div id="core" style="" >
        <div id="alert" class="alert-top">

        </div>
        <div id="header">

        </div>
        <div id="content">

        </div>
        <div id="footer">

        </div>

    </div>
    <div id="layout" style=""></div>
</div>
{literal}
<script language="javascript">
    var syncResult, view, layout, html;
    var App = SUGAR.App.init({
        el: "#core"
    });
    App.api.debug = App.config.debugSugarApi;
    App.start();
</script>
{/literal}