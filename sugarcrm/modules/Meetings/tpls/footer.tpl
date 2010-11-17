{*
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
*}
{{include file='include/EditView/footer.tpl'}}

<div id="scheduler"></div>

<script type="text/javascript">
{literal}
function fill_invitees() { 
	if (typeof(GLOBAL_REGISTRY) != 'undefined')  {    
		SugarWidgetScheduler.fill_invitees(document.EditView);
	} 
}

//stop the form submit
YAHOO.util.Event.on("EditView", "submit", function(ev) {
  YAHOO.util.Event.stopEvent(ev);
  promptForRelatedInvite();
});
function promptForRelatedInvite(){
	var oldParentId = '{/literal}{$fields.parent_id.value}{literal}';
	//no need to ask if the parent_id field is empty
	if(document.getElementById('parent_id').value != "" && (document.getElementById('parent_type').value == "Contacts" || document.getElementById('parent_type').value == "Leads") && oldParentId != document.getElementById('parent_id').value){
		var confirmDeletePopup = new YAHOO.widget.SimpleDialog("Confirm ", 
				{
                width: "400px",
                draggable: true,
                constraintoviewport: true,
                modal: true,
                fixedcenter: true,
                text: SUGAR.language.get('Meetings', 'LBL_INVITE_PROMPT'),
                bodyStyle: "padding:5px",
                buttons: [{
                        text: "{/literal}{$APP.LBL_EMAIL_YES}{literal}",
                        handler: handleYes,
                        isDefault:true
                }, {
                        text: "{/literal}{$APP.LBL_EMAIL_NO}{literal}",
                        handler: handleNo
                }]
	     });
	     confirmDeletePopup.setHeader(SUGAR.language.get('Calls', 'LBL_INVITE_HEADER'));
	    confirmDeletePopup.render(document.body);
	}
}

var handleYes = function() {
       this.hide();
       document.getElementById('invite_parent_id').value = true;
       document.EditView.submit();
    };

    var handleNo = function() {
         this.hide();
         document.getElementById('invite_parent_id').value = false;
         document.EditView.submit();
     };
{/literal}

var root_div = document.getElementById('scheduler');
var sugarContainer_instance = new SugarContainer(document.getElementById('scheduler'));
sugarContainer_instance.start(SugarWidgetScheduler);
{literal}
if ( document.getElementById('save_and_continue') ) {
    var oldclick = document.getElementById('save_and_continue').attributes['onclick'].nodeValue;
    document.getElementById('save_and_continue').onclick = function(){
        fill_invitees();
        eval(oldclick);
    }
}
{/literal}
</script>
</form>