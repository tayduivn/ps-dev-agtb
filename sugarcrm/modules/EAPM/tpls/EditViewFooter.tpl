<script type="text/javascript" src="modules/EAPM/EAPMEdit.js"></script>
<script type="text/javascript" src="cache/include/externalAPI.cache.js?s={$SUGAR_VERSION}&c={$JS_CUSTOM_VERSION}"></script>
<script type="text/javascript">
EAPMFormName = 'EditView';
EAPMEditStart(
{if is_admin($current_user) } true {else} false {/if}
);
</script>