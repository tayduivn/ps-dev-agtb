<script type="text/javascript">
function allowInsideView() {ldelim}
document.getElementById('insideViewFrame').src = '{$URL}';
document.getElementById('insideViewConfirm').style.display = 'none';
document.getElementById('insideViewFrame').style.display = 'block';
YAHOO.util.Connect.asyncRequest('GET', 'index.php?module=Connectors&action=CallConnectorFunc&source_id=ext_rest_insideview&source_func=allowInsideView', {ldelim}{rdelim}, null);
{rdelim}
</script>
<div id='insideViewDiv' style='width:100%;height:400px;overflow:hidden'>
  <div id='insideViewConfirm'>
  <h1>Avast! Thar be a question.</h1>
  The <b>InsideView</b> connector fetches all sorts of useful information about the record you are currently viewing.<br>
    However, in order to do this the connector sends information about the company, and about you up to InsideView in order to find the relevant information (and connect/create an account for you on their server).
    <br>
    We just wanted to make sure this is okay.
  <br><button onclick="allowInsideView(); return false;">Sounds good</button>
  </div>
  <iframe id='insideViewFrame' src='about:blank' style='border:0px; width:100%;height:480px;overflow:hidden;display:none;'></iframe>
</div>