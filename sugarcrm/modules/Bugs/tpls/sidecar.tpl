<!-- sidecar example for using within sugar -->
<script language="javascript" src="../sidecar/lib/jquery/jquery.min.js"></script>
<script language="javascript" src="../sidecar/lib/jquery-ui/js/jquery-ui-1.8.18.custom.min.js"></script>
<script language="javascript" src="../sidecar/lib/backbone/underscore.js"></script>
<script language="javascript" src="../sidecar/lib/backbone/backbone.js"></script>
<script language="javascript" src="../sidecar/lib/handlebars/handlebars-1.0.0.beta.6.js"></script>
<script language="javascript" src="../sidecar/lib/stash/stash.js"></script>
<script language="javascript" src="../sidecar/lib/async/async.js"></script>
<link rel="stylesheet" href="../sidecar/lib/chosen/chosen.css"/>
<script language="javascript" src="../sidecar/lib/chosen/chosen.jquery.js"></script>
<!-- App Scripts -->
<script src='../sidecar/lib/sugarapi/sugarapi.js'></script>
<script src='../sidecar/src/app.js'></script>
<script src='../sidecar/src/utils/utils.js'></script>
<script src='../sidecar/src/core/cache.js'></script>
<script src="../sidecar/src/core/events.js"></script>
<script src='../sidecar/src/view/template.js'></script>
<script src='../sidecar/src/core/context.js'></script>
<script src='../sidecar/src/core/controller.js'></script>
<script src='../sidecar/src/core/router.js'></script>
<script src='../sidecar/src/core/language.js'></script>
<script src='../sidecar/src/core/metadata-manager.js'></script>
<script src='../sidecar/src/utils/logger.js'></script>
<script src='modules/Bugs/tpls/config.js'></script>

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
<script src='../sidecar/src/view/layouts/columns-layout.js'></script>
<script src='../sidecar/src/view/layouts/fluid-layout.js'></script>

<script language="javascript" src="../sidecar/tests/fixtures/metadata.js"></script>

<!--SUGAR REST interface fakeserver + data-->
<link rel="stylesheet" href="../sidecar/lib/twitterbootstrap/bootstrap/css/bootstrap.css"/>
<link rel="stylesheet" href="../sidecar/lib/jquery-ui/css/smoothness/jquery-ui-1.8.18.custom.css"/>
<script src="../sidecar/lib/twitterbootstrap/bootstrap/js/bootstrap.js"></script>


<script src="modules/Bugs/tpls/sidecar-example.js"></script>
{literal}
<style>
	.subhead table {
		margin-top: 0;
	}
</style>
{/literal}

<div id="sidecar" style="" ></div>

{literal}
<script language="javascript">
	var sidecar = SUGAR.App.init({el: "#sidecar", rest:"rest/v10", platform: "base"});
	sidecar.start();
</script>
{/literal}