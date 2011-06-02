<script type="text/javascript">
function allowInsideView() {ldelim}
document.getElementById('insideViewFrame').src = '{$URL}';
document.getElementById('insideViewConfirm').style.display = 'none';
document.getElementById('insideViewFrame').style.display = 'block';
YAHOO.util.Connect.asyncRequest('GET', 'index.php?module=Connectors&action=CallConnectorFunc&source_id=ext_rest_insideview&source_func=allowInsideView', {ldelim}{rdelim}, null);
{rdelim}
</script>
<div id='insideViewDiv' style='width:100%;overflow:hidden'>
  <div id='insideViewConfirm'>
  InsideView is a sales intelligence solution that helps you find, qualify, engage, and close more sales by providing relevant company information. The InsideView connector allows you to access useful information about the record you are currently viewing. In order to access this information, the connector sends information about you and your company to InsideView.
  <br/><br/>
  Do you authorize the sending of this information to InsideView in order to use the connector?
  <br/><br/><button onclick="allowInsideView(); return false;" class='button primary'>Yes, Connect to InsideView</button>
  </div>
  <iframe id='insideViewFrame' src='about:blank' style='border:0px; width:100%;height:480px;overflow:hidden;display:none;'></iframe>
</div>