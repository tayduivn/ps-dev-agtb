// Copyright 2010, Fonality Inc. ALL RIGHTS RESERVED.

var no_rollout = 0;
var t;
var time_out;

// rollover function pops up DTHML

function createDialDiv(){
	dialDiv = document.createElement('div');
	dialDiv.className = 'dataLabel';
	dialDiv.style.background = '#ffffff';
	dialDiv.style.color = '#c60c30';
	dialDiv.style.position = 'absolute';
	dialDiv.id = 'ajaxDialDiv';
	document.body.appendChild(dialDiv);
	this.dialDiv = document.getElementById('ajaxDialDiv');
}

function positionStatus(){
	if(document.documentElement != 'undefined'){
		viewwindow = document.documentElement;
	} else {
		viewwindow = document.body;
	}
	this.dialDiv.style.top = viewwindow.scrollTop + (viewwindow.clientHeight / 2) - 20 + 'px';
	dialDivRegion = YAHOO.util.Dom.getRegion(this.dialDiv);
	dialDivWidth = dialDivRegion.right-dialDivRegion.left;
	this.dialDiv.style.left = YAHOO.util.Dom.getViewportWidth()/2-dialDivWidth/2+'px';
}

function showDialStatus(text){
	if(!this.dialDiv){
		this.createDialDiv();
	}
	else{
		this.dialDiv.style.display='';
	}
	this.dialDiv.innerHTML="<table style='border-color: #c60c30; border-width: thin; border-style: solid;'><tr height='20'><td style='color: #c60c30; font-size: 14px'><strong>&nbsp;" + text + "&nbsp;</strong></td></tr></table>";
	this.positionStatus();
	if(!this.shown){
		this.shown=true;
		this.dialDiv.style.display='';
		if(window.onscroll)this.oldOnScroll=window.onscroll;
		window.onscroll=this.positionStatus;
	}
}

function hideDialStatus(){
	if(!this.shown)
		return;
	this.shown=false;
	if(this.oldOnScroll){
		window.onscroll=this.oldOnScroll;
	} else {
		window.onscroll='';
	}
	this.dialDiv.style.display='none';
}

function ccall_number(span, parent_type, parent_id, contact_id, action, popup){
	var num;
	if(typeof(span) == 'string')
	{
		num = span;
	}
	else{
		if(navigator.appVersion.indexOf("MSIE")!=-1){
			num = span.innerText;
		} else {
			num = span.textContent;
		}
	}

	//ajaxStatus.showStatus("<table style='margin-top:100px; border-color: #c60c30; border-width: thin; border-style: solid;'><tr height='20'><td style='color: #c60c30; font-size: 14px'><strong>Dialing " + num + "</strong></td></tr></table>");
	showDialStatus("Dialing " + num);

	var dt=new Date();

	// post the url through ajax
	my_http_fetch_async('uae_dial_ajax.php', 'phone='+encodeURIComponent(num)+'&parent_type='+parent_type+'&parent_id='+parent_id+'&contact_id='+contact_id+'&action='+action+'&time='+dt.getTime(), popup);
}

function my_http_fetch_async(url,post_data,popup) {
	global_xmlhttp = getXMLHTTPinstance();
	var method = 'GET';

	if(typeof(post_data) != 'undefined') method = 'POST';
	try {
		global_xmlhttp.open(method, url,true);
	}
	catch(e) {
		alert('message:'+e.message+":url:"+url);
	}
	if(method == 'POST') {
		global_xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	}
	global_xmlhttp.onreadystatechange = function() {
		if(global_xmlhttp.readyState==4) {
			if(global_xmlhttp.status == 200) {
				var responseText = global_xmlhttp.responseText;
				if(responseText.substr(0,5) != "error"){
					// 3 seconds to make sure the call is connected
					setTimeout(function(){
						hideDialStatus();
						// if need to be redirected
						if(responseText != ''){
							if(popup == '1'){
								window.open(responseText);
							} else {
								document.location.href = responseText;
							}
						}
					},3000);
				} else {
					var error_msg;
					var error_txt = '';
					var confirm_txt = '';
					var redir = '';
					error_msg = responseText.substr(6);
					if(error_msg == "Authentication failed"){
						confirm_txt = "Your Fonality username & password are either missing or incorrect.  Click OK to go to your PBX Settings page, or click Cancel to stay on this page.";
						redir = "index.php?module=fonuae_PBXSettings&action=EditView";
						if(pbx_setting_id != ''){
							redir += "&record=" + pbx_setting_id;
						}
					} else if(error_msg == "Your SugarCRM session has expired"){
						error_txt = "\nYou will now be redirected to the login page.";
					} else {
						error_txt = '';
					}
					showDialStatus(error_msg);
					if(error_txt != ''){
						alert(error_msg + error_txt);
					}
					if(confirm_txt != ''){
						if(confirm(confirm_txt)){
							window.location.href = redir;
						}
					}
					setTimeout(function(){
						hideDialStatus();
					},3000);
					if(error_msg == "Your SugarCRM session has expired"){
						location.reload(true);
					}
				}
			} else {
				alert("Internal Error.\nPlease contact your administrator");
			}
		}
	}
	global_xmlhttp.send(post_data);
}
