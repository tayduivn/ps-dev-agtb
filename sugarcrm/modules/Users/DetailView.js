/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: DetailView.js 55503 2010-03-22 15:01:56Z jmertic $

var appel;
function generatepwd(id)
{
    callback = {
        success: function(o)
        {
            checkok=o.responseText;
            if (checkok.charAt(0) != '1')
                YAHOO.SUGAR.MessageBox.show({title: SUGAR.language.get("Users", "LBL_CANNOT_SEND_PASSWORD"), msg: checkok});
            else
                YAHOO.SUGAR.MessageBox.show({title: SUGAR.language.get("Users", "LBL_PASSWORD_SENT"), msg: SUGAR.language.get("Users", "LBL_NEW_USER_PASSWORD_2")} );
        },
        failure: function(o)
        {
            YAHOO.SUGAR.MessageBox.show({title: SUGAR.language.get("Users", "LBL_CANNOT_SEND_PASSWORD"), msg: SUGAR.language.get("app_strings", "LBL_AJAX_FAILURE")});
        }
    }
	PostData = '&to_pdf=1&module=Users&action=GeneratePassword&userId='+id;
	YAHOO.util.Connect.asyncRequest('POST', 'index.php', callback, PostData);	
}

function set_return_user_and_save(popup_reply_data)
{
	var form_name = popup_reply_data.form_name;
	var name_to_value_array;
	if(popup_reply_data.selection_list)
	{
		name_to_value_array = popup_reply_data.selection_list;
	}else if(popup_reply_data.teams){
		name_to_value_array = new Array();
		for (var the_key in popup_reply_data.teams){
			name_to_value_array.push(popup_reply_data.teams[the_key].team_id);
		}
	}else
	{
		name_to_value_array = popup_reply_data.name_to_value_array;
	}
	
	var query_array =  new Array();
	for (var the_key in name_to_value_array)
	{
		if(the_key == 'toJSON')
		{
			/* just ignore */
		}
		else
		{
			query_array.push("record[]="+name_to_value_array[the_key]);
		}
	}
	query_array.push('user_id='+get_user_id(form_name));
	query_array.push('action=AddUserToTeam');
	query_array.push('module=Teams');
	var query_string = query_array.join('&');
	
	var returnstuff = http_fetch_sync('index.php',query_string);
	
	document.location.reload(true);
}

function get_user_id(form_name)
{
	return window.document.forms[form_name].elements['user_id'].value;
}

function user_status_display(field){
	switch (field){
	
		case 'RegularUser':
			//BEGIN SUGARCRM flav!=sales ONLY
		    document.getElementById("calendar_options").style.display="";
			document.getElementById("edit_tabs").style.display="";
			//END SUGARCRM flav!=sales ONLY
		    document.getElementById("locale").style.display="";
			document.getElementById("settings").style.display="";
			document.getElementById("information").style.display="";
			break;
			
		case 'GroupUser':
			//BEGIN SUGARCRM flav!=sales ONLY
		    document.getElementById("calendar_options").style.display="none";
			document.getElementById("edit_tabs").style.display="none";
			//END SUGARCRM flav!=sales ONLY
		    document.getElementById("locale").style.display="none";
			document.getElementById("settings").style.display="none";
			document.getElementById("information").style.display="none";
            //BEGIN SUGARCRM flav=pro ONLY
			if(document.getElementById("pdf")) {
				document.getElementById("pdf").style.display="none";
			}
            //END SUGARCRM flav=pro ONLY
            document.getElementById("email_options_link_type").style.display="none";
	    break;

		case 'PortalUser':
			//BEGIN SUGARCRM flav!=sales ONLY
		    document.getElementById("calendar_options").style.display="none";
			document.getElementById("edit_tabs").style.display="none";
			//END SUGARCRM flav!=sales ONLY
		    document.getElementById("locale").style.display="none";
			document.getElementById("settings").style.display="none";
			document.getElementById("information").style.display="none";
			//BEGIN SUGARCRM flav=pro ONLY
			if(document.getElementById("pdf")) {
				document.getElementById("pdf").style.display="none";
			}
            //END SUGARCRM flav=pro ONLY
            document.getElementById("email_options_link_type").style.display="none";
	    break;
	}
}
