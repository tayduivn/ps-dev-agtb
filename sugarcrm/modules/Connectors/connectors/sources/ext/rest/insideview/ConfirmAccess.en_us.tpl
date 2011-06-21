<script type="text/javascript">
function allowInsideView() {ldelim}
document.getElementById('insideViewFrame').src = '{$URL}';
document.getElementById('insideViewConfirm').style.display = 'none';
document.getElementById('insideViewFrame').style.display = 'block';
document.getElementById('insideViewDiv').style.height='400px';
YAHOO.util.Connect.asyncRequest('GET', 'index.php?module=Connectors&action=CallConnectorFunc&source_id=ext_rest_insideview&source_func=allowInsideView', {ldelim}{rdelim}, null);
{rdelim}
</script>
<div id='insideViewDiv' style='width:100%;overflow:hidden'>
  <div id='insideViewConfirm'>
  InsideView now comes preinstalled in Sugar, giving you access to relevant company information, contacts, news, and social media insights all within your CRM. The InsideView connector is a FREE service that automatically displays in your leads, accounts, contacts and opportunities.
  <br/><br/>
  By clicking 'Continue' you agree to InsideView's  <a href='http://www.insideview.com/cat-terms-use.html' target='_blank'>terms of use</a> . Your SugarCRM user information will be used to create your new InsideView account and will be protected in accordance with InsideView's <a href='http://www.insideview.com/cat-privacy.html' target='_blank'>privacy policy</a> . Click 'Continue' to get started with InsideView.
  <br/><br/><button onclick="allowInsideView(); return false;" class='button primary'>Continue</button>
  <br/><br/>
  </div>
  <iframe id='insideViewFrame' src='about:blank' style='border:0px; width:100%;height:480px;overflow:hidden;display:none;'></iframe>
</div>