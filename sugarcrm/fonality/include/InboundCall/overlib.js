function create_new_overlib(id, theme, form, parent_module, opportunities, cases, projects, contacts) {
	var myover = "";
	if(opportunities == "1")
	{
		myover += "<a style='width: 150px; cursor: pointer'" +
			  " onClick='document." + form + ".type.value=\"Opportunities\";" +
			  "document." + form + ".parent_type.value=\"" + parent_module + "\";" +
			  "document." + form + ".parent_id.value=\"" + id + "\";" + 
			  "document." + form + ".submit();'>" +
                    	  "<img border='0' src='themes/default/images/Opportunities.gif' style='margin-right:5px' align='absmiddle'>" +
                    	  "Opportunity</a><br>";
	}
	
	if(cases == "1")
	{
		myover += "<a style='width: 150px; cursor: pointer'" +
			  " onClick='document." + form + ".type.value=\"Cases\";" +
			  "document." + form + ".parent_type.value=\"" + parent_module + "\";" +
			  "document." + form + ".parent_id.value=\"" + id + "\";" + 
			  "document." + form + ".submit();'>" +
                    	  "<img border='0' src='themes/default/images/Cases.gif' style='margin-right:5px'>" +
                    	  "Case</a><br>";
	}
	
	if(projects == "1")
	{
		myover += "<a style='width: 150px; cursor: pointer'" +
			  " onClick='document." + form + ".type.value=\"Projects\";" +
			  "document." + form + ".parent_type.value=\"" + parent_module + "\";" +
			  "document." + form + ".parent_id.value=\"" + id + "\";" + 
			  "document." + form + ".submit();'>" +
                    	  "<img border='0' src='themes/default/images/Project.gif' style='margin-right:5px'>" +
                    	  "Project</a><br>";
	}
	
	if(contacts == "1")
	{
		myover += "<a style='width: 150px; cursor: pointer'" +
			  " onClick='document." + form + ".type.value=\"Contacts\";" +
			  "document." + form + ".parent_type.value=\"" + parent_module + "\";" +
			  "document." + form + ".parent_id.value=\"" + id + "\";" + 
			  "document." + form + ".submit();'" +
                    	  "<img border='0' src='themes/default/images/Contacts.gif' style='margin-right:5px'>" +
                    	  "Contact</a>";
	}
    return overlib(myover, CAPTION, '<div style=\'float:left\'>Create New</div><div style=\'float: right\'>', STICKY, MOUSEOFF, 3000, CLOSETEXT, '<img border=0 src="themes/' + theme + '/images/close.gif">', WIDTH, 150, CLOSETITLE, SUGAR.language.get('app_strings', 'LBL_ADDITIONAL_DETAILS_CLOSE_TITLE'),
CLOSECLICK,FGCLASS,'olFgClass',CGCLASS,'olCgClass',BGCLASS,'olBgClass',TEXTFONTCLASS,'olFontClass',CAPTIONFONTCLASS,'olCapFontClass',CLOSEFONTCLASS,'olCloseFontClass');
}
