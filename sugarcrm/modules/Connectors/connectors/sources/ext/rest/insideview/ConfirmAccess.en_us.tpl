<script type="text/javascript">
function allowInsideView() {ldelim}
document.getElementById('insideViewFrame').src = '{$URL}';
document.getElementById('insideViewConfirm').style.display = 'none';
document.getElementById('insideViewFrame').style.display = 'block';
document.getElementById('insideViewDiv').style.height='400px';
YAHOO.util.Connect.asyncRequest('GET', 'index.php?module=Connectors&action=CallConnectorFunc&source_id=ext_rest_insideview&source_func=allowInsideView', {ldelim}{rdelim}, null);
{rdelim}
</script>
<div id='insideViewDiv' style='width:100%'>
  <div id='insideViewConfirm' class="detail view" style="padding: 10px;">
 
  <strong style='color: #d71e00;'>InsideView</strong> now comes preinstalled in <strong>SugarCRM</strong>, giving you access to relevant company information, contacts, news, and social media insights all within your CRM. The InsideView connector is a <strong>FREE</strong> service that automatically displays in your leads, accounts, contacts and opportunities.
  <br/><br/>
  By proceeding you agree to InsideView's  <a href='http://www.insideview.com/cat-terms-use.html' target='_blank' style='text-decoration: none;'>terms of use</a> and <a href='http://www.insideview.com/cat-privacy.html' target='_blank' style='text-decoration: none;'>privacy policy</a>.
  
  <hr>
  <img src="{$logo}" border="0" style="margin-bottom: 5px;"> <button onclick="allowInsideView(); return false;" class='button primary' style='float: right; border: 1px solid #d71e00; background-color: #eeeeee; background-image: none; text-shadow: none; color: #222; margin-top: 5px;'>Get Started</button>
  </div>
  <iframe id='insideViewFrame' src='about:blank' style='border:0px; width:100%;height:480px;overflow:hidden;display:none;'></iframe>
</div>