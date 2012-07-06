<?php

require_once('include/MVC/View/SugarView.php');

class SidecarView extends SugarView
{
    public function SidecarView($bean = null, $view_object_map = array())
    {
        $this->options['use_table_container'] = false;
        parent::SugarView($bean, $view_object_map);
    }

    public function getThemeCss()
    {
        $themeObject = SugarThemeRegistry::current();

        $html = '<link rel="stylesheet" type="text/css" href="'.$themeObject->getCSSURL('bootstrap.css').'" />';
        $html .= '<link rel="stylesheet" type="text/css" href="../sidecar/lib/jquery-ui/css/smoothness/jquery-ui-1.8.18.custom.css" />';
        $html .= '<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600" rel="stylesheet" type="text/css">';
        return $html;
    }

    public function _displayJavascript()
    {
        parent::_displayJavascript();

        echo "<script src='../sidecar/lib/jquery-ui/js/jquery-ui-1.8.18.custom.min.js'></script>
<script src='../sidecar/lib/backbone/underscore.js'></script>
<script src='../sidecar/lib/backbone/backbone.js'></script>
<script src='../sidecar/lib/handlebars/handlebars-1.0.0.beta.6.js'></script>
<script src='../sidecar/lib/stash/stash.js'></script>
<script src='../sidecar/lib/async/async.js'></script>
<script src='../sidecar/lib/jstree/jquery.jstree.js'></script>
<script src='../sidecar/lib/chosen/chosen.jquery.js'></script>
<script src='../sidecar/lib/sinon/sinon.js'></script>
<script src='../sidecar/lib/jeditable/jquery.jeditable.js'></script>
<script src='../sidecar/lib/datatables/media/js/jquery.dataTables.js'></script>
<script src='../sidecar/lib/datatables/media/js/dataTables.fnMultiFilter.js'></script>
<script src='../sidecar/lib/sugarapi/sugarapi.js'></script>
<script src='../sidecar/src/app.js'></script>
<script src='../sidecar/src/utils/utils.js'></script>
<script src='../sidecar/src/core/cache.js'></script>
<script src='../sidecar/src/core/events.js'></script>
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
<script type='text/javascript' src='include/SugarCharts/Jit/js/Jit/jit.js'></script>
<script type='text/javascript' src='include/SugarCharts/Jit/js/sugarCharts.js'></script>";
    }

}
