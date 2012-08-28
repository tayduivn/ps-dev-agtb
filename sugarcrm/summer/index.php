<?php
if (empty($session_id)) session_start();
if(!empty($_REQUEST['token'])) {
    chdir('..');
    if(!defined('sugarEntry'))define('sugarEntry', true);
    include 'include/entryPoint.php';
    require_once 'summer/splash/BoxOfficeClient.php';
    $box = BoxOfficeClient::getInstance();
    $box->createSession();
    // reload
    if(empty($_SESSION['authenticated_user_id'])) {
        $box->noLogin();
    }
    header("Location: index.php");
    die();
}
if (empty($_SESSION['authenticated_user_id'])) {
    header('Location: splash/');
    die();
}

?>
<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="x-ua-compatible" content="IE=8">
    <link rel="stylesheet" href="../sidecar/lib/chosen/chosen.css"/>

    <!-- Third party library scripts -->
    <script type='text/javascript' src='https://www.google.com/jsapi'></script>
    <script>
        google.load('visualization', '1', {packages:['corechart', 'geochart', 'gauge']});
    </script>

    <script src="../sidecar/lib/jquery/jquery.min.js"></script>
    <script src="../sidecar/lib/jquery/jquery.iframe.transport.js"></script>
    <script src="../sidecar/lib/jquery-ui/js/jquery-ui-1.8.18.custom.min.js"></script>
    <script src="../sidecar/lib/backbone/underscore.js"></script>
    <script src="../sidecar/lib/backbone/backbone.js"></script>
    <script src="../sidecar/lib/handlebars/handlebars-1.0.0.beta.6.js"></script>
    <script src="../sidecar/lib/stash/stash.js"></script>
    <script src="../sidecar/lib/async/async.js"></script>
    <script src="../sidecar/lib/chosen/chosen.jquery.js"></script>
    <script src="../sidecar/lib/sugar/sugar.searchahead.js"></script>
    <script src="../sidecar/lib/sugar/sugar.timeago.js"></script>
    <script src="lib/jquery/jquery.fancybox-1.3.4.js"></script>
    <script
        src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDhofIE96RHrdEd7mBRLaHeYoPrFcBakac&sensor=true"></script>
    <script src="lib/Crypto/Crypto.js"></script>

    <!-- App Scripts -->
    <script src='../sidecar/lib/sugarapi/sugarapi.js'></script>
    <script src='../sidecar/src/app.js'></script>
    <script src='../sidecar/src/utils/date.js'></script>
    <script src='../sidecar/src/utils/utils.js'></script>
    <script src='../sidecar/src/core/cache.js'></script>
    <script src="../sidecar/src/core/events.js"></script>
    <script src='../sidecar/src/core/error.js'></script>
    <script src='error.js'></script>
    <script src='sugarAuthStore.js'></script>
    <script src='../sidecar/src/view/template.js'></script>
    <script src='../sidecar/src/core/context.js'></script>
    <script src='../sidecar/src/core/controller.js'></script>
    <script src='../sidecar/src/core/router.js'></script>
    <script src='../sidecar/src/core/language.js'></script>
    <script src='../sidecar/src/core/metadata-manager.js'></script>
    <script src='../sidecar/src/core/acl.js'></script>
    <script src='../sidecar/src/core/user.js'></script>
    <script src='user.js'></script>
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
    <script src='views/alert-view.js'></script>
    <script src='../sidecar/src/view/alert.js'></script>
    <script src='summer.js'></script>

    <script src="../sidecar/lib/sinon/sinon.js"></script>
    <script src="../sidecar/lib/sugarapi/demoServerData.js"></script>
    <script src="../sidecar/lib/sugarapi/demoRestServer.js"></script>
    <script src="../sidecar/lib/sinon/sinon.js"></script>
    <link rel="stylesheet" href="../sidecar/lib/jquery-ui/css/smoothness/jquery-ui-1.8.18.custom.css"/>
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600" rel="stylesheet" type="text/css">


    <!-- Styleguide scripts that are not useful yet -->
    <script src="../styleguide/assets/js/bootstrap-transition.js"></script>
    <script src="../styleguide/assets/js/bootstrap-collapse.js"></script>
    <script src="../styleguide/assets/js/bootstrap-scrollspy.js"></script>
    <script src="../styleguide/assets/js/bootstrap-tab.js"></script>
    <script src="../styleguide/assets/js/bootstrap-typeahead.js"></script>
    <script src="lib/twitterbootstrap/js/jquery.dataTables.js"></script>
    <script src="lib/twitterbootstrap/js/wicked.js"></script>
    <script src="../styleguide/styleguide/js/jquery.jeditable.js"></script>
    <script src="lib/twitterbootstrap/js/editable.js"></script>

    <!-- Styleguide scripts that need to be included -->

    <script src="../styleguide/assets/js/bootstrap-button.js"></script>
    <script src="../styleguide/assets/js/bootstrap-tooltip.js"></script>
    <script src="../styleguide/assets/js/bootstrap-popover.js"></script>
    <script src="../styleguide/assets/js/bootstrap-dropdown.js"></script>
    <script src="../styleguide/assets/js/bootstrap-modal.js"></script>
    <script src="../styleguide/assets/js/bootstrap-alert.js"></script>
    <script src="summer-ui.js"></script>

</head>
<body>
<div>
    <div id="sidecar">
        <div id="alert" class="alert-top">

        </div>
        <div id="header">

        </div>
        <div id="subnav">

        </div>
        <div id="content">

        </div>
        <div id="footer">

        </div>
        <div id="todo-widget-container" class="btn-toolbar pull-right" style="float: right;z-index: 1031;position: fixed;right: 210px;bottom: -6px;">

        </div>
    </div>
</div>

<script language="javascript">
    var syncResult, view, layout, html;
    SUGAR.App[SUGAR.App.config.authStore || "cache"].set('AuthAccessToken', '<?php echo session_id();?>');
    SUGAR.App[SUGAR.App.config.authStore || "cache"].set('AuthRefreshToken', '<?php echo session_id();?>');

    var App = SUGAR.App.init({
        el:"#sidecar",
        callback:function (app) {

            app.start();
            app.api.me("read", null, null, {
                success:function (data) {
                    if (data.current_user) {
                        app.user._reset(data ? data.current_user : null);
                    }
                    app.trigger("app:login:success", data);
                    //callback(null, data);
                },
                error:function (error) {
                    //callback(error);
                }
            });
        }

    });

    App.api.debug = App.config.debugSugarApi;
</script>
</body>
</html>
