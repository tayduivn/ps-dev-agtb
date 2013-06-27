<!DOCTYPE HTML>
<HTML>
<body>
<script>
window.opener.postMessage('{$authorization|@json|escape:javascript}', '{$siteUrl|escape:javascript}')
window.close()
</script>
</body>
</HTML>