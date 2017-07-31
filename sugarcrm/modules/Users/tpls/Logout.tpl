{sugar_getscript file="sidecar/minified/sidecar.min.js"}
{sugar_getscript file="cache/config.js"}
{sugar_getscript file="cache/include/javascript/sugar_grp7.min.js"}
<script language="javascript">
    var App;
    App = SUGAR.App.init({ldelim}
    {rdelim});
    App.logout();
    document.location = "{$REDIRECT_URL}";
</script>
