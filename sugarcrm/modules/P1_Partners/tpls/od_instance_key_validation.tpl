{literal}
<script type="text/javascript">

function alphanumeric(alphane) {
        var numaric = alphane;
        var num = '0123456789';
        for(var i=0; i<num.length; i++) {
                if(alphane.charAt(0) == num.charAt(i))
                        return false;
       }
       for(var j=0; j<numaric.length; j++) {
                var alphaa = numaric.charAt(j);
                var hh = alphaa.charCodeAt(0);
                if((hh > 47 && hh<59) || (hh > 64 && hh<91) || (hh > 96 && hh<123))                
	    { }
                else    {
                        return false;
                }
        }
        return true;
}

function getJax() {
        msxmll = {
                0: 'MSXML2.XMLHTTP.3.0',
                1: 'MSXML2.XMLHTTP',
                2: 'Microsoft.XMLHTTP'
        }

        var http;

        try
        {
                http = new XMLHttpRequest();
        }
        catch(e) {
                for(var i in msxmll) {
                        try
                        {
                                http = new ActiveXObject(msxmll[i]);                                
                                break;
                        }
                        catch(e){}
                }
        }
        finally
        {
                return http;
        }
}

function jaxit(odurl) {
        xmlHttp = getJax();
        var instance_name = document.getElementById('instance_name').value;
        if (typeof(xmlHttp) != 'object') {
                handleJaxError("getJax() = false");
        }
        document.getElementById('invalidinstance').value = '0';
        xmlHttp.onreadystatechange=function() {
                if(xmlHttp.readyState==4) {
                        if(xmlHttp.status == 200) {
                                if(xmlHttp.responseText=='1'){
                                        document.getElementById('jaxresult').innerHTML='<font color=#FF0000><b>On-demand account name already exists. Try again</b></font>';
                                        document.getElementById('invalidinstance').value = '1';
                                } else if(xmlHttp.responseText=='0') {
                                        if(alphanumeric(instance_name) == false || instance_name.length < 3 || instance_name.length > 16){
                                                document.getElementById('jaxresult').innerHTML='<font color=#FF0000><b>Account name must be between 3-16 characters.<br />No spaces.<br />Must contain only letters and numbers.<br />First character must be a letter.</b></font>';
                                                document.getElementById('invalidinstance').value = '1';
                                        }
                                        else {
                                                document.getElementById('jaxresult').innerHTML='<font color=#348017><b>On-demand account name accepted</b></font>';
                                                document.getElementById('invalidinstance').value = '0';
                                        }
                                } else {
                                        document.getElementById('jaxresult').innerHTML='<font color=#FF0000><b>Invalid entry</b></font>';
                                        document.getElementById('invalidinstance').value = '1';
                                }
                        }
                        else
                                handleJaxError("jaxit() = " + xmlHttp.status);
                }
		if(xmlHttp.readyState==0) {
			handleJaxError("jaxit() = " + xmlHttp.readyState);
        	}
	}
                
	var url = 'https://sugarinternal.sugarondemand.com/'+odurl+instance_name;	
	xmlHttp.open("GET",url,true);
        xmlHttp.send(null);
}

function handleJaxError(str) {
        document.getElementById('jaxresult').innerHTML = "<font color=#FF0000><b>Error.  Please contact webmaster@sugarcrm.com with the following information:<br>" + str + "</b><br><br>Please try clearing your web browser's cookies and history before trying again.</font>";
        document.getElementById('evalWizForm').instance_name.focus();
        document.getElementById('invalidinstance').value = '1';
}

function proevaljaxit() {
	
	xmlHttp = getJax();
	var useremail = document.evalWizForm.useremail.value;

        if (typeof(xmlHttp) != 'object') {
                handleEvalJaxError("getJax() = false");
        }

        xmlHttp.onreadystatechange=function() {
                if(xmlHttp.readyState==4) {
                        if(xmlHttp.status == 200) {
                                if(xmlHttp.responseText != '0' && xmlHttp.responseText != 'Invalid data passed in'){
					document.getElementById('evaljaxtext').innerHTML = 'Would you like to convert <br />your trial instance?';
					document.getElementById('evaljaxresult').innerHTML = xmlHttp.responseText;
                                }
                        }
                        else {
                                handleEvalJaxError("proevaljaxit() = " + xmlHttp.status);
                	}
		} 
        }
                
        var url = 'https://www.sugarcrm.com/sugarshop/ion3-tools/check.php?qt=qen&a='+useremail;
        xmlHttp.open("GET",url,true);
        xmlHttp.send(null);
}


function ceevaljaxit() {
        
        cexmlHttp = getJax();
        var useremail = document.evalWizForm.useremail.value;

        if (typeof(cexmlHttp) != 'object') {
                handleEvalJaxError("getJax() = false");
        }

        cexmlHttp.onreadystatechange=function() {
                if(cexmlHttp.readyState==4) {
                        if(cexmlHttp.status == 200) {
                                if(cexmlHttp.responseText != '0' && cexmlHttp.responseText != 'Invalid data passed in.' && cexmlHttp.responseText != ''){
                                        document.getElementById('ceevaljaxtext').innerHTML = '<b><u>OR</u></b> Would you like to convert <br />your trial instance?<br /><span style="font-size:10px">(unselect to enter account name)</span>';
                                        document.getElementById('ceevaljaxresult').innerHTML = cexmlHttp.responseText;
                                }
                        }
                        else {
                                handleEvalJaxError("ceevaljaxit() = " + cexmlHttp.status);
                        }
                } 
        }
                
        var url = 'https://www.sugarcrm.com/sugarshop/ceod_check_instance.php?uemail='+useremail;
        cexmlHttp.open("GET",url,true);
       	cexmlHttp.send(null);
}

function evaljaxit() {
//      proevaljaxit();
//      ceevaljaxit();
//        set_od_domain('.sugarondemand.com');
}

function handleEvalJaxError(str) {
        document.getElementById('evaljaxresult').innerHTML = "<font color=#FF0000><b>Error.  Please contact webmaster@sugarcrm.com with the following information:<br>" + str + "</b><br><br>Please try clearing your web browser's cookies and history before trying again.</font>";
}

function checkInstanceName() {
        if(document.getElementById('invalidinstance').value != "" && document.getElementById('invalidinstance').value == '1') {
                alert('Please enter a valid On-Demand Account Name');
                document.getElementById('evalWizForm').instance_name.focus();
                return false;
        }
        else
                return true;
}

function getevalinfo(ename) {
	document.evalWizForm.evalaccount.value = ename;
	if(ename != "") {
		document.getElementById('instance_name').disabled = true;
		document.getElementById('instance_name').style.background ='#dedede';
		document.evalWizForm.instance_name.value = document.evalWizForm.evalaccount.value;
		document.getElementById('jaxresult').innerHTML = "";
		document.getElementById('invalidinstance').value = '0';
	}
	else{
		document.getElementById('instance_name').disabled = false;
		document.getElementById('instance_name').style.background ='#ffffff';
		document.evalWizForm.instance_name.focus();
		document.getElementById('invalidinstance').value = '1';
	}
}
function set_od_domain(setting) {
        myOption = setting;
        document.getElementById('od_domain').innerHTML = myOption;      
}

window.onload = evaljaxit;
</script>
{/literal}

