/**
 * Javascript file for Sugar
 *
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
 * by SugarCRM are Copyright (C) 2005 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: sugar_3.js 57264 2010-07-02 18:45:27Z kjing $

/**
 * Namespace for Sugar Objects
 */
if ( typeof(SUGAR) == "undefined" )	SUGAR = {};
if ( typeof(SUGAR.themes) == "undefined" )	SUGAR.themes = {};


    	/**
    	 * Namespace for Homepage
    	 */
    	 SUGAR.sugarHome= {};
    	/**
    	 * Namespace for Subpanel Utils
    	 */
    	SUGAR.subpanelUtils= {};
    	/**
    	 * AJAX status class
    	 */
    	SUGAR.ajaxStatusClass= {};
    	/**
    	 * Tab selector utils
    	 */
    	SUGAR.tabChooser= {};
    	/**
    	 * General namespace for Sugar utils
    	 */
    	SUGAR.util= {};
    	SUGAR.savedViews= {};
    	/**
    	 * Dashlet utils
    	 */
    	SUGAR.dashlets= {};
    	SUGAR.unifiedSearchAdvanced= {};

    	SUGAR.searchForm= {};
    	SUGAR.language= {};
    	SUGAR.Studio= {};
    	SUGAR.contextMenu= {};

    	SUGAR.config= {};

var nameIndex = 0;
var typeIndex = 1;
var requiredIndex = 2;
var msgIndex = 3;
var jstypeIndex = 5;
var minIndex = 10;
var maxIndex = 11;
var altMsgIndex = 15;
var compareToIndex = 7;
var arrIndex = 12;
var operatorIndex = 13;
var allowblank = 8;
var validate = new Array();
var maxHours = 24;
var requiredTxt = 'Missing Required Field:'
var invalidTxt = 'Invalid Value:'
var secondsSinceLoad = 0;
var inputsWithErrors = new Array();
var tabsWithErrors = new Array();
var lastSubmitTime = 0;
var alertList = new Array();
var oldStartsWith = '';


function isSupportedIE() {
	var userAgent = navigator.userAgent.toLowerCase() ;

	// IE Check supports ActiveX controls
	if (userAgent.indexOf("msie") != -1 && userAgent.indexOf("mac") == -1 && userAgent.indexOf("opera") == -1) {
		var version = navigator.appVersion.match(/MSIE (.\..)/)[1] ;
		if(version >= 5.5 ) {
			return true;
		} else {
			return false;
		}
	}
}

SUGAR.isIE = isSupportedIE();
var isSafari = (navigator.userAgent.toLowerCase().indexOf('safari')!=-1);

// escapes regular expression characters
RegExp.escape = function(text) { // http://simon.incutio.com/archive/2006/01/20/escape
  if (!arguments.callee.sRE) {
    var specials = ['/', '.', '*', '+', '?', '|','(', ')', '[', ']', '{', '}', '\\'];
    arguments.callee.sRE = new RegExp('(\\' + specials.join('|\\') + ')', 'g');
  }
  return text.replace(arguments.callee.sRE, '\\$1');
}

function addAlert(type, name,subtitle, description,time, redirect) {
	var addIndex = alertList.length;
	alertList[addIndex]= new Array();
	alertList[addIndex]['name'] = name;
	alertList[addIndex]['type'] = type;
	alertList[addIndex]['subtitle'] = subtitle;
	alertList[addIndex]['description'] = description.replace(/<br>/gi, "\n").replace(/&amp;/gi,'&').replace(/&lt;/gi,'<').replace(/&gt;/gi,'>').replace(/&#039;/gi,'\'').replace(/&quot;/gi,'"');
	alertList[addIndex]['time'] = time;
	alertList[addIndex]['done'] = 0;
	alertList[addIndex]['redirect'] = redirect;
}
function checkAlerts() {
	secondsSinceLoad += 1;
	var mj = 0;
	var alertmsg = '';
	for(mj = 0 ; mj < alertList.length; mj++) {
		if(alertList[mj]['done'] == 0) {
			if(alertList[mj]['time'] < secondsSinceLoad && alertList[mj]['time'] > -1 ) {
				alertmsg = alertList[mj]['type'] + ":" + alertList[mj]['name'] + "\n" +alertList[mj]['subtitle']+ "\n"+ alertList[mj]['description'] + "\n\n";
				alertList[mj]['done'] = 1;
				if(alertList[mj]['redirect'] == '') {
					alert(alertmsg);
				}
				else if(confirm(alertmsg)) {
					window.location = alertList[mj]['redirect'];
				}
			}
		}
	}

	setTimeout("checkAlerts()", 1000);
}

function toggleDisplay(id) {
	if(this.document.getElementById(id).style.display == 'none') {
		this.document.getElementById(id).style.display = '';
		if(this.document.getElementById(id+"link") != undefined) {
			this.document.getElementById(id+"link").style.display = 'none';
		}
		if(this.document.getElementById(id+"_anchor") != undefined)
			this.document.getElementById(id+"_anchor").innerHTML='[ - ]';
	}
	else {
		this.document.getElementById(id).style.display = 'none'
		if(this.document.getElementById(id+"link") != undefined) {
			this.document.getElementById(id+"link").style.display = '';
		}
		if(this.document.getElementById(id+"_anchor") != undefined)
			this.document.getElementById(id+"_anchor").innerHTML='[+]';
	}
}

function checkAll(form, field, value) {
	for (i = 0; i < form.elements.length; i++) {
		if(form.elements[i].name == field)
			form.elements[i].checked = value;
	}
}

function replaceAll(text, src, rep) {
	offset = text.toLowerCase().indexOf(src.toLowerCase());
	while(offset != -1) {
		text = text.substring(0, offset) + rep + text.substring(offset + src.length ,text.length);
		offset = text.indexOf( src, offset + rep.length + 1);
	}
	return text;
}

function addForm(formname) {
	validate[formname] = new Array();
}

function addToValidate(formname, name, type, required, msg) {
	if(typeof validate[formname] == 'undefined') {
		addForm(formname);
	}
	validate[formname][validate[formname].length] = new Array(name, type,required, msg);
}

function addToValidateRange(formname, name, type,required,  msg,min,max) {
	addToValidate(formname, name, type,required,  msg);
	validate[formname][validate[formname].length - 1][jstypeIndex] = 'range'
	validate[formname][validate[formname].length - 1][minIndex] = min;
	validate[formname][validate[formname].length - 1][maxIndex] = max;
}

function addToValidateIsValidDate(formname, name, type, required, msg) {
	addToValidate(formname, name, type, required, msg);
	validate[formname][validate[formname].length - 1][jstypeIndex] = 'date'
}

function addToValidateIsValidTime(formname, name, type, required, msg) {
	addToValidate(formname, name, type, required, msg);
	validate[formname][validate[formname].length - 1][jstypeIndex] = 'time'
}

function addToValidateDateBefore(formname, name, type, required, msg, compareTo) {
	addToValidate(formname, name, type,required,  msg);
	validate[formname][validate[formname].length - 1][jstypeIndex] = 'isbefore'
	validate[formname][validate[formname].length - 1][compareToIndex] = compareTo;
}

function addToValidateDateBeforeAllowBlank(formname, name, type, required, msg, compareTo, allowBlank) {
	addToValidate(formname, name, type,required,  msg);
	validate[formname][validate[formname].length - 1][jstypeIndex] = 'isbefore'
	validate[formname][validate[formname].length - 1][compareToIndex] = compareTo;
	validate[formname][validate[formname].length - 1][allowblank] = allowBlank;
}

function addToValidateBinaryDependency(formname, name, type, required, msg, compareTo) {
	addToValidate(formname, name, type, required, msg);
	validate[formname][validate[formname].length - 1][jstypeIndex] = 'binarydep';
	validate[formname][validate[formname].length - 1][compareToIndex] = compareTo;
}

function addToValidateComparison(formname, name, type, required, msg, compareTo) {
	addToValidate(formname, name, type, required, msg);
	validate[formname][validate[formname].length - 1][jstypeIndex] = 'comparison';
	validate[formname][validate[formname].length - 1][compareToIndex] = compareTo;
}

function addToValidateIsInArray(formname, name, type, required, msg, arr, operator) {
	addToValidate(formname, name, type, required, msg);
	validate[formname][validate[formname].length - 1][jstypeIndex] = 'in_array';
	validate[formname][validate[formname].length - 1][arrIndex] = arr;
	validate[formname][validate[formname].length - 1][operatorIndex] = operator;
}

function addToValidateVerified(formname, name, type, required, msg, arr, operator) {
	addToValidate(formname, name, type, required, msg);
	validate[formname][validate[formname].length - 1][jstypeIndex] = 'verified';
}

function addToValidateLessThan(formname, name, type, required, msg, max, max_field_msg) {
	addToValidate(formname, name, type, required, msg);
	validate[formname][validate[formname].length - 1][jstypeIndex] = 'less';
    validate[formname][validate[formname].length - 1][maxIndex] = max;
    validate[formname][validate[formname].length - 1][altMsgIndex] = max_field_msg;

}
function addToValidateMoreThan(formname, name, type, required, msg, min) {
	addToValidate(formname, name, type, required, msg);
	validate[formname][validate[formname].length - 1][jstypeIndex] = 'more';
    validate[formname][validate[formname].length - 1][minIndex] = min;
}
function removeFromValidate(formname, name) {
	for(i = 0; i < validate[formname].length; i++){
		if(validate[formname][i][nameIndex] == name){
			validate[formname].splice(i, 1);
		}
	}
}
function checkValidate(formname, name) {
    if(validate[formname]){
	    for(i = 0; i < validate[formname].length; i++){
	        if(validate[formname][i][nameIndex] == name){
	            return true;
	        }
	    }
	}
    return false;
}
var formsWithFieldLogic=null;
var formWithPrecision =null;
function addToValidateFieldLogic(formId,minFieldId, maxFieldId, defaultFieldId, lenFieldId,type,msg){
	this.formId = document.getElementById(formId);
	this.min=document.getElementById(minFieldId);
	this.max= document.getElementById(maxFieldId);
	this._default= document.getElementById(defaultFieldId);
	this.len = document.getElementById(lenFieldId);
	this.msg = msg;
	this.type= type;
}
//@params: formid- Dom id of the form containing the precision and float fields
//         valudId- Dom id of the field containing a float whose precision is to be checked.
//         precisionId- Dom id of the field containing precision value.
function addToValidatePrecision(formId, valueId, precisionId){
	this.form = document.getElementById(formId);
	this.float = document.getElementById(valueId);
	this.precision = document.getElementById(precisionId);
}

//function checkLength(value, referenceValue){
//	return value
//}

function isValidPrecision(value, precision){
	value = trim(value.toString());
	if(precision == '')
		return true;
	if(value == '')
	    return true;
	//#27021
	if( (precision == "0") ){
		if (value.indexOf(".")== -1){
			return true;
		}else{
			return false;
		}
	}
	//#27021   end
	var actualPrecision = value.substr(value.indexOf(".")+1, value.length).length;
	return actualPrecision == precision;
}
function toDecimal(original, precision) {
    precision = (precision == null) ? 2 : precision;
    num = Math.pow(10, precision);
	temp = Math.round(original*num)/num;
	if((temp * 100) % 100 == 0)
		return temp + '.00';
	if((temp * 10) % 10 == 0)
		return temp + '0';
	return temp
}

function isInteger(s) {
	if (typeof s == "string" && s == "")
        return true;
    if(typeof num_grp_sep != 'undefined' && typeof dec_sep != 'undefined')
	{
		s = unformatNumberNoParse(s, num_grp_sep, dec_sep).toString();
	}
	return parseFloat(s) == parseInt(s) && !isNaN(s);
}

function isNumeric(s) {
  if(!/^-*[0-9\.]+$/.test(s)) {
   		return false
   }
   else {
		return true;
   }
}

var date_reg_positions = {'Y': 1,'m': 2,'d': 3};
var date_reg_format = '([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})'
function isDate(dtStr) {

	if(dtStr.length== 0) {
		return true;
	}

    // Check that we have numbers
	myregexp = new RegExp(date_reg_format)
	if(!myregexp.test(dtStr))
		return false

    m = '';
    d = '';
    y = '';

    var dateParts = dtStr.match(date_reg_format);
    for(key in date_reg_positions) {
        index = date_reg_positions[key];
        if(key == 'm') {
           m = dateParts[index];
        } else if(key == 'd') {
           d = dateParts[index];
        } else {
           y = dateParts[index];
        }
    }

    // Check that date is real
    var dd = new Date(y,m,0);
    // reject negative years
    if (y < 1)
        return false;
    // reject month less than 1 and greater than 12
    if (m > 12 || m < 1)
        return false;
    // reject days less than 1 or days not in month (e.g. February 30th)
    if (d < 1 || d > dd.getDate())
        return false;
    return true;
}

function getDateObject(dtStr) {
	if(dtStr.length== 0) {
		return true;
	}

	myregexp = new RegExp(date_reg_format)

	if(myregexp.exec(dtStr)) var dt = myregexp.exec(dtStr)
	else return false;

	var yr = dt[date_reg_positions['Y']];
	var mh = dt[date_reg_positions['m']];
	var dy = dt[date_reg_positions['d']];
    var dtar = dtStr.split(' ');
    if(typeof(dtar[1])!='undefined' && isTime(dtar[1])) {//if it is a timedate, we should make date1 to have time value
        var t1 = dtar[1].replace(/am/i,' AM');
        var t1 = t1.replace(/pm/i,' PM');
        date1 = new Date(Date.parse(mh+'/'+dy+ '/'+yr+' '+t1));
    }
    else
    {
        var date1 = new Date();
        date1.setFullYear(yr); // xxxx 4 char year
        date1.setMonth(mh-1); // 0-11 Bug 4048: javascript Date obj months are 0-index
        date1.setDate(dy); // 1-31
    }
	return date1;
}

function isBefore(value1, value2) {
	var d1 = getDateObject(value1);
	var d2 = getDateObject(value2);
    if(typeof(d2)=='boolean') {// if d2 is not set, we should let it pass, the d2 may not need to be set. the empty check should not be done here.
        return true;
    }
	return d2 >= d1;
}

function isValidEmail(emailStr) {
	
    if(emailStr.length== 0) {
		return true;
	}
	// cn: bug 7128, a period at the end of the string mangles checks. (switched to accept spaces and delimiters)
	var lastChar = emailStr.charAt(emailStr.length - 1);
	if(!lastChar.match(/[^\.]/i)) {
		return false;
	}
	//bug 40068, According to rules in page 6 of http://www.apps.ietf.org/rfc/rfc3696.html#sec-3,
	//first character of local part of an email address
	//should not be a period i.e. '.'

	var firstLocalChar=emailStr.charAt(0);
	if(firstLocalChar.match(/\./)){
		return false;
	}

	//bug 40068, According to rules in page 6 of http://www.apps.ietf.org/rfc/rfc3696.html#sec-3,
	//last character of local part of an email address
	//should not be a period i.e. '.'

	var pos=emailStr.lastIndexOf("@");
	var localPart = emailStr.substr(0, pos);
	var lastLocalChar=localPart.charAt(localPart.length - 1);
	if(lastLocalChar.match(/\./)){
		return false;
	}


	var reg = /@.*?;/g;
    var results;
	while ((results = reg.exec(emailStr)) != null) {
			var original = results[0];
			parsedResult = results[0].replace(';', '::;::');
			emailStr = emailStr.replace (original, parsedResult);
	}

	reg = /.@.*?,/g;
	while ((results = reg.exec(emailStr)) != null) {
			var original = results[0];
			//Check if we were using ; as a delimiter. If so, skip the commas
            if(original.indexOf("::;::") == -1) {
                var parsedResult = results[0].replace(',', '::;::');
			    emailStr = emailStr.replace (original, parsedResult);
            }
	}

	// mfh: bug 15010 - more practical implementation of RFC 2822 from http://www.regular-expressions.info/email.html, modifed to accept CAPITAL LETTERS
	//if(!/[a-zA-Z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?/.test(emailStr))
	//	return false

	//bug 40068, According to rules in page 6 of http://www.apps.ietf.org/rfc/rfc3696.html#sec-3,
	//allowed special characters ! # $ % & ' * + - / = ?  ^ _ ` . { | } ~ in local part
    var emailArr = emailStr.split(/::;::/);
	for (var i = 0; i < emailArr.length; i++) {
		var emailAddress = emailArr[i];
		if (trim(emailAddress) != '') {
			if(!/^\s*[\w.%+\-&'#!\$\*=\?\^_`\{\}~\/]+@([A-Z0-9-]+\.)*[A-Z0-9-]+\.[\w-]{2,}\s*$/i.test(emailAddress) &&
			   !/^.*<[A-Z0-9._%+\-&'#!\$\*=\?\^_`\{\}~]+?@([A-Z0-9-]+\.)*[A-Z0-9-]+\.[\w-]{2,}>\s*$/i.test(emailAddress)) {

			   return false;
			} // if
		}
	} // for
	return true;
}

function isValidPhone(phoneStr) {
	if(phoneStr.length== 0) {
		return true;
	}
	if(!/^[0-9\-\(\)\s]+$/.test(phoneStr))
		return false
	return true
}
function isFloat(floatStr) {
	if(floatStr.length== 0) {
		return true;
	}
	if(!(typeof(num_grp_sep)=='undefined' || typeof(dec_sep)=='undefined')) {
		floatStr = unformatNumberNoParse(floatStr, num_grp_sep, dec_sep).toString();
    }

	return /^(-)?[0-9\.]+$/.test(floatStr);
}
function isDBName(str) {

	if(str.length== 0) {
		return true;
	}
	// must start with a letter
	if(!/^[a-zA-Z][a-zA-Z\_0-9]*$/.test(str))
		return false
	return true
}
var time_reg_format = "[0-9]{1,2}\:[0-9]{2}";
function isTime(timeStr) {
	time_reg_format = time_reg_format.replace('([ap]m)', '');
	time_reg_format = time_reg_format.replace('([AP]M)', '');
	if(timeStr.length== 0){
		return true;
	}
	//we now support multiple time formats
	myregexp = new RegExp(time_reg_format)
	if(!myregexp.test(timeStr))
		return false

	return true
}

function inRange(value, min, max) {
    if (typeof num_grp_sep != 'undefined' && typeof dec_sep != 'undefined')
       value = unformatNumberNoParse(value, num_grp_sep, dec_sep).toString();
	return value >= min && value <= max;
}

function bothExist(item1, item2) {
	if(typeof item1 == 'undefined') { return false; }
	if(typeof item2 == 'undefined') { return false; }
	if((item1 == '' && item2 != '') || (item1 != '' && item2 == '') ) { return false; }
	return true;
}

trim = YAHOO.lang.trim;


function check_form(formname) {
	if (typeof(siw) != 'undefined' && siw
		&& typeof(siw.selectingSomething) != 'undefined' && siw.selectingSomething)
			return false;
	return validate_form(formname, '');
}

function add_error_style(formname, input, txt, flash) {
	if (typeof flash == "undefined")
		flash = true;
	try {
	inputHandle = typeof input == "object" ? input : document.forms[formname][input];
	style = get_current_bgcolor(inputHandle);

	// strip off the colon at the end of the warning strings
	if ( txt.substring(txt.length-1) == ':' )
	    txt = txt.substring(0,txt.length-1)

	if(inputHandle.parentNode.innerHTML.search(txt) == -1) {
        errorTextNode = document.createElement('span');
        errorTextNode.className = 'required';
        errorTextNode.innerHTML = '<br />' + txt;
        if ( inputHandle.parentNode.className.indexOf('x-form-field-wrap') != -1 ) {
            inputHandle.parentNode.parentNode.appendChild(errorTextNode);
        }
        else {
            inputHandle.parentNode.appendChild(errorTextNode);
        }
        if (flash)
        	inputHandle.style.backgroundColor = "#FF0000";
        inputsWithErrors.push(inputHandle);
	}
    if (flash)
    {
		// We only need to setup the flashy-flashy on the first entry, it loops through all fields automatically
	    if ( inputsWithErrors.length == 1 ) {
	      for(wp = 1; wp <= 10; wp++) {
	        window.setTimeout('fade_error_style(style, '+wp*10+')',1000+(wp*50));
	      }
	    }
		if(typeof (window[formname + "_tabs"]) != "undefined") {
	        var tabView = window[formname + "_tabs"];
	        var parentDiv = YAHOO.util.Dom.getAncestorByTagName(inputHandle, "div");
	        if ( tabView.get ) {
	            var tabs = tabView.get("tabs");
	            for (var i in tabs) {
	                if (tabs[i].get("contentEl") == parentDiv
	                		|| YAHOO.util.Dom.isAncestor(tabs[i].get("contentEl"), inputHandle))
	                {
	                    tabs[i].get("labelEl").style.color = "red";
	                    if ( inputsWithErrors.length == 1 )
	                        tabView.selectTab(i);
	                }
	            }
	        }
		}
		window.setTimeout("inputsWithErrors[" + (inputsWithErrors.length - 1) + "].style.backgroundColor = null;", 2000);
    }

  } catch ( e ) {
      // Catch errors here so we don't allow an incomplete record through the javascript validation
  }
}

/**
 * removes all error messages for the current form
 */
function clear_all_errors() {
    for(var wp = 0; wp < inputsWithErrors.length; wp++) {
        if(typeof(inputsWithErrors[wp]) !='undefined' && typeof inputsWithErrors[wp].parentNode != 'undefined' && inputsWithErrors[wp].parentNode != null) {
            if ( inputsWithErrors[wp].parentNode.className.indexOf('x-form-field-wrap') != -1 )
            {
                inputsWithErrors[wp].parentNode.parentNode.removeChild(inputsWithErrors[wp].parentNode.parentNode.lastChild);
            }
            else
            {
                inputsWithErrors[wp].parentNode.removeChild(inputsWithErrors[wp].parentNode.lastChild);
            }
        }
	}
	if (inputsWithErrors.length == 0) return;

	if ( YAHOO.util.Dom.getAncestorByTagName(inputsWithErrors[0], "form") ) {
        var formname = YAHOO.util.Dom.getAncestorByTagName(inputsWithErrors[0], "form").getAttribute("name");
        if(typeof (window[formname + "_tabs"]) != "undefined") {
            var tabView = window[formname + "_tabs"];
            if ( tabView.get ) {
                var tabs = tabView.get("tabs");
                for (var i in tabs) {
                    tabs[i].get("labelEl").style.color = "";
                }
            }
        }
        inputsWithErrors = new Array();
    }
}

function get_current_bgcolor(input) {
	if(input.currentStyle) {// ie
		style = input.currentStyle.backgroundColor;
		return style.substring(1,7);
	}
	else {// moz
		style = '';
		styleRGB = document.defaultView.getComputedStyle(input, '').getPropertyValue("background-color");
		comma = styleRGB.indexOf(',');
		style += dec2hex(styleRGB.substring(4, comma));
		commaPrevious = comma;
		comma = styleRGB.indexOf(',', commaPrevious+1);
		style += dec2hex(styleRGB.substring(commaPrevious+2, comma));
		style += dec2hex(styleRGB.substring(comma+2, styleRGB.lastIndexOf(')')));
		return style;
	}
}

function hex2dec(hex){return(parseInt(hex,16));}
var hexDigit=new Array("0","1","2","3","4","5","6","7","8","9","A","B","C","D","E","F");
function dec2hex(dec){return(hexDigit[dec>>4]+hexDigit[dec&15]);}

function fade_error_style(normalStyle, percent) {
	errorStyle = 'c60c30';
	var r1 = hex2dec(errorStyle.slice(0,2));
	var g1 = hex2dec(errorStyle.slice(2,4));
	var b1 = hex2dec(errorStyle.slice(4,6));

	var r2 = hex2dec(normalStyle.slice(0,2));
	var g2 = hex2dec(normalStyle.slice(2,4));
	var b2 = hex2dec(normalStyle.slice(4,6));


	var pc = percent / 100;

	r= Math.floor(r1+(pc*(r2-r1)) + .5);
	g= Math.floor(g1+(pc*(g2-g1)) + .5);
	b= Math.floor(b1+(pc*(b2-b1)) + .5);

	for(var wp = 0; wp < inputsWithErrors.length; wp++) {
		inputsWithErrors[wp].style.backgroundColor = "#" + dec2hex(r) + dec2hex(g) + dec2hex(b);
	}
}


function validate_form(formname, startsWith){
    requiredTxt = SUGAR.language.get('app_strings', 'ERR_MISSING_REQUIRED_FIELDS');
    invalidTxt = SUGAR.language.get('app_strings', 'ERR_INVALID_VALUE');

	if ( typeof (formname) == 'undefined')
	{
		return false;
	}
	if ( typeof (validate[formname]) == 'undefined')
	{
        disableOnUnloadEditView(document.forms[formname]);
		return true;
	}

	var form = document.forms[formname];
	var isError = false;
	var errorMsg = "";
	var _date = new Date();
	if(_date.getTime() < (lastSubmitTime + 2000) && startsWith == oldStartsWith) { // ignore submits for the next 2 seconds
		return false;
	}
	lastSubmitTime = _date.getTime();
	oldStartsWith = startsWith;

	clear_all_errors(); // remove previous error messages

	inputsWithErrors = new Array();
	for(var i = 0; i < validate[formname].length; i++){
			if(validate[formname][i][nameIndex].indexOf(startsWith) == 0){
				if(typeof form[validate[formname][i][nameIndex]]  != 'undefined'){
					var bail = false;

                    //If a field is not required and it is blank or is binarydependant, skip validation.
                    //Example of binary dependant fields would be the hour/min/meridian dropdowns in a date time combo widget, which require further processing than a blank check
                    if(!validate[formname][i][requiredIndex] && trim(form[validate[formname][i][nameIndex]].value) == '' && (typeof(validate[formname][i][jstypeIndex]) != 'undefined' && validate[formname][i][jstypeIndex]  != 'binarydep'))
                    {
                        continue;
                    }

					if(validate[formname][i][requiredIndex] && validate[formname][i][typeIndex] != 'bool'){
						if(typeof form[validate[formname][i][nameIndex]] == 'undefined' || trim(form[validate[formname][i][nameIndex]].value) == ""){
							add_error_style(formname, validate[formname][i][nameIndex], requiredTxt +' ' + validate[formname][i][msgIndex]);
							isError = true;
						}
					}
					if(!bail){
						switch(validate[formname][i][typeIndex]){
						case 'email':
							if(!isValidEmail(trim(form[validate[formname][i][nameIndex]].value))){
								isError = true;
								add_error_style(formname, validate[formname][i][nameIndex], invalidTxt + " " +	validate[formname][i][msgIndex]);
							}
							 break;
						case 'time':
							if( !isTime(trim(form[validate[formname][i][nameIndex]].value))){
								isError = true;
								add_error_style(formname, validate[formname][i][nameIndex], invalidTxt + " " +	validate[formname][i][msgIndex]);
							} break;
						case 'date': if(!isDate(trim(form[validate[formname][i][nameIndex]].value))){
								isError = true;
								add_error_style(formname, validate[formname][i][nameIndex], invalidTxt + " " +	validate[formname][i][msgIndex]);
							}  break;
						case 'alpha':
							break;
						case 'DBName':
							if(!isDBName(trim(form[validate[formname][i][nameIndex]].value))){
								isError = true;
								add_error_style(formname, validate[formname][i][nameIndex], invalidTxt + " " +	validate[formname][i][msgIndex]);
							}
							break;
						case 'alphanumeric':
							break;
						case 'int':
							if(!isInteger(trim(form[validate[formname][i][nameIndex]].value))){
								isError = true;
								add_error_style(formname, validate[formname][i][nameIndex], invalidTxt + " " +	validate[formname][i][msgIndex]);
							}
							break;
						case 'currency':
						case 'float':
							if(!isFloat(trim(form[validate[formname][i][nameIndex]].value))){
								isError = true;
								add_error_style(formname, validate[formname][i][nameIndex], invalidTxt + " " +	validate[formname][i][msgIndex]);
							}
							break;
						case 'teamset_mass':
							div_element_id = formname + '_' + form[validate[formname][i][nameIndex]].name + '_operation_div';
							input_elements = YAHOO.util.Selector.query('input', document.getElementById(div_element_id));
							primary_field_id = '';
							validation_passed = false;
							replace_selected = false;

							//Loop through the option elements (replace or add currently)
							for(t in input_elements) {
								if(input_elements[t].type && input_elements[t].type == 'radio' && input_elements[t].checked == true && input_elements[t].value == 'replace') {

						           //Now find where the primary radio button is and if a value has been set
						           radio_elements = YAHOO.util.Selector.query('input[type=radio]', document.getElementById(formname + '_team_name_table'));

						           for(x in radio_elements) {
						        	   if(radio_elements[x].name != 'team_name_type') {
						        		  primary_field_id = 'team_name_collection_' + radio_elements[x].value;
						        		  if(radio_elements[x].checked) {
						        			  replace_selected = true;
						        			  if(trim(document.forms[formname].elements[primary_field_id].value) != '') {
		                                         validation_passed = true;
		                                         break;
										      }
						        		  } else if(trim(document.forms[formname].elements[primary_field_id].value) != '') {
						        			  replace_selected = true;
						        		  }
						        	   }
								   }
						        }
							}

							if(replace_selected && !validation_passed) {
						       add_error_style(formname, primary_field_id, SUGAR.language.get('app_strings', 'ERR_NO_PRIMARY_TEAM_SPECIFIED'));
						       isError = true;
							}
							break;
						case 'teamset':
							   table_element_id = formname + '_' + form[validate[formname][i][nameIndex]].name + '_table';
							   if(document.getElementById(table_element_id)) {
								   input_elements = YAHOO.util.Selector.query('input[type=radio]', document.getElementById(table_element_id));
								   has_primary = false;
								   primary_field_id = form[validate[formname][i][nameIndex]].name + '_collection_0';

								   for(t in input_elements) {
									    primary_field_id = form[validate[formname][i][nameIndex]].name + '_collection_' + input_elements[t].value;
								        if(input_elements[t].type && input_elements[t].type == 'radio' && input_elements[t].checked == true) {
								           if(document.forms[formname].elements[primary_field_id].value != '') {
								        	  has_primary = true;
								           }
								           break;
								        }
								   }

								   if(!has_primary) {
									  isError = true;
									  field_id = form[validate[formname][i][nameIndex]].name + '_collection_' + input_elements[0].value;
									  add_error_style(formname, field_id, SUGAR.language.get('app_strings', 'ERR_NO_PRIMARY_TEAM_SPECIFIED'));
								   }
							   }
						       break;
					    case 'error':
							isError = true;
                            add_error_style(formname, validate[formname][i][nameIndex], validate[formname][i][msgIndex]);
							break;
						}

						if(typeof validate[formname][i][jstypeIndex]  != 'undefined'/* && !isError*/){

							switch(validate[formname][i][jstypeIndex]){
							case 'range':
								if(!inRange(trim(form[validate[formname][i][nameIndex]].value), validate[formname][i][minIndex], validate[formname][i][maxIndex])){
									isError = true;
                                    var lbl_validate_range = SUGAR.language.get('app_strings', 'LBL_VALIDATE_RANGE');
                                    add_error_style(formname, validate[formname][i][nameIndex], validate[formname][i][msgIndex] + " value " + form[validate[formname][i][nameIndex]].value + " " + lbl_validate_range + " (" +validate[formname][i][minIndex] + " - " + validate[formname][i][maxIndex] +  ") ");
								}
							break;
							case 'isbefore':
								compareTo = form[validate[formname][i][compareToIndex]];
								if(	typeof compareTo != 'undefined'){
									if(trim(compareTo.value) != '' || (validate[formname][i][allowblank] != 'true') ) {
										date2 = trim(compareTo.value);
										date1 = trim(form[validate[formname][i][nameIndex]].value);

										if(trim(date1).length != 0 && !isBefore(date1,date2)){
											isError = true;
											//jc:#12287 - adding translation for the is not before message
											add_error_style(formname, validate[formname][i][nameIndex], validate[formname][i][msgIndex] + "(" + date1 + ") " + SUGAR.language.get('app_strings', 'MSG_IS_NOT_BEFORE') + ' ' +date2);
										}
									}
								}
							break;
                            case 'less':
                                value=unformatNumber(trim(form[validate[formname][i][nameIndex]].value), num_grp_sep, dec_sep);
								maximum = parseFloat(validate[formname][i][maxIndex]);
								if(	typeof maximum != 'undefined'){
									if(value>maximum) {
                                        isError = true;
                                        add_error_style(formname, validate[formname][i][nameIndex], validate[formname][i][msgIndex] +" " +SUGAR.language.get('app_strings', 'MSG_IS_MORE_THAN')+ ' ' + validate[formname][i][altMsgIndex]);
                                    }
								}
							break;
							case 'more':
                                value=unformatNumber(trim(form[validate[formname][i][nameIndex]].value), num_grp_sep, dec_sep);
								minimum = parseFloat(validate[formname][i][minIndex]);
								if(	typeof minimum != 'undefined'){
									if(value<minimum) {
                                        isError = true;
                                        add_error_style(formname, validate[formname][i][nameIndex], validate[formname][i][msgIndex] +" " +SUGAR.language.get('app_strings', 'MSG_SHOULD_BE')+ ' ' + minimum + ' ' + SUGAR.language.get('app_strings', 'MSG_OR_GREATER'));
                                    }
								}
							break;
                            case 'binarydep':
								compareTo = form[validate[formname][i][compareToIndex]];
								if( typeof compareTo != 'undefined') {
									item1 = trim(form[validate[formname][i][nameIndex]].value);
									item2 = trim(compareTo.value);
									if(!bothExist(item1, item2)) {
										isError = true;
										add_error_style(formname, validate[formname][i][nameIndex], validate[formname][i][msgIndex]);
									}
								}
							break;
							case 'comparison':
								compareTo = form[validate[formname][i][compareToIndex]];
								if( typeof compareTo != 'undefined') {
									item1 = trim(form[validate[formname][i][nameIndex]].value);
									item2 = trim(compareTo.value);
									if(!bothExist(item1, item2) || item1 != item2) {
										isError = true;
										add_error_style(formname, validate[formname][i][nameIndex], validate[formname][i][msgIndex]);
									}
								}
							break;
							case 'in_array':
								arr = eval(validate[formname][i][arrIndex]);
								operator = validate[formname][i][operatorIndex];
								item1 = trim(form[validate[formname][i][nameIndex]].value);
								if (operator.charAt(0) == 'u') {
									item1 = item1.toUpperCase();
									operator = operator.substring(1);
								} else if (operator.charAt(0) == 'l') {
									item1 = item1.toLowerCase();
									operator = operator.substring(1);
								}
								for(j = 0; j < arr.length; j++){
									val = arr[j];
									if((operator == "==" && val == item1) || (operator == "!=" && val != item1)){
										isError = true;
										add_error_style(formname, validate[formname][i][nameIndex], invalidTxt + " " +	validate[formname][i][msgIndex]);
									}
								}
							break;
							case 'verified':
							if(trim(form[validate[formname][i][nameIndex]].value) == 'false'){
							   //Fake an error so form does not submit
							   isError = true;
							}
							break;
							}
						}
					}
				}
			}
		}
/*	nsingh: BUG#15102
	Check min max default field logic.
	Can work with float values as well, but as of 10/8/07 decimal values in MB and studio don't have min and max value constraints.*/
	if(formsWithFieldLogic){
		var invalidLogic=false;
		if(formsWithFieldLogic.min && formsWithFieldLogic.max && formsWithFieldLogic._default) {
			var showErrorsOn={min:{value:'min', show:false, obj:formsWithFieldLogic.min.value},
							max:{value:'max',show:false, obj:formsWithFieldLogic.max.value},
							_default:{value:'default',show:false, obj:formsWithFieldLogic._default.value},
                              len:{value:'len', show:false, obj:parseInt(formsWithFieldLogic.len.value,10)}};

			var min = (formsWithFieldLogic.min.value !='') ? parseFloat(formsWithFieldLogic.min.value) : 'undef';
			var max  = (formsWithFieldLogic.max.value !='') ? parseFloat(formsWithFieldLogic.max.value) : 'undef';
			var _default = (formsWithFieldLogic._default.value!='')? parseFloat(formsWithFieldLogic._default.value) : 'undef';

			/*Check all lengths are <= max size.*/
			for(var i in showErrorsOn){
				if(showErrorsOn[i].value!='len' && showErrorsOn[i].obj.length > showErrorsOn.len.obj){
					invalidLogic=true;
					showErrorsOn[i].show=true;
					showErrorsOn.len.show=true;
				}
			}

			if(min!='undef' && max!='undef' && _default!='undef'){
				if(!inRange(_default,min,max)){
					invalidLogic=true;
					showErrorsOn.min.show=true;
					showErrorsOn.max.show=true;
					showErrorsOn._default.show=true;
				}
			}
			if(min!='undef' && max!= 'undef' && min > max){
				invalidLogic = true;
				showErrorsOn.min.show=true;
				showErrorsOn.max.show=true;
			}
			if(min!='undef' && _default!='undef' && _default < min){

				invalidLogic = true;
				showErrorsOn.min.show=true;
				showErrorsOn._default.show=true;
			}
			if(max!='undef' && _default !='undef' && _default>max){

				invalidLogic = true;
				showErrorsOn.max.show=true;
				showErrorsOn._default.show=true;
			}

			if(invalidLogic){
				isError=true;
				for(var error in showErrorsOn)
					if(showErrorsOn[error].show)
						add_error_style(formname,showErrorsOn[error].value, formsWithFieldLogic.msg);

			}

			else if (!isError)
				formsWithFieldLogic = null;
		}
	}
	if(formWithPrecision){
		if (!isValidPrecision(formWithPrecision.float.value, formWithPrecision.precision.value)){
			isError = true;
			add_error_style(formname, 'default', SUGAR.language.get('app_strings', 'ERR_COMPATIBLE_PRECISION_VALUE'));
		}else if(!isError){
			isError = false;
		}
	}

//END BUG# 15102

	if (isError == true) {
		var nw, ne, sw, se;
		if (self.pageYOffset) // all except Explorer
		{
			nwX = self.pageXOffset;
			seX = self.innerWidth;
			nwY = self.pageYOffset;
			seY = self.innerHeight;
		}
		else if (document.documentElement && document.documentElement.scrollTop) // Explorer 6 Strict
		{
			nwX = document.documentElement.scrollLeft;
			seX = document.documentElement.clientWidth;
			nwY = document.documentElement.scrollTop;
			seY = document.documentElement.clientHeight;
		}
		else if (document.body) // all other Explorers
		{
			nwX = document.body.scrollLeft;
			seX = document.body.clientWidth;
			nwY = document.body.scrollTop;
			seY = document.body.clientHeight;
		}

		var inView = true; // is there an error within viewport of browser
		for(var wp = 0; wp < inputsWithErrors.length; wp++) {
			var elementCoor = findElementPos(inputsWithErrors[wp]);
			if(!(elementCoor.x >= nwX && elementCoor.y >= nwY &&
				elementCoor.x <= seX && elementCoor.y <= seY)) { // if input is not within viewport
					inView = false;
					scrollToTop = elementCoor.y - 75;
					scrollToLeft = elementCoor.x - 75;
			}
			else { // on first input within viewport, don't scroll
				break;
			}
		}


		if(!inView) window.scrollTo(scrollToLeft,scrollToTop);

		return false;
	}

    disableOnUnloadEditView(form);
	return true;

}


/**
 * This array is used to remember mark status of rows in browse mode
 */
var marked_row = new Array;


/**
 * Sets/unsets the pointer and marker in browse mode
 *
 * @param   object    the table row
 * @param   interger  the row number
 * @param   string    the action calling this script (over, out or click)
 * @param   string    the default background color
 * @param   string    the color to use for mouseover
 * @param   string    the color to use for marking a row
 *
 * @return  boolean  whether pointer is set or not
 */
function setPointer(theRow, theRowNum, theAction, theDefaultColor, thePointerColor, theMarkColor) {
    var theCells = null;

    // 1. Pointer and mark feature are disabled or the browser can't get the
    //    row -> exits
    if ((thePointerColor == '' && theMarkColor == '')
        || typeof(theRow.style) == 'undefined') {
        return false;
    }

    // 2. Gets the current row and exits if the browser can't get it
    if (typeof(document.getElementsByTagName) != 'undefined') {
        theCells = theRow.getElementsByTagName('td');
    }
    else if (typeof(theRow.cells) != 'undefined') {
        theCells = theRow.cells;
    }
    else {
        return false;
    }

    // 3. Gets the current color...
    var rowCellsCnt  = theCells.length;
    var domDetect    = null;
    var currentColor = null;
    var newColor     = null;
    // 3.1 ... with DOM compatible browsers except Opera that does not return
    //         valid values with "getAttribute"
    if (typeof(window.opera) == 'undefined'
        && typeof(theCells[0].getAttribute) != 'undefined') {
        currentColor = theCells[0].getAttribute('bgcolor');
        domDetect    = true;
    }
    // 3.2 ... with other browsers
    else {
        currentColor = theCells[0].style.backgroundColor;
        domDetect    = false;
    } // end 3

    // 4. Defines the new color
    // 4.1 Current color is the default one
    if (currentColor == ''
        || (currentColor!= null && (currentColor.toLowerCase() == theDefaultColor.toLowerCase()))) {
        if (theAction == 'over' && thePointerColor != '') {
            newColor              = thePointerColor;
        }
        else if (theAction == 'click' && theMarkColor != '') {
            newColor              = theMarkColor;
            marked_row[theRowNum] = true;
        }
    }
    // 4.1.2 Current color is the pointer one
    else if (currentColor!= null && (currentColor.toLowerCase() == thePointerColor.toLowerCase())
             && (typeof(marked_row[theRowNum]) == 'undefined' || !marked_row[theRowNum])) {
        if (theAction == 'out') {
            newColor              = theDefaultColor;
        }
        else if (theAction == 'click' && theMarkColor != '') {
            newColor              = theMarkColor;
            marked_row[theRowNum] = true;
        }
    }
    // 4.1.3 Current color is the marker one
    else if (currentColor!= null && (currentColor.toLowerCase() == theMarkColor.toLowerCase())) {
        if (theAction == 'click') {
            newColor              = (thePointerColor != '')
                                  ? thePointerColor
                                  : theDefaultColor;
            marked_row[theRowNum] = (typeof(marked_row[theRowNum]) == 'undefined' || !marked_row[theRowNum])
                                  ? true
                                  : null;
        }
    } // end 4

    // 5. Sets the new color...
    if (newColor) {
        var c = null;
        // 5.1 ... with DOM compatible browsers except Opera
        if (domDetect) {
            for (c = 0; c < rowCellsCnt; c++) {
                theCells[c].setAttribute('bgcolor', newColor, 0);
            } // end for
        }
        // 5.2 ... with other browsers
        else {
            for (c = 0; c < rowCellsCnt; c++) {
                theCells[c].style.backgroundColor = newColor;
            }
        }
    } // end 5

    return true;
} // end of the 'setPointer()' function


/**
  * listbox redirection
  */
function goToUrl(selObj, goToLocation) {
    eval("document.location.href = '" + goToLocation + "pos=" + selObj.options[selObj.selectedIndex].value + "'");
}



var json_objects = new Object();

function getXMLHTTPinstance() {
	var xmlhttp = false;
	var userAgent = navigator.userAgent.toLowerCase() ;

	// IE Check supports ActiveX controls
	if (userAgent.indexOf("msie") != -1 && userAgent.indexOf("mac") == -1 && userAgent.indexOf("opera") == -1) {
		var version = navigator.appVersion.match(/MSIE (.\..)/)[1] ;
		if(version >= 5.5 ) {
			try {
				xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
			}
			catch (e) {
				try {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				catch (E) {
					xmlhttp = false;
				}
			}
		}
	}

	if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
		xmlhttp = new XMLHttpRequest();
	}
	return xmlhttp;
}

// NOW LOAD THE OBJECT..
var global_xmlhttp = getXMLHTTPinstance();

function http_fetch_sync(url,post_data) {
	global_xmlhttp = getXMLHTTPinstance();
	var method = 'GET';

	if(typeof(post_data) != 'undefined') method = 'POST';
	try {
		global_xmlhttp.open(method, url,false);
	}
	catch(e) {
		alert('message:'+e.message+":url:"+url);
	}
	if(method == 'POST') {
		global_xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	}

	global_xmlhttp.send(post_data);

	if (SUGAR.util.isLoginPage(global_xmlhttp.responseText))
		return false;

	var args = {"responseText" : global_xmlhttp.responseText,
				"responseXML" : global_xmlhttp.responseXML,
				"request_id" : request_id};
	return args;

}
// this is a GET unless post_data is defined

function http_fetch_async(url,callback,request_id,post_data) {
	var method = 'GET';
	if(typeof(post_data) != 'undefined') {
		method = 'POST';
	}

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
				if (SUGAR.util.isLoginPage(global_xmlhttp.responseText))
					return false;
				var args = {"responseText" : global_xmlhttp.responseText,
							"responseXML" : global_xmlhttp.responseXML,
							"request_id" : request_id };
				callback.call(document,args);
			}
			else {
				alert("There was a problem retrieving the XML data:\n" + global_xmlhttp.statusText);
			}
		}
	}
	global_xmlhttp.send(post_data);
}

function call_json_method(module,action,vars,variable_name,callback) {
	global_xmlhttp.open("GET", "index.php?entryPoint=json&module="+module+"&action="+action+"&"+vars,true);
	global_xmlhttp.onreadystatechange=
	function() {
		if(global_xmlhttp.readyState==4) {
			if(global_xmlhttp.status == 200) {
				// cn: bug 12274 - pass through JSON.parse() to remove security envelope
				json_objects[variable_name] = JSON.parse(global_xmlhttp.responseText);

				// cn: bug 12274 - safe from CSRF, render response as expected
				var respText = JSON.parseNoSecurity(global_xmlhttp.responseText);
				var args = {responseText:respText, responseXML:global_xmlhttp.responseXML};
				callback.call(document, args);
			}
			else {
				alert("There was a problem retrieving the XML data:\n" + global_xmlhttp.statusText);
			}
		}
	}
	global_xmlhttp.send(null);
}

function insert_at_cursor(field, value) {
 //ie:
	if (document.selection) {
		field.focus();
		sel = document.selection.createRange();
		sel.text = value;
	}
 //mozilla:
	else if(field.selectionStart || field.selectionStart == '0') {
		var start_pos = field.selectionStart;
		var end_pos = field.selectionEnd;
		field.value = field.value.substring(0, start_pos) + value + field.value.substring(end_pos, field.value.length);
	}
	else {
		field.value += value;
	}
}

function checkParentType(type,button) {
	if(button == null) {
		return;
	}
	if(typeof disabledModules != 'undefined' && typeof(disabledModules[type]) != 'undefined') {
		button.disabled='disabled';
	}
	else {
		button.disabled = false;
	}
}

function parseDate(input, format) {
	date = input.value;
	format = format.replace(/%/g, '');
	sep = format.charAt(1);
	yAt = format.indexOf('Y')
	// 1-1-06 or 1-12-06 or 1-1-2006 or 1-12-2006
	if(date.match(/^\d{1,2}[\/-]\d{1,2}[\/-]\d{2,4}$/) && yAt == 4) {
		if(date.match(/^\d{1}[\/-].*$/)) date = '0' + date;
		if(date.match(/^\d{2}[\/-]\d{1}[\/-].*$/)) date = date.substring(0,3) + '0' + date.substring(3,date.length);
		if(date.match(/^\d{2}[\/-]\d{2}[\/-]\d{2}$/)) date = date.substring(0,6) + '20' + date.substring(6,date.length);
	}
	// 06-11-1 or 06-1-1
	else if(date.match(/^\d{2,4}[\/-]\d{1,2}[\/-]\d{1,2}$/)) {
		if(date.match(/^\d{2}[\/-].*$/)) date = '20' + date;
		if(date.match(/^\d{4}[\/-]\d{1}[\/-].*$/)) date = date.substring(0,5) + '0' + date.substring(5,date.length);
		if(date.match(/^\d{4}[\/-]\d{2}[\/-]\d{1}$/)) date = date.substring(0,8) + '0' + date.substring(8,date.length);
	}
	else if(date.match(/^\d{4,8}$/)) { // digits only
		digits = 0;
		if(date.match(/^\d{8}$/)) digits = 8;// match for 8 digits
		else if(date.match(/\d{6}/)) digits = 6;// match for 5 digits
		else if(date.match(/\d{4}/)) digits = 4;// match for 5 digits
		else if(date.match(/\d{5}/)) digits = 5;// match for 5 digits

		switch(yAt) {
			case 0:
				switch(digits) {
					case 4: date = '20' + date.substring(0,2) + sep + '0' + date.substring(2, 3) + sep + '0' + date.substring(3,4); break;
					case 5: date = '20' + date.substring(0,2) + sep + date.substring(2, 4) + sep + '0' + date.substring(4,5); break;
					case 6: date = '20' + date.substring(0,2) + sep + date.substring(2, 4) + sep + date.substring(4,6); break;
					case 8: date = date.substring(0,4) + sep + date.substring(4, 6) + sep + date.substring(6,8); break;
				}
				break;
			case 2:
				switch(digits) {
					case 4: date = '0' + date.substring(0,1) + sep + '20' + date.substring(1, 3) + sep + '0' + date.substring(3,4); break;
					case 5: date = date.substring(0,2) + sep + '20' + date.substring(2, 4) + sep + '0' + date.substring(4,5); break;
					case 6: date = date.substring(0,2) + sep + '20' + date.substring(2, 4) + sep + date.substring(4,6); break;
					case 8: date = date.substring(0,2) + sep + date.substring(2, 6) + sep + date.substring(6,8); break;
				}
			case 4:
				switch(digits) {
					case 4: date = '0' + date.substring(0,1) + sep + '0' + date.substring(1, 2) + sep + '20' + date.substring(2,4); break;
					case 5: date = '0' + date.substring(0,1) + sep + date.substring(1, 3) + sep + '20' + date.substring(3,5); break;
					case 6: date = date.substring(0,2) + sep + date.substring(2, 4) + sep + '20' + date.substring(4,6); break;
					case 8: date = date.substring(0,2) + sep + date.substring(2, 4) + sep + date.substring(4,8); break;
				}
				break;
		}
	}
	date = date.replace(/[\/-]/g, sep);
	input.value = date;
}

// find obj's position
function findElementPos(obj) {
    var x = 0;
    var y = 0;
    if (obj.offsetParent) {
      while (obj.offsetParent) {
        x += obj.offsetLeft;
        y += obj.offsetTop;
        obj = obj.offsetParent;
      }
    }//if offsetParent exists
    else if (obj.x && obj.y) {
      y += obj.y
      x += obj.x
    }
	return new coordinate(x, y);
}//findElementPos


// get dimensions of the browser window
function getClientDim() {
	var nwX, nwY, seX, seY;
	if (self.pageYOffset) // all except Explorer
	{
	  nwX = self.pageXOffset;
	  seX = self.innerWidth + nwX;
	  nwY = self.pageYOffset;
	  seY = self.innerHeight + nwY;
	}
	else if (document.documentElement && document.documentElement.scrollTop) // Explorer 6 Strict
	{
	  nwX = document.documentElement.scrollLeft;
	  seX = document.documentElement.clientWidth + nwX;
	  nwY = document.documentElement.scrollTop;
	  seY = document.documentElement.clientHeight + nwY;
	}
	else if (document.body) // all other Explorers
	{
	  nwX = document.body.scrollLeft;
	  seX = document.body.clientWidth + nwX;
	  nwY = document.body.scrollTop;
	  seY = document.body.clientHeight + nwY;
	}
	return {'nw' : new coordinate(nwX, nwY), 'se' : new coordinate(seX, seY)};
}

/**
* stop propagation on events
**/
function freezeEvent(e) {
	if(e) {
	  if (e.preventDefault) e.preventDefault();
	  e.returnValue = false;
	  e.cancelBubble = true;
	  if (e.stopPropagation) e.stopPropagation();
	  return false;
	}
}


/**
 * coordinate class
 **/
function coordinate(_x, _y) {
  var x = _x;
  var y = _y;
  this.add = add;
  this.sub = sub;
  this.x = x;
  this.y = y;

  function add(rh) {
    return new position(this.x + rh.x, this.y + rh.y);
  }

  function sub(rh) {
    return new position(this.x + rh.x, this.y + rh.y);
  }
}

// sends theForm via AJAX and fills in the theDiv
function sendAndRetrieve(theForm, theDiv, loadingStr) {
	function success(data) {
		document.getElementById(theDiv).innerHTML = data.responseText;
		ajaxStatus.hideStatus();
	}
	if(typeof loadingStr == 'undefined') SUGAR.language.get('app_strings', 'LBL_LOADING');
	ajaxStatus.showStatus(loadingStr);
	YAHOO.util.Connect.setForm(theForm);
	var cObj = YAHOO.util.Connect.asyncRequest('POST', 'index.php', {success: success, failure: success});
	return false;
}

//save the form and redirect
function sendAndRedirect(theForm, loadingStr, redirect_location) {
	function success(data) {
		if(redirect_location){
			location.href=redirect_location;
		}
		ajaxStatus.hideStatus();
	}
	if(typeof loadingStr == 'undefined') SUGAR.language.get('app_strings', 'LBL_LOADING');
	ajaxStatus.showStatus(loadingStr);
	YAHOO.util.Connect.setForm(theForm);
	var cObj = YAHOO.util.Connect.asyncRequest('POST', 'index.php', {success: success, failure: success});
	return false;
}

function saveForm(theForm, theDiv, loadingStr) {
	if(check_form(theForm)){
		for(i = 0; i < ajaxFormArray.length; i++){
			if(ajaxFormArray[i] == theForm){
				ajaxFormArray.splice(i, 1);
			}
		}
		return sendAndRetrieve(theForm, loadingStr, theDiv);
	}
	else
		return false;
}

// Builds a "snapshot" of the form, so we can use it to see if someone has changed it.
function snapshotForm(theForm) {
    var snapshotTxt = '';
    var elemList = theForm.elements;
    var elem;
    var elemType;

    for( var i = 0; i < elemList.length ; i++ ) {
        elem = elemList[i];
        if ( typeof(elem.type) == 'undefined' ) {
            continue;
        }

        elemType = elem.type.toLowerCase();

        snapshotTxt = snapshotTxt + elem.name;

        if ( elemType == 'text' || elemType == 'textarea' || elemType == 'password' ) {
            snapshotTxt = snapshotTxt + elem.value;
        }
        else if ( elemType == 'select' || elemType == 'select-one' || elemType == 'select-multiple' ) {
            var optionList = elem.options;
            for ( var ii = 0 ; ii < optionList.length ; ii++ ) {
                if ( optionList[ii].selected ) {
                    snapshotTxt = snapshotTxt + optionList[ii].value;
                }
            }
        }
        else if ( elemType == 'radio' || elemType == 'checkbox' ) {
            if ( elem.selected ) {
                snapshotTxt = snapshotTxt + 'checked';
            }
        }
        else if ( elemType == 'hidden' ) {
            snapshotTxt = snapshotTxt + elem.value;
        }
    }

    return snapshotTxt;
}

function initEditView(theForm) {
    if (SUGAR.util.ajaxCallInProgress()) {
    	window.setTimeout(function(){initEditView(theForm);}, 100);
    	return;
    }
    // we don't need to check if the data is changed in the search popup
    if (theForm.id == 'popup_query_form') {
    	return;
    }
	if ( typeof editViewSnapshots == 'undefined' ) {
        editViewSnapshots = new Object();
    }
    if ( typeof SUGAR.loadedForms == 'undefined' ) {
    	SUGAR.loadedForms = new Object();
    }

    // console.log('DEBUG: Adding checks for '+theForm.id);
    editViewSnapshots[theForm.id] = snapshotForm(theForm);
    SUGAR.loadedForms[theForm.id] = true;

}

function onUnloadEditView(theForm) {

	var dataHasChanged = false;

    if ( typeof editViewSnapshots == 'undefined' ) {
        // No snapshots, move along
        return;
    }

    if ( typeof theForm == 'undefined' ) {
        // Need to check all editViewSnapshots
        for ( var idx in editViewSnapshots ) {

            theForm = document.getElementById(idx);
            // console.log('DEBUG: Checking all forms '+theForm.id);
            if ( theForm == null
                 || typeof editViewSnapshots[theForm.id] == 'undefined'
                 || editViewSnapshots[theForm.id] == null
                 || !SUGAR.loadedForms[theForm.id]) {
                continue;
            }

            var snap = snapshotForm(theForm);
            if ( editViewSnapshots[theForm.id] != snap ) {
                dataHasChanged = true;
            }
        }
    } else {
        // Just need to check a single form for changes
		if ( editViewSnapshots == null  || typeof editViewSnapshots[theForm.id] == 'undefined' || editViewSnapshots[theForm.id] == null ) {
            return;
        }

        // console.log('DEBUG: Checking one form '+theForm.id);
        if ( editViewSnapshots[theForm.id] != snapshotForm(theForm) ) {
            // Data has changed.
        	dataHasChanged = true;
        }
    }

    if ( dataHasChanged == true ) {
    	return SUGAR.language.get('app_strings','WARN_UNSAVED_CHANGES');
    } else {
        return;
    }

}

function disableOnUnloadEditView(theForm) {
    // If you don't pass anything in, it disables all checking
    if ( typeof theForm == 'undefined' || typeof editViewSnapshots == 'undefined' || editViewSnapshots == null ) {
        window.onbeforeunload = null;
        editViewSnapshots = null;

        // console.log('DEBUG: Disabling all edit view checks');

    } else {
        // Otherwise, it just disables it for this form
        if ( typeof(theForm.id) != 'undefined' && typeof(editViewSnapshots[theForm.id]) != 'undefined' ) {
            editViewSnapshots[theForm.id] = null;
        }

        // console.log('DEBUG : Disabling just checks for '+theForm.id);

    }
}

/*
* save some forms using an ajax call
* theForms - the ids of all of theh forms to save
* savingStr - the string to display when saving the form
* completeStr - the string to display when the form has been saved
*/
function saveForms( savingStr, completeStr) {
	index = 0;
	theForms = ajaxFormArray;
	function success(data) {
		var theForm = document.getElementById(ajaxFormArray[0]);
		document.getElementById('multiedit_'+theForm.id).innerHTML = data.responseText;
		var saveAllButton = document.getElementById('ajaxsaveall');
		ajaxFormArray.splice(index, 1);
		if(saveAllButton && ajaxFormArray.length <= 1){
    		saveAllButton.style.visibility = 'hidden';
    	}
		index++;
		if(index == theForms.length){
			ajaxStatus.showStatus(completeStr);
    		window.setTimeout('ajaxStatus.hideStatus();', 2000);
    		if(saveAllButton)
    			saveAllButton.style.visibility = 'hidden';
    	}


	}
	if(typeof savingStr == 'undefined') SUGAR.language.get('app_strings', 'LBL_LOADING');
	ajaxStatus.showStatus(savingStr);

	//loop through the forms saving each one
	for(i = 0; i < theForms.length; i++){
		var theForm = document.getElementById(theForms[i]);
		if(check_form(theForm.id)){
			theForm.action.value='AjaxFormSave';
			YAHOO.util.Connect.setForm(theForm);
			var cObj = YAHOO.util.Connect.asyncRequest('POST', 'index.php', {success: success, failure: success});
		}else{
			ajaxStatus.hideStatus();
		}
		lastSubmitTime = lastSubmitTime-2000;
	}
	return false;
}

// -- start sugarListView class
// js functions used for ListView
function sugarListView() {
}


sugarListView.prototype.confirm_action = function(del) {
	if (del == 1) {
		return confirm( SUGAR.language.get('app_strings', 'NTC_DELETE_CONFIRMATION_NUM') + sugarListView.get_num_selected()  + SUGAR.language.get('app_strings', 'NTC_DELETE_SELECTED_RECORDS'));
	}
	else {
		return confirm( SUGAR.language.get('app_strings', 'NTC_UPDATE_CONFIRMATION_NUM') + sugarListView.get_num_selected()  + SUGAR.language.get('app_strings', 'NTC_DELETE_SELECTED_RECORDS'));
	}

}
sugarListView.get_num_selected = function () {
	if(typeof document.MassUpdate != 'undefined') {
		the_form = document.MassUpdate;
		for(wp = 0; wp < the_form.elements.length; wp++) {
			if(typeof the_form.elements[wp].name != 'undefined' && the_form.elements[wp].name == 'selectCount[]') {
				return the_form.elements[wp].value;
			}
		}
	}
	return 0;

}
sugarListView.update_count = function(count, add) {
	if(typeof document.MassUpdate != 'undefined') {
		the_form = document.MassUpdate;
		for(wp = 0; wp < the_form.elements.length; wp++) {
			if(typeof the_form.elements[wp].name != 'undefined' && the_form.elements[wp].name == 'selectCount[]') {
				if(add)	{
					the_form.elements[wp].value = parseInt(the_form.elements[wp].value,10) + count;
					if (the_form.select_entire_list.value == 1 && the_form.show_plus.value) {
						the_form.elements[wp].value += '+';
					}
				} else {
					if (the_form.select_entire_list.value == 1 && the_form.show_plus.value) {
				        the_form.elements[wp].value = count + '+';
				    } else {
				        the_form.elements[wp].value = count;
				    }
				}
			}
		}
	}
}
sugarListView.prototype.use_external_mail_client = function(no_record_txt, module) {
	selected_records = sugarListView.get_checks_count();
	if(selected_records <1) {
		alert(no_record_txt);
        return false;
	}

    if (document.MassUpdate.select_entire_list.value == 1) {
		if (totalCount > 10) {
			alert(totalCountError);
			return;
		} // if
		select = false;
	}
	else if (document.MassUpdate.massall.checked == true)
		select = false;
	else
		select = true;
    sugarListView.get_checks();
    var ids = "";
    if(select) { // use selected items
		ids = document.MassUpdate.uid.value;
	}
	else { // use current page
		inputs = document.MassUpdate.elements;
		ar = new Array();
		for(i = 0; i < inputs.length; i++) {
			if(inputs[i].name == 'mass[]' && inputs[i].checked && typeof(inputs[i].value) != 'function') {
				ar.push(inputs[i].value);
			}
		}
		ids = ar.join(',');
	}
    YAHOO.util.Connect.asyncRequest("POST", "index.php?", {
        success: this.use_external_mail_client_callback
    }, SUGAR.util.paramsToUrl({
        module: "Emails",
        action: "Compose",
        listViewExternalClient: 1,
        action_module: module,
        uid: ids,
        to_pdf:1
    }));

	return false;
}

sugarListView.prototype.use_external_mail_client_callback = function(o)
{
    if (o.responseText)
        location.href = 'mailto:' + o.responseText;
}

sugarListView.prototype.send_form_for_emails = function(select, currentModule, action, no_record_txt,action_module,totalCount, totalCountError) {
	if (document.MassUpdate.select_entire_list.value == 1) {
		if (totalCount > 10) {
			alert(totalCountError);
			return;
		} // if
		select = false;
	}
	else if (document.MassUpdate.massall.checked == true)
		select = false;
	else
		select = true;

	sugarListView.get_checks();
	// create new form to post (can't access action property of MassUpdate form due to action input)
	var newForm = document.createElement('form');
	newForm.method = 'post';
	newForm.action = action;
	newForm.name = 'newForm';
	newForm.id = 'newForm';
	var uidTa = document.createElement('textarea');
	uidTa.name = 'uid';
	uidTa.style.display = 'none';

	if(select) { // use selected items
		uidTa.value = document.MassUpdate.uid.value;
	}
	else { // use current page
		inputs = document.MassUpdate.elements;
		ar = new Array();
		for(i = 0; i < inputs.length; i++) {
			if(inputs[i].name == 'mass[]' && inputs[i].checked && typeof(inputs[i].value) != 'function') {
				ar.push(inputs[i].value);
			}
		}
		uidTa.value = ar.join(',');
	}

	if(uidTa.value == '') {
		alert(no_record_txt);
		return false;
	}

	var selectedArray = uidTa.value.split(",");
	if(selectedArray.length > 10) {
		alert(totalCountError);
		return;
	} // if
	newForm.appendChild(uidTa);

	var moduleInput = document.createElement('input');
	moduleInput.name = 'module';
	moduleInput.type = 'hidden';
	moduleInput.value = currentModule;
	newForm.appendChild(moduleInput);

	var actionInput = document.createElement('input');
	actionInput.name = 'action';
	actionInput.type = 'hidden';
	actionInput.value = 'Compose';
	newForm.appendChild(actionInput);

	if (typeof action_module != 'undefined' && action_module!= '') {
		var actionModule = document.createElement('input');
		actionModule.name = 'action_module';
		actionModule.type = 'hidden';
		actionModule.value = action_module;
		newForm.appendChild(actionModule);
	}
	//return_info must follow this pattern."&return_module=Accounts&return_action=index"
	if (typeof return_info!= 'undefined' && return_info != '') {
		var params= return_info.split('&');
		if (params.length > 0) {
			for (var i=0;i< params.length;i++) {
				if (params[i].length > 0) {
					var param_nv=params[i].split('=');
					if (param_nv.length==2){
						returnModule = document.createElement('input');
						returnModule.name = param_nv[0];
						returnModule.type = 'hidden';
						returnModule.value = param_nv[1];
						newForm.appendChild(returnModule);
					}
				}
			}
		}
	}

	var isAjaxCall = document.createElement('input');
	isAjaxCall.name = 'ajaxCall';
	isAjaxCall.type = 'hidden';
	isAjaxCall.value = true;
	newForm.appendChild(isAjaxCall);

	var isListView = document.createElement('input');
	isListView.name = 'ListView';
	isListView.type = 'hidden';
	isListView.value = true;
	newForm.appendChild(isListView);

	var toPdf = document.createElement('input');
	toPdf.name = 'to_pdf';
	toPdf.type = 'hidden';
	toPdf.value = true;
	newForm.appendChild(toPdf);

	//Grab the Quick Compose package for the listview
    YAHOO.util.Connect.setForm(newForm);
    var callback =
	{
	  success: function(o) {
	      var resp = YAHOO.lang.JSON.parse(o.responseText);
	      var quickComposePackage = new Object();
	      quickComposePackage.composePackage = resp;
	      quickComposePackage.fullComposeUrl = 'index.php?module=Emails&action=Compose&ListView=true' +
	                                           '&uid=' + uidTa.value + '&action_module=' + action_module;

	      SUGAR.quickCompose.init(quickComposePackage);
	  }
	}

	YAHOO.util.Connect.asyncRequest('POST','index.php', callback,null);

	// awu Bug 18624: Fixing issue where a canceled Export and unselect of row will persist the uid field, clear the field
	document.MassUpdate.uid.value = '';

	return false;
}

sugarListView.prototype.send_form = function(select, currentModule, action, no_record_txt,action_module,return_info) {
	if (document.MassUpdate.select_entire_list.value == 1) {

		if(sugarListView.get_checks_count() < 1) {
		   alert(no_record_txt);
		   return false;
		}

		var href = action;
		if ( action.indexOf('?') != -1 )
			href += '&module=' + currentModule;
		else
			href += '?module=' + currentModule;

		if (return_info)
			href += return_info;
        var newForm = document.createElement('form');
        newForm.method = 'post';
        newForm.action = href;
        newForm.name = 'newForm';
        newForm.id = 'newForm';
        var postTa = document.createElement('textarea');
        postTa.name = 'current_post';
        postTa.value = document.MassUpdate.current_query_by_page.value;
        postTa.style.display = 'none';
        newForm.appendChild(postTa);
        document.MassUpdate.parentNode.appendChild(newForm);
        newForm.submit();
		return;
	}
	else if (document.MassUpdate.massall.checked == true)
		select = false;
	else
		select = true;

	sugarListView.get_checks();
	// create new form to post (can't access action property of MassUpdate form due to action input)
	var newForm = document.createElement('form');
	newForm.method = 'post';
	newForm.action = action;
	newForm.name = 'newForm';
	newForm.id = 'newForm';
	var uidTa = document.createElement('textarea');
	uidTa.name = 'uid';
	uidTa.style.display = 'none';
	uidTa.value = document.MassUpdate.uid.value;

	if(uidTa.value == '') {
		alert(no_record_txt);
		return false;
	}

	newForm.appendChild(uidTa);

	var moduleInput = document.createElement('input');
	moduleInput.name = 'module';
	moduleInput.type = 'hidden';
	moduleInput.value = currentModule;
	newForm.appendChild(moduleInput);

	var actionInput = document.createElement('input');
	actionInput.name = 'action';
	actionInput.type = 'hidden';
	actionInput.value = 'index';
	newForm.appendChild(actionInput);

	if (typeof action_module != 'undefined' && action_module!= '') {
		var actionModule = document.createElement('input');
		actionModule.name = 'action_module';
		actionModule.type = 'hidden';
		actionModule.value = action_module;
		newForm.appendChild(actionModule);
	}
	//return_info must follow this pattern."&return_module=Accounts&return_action=index"
	if (typeof return_info!= 'undefined' && return_info != '') {
		var params= return_info.split('&');
		if (params.length > 0) {
			for (var i=0;i< params.length;i++) {
				if (params[i].length > 0) {
					var param_nv=params[i].split('=');
					if (param_nv.length==2){
						returnModule = document.createElement('input');
						returnModule.name = param_nv[0];
						returnModule.type = 'hidden';
						returnModule.value = param_nv[1];
						newForm.appendChild(returnModule);
					}
				}
			}
		}
	}

	document.MassUpdate.parentNode.appendChild(newForm);

	newForm.submit();
	// awu Bug 18624: Fixing issue where a canceled Export and unselect of row will persist the uid field, clear the field
	document.MassUpdate.uid.value = '';

	return false;
}
//return a count of checked row.
sugarListView.get_checks_count = function() {
	ar = new Array();

	if(document.MassUpdate.uid.value != '') {
		oldUids = document.MassUpdate.uid.value.split(',');
		for(uid in oldUids) {
		    if(typeof(oldUids[uid]) != 'function') {
		       ar[oldUids[uid]] = 1;
		    }
		}
	}
	// build associated array of uids, associated array ensures uniqueness
	inputs = document.MassUpdate.elements;
	for(i = 0; i < inputs.length; i++) {
		if(inputs[i].name == 'mass[]') {
			ar[inputs[i].value]	= (inputs[i].checked) ? 1 : 0; // 0 of it is unchecked
	    }
	}

	// build regular array of uids
	uids = new Array();
	for(i in ar) {
		if((typeof(ar[i]) != 'function') && ar[i] == 1) {
		   uids.push(i);
		}
	}

	return uids.length;
}

// saves the checks on the current page into the uid textarea
sugarListView.get_checks = function() {
	ar = new Array();

	if(document.MassUpdate.uid.value != '') {
		oldUids = document.MassUpdate.uid.value.split(',');
		for(uid in oldUids) {
		    if(typeof(oldUids[uid]) != 'function') {
		       ar[oldUids[uid]] = 1;
		    }
		}
	}

	// build associated array of uids, associated array ensures uniqueness
	inputs = document.MassUpdate.elements;
	for(i = 0; i < inputs.length; i++) {
		if(inputs[i].name == 'mass[]') {
			ar[inputs[i].value]	= (inputs[i].checked) ? 1 : 0; // 0 of it is unchecked
		}
	}

	// build regular array of uids
	uids = new Array();
	for(i in ar) {
		if(typeof(ar[i]) != 'function' && ar[i] == 1) {
		   uids.push(i);
		}
	}

	document.MassUpdate.uid.value = uids.join(',');

	if(uids.length == 0) return false; // return false if no checks to get
	return true; // there are saved checks
}

sugarListView.prototype.order_checks = function(order,orderBy,moduleString){
	checks = sugarListView.get_checks();
	eval('document.MassUpdate.' + moduleString + '.value = orderBy');
	document.MassUpdate.lvso.value = order;
	if(typeof document.MassUpdate.massupdate != 'undefined') {
	   document.MassUpdate.massupdate.value = 'false';
	}

	//we must first clear the action of massupdate, change it to index
   document.MassUpdate.action.value = document.MassUpdate.return_action.value;
   document.MassUpdate.return_module.value='';
   document.MassUpdate.return_action.value='';
   document.MassUpdate.submit();

	return !checks;
}
sugarListView.prototype.save_checks = function(offset, moduleString) {
	checks = sugarListView.get_checks();
	eval('document.MassUpdate.' + moduleString + '.value = offset');

	if(typeof document.MassUpdate.massupdate != 'undefined') {
	   document.MassUpdate.massupdate.value = 'false';
	}

	//we must first clear the action of massupdate, change it to index
       document.MassUpdate.action.value = document.MassUpdate.return_action.value;
       document.MassUpdate.return_module.value='';
       document.MassUpdate.return_action.value='';
	   document.MassUpdate.submit();


	return !checks;
}

sugarListView.prototype.check_item = function(cb, form) {
	if(cb.checked) {
		sugarListView.update_count(1, true);
	}else{
		sugarListView.update_count(-1, true);
		if(typeof form != 'undefined' && form != null) {
			sugarListView.prototype.updateUid(cb, form);
		}
	}
}

/**#28000, remove the  unselect record id from MassUpdate.uid **/
sugarListView.prototype.updateUid = function(cb  , form){
    if(form.name == 'MassUpdate' && form.uid && form.uid.value && cb.value && form.uid.value.indexOf(cb.value) != -1){
        if(form.uid.value.indexOf(','+cb.value)!= -1){
            form.uid.value = form.uid.value.replace(','+cb.value , '');
        }else if(form.uid.value.indexOf(cb.value + ',')!= -1){
            form.uid.value = form.uid.value.replace(cb.value + ',' , '');
        }else if(form.uid.value.indexOf(cb.value)!= -1){
            form.uid.value = form.uid.value.replace(cb.value  , '');
        }
    }
}

sugarListView.prototype.check_entire_list = function(form, field, value, list_count) {
	// count number of items
	count = 0;
	document.MassUpdate.massall.checked = true;
	document.MassUpdate.massall.disabled = true;

	for (i = 0; i < form.elements.length; i++) {
		if(form.elements[i].name == field && form.elements[i].disabled == false) {
			if(form.elements[i].checked != value) count++;
				form.elements[i].checked = value;
				form.elements[i].disabled = true;
		}
	}
	document.MassUpdate.select_entire_list.value = 1;
	//if(value)
	sugarListView.update_count(list_count, false);
	//else sugarListView.update_count(-1 * count, true);
}

sugarListView.prototype.check_all = function(form, field, value, pageTotal) {
	// count number of items
	count = 0;
	document.MassUpdate.massall.checked = value;
	if (document.MassUpdate.select_entire_list &&
		document.MassUpdate.select_entire_list.value == 1)
		document.MassUpdate.massall.disabled = true;
	else
		document.MassUpdate.massall.disabled = false;

	for (i = 0; i < form.elements.length; i++) {
		if(form.elements[i].name == field && !(form.elements[i].disabled == true && form.elements[i].checked == false)) {
			form.elements[i].disabled = false;

			if(form.elements[i].checked != value)
				count++;
			form.elements[i].checked = value;
			if(!value){
				sugarListView.prototype.updateUid(form.elements[i], form);
			}
		}
	}
	if (pageTotal >= 0)
		sugarListView.update_count(pageTotal);
 	else if(value)
		sugarListView.update_count(count, true);
	else
		sugarListView.update_count(-1 * count, true);
}
sugarListView.check_all = sugarListView.prototype.check_all;
sugarListView.confirm_action = sugarListView.prototype.confirm_action;

sugarListView.prototype.check_boxes = function() {
	var inputsCount = 0;
	var checkedCount = 0;
	var existing_onload = window.onload;
	var theForm = document.MassUpdate;
	inputs_array = theForm.elements;

	if(typeof theForm.uid.value != 'undefined' && theForm.uid.value != "") {
		checked_items = theForm.uid.value.split(",");
		if (theForm.select_entire_list.value == 1)
			document.MassUpdate.massall.disabled = true;

		for(wp = 0 ; wp < inputs_array.length; wp++) {
			if(inputs_array[wp].name == "mass[]") {
				inputsCount++;
				if (theForm.select_entire_list.value == 1) {
					inputs_array[wp].checked = true;
					inputs_array[wp].disabled = true;
					checkedCount++;
				}
				else {
					for(i in checked_items) {
						if(inputs_array[wp].value == checked_items[i]) {
							checkedCount++;
							inputs_array[wp].checked = true;
						}
					}
				}
			}
		}
		if (theForm.select_entire_list.value == 0)
			sugarListView.update_count(checked_items.length);
		else
			sugarListView.update_count(0, true);

	}
	else {
		for(wp = 0 ; wp < inputs_array.length; wp++) {
			if(inputs_array[wp].name == "mass[]") {
				inputs_array[wp].checked = false;
				inputs_array[wp].disabled = false;
			}
		}
		if (document.MassUpdate.massall) {
			document.MassUpdate.massall.checked = false;
			document.MassUpdate.massall.disabled = false;
		}
		sugarListView.update_count(0)
	}
	if(checkedCount > 0 && checkedCount == inputsCount)
		document.MassUpdate.massall.checked = true;

}


/**
 * This function is used in Email Template Module's listview.
 * It will check whether the templates are used in Campaing->EmailMarketing.
 * If true, it will notify user.
 */
function check_used_email_templates() {
	var ids = document.MassUpdate.uid.value;
	var call_back = {
		success:function(r) {
			if(r.responseText != '') {
				if(!confirm(SUGAR.language.get('app_strings','NTC_TEMPLATES_IS_USED') + r.responseText)) {
					return false;
				}
			}
			document.MassUpdate.submit();
			return false;
		}
		};
	url = "index.php?module=EmailTemplates&action=CheckDeletable&from=ListView&to_pdf=1&records="+ids;
	YAHOO.util.Connect.asyncRequest('POST',url, call_back,null);

}

sugarListView.prototype.send_mass_update = function(mode, no_record_txt, del) {
	formValid = check_form('MassUpdate');
	if(!formValid && !del) return false;


	if (document.MassUpdate.select_entire_list &&
		document.MassUpdate.select_entire_list.value == 1)
		mode = 'entire';
	else
		mode = 'selected';

	var ar = new Array();

	switch(mode) {
		case 'selected':
			for(wp = 0; wp < document.MassUpdate.elements.length; wp++) {
				var reg_for_existing_uid = new RegExp('^'+RegExp.escape(document.MassUpdate.elements[wp].value)+'[\s]*,|,[\s]*'+RegExp.escape(document.MassUpdate.elements[wp].value)+'[\s]*,|,[\s]*'+RegExp.escape(document.MassUpdate.elements[wp].value)+'$|^'+RegExp.escape(document.MassUpdate.elements[wp].value)+'$');
				//when the uid is already in document.MassUpdate.uid.value, we should not add it to ar.
				if(typeof document.MassUpdate.elements[wp].name != 'undefined'
					&& document.MassUpdate.elements[wp].name == 'mass[]'
						&& document.MassUpdate.elements[wp].checked
						&& !reg_for_existing_uid.test(document.MassUpdate.uid.value)) {
							ar.push(document.MassUpdate.elements[wp].value);
				}
			}
			if(document.MassUpdate.uid.value != '') document.MassUpdate.uid.value += ',';
			document.MassUpdate.uid.value += ar.join(',');
			if(document.MassUpdate.uid.value == '') {
				alert(no_record_txt);
				return false;
			}
			if(typeof(current_admin_id)!='undefined' && document.MassUpdate.module!= 'undefined' && document.MassUpdate.module.value == 'Users' && (document.MassUpdate.is_admin.value!='' || document.MassUpdate.status.value!='')) {
				var reg_for_current_admin_id = new RegExp('^'+current_admin_id+'[\s]*,|,[\s]*'+current_admin_id+'[\s]*,|,[\s]*'+current_admin_id+'$|^'+current_admin_id+'$');
				if(reg_for_current_admin_id.test(document.MassUpdate.uid.value)) {
					//if current user is admin, we should not allow massupdate the user_type and status of himself
					alert(SUGAR.language.get('Users','LBL_LAST_ADMIN_NOTICE'));
					return false;
				}
			}
			break;
		case 'entire':
			var entireInput = document.createElement('input');
			entireInput.name = 'entire';
			entireInput.type = 'hidden';
			entireInput.value = 'index';
			document.MassUpdate.appendChild(entireInput);
			//confirm(no_record_txt);
			if(document.MassUpdate.module!= 'undefined' && document.MassUpdate.module.value == 'Users' && (document.MassUpdate.is_admin.value!='' || document.MassUpdate.status.value!='')) {
				alert(SUGAR.language.get('Users','LBL_LAST_ADMIN_NOTICE'));
				return false;
			}
			break;
	}

	if(!sugarListView.confirm_action(del))
		return false;

	if(del == 1) {
		var deleteInput = document.createElement('input');
		deleteInput.name = 'Delete';
		deleteInput.type = 'hidden';
		deleteInput.value = true;
		document.MassUpdate.appendChild(deleteInput);
		if(document.MassUpdate.module!= 'undefined' && document.MassUpdate.module.value == 'EmailTemplates') {
			check_used_email_templates();
			return false;
		}

	}

	document.MassUpdate.submit();
	return false;
}


sugarListView.prototype.clear_all = function() {
	document.MassUpdate.uid.value = '';
	document.MassUpdate.select_entire_list.value = 0;
	sugarListView.check_all(document.MassUpdate, 'mass[]', false);
	document.MassUpdate.massall.checked = false;
	document.MassUpdate.massall.disabled = false;
	sugarListView.update_count(0);
}

sListView = new sugarListView();
// -- end sugarListView class

// format and unformat numbers
function unformatNumber(n, num_grp_sep, dec_sep) {
	var x=unformatNumberNoParse(n, num_grp_sep, dec_sep);
	x=x.toString();
	if(x.length > 0) {
		return parseFloat(x);
	}
	return '';
}

function unformatNumberNoParse(n, num_grp_sep, dec_sep) {
	if(typeof num_grp_sep == 'undefined' || typeof dec_sep == 'undefined') return n;
	n = n ? n.toString() : '';
	if(n.length > 0) {
	    num_grp_sep_re = new RegExp('\\'+num_grp_sep, 'g');
	    n = n.replace(num_grp_sep_re, '').replace(dec_sep, '.');

        if(typeof CurrencySymbols != 'undefined') {
            // Need to strip out the currency symbols from the start.
            for ( var idx in CurrencySymbols ) {
                n = n.replace(CurrencySymbols[idx], '');
            }
        }
		return n;
	}
	return '';
}

// round parameter can be negative for decimal, precision has to be postive
function formatNumber(n, num_grp_sep, dec_sep, round, precision) {
  if(typeof num_grp_sep == 'undefined' || typeof dec_sep == 'undefined') return n;
  n = n ? n.toString() : '';
  if(n.split) n = n.split('.');
  else return n;

  if(n.length > 2) return n.join('.'); // that's not a num!
  // round
  if(typeof round != 'undefined') {
    if(round > 0 && n.length > 1) { // round to decimal
      n[1] = parseFloat('0.' + n[1]);
      n[1] = Math.round(n[1] * Math.pow(10, round)) / Math.pow(10, round);
      n[1] = n[1].toString().split('.')[1];
    }
    if(round <= 0) { // round to whole number
        n[0] = Math.round(parseInt(n[0],10) * Math.pow(10, round)) / Math.pow(10, round);
      n[1] = '';
    }
  }

  if(typeof precision != 'undefined' && precision >= 0) {
    if(n.length > 1 && typeof n[1] != 'undefined') n[1] = n[1].substring(0, precision); // cut off precision
	else n[1] = '';
    if(n[1].length < precision) {
      for(var wp = n[1].length; wp < precision; wp++) n[1] += '0';
    }
  }

  regex = /(\d+)(\d{3})/;
  while(num_grp_sep != '' && regex.test(n[0])) n[0] = n[0].replace(regex, '$1' + num_grp_sep + '$2');
  return n[0] + (n.length > 1 && n[1] != '' ? dec_sep + n[1] : '');
}

// --- begin ajax status class
SUGAR.ajaxStatusClass = function() {};
SUGAR.ajaxStatusClass.prototype.statusDiv = null;
SUGAR.ajaxStatusClass.prototype.oldOnScroll = null;
SUGAR.ajaxStatusClass.prototype.shown = false; // state of the status window

// reposition the status div, top and centered
SUGAR.ajaxStatusClass.prototype.positionStatus = function() {
	this.statusDiv.style.top = document.body.scrollTop + 8 + 'px';
	statusDivRegion = YAHOO.util.Dom.getRegion(this.statusDiv);
	statusDivWidth = statusDivRegion.right - statusDivRegion.left;
	this.statusDiv.style.left = YAHOO.util.Dom.getViewportWidth() / 2 - statusDivWidth / 2 + 'px';
}

// private func, create the status div
SUGAR.ajaxStatusClass.prototype.createStatus = function(text) {
	statusDiv = document.createElement('div');
	statusDiv.className = 'dataLabel';
	statusDiv.style.background = '#ffffff';
	statusDiv.style.color = '#c60c30';
	statusDiv.style.position = 'absolute';
	statusDiv.style.opacity = .8;
	statusDiv.style.filter = 'alpha(opacity=80)';
	statusDiv.id = 'ajaxStatusDiv';
	document.body.appendChild(statusDiv);
	this.statusDiv = document.getElementById('ajaxStatusDiv');
}

// public - show the status div with text
SUGAR.ajaxStatusClass.prototype.showStatus = function(text) {
	if(!this.statusDiv) {
		this.createStatus(text);
	}
	else {
		this.statusDiv.style.display = '';
	}
	this.statusDiv.style.zIndex = 20;
	this.statusDiv.innerHTML = '&nbsp;<b>' + text + '</b>&nbsp;';
	this.positionStatus();
	if(!this.shown) {
		this.shown = true;
		this.statusDiv.style.display = '';
		if(window.onscroll) this.oldOnScroll = window.onscroll; // save onScroll
		window.onscroll = this.positionStatus;
	}
}

// public - hide it
SUGAR.ajaxStatusClass.prototype.hideStatus = function(text) {
	if(!this.shown) return;
	this.shown = false;
	if(this.oldOnScroll) window.onscroll = this.oldOnScroll;
	else window.onscroll = '';
	this.statusDiv.style.display = 'none';
}

SUGAR.ajaxStatusClass.prototype.flashStatus = function(text, time){
	this.showStatus(text);
	window.setTimeout('ajaxStatus.hideStatus();', time);
}


var ajaxStatus = new SUGAR.ajaxStatusClass();
// --- end ajax status class

/**
 * Unified Search Advanced - for global search
 */
SUGAR.unifiedSearchAdvanced = function() {
	var usa_div;
	var usa_img;
	var usa_open;
	var usa_content;
	var anim_open;
	var anim_close;

	return {
		init: function() {
			SUGAR.unifiedSearchAdvanced.usa_div = document.getElementById('unified_search_advanced_div');
			SUGAR.unifiedSearchAdvanced.usa_img = document.getElementById('unified_search_advanced_img');

			if(!SUGAR.unifiedSearchAdvanced.usa_div || !SUGAR.unifiedSearchAdvanced.usa_img) return;
			var attributes = { height: { to: 300 } };
            SUGAR.unifiedSearchAdvanced.anim_open = new YAHOO.util.Anim('unified_search_advanced_div', attributes );
			SUGAR.unifiedSearchAdvanced.anim_open.duration = 0.75;
			SUGAR.unifiedSearchAdvanced.anim_close = new YAHOO.util.Anim('unified_search_advanced_div', { height: {to: 0} } );
			SUGAR.unifiedSearchAdvanced.anim_close.duration = 0.75;
			//SUGAR.unifiedSearchAdvanced.anim_close.onComplete.subscribe(function() {SUGAR.unifiedSearchAdvanced.usa_div.style.display = 'none'});

			SUGAR.unifiedSearchAdvanced.usa_img._x = YAHOO.util.Dom.getX(SUGAR.unifiedSearchAdvanced.usa_img);
			SUGAR.unifiedSearchAdvanced.usa_img._y = YAHOO.util.Dom.getY(SUGAR.unifiedSearchAdvanced.usa_img);


			SUGAR.unifiedSearchAdvanced.usa_open = false;
			SUGAR.unifiedSearchAdvanced.usa_content = null;

		   YAHOO.util.Event.addListener('unified_search_advanced_img', 'click', SUGAR.unifiedSearchAdvanced.get_content);
		},

		get_content: function(e) {
	   		if(SUGAR.unifiedSearchAdvanced.usa_content == null) {
		   		ajaxStatus.showStatus(SUGAR.language.get('app_strings', 'LBL_LOADING'));
				var cObj = YAHOO.util.Connect.asyncRequest('GET','index.php?to_pdf=1&module=Home&action=UnifiedSearch&usa_form=true',
														  {success: SUGAR.unifiedSearchAdvanced.animate, failure: SUGAR.unifiedSearchAdvanced.animate}, null);
			}
			else SUGAR.unifiedSearchAdvanced.animate();
	    },

		animate: function(data) {
			ajaxStatus.hideStatus();

			if(data) {
				SUGAR.unifiedSearchAdvanced.usa_content = data.responseText;
				SUGAR.unifiedSearchAdvanced.usa_div.innerHTML = SUGAR.unifiedSearchAdvanced.usa_content;
			}
			if(SUGAR.unifiedSearchAdvanced.usa_open) {
				document.UnifiedSearch.advanced.value = 'false';
				SUGAR.unifiedSearchAdvanced.anim_close.animate();
			}
			else {
				document.UnifiedSearch.advanced.value = 'true';
				SUGAR.unifiedSearchAdvanced.usa_div.style.display = '';
				YAHOO.util.Dom.setX(SUGAR.unifiedSearchAdvanced.usa_div, SUGAR.unifiedSearchAdvanced.usa_img._x - 90);
				YAHOO.util.Dom.setY(SUGAR.unifiedSearchAdvanced.usa_div, SUGAR.unifiedSearchAdvanced.usa_img._y + 15);
				SUGAR.unifiedSearchAdvanced.anim_open.animate();
			}
	      	SUGAR.unifiedSearchAdvanced.usa_open = !SUGAR.unifiedSearchAdvanced.usa_open;

			return false;
		},

		checkUsaAdvanced: function() {
			if(document.UnifiedSearch.advanced.value == 'true') {
				document.UnifiedSearchAdvanced.query_string.value = document.UnifiedSearch.query_string.value;
				document.UnifiedSearchAdvanced.submit();
				return false;
			}
			return true;
		}
};
}();
if(typeof YAHOO != 'undefined') YAHOO.util.Event.addListener(window, 'load', SUGAR.unifiedSearchAdvanced.init);


SUGAR.ui = {
	/**
	 * Toggles the header
	 */
	toggleHeader : function() {
		var h = document.getElementById('header');

		if(h != null) {
			if(h != null) {
				if(h.style.display == 'none') {
					h.style.display = '';
				} else {
					h.style.display = 'none';
				}
			}
		} else {
			alert(SUGAR.language.get("app_strings", "ERR_NO_HEADER_ID"));
		}
	}
};


/**
 * General Sugar Utils
 */
SUGAR.util = function () {
	var additionalDetailsCache;
	var additionalDetailsCalls;
	var additionalDetailsRpcCall;

	return {
		getAndRemove : function (el) {
			if (YAHOO && YAHOO.util && YAHOO.util.Dom)
				el = YAHOO.util.Dom.get(el);
			else if (typeof (el) == "string")
				el = document.getElementById(el);
			if (el && el.parentNode)
				el.parentNode.removeChild(el);

			return el;
		},
		paramsToUrl : function (params) {
			url = "";
			for (i in params) {
				url += i + "=" + params[i] + "&";
			}
			return url;
		},
	    evalScript:function(text){
			if (isSafari) {
				var waitUntilLoaded = function(){
					SUGAR.evalScript_waitCount--;
					if (SUGAR.evalScript_waitCount == 0) {
                      var headElem = document.getElementsByTagName('head')[0];
                      for ( var i = 0; i < SUGAR.evalScript_evalElem.length; i++) {
                        var tmpElem = document.createElement('script');
                        tmpElem.type = 'text/javascript';
                        tmpElem.text = SUGAR.evalScript_evalElem[i];
                        headElem.appendChild(tmpElem);
                      }
					}
				};

				var tmpElem = document.createElement('div');
				tmpElem.innerHTML = text;
				var results = tmpElem.getElementsByTagName('script');
				if (results == null) {
					// No scripts found, bail out
					return;
				}

				var headElem = document.getElementsByTagName('head')[0];
				var tmpElem = null;
				SUGAR.evalScript_waitCount = 0;
				SUGAR.evalScript_evalElem = new Array();
				for (var i = 0; i < results.length; i++) {
					if (typeof(results[i]) != 'object') {
						continue;
					};
					tmpElem = document.createElement('script');
					tmpElem.type = 'text/javascript';
					if (results[i].src != null && results[i].src != '') {
						tmpElem.src = results[i].src;
					} else {
                        // Need to defer execution of these scripts until the
                        // required javascript files are fully loaded
                        SUGAR.evalScript_evalElem[SUGAR.evalScript_evalElem.length] = results[i].text;
                        continue;
					}
					tmpElem.addEventListener('load', waitUntilLoaded);
					SUGAR.evalScript_waitCount++;
					headElem.appendChild(tmpElem);
				}
                // Add some code to handle pages without any external scripts
				SUGAR.evalScript_waitCount++;
                waitUntilLoaded();

				// Don't try and process things the IE way
				return;
			}

	        var objRegex = /<\s*script([^>]*)>((.|\s|\v|\0)*?)<\s*\/script\s*>/igm;
			var lastIndex = -1;
			var result =  objRegex.exec(text);
            while(result && result.index > lastIndex){
            	lastIndex = result.index
				try{
					var script = document.createElement('script');
                  	script.type= 'text/javascript';
                  	if(result[1].indexOf("src=") > -1){
						var srcRegex = /.*src=['"]([a-zA-Z0-9\&\/\.\?=:]*)['"].*/igm;
						var srcResult =  result[1].replace(srcRegex, '$1');
						script.src = srcResult;
                  	}else{
                  		script.text = result[2];
                  	}
                  	document.body.appendChild(script)
	              }
	              catch(e) {

                  }
                  result =  objRegex.exec(text);
			}
	    },
		/**
		 * Gets the sidebar object
		 * @return object pointer to the sidebar element
		 */
		getLeftColObj: function() {
			leftColObj = document.getElementById('leftCol');
			while(leftColObj.nodeName != 'TABLE') {
				leftColObj = leftColObj.firstChild;
			}
			leftColTable = leftColObj;
			leftColTd = leftColTable.getElementsByTagName('td')[0];
			leftColTdRegion = YAHOO.util.Dom.getRegion(leftColTd);
			leftColTd.style.width = (leftColTdRegion.right - leftColTdRegion.left) + 'px';

			return leftColTd;
		},
		/**
		 * Fills the shortcut menu placeholders w/ actual content
		 * Call this on load event
		 *
		 * @param shortcutContent Array array of content to fill in
		 */
		fillShortcuts: function(e, shortcutContent) {
			return ;
/*
            // don't do this if leftCol isn't available
            if (document.getElementById('leftCol') == undefined) { return; }

	    	spans = document.getElementById('leftCol').getElementsByTagName('span');
			hideCol = document.getElementById('HideMenu').getElementsByTagName('span');
			w = spans.length + 1;
			for(i in hideCol) {
				spans[w] = hideCol[i];
				w++;
			}
		    for(je in shortcutContent) {
		    	for(wp in spans) {
		    		if(typeof spans[wp].innerHTML != 'undefined' && spans[wp].innerHTML == ('wp_shortcut_fill_' + je)) {
		    			if(typeof spans[wp].parentNode.parentNode == 'object') {
		    				if(typeof spans[wp].parentNode.parentNode.onclick != 'undefined') {
		    					spans[wp].parentNode.parentNode.onclick = null;
		    				}
		    				// If the wp_shortcut span is contained by an A tag, replace the A with a DIV.
		    				if(spans[wp].parentNode.tagName == 'A' && !isIE) {
		    					var newDiv = document.createElement('DIV');
		    					var parentAnchor = spans[wp].parentNode;

		    					spans[wp].parentNode.parentNode.style.display = 'none';

		    					// Copy styles over to the new container div
		    					if(window.getComputedStyle) {
			    					var parentStyle = window.getComputedStyle(parentAnchor, '');
			    					for(var styleName in parentStyle) {
				    					if(typeof parentStyle[styleName] != 'function'
	   			    				    && styleName != 'display'
	   			    				    && styleName != 'borderWidth'
				    				    && styleName != 'visibility') {
				    				    	try {
						    					newDiv.style[styleName] = parentStyle[styleName];
						    				} catch(e) {
						    					// Catches .length and .parentRule, and others
						    				}
					    				}
				    				}
				    			}

			    				// Replace the A with the DIV
		    					newDiv.appendChild(spans[wp]);
		    					parentAnchor.parentNode.replaceChild(newDiv, parentAnchor);

		    					spans[wp].parentNode.parentNode.style.display = '';
		    				}
		    			}
			            spans[wp].innerHTML = shortcutContent[je]; // fill w/ content
			            if(spans[wp].style) spans[wp].style.display = '';
		    		}
		    	}
			}*/
		},
		/**
		 * Make an AJAX request.
		 *
		 * @param	url				string	resource to load
		 * @param	theDiv			string	id of element to insert loaded data into
		 * @param	postForm		string	if set, a POST request will be made to resource specified by url using the form named by postForm
		 * @param	callback		string	name of function to invoke after HTTP response is recieved
		 * @param	callbackParam	any		parameter to pass to callback when invoked
		 * @param	appendMode		bool	if true, HTTP response will be appended to the contents of theDiv, or else contents will be overriten.
		 */
	    retrieveAndFill: function(url, theDiv, postForm, callback, callbackParam, appendMode) {
			if(typeof theDiv == 'string') {
				try {
					theDiv = document.getElementById(theDiv);
				}
		        catch(e) {
					return;
				}
			}

			var success = function(data) {
				if (typeof theDiv != 'undefined' && theDiv != null)
				{
					try {
						if (typeof appendMode != 'undefined' && appendMode)
						{
							theDiv.innerHTML += data.responseText;
						}
						else
						{
							theDiv.innerHTML = data.responseText;
						}
					}
					catch (e) {
						return;
					}
				}
				if (typeof callback != 'undefined' && callback != null) callback(callbackParam);
		  	}

			if(typeof postForm == 'undefined' || postForm == null) {
				var cObj = YAHOO.util.Connect.asyncRequest('GET', url, {success: success, failure: success});
			}
			else {
				YAHOO.util.Connect.setForm(postForm);
				var cObj = YAHOO.util.Connect.asyncRequest('POST', url, {success: success, failure: success});
			}
		},
		checkMaxLength: function() { // modified from http://www.quirksmode.org/dom/maxlength.html
			var maxLength = this.getAttribute('maxlength');
			var currentLength = this.value.length;
			if (currentLength > maxLength) {
				this.value = this.value.substring(0, maxLength);
			}
			// not innerHTML
		},
		/**
		 * Adds maxlength attribute to textareas
		 */
		setMaxLength: function() { // modified from http://www.quirksmode.org/dom/maxlength.html
			var x = document.getElementsByTagName('textarea');
			for (var i=0;i<x.length;i++) {
				if (x[i].getAttribute('maxlength')) {
					x[i].onkeyup = x[i].onchange = SUGAR.util.checkMaxLength;
					x[i].onkeyup();
				}
			}
		},

		/**
		 * Retrieves additional details dynamically
		 */
		getAdditionalDetails: function(bean, id, spanId) {
			go = function() {
				oReturn = function(body, caption, width, theme) {
					var _refx = 25-width;
					return overlib(body, CAPTION, caption, STICKY, MOUSEOFF, 1000, WIDTH, width, CLOSETEXT, ('<img border=0 style="margin-left:2px; margin-right: 2px;" src=index.php?entryPoint=getImage&themeName='+SUGAR.themes.theme_name+'&imageName=close.gif>'), CLOSETITLE, SUGAR.language.get('app_strings','LBL_ADDITIONAL_DETAILS_CLOSE_TITLE'), CLOSECLICK, FGCLASS, 'olFgClass', CGCLASS, 'olCgClass', BGCLASS, 'olBgClass', TEXTFONTCLASS, 'olFontClass', CAPTIONFONTCLASS, 'olCapFontClass', CLOSEFONTCLASS, 'olCloseFontClass', REF, spanId, REFC, 'LL', REFX, _refx);
				}

				success = function(data) {
					eval(data.responseText);

					SUGAR.util.additionalDetailsCache[spanId] = new Array();
					SUGAR.util.additionalDetailsCache[spanId]['body'] = result['body'];
					SUGAR.util.additionalDetailsCache[spanId]['caption'] = result['caption'];
					SUGAR.util.additionalDetailsCache[spanId]['width'] = result['width'];
					SUGAR.util.additionalDetailsCache[spanId]['theme'] = result['theme'];
					ajaxStatus.hideStatus();
					return oReturn(SUGAR.util.additionalDetailsCache[spanId]['body'], SUGAR.util.additionalDetailsCache[spanId]['caption'], SUGAR.util.additionalDetailsCache[spanId]['width'], SUGAR.util.additionalDetailsCache[spanId]['theme']);
				}

				if(typeof SUGAR.util.additionalDetailsCache[spanId] != 'undefined')
					return oReturn(SUGAR.util.additionalDetailsCache[spanId]['body'], SUGAR.util.additionalDetailsCache[spanId]['caption'], SUGAR.util.additionalDetailsCache[spanId]['width'], SUGAR.util.additionalDetailsCache[spanId]['theme']);

				if(typeof SUGAR.util.additionalDetailsCalls[spanId] != 'undefined') // call already in progress
					return;
				ajaxStatus.showStatus(SUGAR.language.get('app_strings', 'LBL_LOADING'));
				url = 'index.php?to_pdf=1&module=Home&action=AdditionalDetailsRetrieve&bean=' + bean + '&id=' + id;
				SUGAR.util.additionalDetailsCalls[spanId] = YAHOO.util.Connect.asyncRequest('GET', url, {success: success, failure: success});

				return false;
			}
			SUGAR.util.additionalDetailsRpcCall = window.setTimeout('go()', 250);
		},
		clearAdditionalDetailsCall: function() {
			if(typeof SUGAR.util.additionalDetailsRpcCall == 'number') window.clearTimeout(SUGAR.util.additionalDetailsRpcCall);
		},
		/**
		 * A function that extends functionality from parent to child.
 		 */
		extend : function(subc, superc, overrides) {
			subc.prototype = new superc;	// set the superclass
			// overrides
			if (overrides) {
			    for (var i in overrides)	subc.prototype[i] = overrides[i];
			}
		},
		hrefURL : function(url) {
			if(SUGAR.isIE) {
				// IE needs special treatment since otherwise it would not pass Referer
				var trampoline = document.createElement('a');
				trampoline.href = url;
				document.body.appendChild(trampoline);
				trampoline.click();
				document.body.removeChild(trampoline);
			} else {
				document.location.href = url;
			}
		},

		openWindow : function(URL, windowName, windowFeatures) {
			if(SUGAR.isIE) {
				// IE needs special treatment since otherwise it would not pass Referer
				win = window.open('', windowName, windowFeatures);
				var trampoline = document.createElement('a');
				trampoline.href = URL;
				trampoline.target = windowName;
				document.body.appendChild(trampoline);
				trampoline.click();
				document.body.removeChild(trampoline);
			} else {
				win = window.open(URL, windowName, windowFeatures);
			}
			return win;
		}
	};
}(); // end util
SUGAR.util.additionalDetailsCache = new Array();
SUGAR.util.additionalDetailsCalls = new Array();
if(typeof YAHOO != 'undefined') YAHOO.util.Event.addListener(window, 'load', SUGAR.util.setMaxLength); // allow textareas to obey maxlength attrib

SUGAR.savedViews = function() {
	var selectedOrderBy;
	var selectedSortOrder;
	var displayColumns;
	var hideTabs;
	var columnsMeta; // meta data for the display columns

	return {
		setChooser: function() {

			var displayColumnsDef = new Array();
			var hideTabsDef = new Array();

		    var left_td = document.getElementById('display_tabs_td');
		    if(typeof left_td == 'undefined' || left_td == null) return; // abort!
		    var right_td = document.getElementById('hide_tabs_td');

		    var displayTabs = left_td.getElementsByTagName('select')[0];
		    var hideTabs = right_td.getElementsByTagName('select')[0];

			for(i = 0; i < displayTabs.options.length; i++) {
				displayColumnsDef.push(displayTabs.options[i].value);
			}

			if(typeof hideTabs != 'undefined') {
				for(i = 0; i < hideTabs.options.length; i++) {
			         hideTabsDef.push(hideTabs.options[i].value);
				}
			}
			if (!SUGAR.savedViews.clearColumns)
				document.getElementById('displayColumnsDef').value = displayColumnsDef.join('|');
			document.getElementById('hideTabsDef').value = hideTabsDef.join('|');
		},

		select: function(saved_search_select) {
			for(var wp = 0; wp < document.search_form.saved_search_select.options.length; wp++) {
				if(typeof document.search_form.saved_search_select.options[wp].value != 'undefined' &&
					document.search_form.saved_search_select.options[wp].value == saved_search_select) {
						document.search_form.saved_search_select.selectedIndex = wp;
						document.search_form.ss_delete.style.display = '';
						document.search_form.ss_update.style.display = '';
				}
			}
		},
		saved_search_action: function(action, delete_lang) {
			if(action == 'delete') {
				if(!confirm(delete_lang)) return;
			}
			if(action == 'save') {
				if(document.search_form.saved_search_name.value.replace(/^\s*|\s*$/g, '') == '') {
					alert(SUGAR.language.get('app_strings', 'LBL_SAVED_SEARCH_ERROR'));
					return;
				}
			}

			// This check is needed for the Activities module (Calls/Meetings/Tasks).
			if (document.search_form.saved_search_action)
			{
				document.search_form.saved_search_action.value = action;
				document.search_form.search_module.value = document.search_form.module.value;
				document.search_form.module.value = 'SavedSearch';
				// Bug 31922 - Make sure to specify that we want to hit the index view here of
				// the SavedSearch module, since the ListView doesn't have the logic to save the
				// search and redirect back
				document.search_form.action.value = 'index';
			}
			document.search_form.submit();
		},
		shortcut_select: function(selectBox, module) {
			//build url
			selecturl = 'index.php?module=SavedSearch&search_module=' + module + '&action=index&saved_search_select=' + selectBox.options[selectBox.selectedIndex].value
			//add searchFormTab to url if it is available.  This determines what tab to render
			if(typeof(document.getElementById('searchFormTab'))!='undefined'){
				selecturl = selecturl + '&searchFormTab=' + document.search_form.searchFormTab.value;
			}
			//add showSSDIV to url if it is available.  This determines whether saved search sub form should
			//be rendered open or not
			if(document.getElementById('showSSDIV') && typeof(document.getElementById('showSSDIV') !='undefined')){
				selecturl = selecturl + '&showSSDIV='+document.getElementById('showSSDIV').value;
			}
			//use created url to navigate
			document.location.href = selecturl;
		},
		handleForm: function() {
			SUGAR.tabChooser.movementCallback = function(left_side, right_side) {
				while(document.getElementById('orderBySelect').childNodes.length != 0) { // clear out order by options
					document.getElementById('orderBySelect').removeChild(document.getElementById('orderBySelect').lastChild);
				}

				var selectedIndex = 0;
				var nodeCount = -1; // need this because the counter i also includes "undefined" nodes
									// which was breaking Calls and Meetings

				for(i in left_side.childNodes) { // fill in order by options
					if(typeof left_side.childNodes[i].nodeName != 'undefined' &&
						left_side.childNodes[i].nodeName.toLowerCase() == 'option' &&
						typeof SUGAR.savedViews.columnsMeta[left_side.childNodes[i].value] != 'undefined' && // check if column is sortable
						typeof SUGAR.savedViews.columnsMeta[left_side.childNodes[i].value]['sortable'] == 'undefined' &&
						SUGAR.savedViews.columnsMeta[left_side.childNodes[i].value]['sortable'] != false) {
							nodeCount++;
							optionNode = document.createElement('option');
							optionNode.value = left_side.childNodes[i].value;
							optionNode.innerHTML = left_side.childNodes[i].innerHTML;
							document.getElementById('orderBySelect').appendChild(optionNode);
							if(optionNode.value == SUGAR.savedViews.selectedOrderBy)
								selectedIndex = nodeCount;
					}
				}
				// Firefox needs this to be set after all the option nodes are created.
				document.getElementById('orderBySelect').selectedIndex = selectedIndex;
			};
			SUGAR.tabChooser.movementCallback(document.getElementById('display_tabs_td').getElementsByTagName('select')[0]);

			// This check is needed for the Activities module (Calls/Meetings/Tasks).
			if (document.search_form.orderBy)
				document.search_form.orderBy.options.value = SUGAR.savedViews.selectedOrderBy;

			// handle direction
			if(SUGAR.savedViews.selectedSortOrder == 'DESC') document.getElementById('sort_order_desc_radio').checked = true;
			else document.getElementById('sort_order_asc_radio').checked = true;
		}
	};
}();

SUGAR.searchForm = function() {
	var url;
	return {
		// searchForm tab selector util
		searchFormSelect: function(view, previousView) {
			var module = view.split('|')[0];
			var theView = view.split('|')[1];
			// retrieve form
			var handleDisplay = function() { // hide other divs
				document.search_form.searchFormTab.value = theView;
				patt = module+"(.*)SearchForm$";
				divId=document.search_form.getElementsByTagName('div');
				// Hide all the search forms and retrive the name of the previous search tab (useful for the first load because previousView is empty)
				for (i=0;i<divId.length;i++){
					if(divId[i].id.match(module)==module){
						if(divId[i].id.match('SearchForm')=='SearchForm'){
	                        if(document.getElementById(divId[i].id).style.display == ''){
	                           previousTab=divId[i].id.match(patt)[1];
	                        }
	                        document.getElementById(divId[i].id).style.display = 'none';
	                    }
					}
				}
				// show the good search form.
				document.getElementById(module + theView + 'SearchForm').style.display = '';
                //if its not the first tab show there is a previous tab.
                if(previousView) {
                     thepreviousView=previousView.split('|')[1];
                 }
                 else{
                     thepreviousView=previousTab;
                 }
                 thepreviousView=thepreviousView.replace(/_search/, "");
                 // Process to retrieve the completed field from one tab to an other.
                 for(num in document.search_form.elements) {
                     if(document.search_form.elements[num]) {
                         el = document.search_form.elements[num];
                         pattern="^(.*)_"+thepreviousView+"$";
                         if(typeof el.type != 'undefined' && typeof el.name != 'undefined' && el.name.match(pattern)) {
                             advanced_input_name = el.name.match(pattern)[1]; // strip
                             advanced_input_name = advanced_input_name+"_"+theView.replace(/_search/, "");
                             if(typeof document.search_form[advanced_input_name] != 'undefined')  // if advanced input of same name exists
                                 SUGAR.searchForm.copyElement(advanced_input_name, el);
                         }
                     }
                 }
			}

			// if tab is not cached
			if(document.getElementById(module + theView + 'SearchForm').innerHTML == '') {
				ajaxStatus.showStatus(SUGAR.language.get('app_strings', 'LBL_LOADING'));
				var success = function(data) {
					document.getElementById(module + theView + 'SearchForm').innerHTML = data.responseText;

					SUGAR.util.evalScript(data.responseText);
					// pass script variables to global scope
					if(theView == 'saved_views') {
						if(typeof columnsMeta != 'undefined') SUGAR.savedViews.columnsMeta = columnsMeta;
						if(typeof selectedOrderBy != 'undefined') SUGAR.savedViews.selectedOrderBy = selectedOrderBy;
						if(typeof selectedSortOrder != 'undefined') SUGAR.savedViews.selectedSortOrder = selectedSortOrder;
					}

					handleDisplay();
					enableQS(true);
					ajaxStatus.hideStatus();
				}
				url = 	'index.php?module=' + module + '&action=index&search_form_only=true&to_pdf=true&search_form_view=' + theView;

				//check to see if tpl has been specified.  If so then pass location through url string
				var tpl ='';
				if(document.getElementById('search_tpl') !=null && typeof(document.getElementById('search_tpl')) != 'undefined'){
					tpl = document.getElementById('search_tpl').value;
					if(tpl != ''){url += '&search_tpl='+tpl;}
				}

				if(theView == 'saved_views') // handle the tab chooser
					url += '&displayColumns=' + SUGAR.savedViews.displayColumns + '&hideTabs=' + SUGAR.savedViews.hideTabs + '&orderBy=' + SUGAR.savedViews.selectedOrderBy + '&sortOrder=' + SUGAR.savedViews.selectedSortOrder;

				var cObj = YAHOO.util.Connect.asyncRequest('GET', url, {success: success, failure: success});
			}
			else { // that form already retrieved
				handleDisplay();
			}
		},

		// copies one input to another
		copyElement: function(inputName, copyFromElement) {
			switch(copyFromElement.type) {
				case 'select-one':
				case 'text':
					document.search_form[inputName].value = copyFromElement.value;
					break;
			}
		},
        // This function is here to clear the form, instead of "resubmitting it
		clear_form: function(form) {
            var elemList = form.elements;
            var elem;
            var elemType;

            for( var i = 0; i < elemList.length ; i++ ) {
                elem = elemList[i];
                if ( typeof(elem.type) == 'undefined' ) {
                    continue;
                }

                elemType = elem.type.toLowerCase();

                if ( elemType == 'text' || elemType == 'textarea' || elemType == 'password' ) {
                    elem.value = '';
                }
                else if ( elemType == 'select' || elemType == 'select-one' || elemType == 'select-multiple' ) {
                    // We have, what I hope, is a select box, time to unselect all options
                    var optionList = elem.options;
                    for ( var ii = 0 ; ii < optionList.length ; ii++ ) {
                        optionList[ii].selected = false;
                    }
                }
                else if ( elemType == 'radio' || elemType == 'checkbox' ) {
                    elem.checked = false;
                    elem.selected = false;
                }
                else if ( elemType == 'hidden' ) {
                    // We only want to reset the hidden values that link to the select boxes.
                    if ( ( elem.name.length > 3 && elem.name.substring(elem.name.length-3) == '_id' )
                         || ((elem.name.length > 9) && (elem.name.substring(elem.name.length - 9) == '_id_basic'))
                         || ( elem.name.length > 12 && elem.name.substring(elem.name.length-12) == '_id_advanced' ) ) {
                        elem.value = '';
                    }
                }
            }
			SUGAR.savedViews.clearColumns = true;
		}
	};
}();
// Code for the column/tab chooser used on homepage and in admin section
SUGAR.tabChooser = function () {
	var	object_refs = new Array();
	return {
			/* Describe certain transfers as invalid */
			frozenOptions: [],

			movementCallback: function(left_side, right_side) {},
			orderCallback: function(left_side, right_side) {},

			freezeOptions: function(left_name, right_name, target) {
				if(!SUGAR.tabChooser.frozenOptions) { SUGAR.tabChooser.frozenOptions = []; }
				if(!SUGAR.tabChooser.frozenOptions[left_name]) { SUGAR.tabChooser.frozenOptions[left_name] = []; }
				if(!SUGAR.tabChooser.frozenOptions[left_name][right_name]) { SUGAR.tabChooser.frozenOptions[left_name][right_name] = []; }
				if(typeof target == 'array') {
					for(var i in target) {
						SUGAR.tabChooser.frozenOptions[left_name][right_name][target[i]] = true;
					}
				} else {
					SUGAR.tabChooser.frozenOptions[left_name][right_name][target] = true;
				}
			},

			buildSelectHTML: function(info) {
				var text = "<select";

		        if(typeof (info['select']['size']) != 'undefined') {
		                text +=" size=\""+ info['select']['size'] +"\"";
		        }

		        if(typeof (info['select']['name']) != 'undefined') {
		                text +=" name=\""+ info['select']['name'] +"\"";
		        }

		        if(typeof (info['select']['style']) != 'undefined') {
		                text +=" style=\""+ info['select']['style'] +"\"";
		        }

		        if(typeof (info['select']['onchange']) != 'undefined') {
		                text +=" onChange=\""+ info['select']['onchange'] +"\"";
		        }

		        if(typeof (info['select']['multiple']) != 'undefined') {
		                text +=" multiple";
		        }
		        text +=">";

		        for(i=0; i<info['options'].length;i++) {
		                option = info['options'][i];
		                text += "<option value=\""+option['value']+"\" ";
		                if ( typeof (option['selected']) != 'undefined' && option['selected']== true) {
		                        text += "SELECTED";
		                }
		                text += ">"+option['text']+"</option>";
		        }
		        text += "</select>";
		        return text;
			},

			left_to_right: function(left_name, right_name, left_size, right_size) {
				SUGAR.savedViews.clearColumns = false;
			    var left_td = document.getElementById(left_name+'_td');
			    var right_td = document.getElementById(right_name+'_td');

			    var display_columns_ref = left_td.getElementsByTagName('select')[0];
			    var hidden_columns_ref = right_td.getElementsByTagName('select')[0];

			    var selected_left = new Array();
			    var notselected_left = new Array();
			    var notselected_right = new Array();

			    var left_array = new Array();

			    var frozen_options = SUGAR.tabChooser.frozenOptions;
			    frozen_options = frozen_options && frozen_options[left_name] && frozen_options[left_name][right_name]?frozen_options[left_name][right_name]:[];

			        // determine which options are selected in left
			    for (i=0; i < display_columns_ref.options.length; i++)
			    {
			        if ( display_columns_ref.options[i].selected == true && !frozen_options[display_columns_ref.options[i].value])
			        {
			            selected_left[selected_left.length] = {text: display_columns_ref.options[i].text, value: display_columns_ref.options[i].value};
			        }
			        else
			        {
			            notselected_left[notselected_left.length] = {text: display_columns_ref.options[i].text, value: display_columns_ref.options[i].value};
			        }

			    }

			    for (i=0; i < hidden_columns_ref.options.length; i++)
			    {
			        notselected_right[notselected_right.length] = {text:hidden_columns_ref.options[i].text, value:hidden_columns_ref.options[i].value};

			    }

			    var left_select_html_info = new Object();
			    var left_options = new Array();
			    var left_select = new Object();

			    left_select['name'] = left_name+'[]';
			    left_select['id'] = left_name;
			    left_select['size'] = left_size;
			    left_select['multiple'] = 'true';

			    var right_select_html_info = new Object();
			    var right_options = new Array();
			    var right_select = new Object();

			    right_select['name'] = right_name+'[]';
			    right_select['id'] = right_name;
			    right_select['size'] = right_size;
			    right_select['multiple'] = 'true';

			    for (i = 0; i < notselected_right.length; i++) {
			        right_options[right_options.length] = notselected_right[i];
			    }

			    for (i = 0; i < selected_left.length; i++) {
			        right_options[right_options.length] = selected_left[i];
			    }
			    for (i = 0; i < notselected_left.length; i++) {
			        left_options[left_options.length] = notselected_left[i];
			    }
			    left_select_html_info['options'] = left_options;
			    left_select_html_info['select'] = left_select;
			    right_select_html_info['options'] = right_options;
			    right_select_html_info['select'] = right_select;
			    right_select_html_info['style'] = 'background: lightgrey';

			    var left_html = this.buildSelectHTML(left_select_html_info);
			    var right_html = this.buildSelectHTML(right_select_html_info);

			    left_td.innerHTML = left_html;
			    right_td.innerHTML = right_html;

				object_refs[left_name] = left_td.getElementsByTagName('select')[0];
				object_refs[right_name] = right_td.getElementsByTagName('select')[0];

				this.movementCallback(object_refs[left_name], object_refs[right_name]);

			    return false;
			},


			right_to_left: function(left_name, right_name, left_size, right_size, max_left) {
				SUGAR.savedViews.clearColumns = false;
			    var left_td = document.getElementById(left_name+'_td');
			    var right_td = document.getElementById(right_name+'_td');

			    var display_columns_ref = left_td.getElementsByTagName('select')[0];
			    var hidden_columns_ref = right_td.getElementsByTagName('select')[0];

			    var selected_right = new Array();
			    var notselected_right = new Array();
			    var notselected_left = new Array();

			    var frozen_options = SUGAR.tabChooser.frozenOptions;
			    frozen_options = SUGAR.tabChooser.frozenOptions && SUGAR.tabChooser.frozenOptions[right_name] && SUGAR.tabChooser.frozenOptions[right_name][left_name]?SUGAR.tabChooser.frozenOptions[right_name][left_name]:[];

			    for (i=0; i < hidden_columns_ref.options.length; i++)
			    {
			        if (hidden_columns_ref.options[i].selected == true && !frozen_options[hidden_columns_ref.options[i].value])
			        {
			            selected_right[selected_right.length] = {text:hidden_columns_ref.options[i].text, value:hidden_columns_ref.options[i].value};
			        }
			        else
			        {
			            notselected_right[notselected_right.length] = {text:hidden_columns_ref.options[i].text, value:hidden_columns_ref.options[i].value};
			        }

			    }

			    if(max_left != '' && (display_columns_ref.length + selected_right.length) > max_left) {
			    	alert('Maximum of ' + max_left + ' columns can be displayed.');
					return;
			    }

			    for (i=0; i < display_columns_ref.options.length; i++)
			    {
			        notselected_left[notselected_left.length] = {text:display_columns_ref.options[i].text, value:display_columns_ref.options[i].value};

			    }

			    var left_select_html_info = new Object();
			    var left_options = new Array();
			    var left_select = new Object();

			    left_select['name'] = left_name+'[]';
			    left_select['id'] = left_name;
			    left_select['multiple'] = 'true';
			    left_select['size'] = left_size;

			    var right_select_html_info = new Object();
			    var right_options = new Array();
			    var right_select = new Object();

			    right_select['name'] = right_name+ '[]';
			    right_select['id'] = right_name;
			    right_select['multiple'] = 'true';
			    right_select['size'] = right_size;

			    for (i = 0; i < notselected_left.length; i++) {
			        left_options[left_options.length] = notselected_left[i];
			    }

			    for (i = 0; i < selected_right.length; i++) {
			        left_options[left_options.length] = selected_right[i];
			    }
			    for (i = 0; i < notselected_right.length; i++) {
			        right_options[right_options.length] = notselected_right[i];
			    }
			    left_select_html_info['options'] = left_options;
			    left_select_html_info['select'] = left_select;
			    right_select_html_info['options'] = right_options;
			    right_select_html_info['select'] = right_select;
			    right_select_html_info['style'] = 'background: lightgrey';

			    var left_html = this.buildSelectHTML(left_select_html_info);
			    var right_html = this.buildSelectHTML(right_select_html_info);

			    left_td.innerHTML = left_html;
			    right_td.innerHTML = right_html;

				object_refs[left_name] = left_td.getElementsByTagName('select')[0];
				object_refs[right_name] = right_td.getElementsByTagName('select')[0];

				this.movementCallback(object_refs[left_name], object_refs[right_name]);

			    return false;
			},

			up: function(name, left_name, right_name) {
				SUGAR.savedViews.clearColumns = false;
			    var left_td = document.getElementById(left_name+'_td');
			    var right_td = document.getElementById(right_name+'_td');
			    var td = document.getElementById(name+'_td');
			    var obj = td.getElementsByTagName('select')[0];
			    obj = (typeof obj == "string") ? document.getElementById(obj) : obj;
			    if (obj.tagName.toLowerCase() != "select" && obj.length < 2)
			        return false;
			    var sel = new Array();

			    for (i=0; i<obj.length; i++) {
			        if (obj[i].selected == true) {
			            sel[sel.length] = i;
			        }
			    }
			    for (i=0; i < sel.length; i++) {
			        if (sel[i] != 0 && !obj[sel[i]-1].selected) {
			            var tmp = new Array(obj[sel[i]-1].text, obj[sel[i]-1].value);
			            obj[sel[i]-1].text = obj[sel[i]].text;
			            obj[sel[i]-1].value = obj[sel[i]].value;
			            obj[sel[i]].text = tmp[0];
			            obj[sel[i]].value = tmp[1];
			            obj[sel[i]-1].selected = true;
			            obj[sel[i]].selected = false;
			        }
			    }

				object_refs[left_name] = left_td.getElementsByTagName('select')[0];
				object_refs[right_name] = right_td.getElementsByTagName('select')[0];

				this.orderCallback(object_refs[left_name], object_refs[right_name]);

			    return false;
			},

			down: function(name, left_name, right_name) {
				SUGAR.savedViews.clearColumns = false;
			   	var left_td = document.getElementById(left_name+'_td');
			    var right_td = document.getElementById(right_name+'_td');
			    var td = document.getElementById(name+'_td');
			    var obj = td.getElementsByTagName('select')[0];
			    if (obj.tagName.toLowerCase() != "select" && obj.length < 2)
			        return false;
			    var sel = new Array();
			    for (i=obj.length-1; i>-1; i--) {
			        if (obj[i].selected == true) {
			            sel[sel.length] = i;
			        }
			    }
			    for (i=0; i < sel.length; i++) {
			        if (sel[i] != obj.length-1 && !obj[sel[i]+1].selected) {
			            var tmp = new Array(obj[sel[i]+1].text, obj[sel[i]+1].value);
			            obj[sel[i]+1].text = obj[sel[i]].text;
			            obj[sel[i]+1].value = obj[sel[i]].value;
			            obj[sel[i]].text = tmp[0];
			            obj[sel[i]].value = tmp[1];
			            obj[sel[i]+1].selected = true;
			            obj[sel[i]].selected = false;
			        }
			    }

				object_refs[left_name] = left_td.getElementsByTagName('select')[0];
				object_refs[right_name] = right_td.getElementsByTagName('select')[0];

				this.orderCallback(object_refs[left_name], object_refs[right_name]);

			    return false;
			}
		};
}(); // end tabChooser

SUGAR.language = function() {
    return {
        languages : new Array(),

        setLanguage: function(module, data) {
           if (!SUGAR.language.languages) {

           }
            SUGAR.language.languages[module] = data;
        },

        get: function(module, str) {
            if(typeof SUGAR.language.languages[module] == 'undefined'
            || typeof SUGAR.language.languages[module][str] == 'undefined')
                return 'undefined';

            return SUGAR.language.languages[module][str];
        }
    };
}();

SUGAR.contextMenu = function() {
	return {
		objects: new Object(),
		objectTypes: new Object(),
		/**
		 * Registers a new object for the context menu.
		 * objectType - name of the type
		 * id - element id
		 * metaData - metaData to pass to the action function
		 **/
		registerObject: function(objectType, id, metaData) {
			SUGAR.contextMenu.objects[id] = new Object();
            SUGAR.contextMenu.objects[id] = {'objectType' : objectType, 'metaData' : metaData};
		},
		/**
		 * Registers a new object type
		 * name - name of the type
		 * menuItems - array of menu items
		 **/
		registerObjectType: function(name, menuItems) {
			SUGAR.contextMenu.objectTypes[name] = new Object();
			SUGAR.contextMenu.objectTypes[name] = {'menuItems' : menuItems, 'objects' : new Array()};
		},
		/**
		 * Determines which menu item was clicked
		 **/
		getListItemFromEventTarget: function(p_oNode) {
            var oLI;
            if(p_oNode.tagName == "LI") {
	            oLI = p_oNode;
            }
            else {
	            do {
	                if(p_oNode.tagName == "LI") {
	                    oLI = p_oNode;
	                    break;
	                }

	            } while((p_oNode = p_oNode.parentNode));
  	        }
            return oLI;
         },
         /**
          * handles movement within context menu
          **/
         onContextMenuMove: function() {
            var oNode = this.contextEventTarget;
            var bDisabled = (oNode.tagName == "UL");
            var i = this.getItemGroups()[0].length - 1;
            do {
                this.getItem(i).cfg.setProperty("disabled", bDisabled);
            }
            while(i--);
        },
        /**
         * handles clicks on a context menu ITEM
         **/
		onContextMenuItemClick: function(p_sType, p_aArguments, p_oItem) {
            var oLI = SUGAR.contextMenu.getListItemFromEventTarget(this.parent.contextEventTarget);
            id = this.parent.contextEventTarget.parentNode.id; // id of the target
            funct = eval(SUGAR.contextMenu.objectTypes[SUGAR.contextMenu.objects[id]['objectType']]['menuItems'][this.index]['action']);
            funct(this.parent.contextEventTarget, SUGAR.contextMenu.objects[id]['metaData']);
		},
		/**
		 * Initializes all context menus registered
		 **/
		init: function() {
			for(var i in SUGAR.contextMenu.objects) { // make a variable called objects in objectTypes containg references to all triggers
                if(typeof SUGAR.contextMenu.objectTypes[SUGAR.contextMenu.objects[i]['objectType']]['objects'] == 'undefined')
                    SUGAR.contextMenu.objectTypes[SUGAR.contextMenu.objects[i]['objectType']]['objects'] = new Array();
				SUGAR.contextMenu.objectTypes[SUGAR.contextMenu.objects[i]['objectType']]['objects'].push(document.getElementById(i));
			}
            // register the menus
			for(var i in SUGAR.contextMenu.objectTypes) {
	            var oContextMenu = new YAHOO.widget.ContextMenu(i, {'trigger': SUGAR.contextMenu.objectTypes[i]['objects']});
				var aMainMenuItems = SUGAR.contextMenu.objectTypes[i]['menuItems'];
	            var nMainMenuItems = aMainMenuItems.length;
	            var oMenuItem;
	            for(var j = 0; j < nMainMenuItems; j++) {
	                oMenuItem = new YAHOO.widget.ContextMenuItem(aMainMenuItems[j].text, { helptext: aMainMenuItems[j].helptext });
	                oMenuItem.clickEvent.subscribe(SUGAR.contextMenu.onContextMenuItemClick, oMenuItem, true);
	                oContextMenu.addItem(oMenuItem);
	            }
	            //  Add a "move" event handler to the context menu
	            oContextMenu.moveEvent.subscribe(SUGAR.contextMenu.onContextMenuMove, oContextMenu, true);
	            // Add a "keydown" event handler to the context menu
	            oContextMenu.keyDownEvent.subscribe(SUGAR.contextMenu.onContextMenuItemClick, oContextMenu, true);
	            // Render the context menu
	            oContextMenu.render(document.body);
	        }
		}
	};
}();

SUGAR.contextMenu.actions = function() {
	return {
		/**
		 * redirects to a new note with the clicked on object as the target
		 **/
		createNote: function(itemClicked, metaData) {
			loc = 'index.php?module=Notes&action=EditView';
			for(i in metaData) {
				if(i == 'notes_parent_type') loc += '&parent_type=' + metaData[i];
				else if(i != 'module' && i != 'parent_type') loc += '&' + i + '=' + metaData[i];
			}
			document.location = loc;
		},
		/**
		 * redirects to a new note with the clicked on object as the target
		 **/
		scheduleMeeting: function(itemClicked, metaData) {
			loc = 'index.php?module=Meetings&action=EditView';
			for(i in metaData) {
				if(i != 'module') loc += '&' + i + '=' + metaData[i];
			}
			document.location = loc;
		},
		/**
		 * redirects to a new note with the clicked on object as the target
		 **/
		scheduleCall: function(itemClicked, metaData) {
			loc = 'index.php?module=Calls&action=EditView';
			for(i in metaData) {
				if(i != 'module') loc += '&' + i + '=' + metaData[i];
			}
			document.location = loc;
		},
		/**
		 * redirects to a new contact with the clicked on object as the target
		 **/
		createContact: function(itemClicked, metaData) {
			loc = 'index.php?module=Contacts&action=EditView';
			for(i in metaData) {
				if(i != 'module') loc += '&' + i + '=' + metaData[i];
			}
			document.location = loc;
		},
		/**
		 * redirects to a new task with the clicked on object as the target
		 **/
		createTask: function(itemClicked, metaData) {
			loc = 'index.php?module=Tasks&action=EditView';
			for(i in metaData) {
				if(i != 'module') loc += '&' + i + '=' + metaData[i];
			}
			document.location = loc;
		},
		/**
		 * redirects to a new opportunity with the clicked on object as the target
		 **/
		createOpportunity: function(itemClicked, metaData) {
			loc = 'index.php?module=Opportunities&action=EditView';
			for(i in metaData) {
				if(i != 'module') loc += '&' + i + '=' + metaData[i];
			}
			document.location = loc;
		},
		/**
		 * redirects to a new opportunity with the clicked on object as the target
		 **/
		createCase: function(itemClicked, metaData) {
			loc = 'index.php?module=Cases&action=EditView';
			for(i in metaData) {
				if(i != 'module') loc += '&' + i + '=' + metaData[i];
			}
			document.location = loc;
		},
		/**
		 * handles add to favorites menu selection
		 **/
		addToFavorites: function(itemClicked, metaData) {
			success = function(data) {
			}
			var cObj = YAHOO.util.Connect.asyncRequest('GET', 'index.php?to_pdf=true&module=Home&action=AddToFavorites&target_id=' + metaData['id'] + '&target_module=' + metaData['module'], {success: success, failure: success});

		}
	};
}();
//if(typeof YAHOO != 'undefined') YAHOO.util.Event.addListener(window, 'load', SUGAR.contextMenu.init);

// initially from popup_parent_helper.js
var popup_request_data;
var close_popup;

function get_popup_request_data()
{
	return window.document.popup_request_data;
}

function get_close_popup()
{
	return window.document.close_popup;
}

function open_popup(module_name, width, height, initial_filter, close_popup, hide_clear_button, popup_request_data, popup_mode, create, metadata)
{
	if (typeof(popupCount) == "undefined" || popupCount == 0)
	   popupCount = 1;

	// set the variables that the popup will pull from
	window.document.popup_request_data = popup_request_data;
	window.document.close_popup = close_popup;

	// launch the popup
	URL = 'index.php?'
		+ 'module=' + module_name
		+ '&action=Popup';

	if (initial_filter != '') {
		URL += '&query=true' + initial_filter;
		// Bug 41891 - Popup Window Name
		popupName = initial_filter.replace(/[^a-z_\-0-9]+/ig, '_');
		windowName = module_name + '_popup_window' + popupName;
	} else {
		windowName = module_name + '_popup_window' + popupCount;
	}
	popupCount++;

	if (hide_clear_button) {
		URL += '&hide_clear_button=true';
	}

	windowFeatures = 'width=' + width
		+ ',height=' + height
		+ ',resizable=1,scrollbars=1';

	if (popup_mode == '' && popup_mode == 'undefined') {
		popup_mode='single';
	}
	URL+='&mode='+popup_mode;
	if (create == '' && create == 'undefined') {
		create = 'false';
	}
	URL+='&create='+create;

	if (metadata != '' && metadata != 'undefined') {
		URL+='&metadata='+metadata;
	}

	win = SUGAR.util.openWindow(URL, windowName, windowFeatures);

	if(window.focus)
	{
		// put the focus on the popup if the browser supports the focus() method
		win.focus();
	}

	win.popupCount = popupCount;

	return win;
}

/**
 * The reply data must be a JSON array structured with the following information:
 *  1) form name to populate
 *  2) associative array of input names to values for populating the form
 */
var from_popup_return  = false;

function set_return_basic(popup_reply_data,filter)
{
	var form_name = popup_reply_data.form_name;
	var name_to_value_array = popup_reply_data.name_to_value_array;
	for (var the_key in name_to_value_array)
	{
		if(the_key == 'toJSON')
		{
			/* just ignore */
		}
		else if(the_key.match(filter))
		{
			var displayValue=name_to_value_array[the_key].replace(/&amp;/gi,'&').replace(/&lt;/gi,'<').replace(/&gt;/gi,'>').replace(/&#039;/gi,'\'').replace(/&quot;/gi,'"');;
			// begin andopes change: support for enum fields (SELECT)
			if(window.document.forms[form_name] && window.document.forms[form_name].elements[the_key]) {
				if(window.document.forms[form_name].elements[the_key].tagName == 'SELECT') {
					var selectField = window.document.forms[form_name].elements[the_key];
					for(var i = 0; i < selectField.options.length; i++) {
						if(selectField.options[i].text == displayValue) {
							selectField.options[i].selected = true;
							break;
						}
					}
				} else {
					window.document.forms[form_name].elements[the_key].value = displayValue;
				}
			}
			// end andopes change: support for enum fields (SELECT)
		}
	}
}

function set_return(popup_reply_data)
{
	from_popup_return = true;
	var form_name = popup_reply_data.form_name;
	var name_to_value_array = popup_reply_data.name_to_value_array;
	if(typeof name_to_value_array != 'undefined' && name_to_value_array['account_id'])
	{
		var label_str = '';
		var label_data_str = '';
		var current_label_data_str = '';
		for (var the_key in name_to_value_array)
		{
			if(the_key == 'toJSON')
			{
				/* just ignore */
			}
			else
			{
				var displayValue=name_to_value_array[the_key].replace(/&amp;/gi,'&').replace(/&lt;/gi,'<').replace(/&gt;/gi,'>').replace(/&#039;/gi,'\'').replace(/&quot;/gi,'"');
				if(window.document.forms[form_name] && document.getElementById(the_key+'_label') && !the_key.match(/account/)) {
					var data_label = document.getElementById(the_key+'_label').innerHTML.replace(/\n/gi,'');
					label_str += data_label + ' \n';
					label_data_str += data_label  + ' ' + displayValue + '\n';
					if(window.document.forms[form_name].elements[the_key]) {
						current_label_data_str += data_label + ' ' + window.document.forms[form_name].elements[the_key].value +'\n';
					}
				}
			}
		}
        if(label_data_str != label_str && current_label_data_str != label_str){
        	if(confirm(SUGAR.language.get('app_strings', 'NTC_OVERWRITE_ADDRESS_PHONE_CONFIRM') + '\n\n' + label_data_str))
			{
				set_return_basic(popup_reply_data,/\S/);
			}else{
				set_return_basic(popup_reply_data,/account/);
			}
		}else if(label_data_str != label_str && current_label_data_str == label_str){
			set_return_basic(popup_reply_data,/\S/);
		}else if(label_data_str == label_str){
			set_return_basic(popup_reply_data,/account/);
		}
	}else{
		set_return_basic(popup_reply_data,/\S/);
	}
}

function set_return_and_save(popup_reply_data)
{
	var form_name = popup_reply_data.form_name;
	var name_to_value_array = popup_reply_data.name_to_value_array;

	for (var the_key in name_to_value_array)
	{
		if(the_key == 'toJSON')
		{
			/* just ignore */
		}
		else
		{
			window.document.forms[form_name].elements[the_key].value = name_to_value_array[the_key];
		}
	}

	window.document.forms[form_name].return_module.value = window.document.forms[form_name].module.value;
	window.document.forms[form_name].return_action.value = 'DetailView';
	window.document.forms[form_name].return_id.value = window.document.forms[form_name].record.value;
	window.document.forms[form_name].action.value = 'Save';
	window.document.forms[form_name].submit();
}

/**
 * This is a helper function to construct the initial filter that can be
 * passed into the open_popup() function.  It assumes that there is an
 * account_id and account_name field in the given form_name to use to
 * construct the intial filter string.
 */
function get_initial_filter_by_account(form_name)
{
	var account_id = window.document.forms[form_name].account_id.value;
	var account_name = escape(window.document.forms[form_name].account_name.value);
	var initial_filter = "&account_id=" + account_id + "&account_name=" + account_name;

	return initial_filter;
}
// end code from popup_parent_helper.js

// begin code for address copy
/**
 * This is a function used by the Address widget that will fill
 * in the given array fields using the fromKey and toKey as a
 * prefix into the form objects HTML elements.
 *
 * @param form The HTML form object to parse
 * @param fromKey The prefix of elements to copy from
 * @param toKey The prefix of elements to copy into
 * @return boolean true if successful, false otherwise
 */
function copyAddress(form, fromKey, toKey) {

    var elems = new Array("address_street", "address_city", "address_state", "address_postalcode", "address_country");
    var checkbox = document.getElementById(toKey + "_checkbox");

    if(typeof checkbox != "undefined") {
        if(!checkbox.checked) {
		    for(x in elems) {
		        t = toKey + "_" + elems[x];
			    document.getElementById(t).removeAttribute('readonly');
		    }
        } else {
		    for(x in elems) {
			    f = fromKey + "_" + elems[x];
			    t = toKey + "_" + elems[x];

			    document.getElementById(t).value = document.getElementById(f).value;
			    document.getElementById(t).setAttribute('readonly', true);
		    }
	    }
    }
  	return true;
}
// end code for address copy

/**
 * This function is used in Email Template Module.
 * It will check whether the template is used in Campaing->EmailMarketing.
 * If true, it will notify user.
 */

function check_deletable_EmailTemplate() {
	id = document.getElementsByName('record')[0].value;
	currentForm = document.getElementById('form');
	var call_back = {
		success:function(r) {
			if(r.responseText == 'true') {
				if(!confirm(SUGAR.language.get('app_strings','NTC_TEMPLATE_IS_USED'))) {
					return false;
				}
			} else {
				if(!confirm(SUGAR.language.get('app_strings','NTC_DELETE_CONFIRMATION'))) {
					return false;
				}
			}
			currentForm.return_module.value='EmailTemplates';
			currentForm.return_action.value='ListView';
			currentForm.action.value='Delete';
			currentForm.submit();
		}
		};
	url = "index.php?module=EmailTemplates&action=CheckDeletable&from=DetailView&to_pdf=1&record="+id;
	YAHOO.util.Connect.asyncRequest('POST',url, call_back,null);
}

SUGAR.image = {
     remove_upload_imagefile : function(field_name) {
            var field=document.getElementById('remove_imagefile_' + field_name);
            field.value=1;

            //enable the file upload button.
            var field=document.getElementById( field_name);
            field.style.display="";

            //hide the image and remove button.
            var field=document.getElementById('img_' + field_name);
            field.style.display="none";
            var field=document.getElementById('bt_remove_' + field_name);
            field.style.display="none";

            if(document.getElementById(field_name + '_duplicate')) {
               var field = document.getElementById(field_name + '_duplicate');
               field.value = "";
            }
    },

    confirm_imagefile : function(field_name) {
            var field=document.getElementById(field_name);
            var filename=field.value;
            var fileExtension = filename.substring(filename.lastIndexOf(".")+1);
            fileExtension = fileExtension.toLowerCase();
            if (fileExtension == "jpg" || fileExtension == "jpeg"
                || fileExtension == "gif" || fileExtension == "png" || fileExtension == "bmp"){
                    //image file
                }
            else{
                field.value=null;
                alert(SUGAR.language.get('app_strings', 'LBL_UPLOAD_IMAGE_FILE_INVALID'));
            }
    },

    lightbox : function(image)
	{
        if (typeof(SUGAR.image.lighboxWindow) == "undefined")
			SUGAR.image.lighboxWindow = new YAHOO.widget.SimpleDialog('sugarImageViewer', {
	            type:'message',
	            modal:true,
	            id:'sugarMsgWindow',
	            close:true,
	            title:"Alert",
	            msg: "<img src='" + image + "'> </img>",
	            buttons: [ ]
	        });
		SUGAR.image.lighboxWindow.setBody("<img src='" + image + "'> </img>");
		SUGAR.image.lighboxWindow.render(document.body);
        SUGAR.image.lighboxWindow.show();
		SUGAR.image.lighboxWindow.center()
    }
}

SUGAR.util.isTouchScreen = function()
{
    // first check if we have forced use of the touch enhanced interface
    if ( Get_Cookie("touchscreen") == '1' ) {
        return true;
    }

    // next check if we should use the touch interface with our device
    if ( (navigator.userAgent.match(/iPad/i) != null) ) {
        return true;
    }

    return false;
}

SUGAR.util.isLoginPage = function(content)
{
	//skip if this is packageManager screen
	if(SUGAR.util.isPackageManager()) {return false;}
	var loginPageStart = "<!DOCTYPE";
	if (content.substr(0, loginPageStart.length) == loginPageStart && content.indexOf("<html>") != -1  && content.indexOf("login_module") != -1) {
		window.location.href = window.location.protocol + window.location.pathname;
		return true;
	}
}

SUGAR.util.isPackageManager=function(){
	if(typeof(document.the_form) !='undefined' && typeof(document.the_form.language_pack_escaped) !='undefined'){
		return true;
	}else{return false;}
}

SUGAR.util.ajaxCallInProgress = function(){
	return SUGAR_callsInProgress != 0;
}

SUGAR.util.closeActivityPanel = {
    show:function(module,id,new_status,viewType,parentContainerId){
        if (SUGAR.util.closeActivityPanel.panel)
			SUGAR.util.closeActivityPanel.panel.destroy();
	    var singleModule = SUGAR.language.get("app_list_strings", "moduleListSingular")[module];
	    singleModule = typeof(singleModule != 'undefined') ? singleModule.toLowerCase() : '';
	    var closeText =  SUGAR.language.get("app_strings", "LBL_CLOSE_ACTIVITY_CONFIRM").replace("#module#",singleModule);
        SUGAR.util.closeActivityPanel.panel =
	    new YAHOO.widget.SimpleDialog("closeActivityDialog",
	             { width: "300px",
	               fixedcenter: true,
	               visible: false,
	               draggable: false,
	               close: true,
	               text: closeText,
	               constraintoviewport: true,
	               buttons: [ { text:SUGAR.language.get("app_strings", "LBL_EMAIL_OK"), handler:function(){
	                   if (SUGAR.util.closeActivityPanel.panel)
                            SUGAR.util.closeActivityPanel.panel.hide();

                        ajaxStatus.showStatus(SUGAR.language.get('app_strings', 'LBL_SAVING'));
                        var args = "action=save&id=" + id + "&record=" + id + "&status=" + new_status + "&module=" + module;
                        // 20110307 Frank Steegmans: Fix for bug 42361, Any field with a default configured in any activity will be set to this default when closed using the close dialog
                        // TODO: Take id out and regression test. Left id in for now to not create any other unexpected problems
                        //var args = "action=save&id=" + id + "&status=" + new_status + "&module=" + module;
                        var callback = {
                            success:function(o)
                            {	//refresh window to show updated changes
								window.location.reload(true);
								/*
                                if(viewType == 'dashlet')
                                {
                                    SUGAR.mySugar.retrieveDashlet(o.argument['parentContainerId']);
                                    ajaxStatus.hideStatus();
                                }
                                else if(viewType == 'subpanel'){
                                    showSubPanel(o.argument['parentContainerId'],null,true);
									if(o.argument['parentContainerId'] == 'activities'){
										showSubPanel('history',null,true);
									}
									ajaxStatus.hideStatus();

                                }else if(viewType == 'listview'){
                                    document.location = 'index.php?module=' + module +'&action=index';
									}
								*/
                            },
                            argument:{'parentContainerId':parentContainerId}
                        };

                        YAHOO.util.Connect.asyncRequest('POST', 'index.php', callback, args);

	               }, isDefault:true },
	                          { text:SUGAR.language.get("app_strings", "LBL_EMAIL_CANCEL"),  handler:function(){SUGAR.util.closeActivityPanel.panel.hide(); }} ]
	             } );

	    SUGAR.util.closeActivityPanel.panel.setHeader(SUGAR.language.get("app_strings", "LBL_CLOSE_ACTIVITY_HEADER"));
        SUGAR.util.closeActivityPanel.panel.render(document.body);
        SUGAR.util.closeActivityPanel.panel.show();
    }
}

SUGAR.util.setEmailPasswordDisplay = function(id, exists) {
	link = document.getElementById(id+'_link');
	pwd = document.getElementById(id);
	if(!pwd || !link) return;
	if(exists) {
    	pwd.style.display = 'none';
    	link.style.display = '';
	} else {
    	pwd.style.display = '';
    	link.style.display = 'none';
	}
}

SUGAR.util.setEmailPasswordEdit = function(id) {
	link = document.getElementById(id+'_link');
	pwd = document.getElementById(id);
	if(!pwd || !link) return;
	pwd.style.display = '';
	link.style.display = 'none';
}
// End of File include/javascript/sugar_3.js
                                
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

 // $Id: cookie.js 36440 2008-06-09 17:12:23Z dwheeler $
 
function Get_Cookie(name) {
  var start = document.cookie.indexOf(name + '=');
  var len = start + name.length + 1;
  if ((!start) && (name != document.cookie.substring(0,name.length)))
    return null;
  if (start == -1)
    return null;
  var end = document.cookie.indexOf(';',len);
  if (end == -1) end = document.cookie.length;
  if(end == start){
  	return '';
  }
  return unescape(document.cookie.substring(len,end));
}

function Set_Cookie( name, value, expires, path, domain, secure ) 
{
// set time, it's in milliseconds
var today = new Date();
today.setTime( today.getTime() );

/*
if the expires variable is set, make the correct 
expires time, the current script below will set 
it for x number of days, to make it for hours, 
delete * 24, for minutes, delete * 60 * 24
*/
if ( expires )
{
expires = expires * 1000 * 60 * 60 * 24;
}
var expires_date = new Date( today.getTime() + (expires) );

document.cookie = name + "=" +escape( value ) +
( ( expires ) ? ";expires=" + expires_date.toGMTString() : "" ) + 
( ( path ) ? ";path=" + path : "" ) + 
( ( domain ) ? ";domain=" + domain : "" ) +
( ( secure ) ? ";secure" : "" );
}

function Delete_Cookie(name,path,domain) {
  if (Get_Cookie(name))
    document.cookie =
      name + '=' +
      ( (path) ? ';path=' + path : '') +
      ( (domain) ? ';domain=' + domain : '') +
      ';expires=Thu, 01-Jan-1970 00:00:01 GMT';
}

/*
returns an array of cookie values from a single cookie
*/
function get_sub_cookies(cookie){
	var cookies = new Array();
	var end ='';
	if(cookie && cookie != ''){
		end = cookie.indexOf('#')
		while(end > -1){
			var cur = cookie.substring(0, end);
			 cookie = cookie.substring(end + 1, cookie.length);
			var name = cur.substring(0, cur.indexOf('='));
			var value = cur.substring(cur.indexOf('=') + 1, cur.length);
			cookies[name] = value;
			
			end = cookie.indexOf('#')
		}
	}
	return cookies;
}

function subs_to_cookie(cookies){

	
	var cookie = '';
		for (var i in cookies)
		{
			if (typeof(cookies[i]) != "function") {
				cookie += i  + '=' + cookies[i] + '#';
			}
		}
	return cookie;
}

// End of File include/javascript/cookie.js
                                
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


var menuStack = new Array();
var hiddenElmStack = new Array();
var currentMenu = null;
var closeMenusDelay = null;
var openMenusDelay = null;
var delayTime = 75;					// ms for menu open delay


function eraseTimeout(tId) {
	//if (tId != null)
	window.clearTimeout(tId);
	return null;
}

function tbButtonMouseOverOrig(id){
	closeMenusDelay = eraseTimeout(closeMenusDelay);
	var menuName = id.replace(/Handle/i,'Menu');
	var menu = getLayer(menuName);
	//if (menu) menu.className = 'tbButtonMouseOverUp';
	if (currentMenu){
		closeAllMenus();
	}
	popupMenu(id, menu);
}
function tbButtonMouseOver(id,top,left,leftOffset){
	closeMenusDelay = eraseTimeout(closeMenusDelay);
	if (openMenusDelay == null){
		openMenusDelay = window.setTimeout("showMenu('"+id+"','"+top+"','"+left+"','"+leftOffset+"')", delayTime);
	}
}
function showMenu(id,top,left,leftOffset){
	openMenusDelay = eraseTimeout(openMenusDelay);
	var menuName = id.replace(/Handle/i,'Menu');
	var menu = getLayer(menuName);
	//if (menu) menu.className = 'tbButtonMouseOverUp';
	if (currentMenu){
		closeAllMenus();
	}
	popupMenu(id, menu, top,left,leftOffset);
}

function showSubMenu(id){
	closeMenusDelay = eraseTimeout(closeMenusDelay);
	var menuName = id.replace(/Handle/i,'Menu');
	var menu = getLayer(menuName);
//	if (currentMenu){
//		closeMenus();
//	}
//	popupMenu(id, menu);
	popupSubMenu(id, menu);
}

function popupMenu(handleID, menu, top, left, leftOffset){
	var bw = checkBrowserWidth();
	var menuName = handleID.replace(/Handle/i,'Menu');
	var menuWidth = 120;
	var imgWidth = document.getElementById(handleID).width;
	if (menu){
		var menuHandle = getLayer(handleID);
		var p=menuHandle;
		if (left == "") {
		var left = 0;
		while(p&&p.tagName.toUpperCase()!='BODY'){
			left+=p.offsetLeft;
			p=p.offsetParent;
		}
		left+=parseInt(leftOffset);
			
		}
		if (left+menuWidth>bw) {
			left = left-menuWidth+imgWidth;
		}
		setMenuVisible(menu, left, top, false);
	}
}


function popupSubMenu(handleID, menu){
	if (menu){
		var menuHandle = getLayer(handleID);
		var p=menuHandle;
		//var top = p.offsetHeight, left = 0;
		var top = 0, left = p.offsetWidth;
		while(p&&p.tagName.toUpperCase()!='BODY'){
			top+=p.offsetTop;
			left+=p.offsetLeft;
			p=p.offsetParent;
		}
		if (is.ie && is.mac){
			top -= 3;
			left -= 10;
		}
/*
		if (menu.isSubMenu){
			try{
				if (blnNetscape6){
					left+=(getLayer(menu.parentid).offsetWidth - 4);
				}else{
					left+=(getLayer(menu.parentid).clientWidth - 8);
				}
			}catch(e){
			}
		}else{
			top += menuItem.offsetHeight;
		}
*/
		//menu.x = left;
		//menu.y = top;
		setMenuVisible(menu, left, top, true);
		//fixWidth(paneID, menu);
	}
}

function closeMenusOrig(){
	if (currentMenu){
		setMenuVisibility(currentMenu, false);
//		currentMenu = null;
	}
}

function closeSubMenus(handle){
	closeMenusDelay = eraseTimeout(closeMenusDelay);
	if (menuStack.length > 0){
		for (var i = menuStack.length-1; i >=0; i--){
			var menu = menuStack[menuStack.length-1];
			if (menu.id == handle.getAttribute('parentid')){
				currentMenu = menu;
				break;
			}else{
				closeMenu(menu);
				//menuStack.pop();
				menuPop();
			}
		}
	}
}

function closeMenu(menu){
	setMenuVisibility(menu, false);
}

function closeMenusOrig(){
	if (menuStack.length > 0){
		for (var i = menuStack.length-1; i >=0; i--){
			//var menu = menuStack.pop();
			var menu = menuPop();
			closeMenu(menu);
		}
	}
	currentMenu = null;
}

function closeMenus(){
	if (closeMenusDelay == null){
		closeMenusDelay = window.setTimeout("closeAllMenus()", delayTime);
	}
}

function closeAllMenus(){
	closeMenusDelay = eraseTimeout(closeMenusDelay);
	if (menuStack.length > 0){
		for (var i = menuStack.length-1; i >=0; i--){
			//var menu = menuStack.pop();
			var menu = menuPop();
			closeMenu(menu);
		}
	}
	currentMenu = null;
}

function setMenuVisible(menu, x, y, isSubMenu){
/*
   var id = menu.id;
   var left=0;
   var top=0;
   var menuItem = menu.getMenuItemElm();

   if (menuItem && menu){
      if (menu.isTopMenu){
         menuItem.className = 'tbButtonMouseDown';
      }
   }
*/
	if (menu){
		//menu.x = left;
		//menu.y = top;
		if (isSubMenu){
			if (menu.getAttribute('parentid') == currentMenu.getAttribute('parentid')){
				//menuStack.pop();
				menuPop();
				setMenuVisibility(currentMenu, false);
			}
		}else{
			//menuStack.pop();
			menuPop();
			setMenuVisibility(currentMenu, false);
		}
		currentMenu = menu;
		//menuStack.push(menu);
		menuPush(menu);
		setMenuVisibility(menu, true, x, y);
	}
}

function getLayer(layerid){
/*
	if (document.layers && layerid){
		if (document.layers[layerid]) return document.layers[layerid];
	}
	if (document.links && layerid){
		if (document.links[layerid]) return document.links[layerid];
	}
	if (document.all && layerid){
		if (document.all(layerid)) return document.all(layerid);
	}
*/
	return document.getElementById(layerid);
}

function setMenuVisibility(menu, on, x, y){
	var parent = menu;
	if (menu){
/*
		menu.visible = on;
		setLayer(menu.id, !menu.visible, menu.x, menu.y);
		setLayer(menu.id, !menu.visible, 0, 0);
		menu.visible = on;
*/
		setLayer(menu.id, !on, x, y);
		if (is.ie){
			if (!on){
				if (!menu.getAttribute('parentid')){
					showElement("SELECT");
				}
			}else{
				hideElement("SELECT", x, y, menu.offsetWidth, menu.offsetHeight);
			}
		}
/*
		setLayer(menu.id, !menu.visible, 0, 0);
		var menuWidth, menuHeight;
		var menuLayer = getLayer(menu.id);
		if (menuLayer){
			if (blnIE55){
				menuWidth = menuLayer.clientWidth;
				menuHeight = menuLayer.clientHeight;
			}else{
				menuWidth = menuLayer.offsetWidth;
				menuHeight = menuLayer.offsetHeight;
			}
			if (menu.x+menuWidth > clientWindowWidth){
				menu.x = clientWindowWidth - menuWidth - 25;
				if (menu.x < 10){
					menu.x = 10;
				}
			}
			if (menu.y+menuHeight > clientWindowHeight){
				menu.y = clientWindowHeight - menuHeight - 25;
				if (menu.y < 10){
					menu.y = 10;
				}
			}
			setLayer(menu.id, !menu.visible, menu.x, menu.y);
		}
*/
	}
/*
	var parentid = menu.parentid;
	while (parentid){
		parent = getMenu(menu.paneID, parentid);
		if (parent){
			parent.visible = on;
			setLayer(parent.id, !parent.visible, parent.x, parent.y);
			parentid = parent.parentid;
			if (on == false) currentMenu = parent;
		}else{
			parentid = null;
		}
	}
	}
	return parent;
*/
}

function menuPop(){
	if (is.ie && (is.mac || !is.ie5_5up)){
		var menu = menuStack[menuStack.length-1];
		var newMenuStack = new Array();
		for (var i = 0; i < menuStack.length-1; i++){
			newMenuStack[newMenuStack.length] = menuStack[i];
		}
		menuStack = newMenuStack;
		return menu;
	}else{
		return menuStack.pop();
	}
}

function menuPush(menu){
	if (is.ie && (is.mac || !is.ie5_5up)){
		menuStack[menuStack.length] = menu;
	}else{
		menuStack.push(menu);
	}
}

function checkBrowserWidth(){
	var	windowWidth;
	if (is.ie){
		windowWidth = document.body.clientWidth;
	}else{
		// 17px for scrollbar width
		windowWidth = window.innerWidth - 16;
	}
	if (windowWidth >= 1000){
		showSB('sbContent',true,'sb');
	}else{
		showSB('sbContent',false,'sb');
	}
	return windowWidth;
}

function showSB(id, hideit, imgIdPrefix){
	setLayer(id, !hideit, -1, -1);
	setLayer(imgIdPrefix+'On', !hideit, -1, -1);
	setLayer(imgIdPrefix+'Off', hideit, -1, -1);
}

function setLayer(id, hidden, x, y){
	var layer = getLayer(id);
	setLayerElm(layer, hidden, x, y);
}

function setLayerElm(layer, hideit, x, y){
	if (layer && layer.style){
		if (hideit){
			layer.style.visibility='hidden';
			//layer.style.display='none';
		}else{
			layer.style.display='block';
			layer.style.visibility='visible';
		}
		if (x >=0 && x != ""){
            //alert(layer.id+': '+x+', '+y+'\n'+layer.offsetLeft+', '+layer.offsetTop);
			//layer.style.left=x;
			//layer.style.top=y;
			layer.style.left = x+'px';
		}
		if (y >= 0 && y != "") {
			layer.style.top = y+'px';
		}
	}
}

function hiliteItem(menuItem,changeClass){
	closeMenusDelay = eraseTimeout(closeMenusDelay);
	if (changeClass=='yes') {
		if (menuItem.getAttribute('avid') == 'false'){
			menuItem.className = 'menuItemHiliteX';
		}else{
			menuItem.className = 'menuItemHilite';
		}
	}
}
function unhiliteItem(menuItem){
	closeMenusDelay = eraseTimeout(closeMenusDelay);
	if (menuItem.getAttribute('avid') == 'false'){
		menuItem.className = 'menuItemX';
	}else{
		menuItem.className = 'menuItem';
	}
}

function showElement(elmID){
	for (i = 0; i < document.getElementsByTagName(elmID).length; i++)	{
		obj = document.getElementsByTagName(elmID)[i];
		if (! obj || ! obj.offsetParent)
			continue;
		obj.style.visibility = "";
	}
}
function showElementNew(elmID){
	if (hiddenElmStack.length > 0){
		for (var i = hiddenElmStack.length-1; i >=0; i--){
			var obj = hiddenElmStack[hiddenElmStack.length-1];
			obj.style.visibility = "";;
			hiddenElmStack.pop();
		}
	}
}

function hideElement(elmID,x,y,w,h){
	for (i = 0; i < document.getElementsByTagName(elmID).length; i++){
		obj = document.getElementsByTagName(elmID)[i];
		if (! obj || ! obj.offsetParent)
			continue;

		// Find the element's offsetTop and offsetLeft relative to the BODY tag.
		objLeft   = obj.offsetLeft;
		objTop    = obj.offsetTop;
		objParent = obj.offsetParent;
		while (objParent.tagName.toUpperCase() != "BODY"){
			objLeft  += objParent.offsetLeft;
			objTop   += objParent.offsetTop;
			if(objParent.offsetParent == null)
				break;
			else
				objParent = objParent.offsetParent;
		}
		// Adjust the element's offsetTop relative to the dropdown menu
		objTop = objTop - y;

		if (x > (objLeft + obj.offsetWidth) || objLeft > (x + w))
			;
		else if (objTop > h)
			;
		else if ((y + h) <= 80)
			;
		else {
			obj.style.visibility = "hidden";
			//hiddenElmStack.push(obj);
		}
	}
}

function Is (){
    // convert all characters to lowercase to simplify testing
    var agt = navigator.userAgent.toLowerCase();

    // *** BROWSER VERSION ***
    // Note: On IE5, these return 4, so use is.ie5up to detect IE5.
    this.major = parseInt(navigator.appVersion);
    this.minor = parseFloat(navigator.appVersion);

    // Note: Opera and WebTV spoof Navigator.  We do strict client detection.
    // If you want to allow spoofing, take out the tests for opera and webtv.
    this.nav  = ((agt.indexOf('mozilla')!=-1) && (agt.indexOf('spoofer')==-1)
                && (agt.indexOf('compatible') == -1) && (agt.indexOf('opera')==-1)
                && (agt.indexOf('webtv')==-1) && (agt.indexOf('hotjava')==-1));
    this.nav2 = (this.nav && (this.major == 2));
    this.nav3 = (this.nav && (this.major == 3));
    this.nav4 = (this.nav && (this.major == 4));
    this.nav4up = (this.nav && (this.major >= 4));
    this.navonly      = (this.nav && ((agt.indexOf(";nav") != -1) ||
                          (agt.indexOf("; nav") != -1)) );
    this.nav6 = (this.nav && (this.major == 5));
    this.nav6up = (this.nav && (this.major >= 5));
    this.gecko = (agt.indexOf('gecko') != -1);

    this.nav7 = (this.gecko && (this.major >= 5) && (agt.indexOf('netscape/7')!=-1));
    this.moz1 = false;
    this.moz1up = false;
    this.moz1_1 = false;
    this.moz1_1up = false;
    if (this.nav6up){
//    if (this.nav){
       myRegEx = new RegExp("rv:\\d*.\\d*.\\d*");
       //myFind = myRegEx.exec("; rv:9.10.5)");
       myFind = myRegEx.exec(agt);
	   if(myFind!=null){
         var strVersion = myFind.toString();
         strVersion = strVersion.replace(/rv:/,'');
         var arrVersion = strVersion.split('.');
         var major = parseInt(arrVersion[0]);
         var minor = parseInt(arrVersion[1]);
         if (arrVersion[2]) var revision = parseInt(arrVersion[2]);
         this.moz1 = ((major == 1) && (minor == 0));
         this.moz1up = ((major == 1) && (minor >= 0));
         this.moz1_1 = ((major == 1) && (minor == 1));
         this.moz1_1up = ((major == 1) && (minor >= 1));
	  }
    }

    this.ie     = ((agt.indexOf("msie") != -1) && (agt.indexOf("opera") == -1));
    this.ie3    = (this.ie && (this.major < 4));
    this.ie4    = (this.ie && (this.major == 4) && (agt.indexOf("msie 4")!=-1) );
    this.ie4up  = (this.ie  && (this.major >= 4));
    this.ie5    = (this.ie && (this.major == 4) && (agt.indexOf("msie 5.0")!=-1) );
    this.ie5_5  = (this.ie && (this.major == 4) && (agt.indexOf("msie 5.5") !=-1));
    this.ie5up  = (this.ie  && !this.ie3 && !this.ie4);

    this.ie5_5up =(this.ie && !this.ie3 && !this.ie4 && !this.ie5);
    this.ie6    = (this.ie && (this.major == 4) && (agt.indexOf("msie 6.")!=-1) );
    this.ie6up  = (this.ie  && !this.ie3 && !this.ie4 && !this.ie5 && !this.ie5_5);

	this.mac    = (agt.indexOf("mac") != -1);
}

function runPageLoadItems (){
	var myVar;
	checkBrowserWidth();
}
var is = new Is();

if (is.ie) {
	document.write('<style type="text/css">');
	document.write('body {font-size: x-small;}');
	document.write ('</style>');
}

// End of File include/javascript/menu.js
                                
/*

Modification information for LGPL compliance

r56990 - 2010-06-16 13:05:36 -0700 (Wed, 16 Jun 2010) - kjing - snapshot "Mango" svn branch to a new one for GitHub sync

r56989 - 2010-06-16 13:01:33 -0700 (Wed, 16 Jun 2010) - kjing - defunt "Mango" svn dev branch before github cutover

r56972 - 2010-06-15 19:49:32 -0700 (Tue, 15 Jun 2010) - sadek - Bug 37984: Workaround for a Google chrome bug. Still works in other browsers, and now in chrome too. See http://code.google.com/p/chromium/issues/detail?id=38641

r55980 - 2010-04-19 13:31:28 -0700 (Mon, 19 Apr 2010) - kjing - create Mango (6.1) based on windex

r51719 - 2009-10-22 10:18:00 -0700 (Thu, 22 Oct 2009) - mitani - Converted to Build 3  tags and updated the build system 

r51634 - 2009-10-19 13:32:22 -0700 (Mon, 19 Oct 2009) - mitani - Windex is the branch for Sugar Sales 1.0 development

r50375 - 2009-08-24 18:07:43 -0700 (Mon, 24 Aug 2009) - dwong - branch kobe2 from tokyo r50372

r47729 - 2009-05-29 14:12:28 -0700 (Fri, 29 May 2009) - dwheeler - 30560: Fixed a bug in calendar.js where it referenced the depreciated IE variable document.body.scrollTop which has been replaced by document.documentElement.scrollTop in IE7.

r42807 - 2008-12-29 11:16:59 -0800 (Mon, 29 Dec 2008) - dwong - Branch from trunk/sugarcrm r42806 to branches/tokyo/sugarcrm

r40171 - 2008-09-30 15:25:06 -0700 (Tue, 30 Sep 2008) - rob - Adds a shortcut to some old code that was working around a bug in very old browsers (NN4, IE5), left it alone for IE (so it would catch IE5)
FIXED: 25076, 25249, 25150

r26391 - 2007-09-04 18:50:23 -0700 (Tue, 04 Sep 2007) - dwheeler - Bug 14707:
Modified datetime field JSCalendar to be compatible with the YUI overlay panel.

r24533 - 2007-07-23 01:35:36 -0700 (Mon, 23 Jul 2007) - clee - Added fixes for IE javascript errors where calendar selection causes other form fields to be disabled/readonly.


*/

/*  Copyright Mihai Bazon, 2002, 2003  |  http://dynarch.com/mishoo/
 * ------------------------------------------------------------------
 *
 * The DHTML Calendar, version 0.9.6 "Keep cool but don't freeze"
 *
 * Details and latest version at:
 * http://dynarch.com/mishoo/calendar.epl
 *
 * This script is distributed under the GNU Lesser General Public License.
 * Read the entire license text here: http://www.gnu.org/licenses/lgpl.html
 */

// $Id$

/** The Calendar object constructor. */
Calendar = function (firstDayOfWeek, dateStr, onSelected, onClose, inputField) {

	// member variables
	this.activeDiv = null;
	this.currentDateEl = null;
	this.getDateStatus = null;
	this.timeout = null;
	this.onSelected = onSelected || null;
	this.onClose = onClose || null;
	this.dragging = false;
	this.hidden = false;
	this.minYear = 1970;
	this.maxYear = 2050;
	this.dateFormat = Calendar._TT["DEF_DATE_FORMAT"];
	this.ttDateFormat = Calendar._TT["TT_DATE_FORMAT"];
	this.isPopup = true;
	this.weekNumbers = true;
	this.firstDayOfWeek = firstDayOfWeek; // 0 for Sunday, 1 for Monday, etc.
	this.showsOtherMonths = false;
	this.dateStr = dateStr;
	this.ar_days = null;
	this.showsTime = false;
	this.time24 = true;
	this.yearStep = 2;
	// HTML elements
	this.table = null;
	this.element = null;
	this.tbody = null;
	this.firstdayname = null;
	// Combo boxes
	this.monthsCombo = null;
	this.yearsCombo = null;
	this.hilitedMonth = null;
	this.activeMonth = null;
	this.hilitedYear = null;
	this.activeYear = null;
	// Information
	this.dateClicked = false;
	this.inputField = inputField || null;

	// one-time initializations
	if (typeof Calendar._SDN == "undefined") {
		// table of short day names
		if (typeof Calendar._SDN_len == "undefined")
			Calendar._SDN_len = 3;
		var ar = new Array();
		for (var i = 8; i > 0;) {
			ar[--i] = Calendar._DN[i].substr(0, Calendar._SDN_len);
		}
		Calendar._SDN = ar;
		// table of short month names
		if (typeof Calendar._SMN_len == "undefined")
			Calendar._SMN_len = 3;
		ar = new Array();
		for (var i = 12; i > 0;) {
			ar[--i] = Calendar._MN[i].substr(0, Calendar._SMN_len);
		}
		Calendar._SMN = ar;
	}
};

// ** constants

/// "static", needed for event handlers.
Calendar._C = null;

if(typeof jscal_today != 'undefined') {;
	Calendar.dateToday = jscal_today;
}

/// detect a special case of "web browser"
Calendar.is_ie = ( /msie/i.test(navigator.userAgent) &&
		   !/opera/i.test(navigator.userAgent) );

Calendar.is_ie5 = ( Calendar.is_ie && /msie 5\.0/i.test(navigator.userAgent) );

/// detect Opera browser
Calendar.is_opera = /opera/i.test(navigator.userAgent);

/// detect KHTML-based browsers
Calendar.is_khtml = /Konqueror|Safari|KHTML/i.test(navigator.userAgent);

// BEGIN: UTILITY FUNCTIONS; beware that these might be moved into a separate
//        library, at some point.

Calendar.getAbsolutePos = function(el) {
	var SL = 0, ST = 0;
	var is_div = /^div$/i.test(el.tagName);
	if (is_div && el.scrollLeft)
		SL = el.scrollLeft;
	if (is_div && el.scrollTop)
		ST = el.scrollTop;
	var r = { x: el.offsetLeft - SL, y: el.offsetTop - ST };
	if (el.offsetParent) {
		var tmp = this.getAbsolutePos(el.offsetParent);
		r.x += tmp.x;
		r.y += tmp.y;
	}
	return r;
};

Calendar.isRelated = function (el, evt) {
	var related = evt.relatedTarget;
	if (!related) {
		var type = evt.type;
		if (type == "mouseover") {
			related = evt.fromElement;
		} else if (type == "mouseout") {
			related = evt.toElement;
		}
	}
	while (related) {
		if (related == el) {
			return true;
		}
		related = related.parentNode;
	}
	return false;
};

Calendar.removeClass = function(el, className) {
	if (!(el && el.className)) {
		return;
	}
	var cls = el.className.split(" ");
	var ar = new Array();
	for (var i = cls.length; i > 0;) {
		if (cls[--i] != className) {
			ar[ar.length] = cls[i];
		}
	}
	el.className = ar.join(" ");
};

Calendar.addClass = function(el, className) {
	Calendar.removeClass(el, className);
	el.className += " " + className;
};

Calendar.getElement = function(ev) {
	if (Calendar.is_ie) {
		return window.event.srcElement;
	} else {
		return ev.currentTarget;
	}
};

Calendar.getTargetElement = function(ev) {
	if (Calendar.is_ie) {
		return window.event.srcElement;
	} else {
		return ev.target;
	}
};

Calendar.stopEvent = function(ev) {
	ev || (ev = window.event);
	if (Calendar.is_ie) {
		ev.cancelBubble = true;
		ev.returnValue = false;
	} else {
		ev.preventDefault();
		ev.stopPropagation();
	}
	return false;
};

Calendar.addEvent = function(el, evname, func) {
	if (el.attachEvent) { // IE
		el.attachEvent("on" + evname, func);
	} else if (el.addEventListener) { // Gecko / W3C
		el.addEventListener(evname, func, true);
	} else {
		el["on" + evname] = func;
	}
};

Calendar.removeEvent = function(el, evname, func) {
	if (el.detachEvent) { // IE
		el.detachEvent("on" + evname, func);
	} else if (el.removeEventListener) { // Gecko / W3C
		el.removeEventListener(evname, func, true);
	} else {
		el["on" + evname] = null;
	}
};

Calendar.createElement = function(type, parent) {
	var el = null;
	if (document.createElementNS) {
		// use the XHTML namespace; IE won't normally get here unless
		// _they_ "fix" the DOM2 implementation.
		el = document.createElementNS("http://www.w3.org/1999/xhtml", type);
	} else {
		el = document.createElement(type);
	}
	if (typeof parent != "undefined") {
		parent.appendChild(el);
	}
	return el;
};

// END: UTILITY FUNCTIONS

// BEGIN: CALENDAR STATIC FUNCTIONS

/** Internal -- adds a set of events to make some element behave like a button. */
Calendar._add_evs = function(el) {
	with (Calendar) {
		addEvent(el, "mouseover", dayMouseOver);
		addEvent(el, "mousedown", dayMouseDown);
		addEvent(el, "mouseout", dayMouseOut);
		if (is_ie) {
			addEvent(el, "dblclick", dayMouseDblClick);
			el.setAttribute("unselectable", true);
		}
	}
};

Calendar.findMonth = function(el) {
	if (typeof el.month != "undefined") {
		return el;
	} else if (typeof el.parentNode.month != "undefined") {
		return el.parentNode;
	}
	return null;
};

Calendar.findYear = function(el) {
	if (typeof el.year != "undefined") {
		return el;
	} else if (typeof el.parentNode.year != "undefined") {
		return el.parentNode;
	}
	return null;
};

Calendar.showMonthsCombo = function () {
	var cal = Calendar._C;
	if (!cal) {
		return false;
	}
	var cal = cal;
	var cd = cal.activeDiv;
	var mc = cal.monthsCombo;
	if (cal.hilitedMonth) {
		Calendar.removeClass(cal.hilitedMonth, "hilite");
	}
	if (cal.activeMonth) {
		Calendar.removeClass(cal.activeMonth, "active");
	}
	var mon = cal.monthsCombo.getElementsByTagName("div")[cal.date.getMonth()];
	Calendar.addClass(mon, "active");
	cal.activeMonth = mon;
	var s = mc.style;
	s.display = "block";
	if (cd.navtype < 0)
		s.left = cd.offsetLeft + "px";
	else {
		var mcw = mc.offsetWidth;
		if (typeof mcw == "undefined")
			// Konqueror brain-dead techniques
			mcw = 50;
		s.left = (cd.offsetLeft + cd.offsetWidth - mcw) + "px";
	}
	s.top = (cd.offsetTop + cd.offsetHeight) + "px";
};

Calendar.showYearsCombo = function (fwd) {
	var cal = Calendar._C;
	if (!cal) {
		return false;
	}
	var cal = cal;
	var cd = cal.activeDiv;
	var yc = cal.yearsCombo;
	if (cal.hilitedYear) {
		Calendar.removeClass(cal.hilitedYear, "hilite");
	}
	if (cal.activeYear) {
		Calendar.removeClass(cal.activeYear, "active");
	}
	cal.activeYear = null;
	var Y = cal.date.getFullYear() + (fwd ? 1 : -1);
	var yr = yc.firstChild;
	var show = false;
	for (var i = 12; i > 0; --i) {
		if (Y >= cal.minYear && Y <= cal.maxYear) {
			yr.firstChild.data = Y;
			yr.year = Y;
			yr.style.display = "block";
			show = true;
		} else {
			yr.style.display = "none";
		}
		yr = yr.nextSibling;
		Y += fwd ? cal.yearStep : -cal.yearStep;
	}
	if (show) {
		var s = yc.style;
		s.display = "block";
		if (cd.navtype < 0)
			s.left = cd.offsetLeft + "px";
		else {
			var ycw = yc.offsetWidth;
			if (typeof ycw == "undefined")
				// Konqueror brain-dead techniques
				ycw = 50;
			s.left = (cd.offsetLeft + cd.offsetWidth - ycw) + "px";
		}
		s.top = (cd.offsetTop + cd.offsetHeight) + "px";
	}
};

// event handlers

Calendar.tableMouseUp = function(ev) {
	var cal = Calendar._C;
	if (!cal) {
		return false;
	}
	if (cal.timeout) {
		clearTimeout(cal.timeout);
	}
	var el = cal.activeDiv;
	if (!el) {
		return false;
	}
	var target = Calendar.getTargetElement(ev);
	ev || (ev = window.event);
	Calendar.removeClass(el, "active");
	if (target == el || target.parentNode == el) {
		Calendar.cellClick(el, ev);
	}
	var mon = Calendar.findMonth(target);
	var date = null;
	if (mon) {
		date = new Date(cal.date);
		if (mon.month != date.getMonth()) {
			date.setMonth(mon.month);
			cal.setDate(date);
			cal.dateClicked = false;
			cal.callHandler();
		}
	} else {
		var year = Calendar.findYear(target);
		if (year) {
			date = new Date(cal.date);
			if (year.year != date.getFullYear()) {
				date.setFullYear(year.year);
				cal.setDate(date);
				cal.dateClicked = false;
				cal.callHandler();
			}
		}
	}
	with (Calendar) {
		removeEvent(document, "mouseup", tableMouseUp);
		removeEvent(document, "mouseover", tableMouseOver);
		removeEvent(document, "mousemove", tableMouseOver);
		cal._hideCombos();
		_C = null;
		return stopEvent(ev);
	}
};

Calendar.tableMouseOver = function (ev) {
	var cal = Calendar._C;
	if (!cal) {
		return;
	}
	var el = cal.activeDiv;
	var target = Calendar.getTargetElement(ev);
	if (target == el || target.parentNode == el) {
		Calendar.addClass(el, "hilite active");
		Calendar.addClass(el.parentNode, "rowhilite");
	} else {
		if (typeof el.navtype == "undefined" || (el.navtype != 50 && (el.navtype == 0 || Math.abs(el.navtype) > 2)))
			Calendar.removeClass(el, "active");
		Calendar.removeClass(el, "hilite");
		Calendar.removeClass(el.parentNode, "rowhilite");
	}
	ev || (ev = window.event);
	if (el.navtype == 50 && target != el) {
		var pos = Calendar.getAbsolutePos(el);
		var w = el.offsetWidth;
		var x = ev.clientX;
		var dx;
		var decrease = true;
		if (x > pos.x + w) {
			dx = x - pos.x - w;
			decrease = false;
		} else
			dx = pos.x - x;

		if (dx < 0) dx = 0;
		var range = el._range;
		var current = el._current;
		var count = Math.floor(dx / 10) % range.length;
		for (var i = range.length; --i >= 0;)
			if (range[i] == current)
				break;
		while (count-- > 0)
			if (decrease) {
				if (--i < 0)
					i = range.length - 1;
			} else if ( ++i >= range.length )
				i = 0;
		var newval = range[i];
		el.firstChild.data = newval;

		cal.onUpdateTime();
	}
	var mon = Calendar.findMonth(target);
	if (mon) {
		if (mon.month != cal.date.getMonth()) {
			if (cal.hilitedMonth) {
				Calendar.removeClass(cal.hilitedMonth, "hilite");
			}
			Calendar.addClass(mon, "hilite");
			cal.hilitedMonth = mon;
		} else if (cal.hilitedMonth) {
			Calendar.removeClass(cal.hilitedMonth, "hilite");
		}
	} else {
		if (cal.hilitedMonth) {
			Calendar.removeClass(cal.hilitedMonth, "hilite");
		}
		var year = Calendar.findYear(target);
		if (year) {
			if (year.year != cal.date.getFullYear()) {
				if (cal.hilitedYear) {
					Calendar.removeClass(cal.hilitedYear, "hilite");
				}
				Calendar.addClass(year, "hilite");
				cal.hilitedYear = year;
			} else if (cal.hilitedYear) {
				Calendar.removeClass(cal.hilitedYear, "hilite");
			}
		} else if (cal.hilitedYear) {
			Calendar.removeClass(cal.hilitedYear, "hilite");
		}
	}
	return Calendar.stopEvent(ev);
};

Calendar.tableMouseDown = function (ev) {
	if (Calendar.getTargetElement(ev) == Calendar.getElement(ev)) {
		return Calendar.stopEvent(ev);
	}
};

Calendar.calDragIt = function (ev) {
	var cal = Calendar._C;
	if (!(cal && cal.dragging)) {
		return false;
	}
	var posX;
	var posY;
	if (Calendar.is_ie) {
		posY = window.event.clientY + document.body.scrollTop;
		posX = window.event.clientX + document.body.scrollLeft;
	} else {
		posX = ev.pageX;
		posY = ev.pageY;
	}
	cal.hideShowCovered();
	var st = cal.element.style;
	st.left = (posX - cal.xOffs) + "px";
	st.top = (posY - cal.yOffs) + "px";
	return Calendar.stopEvent(ev);
};

Calendar.calDragEnd = function (ev) {
	var cal = Calendar._C;
	if (!cal) {
		return false;
	}
	cal.dragging = false;
	with (Calendar) {
		removeEvent(document, "mousemove", calDragIt);
		removeEvent(document, "mouseup", calDragEnd);
		tableMouseUp(ev);
	}
	cal.hideShowCovered();
};

Calendar.dayMouseDown = function(ev) {
	var el = Calendar.getElement(ev);
	if (el.disabled) {
		return false;
	}
	var cal = el.calendar;
	cal.activeDiv = el;
	Calendar._C = cal;
	if (el.navtype != 300) with (Calendar) {
		if (el.navtype == 50) {
			el._current = el.firstChild.data;
			addEvent(document, "mousemove", tableMouseOver);
		} else
			addEvent(document, Calendar.is_ie5 ? "mousemove" : "mouseover", tableMouseOver);
		addClass(el, "hilite active");
		addEvent(document, "mouseup", tableMouseUp);
	} else if (cal.isPopup) {
		cal._dragStart(ev);
	}
	if (el.navtype == -1 || el.navtype == 1) {
		if (cal.timeout) clearTimeout(cal.timeout);
		cal.timeout = setTimeout("Calendar.showMonthsCombo()", 250);
	} else if (el.navtype == -2 || el.navtype == 2) {
		if (cal.timeout) clearTimeout(cal.timeout);
		cal.timeout = setTimeout((el.navtype > 0) ? "Calendar.showYearsCombo(true)" : "Calendar.showYearsCombo(false)", 250);
	} else {
		cal.timeout = null;
	}
	return Calendar.stopEvent(ev);
};

Calendar.dayMouseDblClick = function(ev) {
	Calendar.cellClick(Calendar.getElement(ev), ev || window.event);
	if (Calendar.is_ie) {
		document.selection.empty();
	}
};

Calendar.dayMouseOver = function(ev) {
	var el = Calendar.getElement(ev);
	if (Calendar.isRelated(el, ev) || Calendar._C || el.disabled) {
		return false;
	}
	if (el.ttip) {
		if (el.ttip.substr(0, 1) == "_") {
			el.ttip = el.caldate.print(el.calendar.ttDateFormat) + el.ttip.substr(1);
		}
		el.calendar.tooltips.firstChild.data = el.ttip;
	}
	if (el.navtype != 300) {
		Calendar.addClass(el, "hilite");
		if (el.caldate) {
			Calendar.addClass(el.parentNode, "rowhilite");
		}
	}
	return Calendar.stopEvent(ev);
};

Calendar.dayMouseOut = function(ev) {
	with (Calendar) {
		var el = getElement(ev);
		if (isRelated(el, ev) || _C || el.disabled) {
			return false;
		}
		removeClass(el, "hilite");
		if (el.caldate) {
			removeClass(el.parentNode, "rowhilite");
		}
		el.calendar.tooltips.firstChild.data = _TT["SEL_DATE"];
		return stopEvent(ev);
	}
};

/**
 *  A generic "click" handler :) handles all types of buttons defined in this
 *  calendar.
 */
Calendar.cellClick = function(el, ev) {
	var cal = el.calendar;
	var closing = false;
	var newdate = false;
	var date = null;
	if (typeof el.navtype == "undefined") {
		Calendar.removeClass(cal.currentDateEl, "selected");
		Calendar.addClass(el, "selected");
		closing = (cal.currentDateEl == el);
		if (!closing) {
			cal.currentDateEl = el;
		}
		cal.date = new Date(el.caldate);
		date = cal.date;
		newdate = true;
		// a date was clicked
		if (!(cal.dateClicked = !el.otherMonth))
			cal._init(cal.firstDayOfWeek, date);
	} else {
		if (el.navtype == 200) {
			Calendar.removeClass(el, "hilite");
			cal.callCloseHandler();
			return;
		}
		if(el.navtype == 0) {
			if(typeof Calendar.dateToday != 'undefined') date = new Date(parseFloat(Calendar.dateToday)); // new today's date based off params
			else date = new Date();
		}
		else {
			date = new Date(cal.date);
		}
		// unless "today" was clicked, we assume no date was clicked so
		// the selected handler will know not to close the calenar when
		// in single-click mode.
		// cal.dateClicked = (el.navtype == 0);
		cal.dateClicked = false;
		var year = date.getFullYear();
		var mon = date.getMonth();
		function setMonth(m) {
			var day = date.getDate();
			var max = date.getMonthDays(m);
			if (day > max) {
				date.setDate(max);
			}
			date.setMonth(m);
		};
		switch (el.navtype) {
		    case 400:
			Calendar.removeClass(el, "hilite");
			var text = Calendar._TT["ABOUT"];
			if (typeof text != "undefined") {
				text += cal.showsTime ? Calendar._TT["ABOUT_TIME"] : "";
			} else {
				// FIXME: this should be removed as soon as lang files get updated!
				text = "Help and about box text is not translated into this language.\n" +
					"If you know this language and you feel generous please update\n" +
					"the corresponding file in \"lang\" subdir to match calendar-en.js\n" +
					"and send it back to <mishoo@infoiasi.ro> to get it into the distribution  ;-)\n\n" +
					"Thank you!\n" +
					"http://dynarch.com/mishoo/calendar.epl\n";
			}
			alert(text);
			return;
		    case -2:
			if (year > cal.minYear) {
				date.setFullYear(year - 1);
			}
			break;
		    case -1:
			if (mon > 0) {
				setMonth(mon - 1);
			} else if (year-- > cal.minYear) {
				date.setFullYear(year);
				setMonth(11);
			}
			break;
		    case 1:
			if (mon < 11) {
				setMonth(mon + 1);
			} else if (year < cal.maxYear) {
				date.setFullYear(year + 1);
				setMonth(0);
			}
			break;
		    case 2:
			if (year < cal.maxYear) {
				date.setFullYear(year + 1);
			}
			break;
		    case 100:
			cal.setFirstDayOfWeek(el.fdow);
			return;
		    case 50:
			var range = el._range;
			var current = el.firstChild.data;
			for (var i = range.length; --i >= 0;)
				if (range[i] == current)
					break;
			if (ev && ev.shiftKey) {
				if (--i < 0)
					i = range.length - 1;
			} else if ( ++i >= range.length )
				i = 0;
			var newval = range[i];
			el.firstChild.data = newval;
			cal.onUpdateTime();
			return;
		    case 0:
			// TODAY will bring us here
			if ((typeof cal.getDateStatus == "function") && cal.getDateStatus(date, date.getFullYear(), date.getMonth(), date.getDate())) {
				// remember, "date" was previously set to new
				// Date() if TODAY was clicked; thus, it
				// contains today date.
				return false;
			}
			break;
		}
		if (!date.equalsTo(cal.date)) {
			cal.setDate(date);
			newdate = true;
		}
	}
	if (newdate) {
		cal.callHandler();
	}
	if (closing) {
		Calendar.removeClass(el, "hilite");
		cal.callCloseHandler();
	}
};

// END: CALENDAR STATIC FUNCTIONS

// BEGIN: CALENDAR OBJECT FUNCTIONS

/**
 *  This function creates the calendar inside the given parent.  If _par is
 *  null than it creates a popup calendar inside the BODY element.  If _par is
 *  an element, be it BODY, then it creates a non-popup calendar (still
 *  hidden).  Some properties need to be set before calling this function.
 */
Calendar.prototype.create = function (_par) {
	var parent = null;
	if (! _par) {
		// default parent is the document body, in which case we create
		// a popup calendar.
		parent = document.getElementsByTagName("body")[0];
		this.isPopup = true;
	} else {
		parent = _par;
		this.isPopup = false;
	}
	if(this.dateStr) this.date = new Date(this.dateStr)
	else if(typeof Calendar.dateToday == 'undefined') this.date = new Date();
	else this.date = new Date(Calendar.dateToday);

	var table = Calendar.createElement("table");
	this.table = table;
	table.cellSpacing = 0;
	table.cellPadding = 0;
	table.calendar = this;
	Calendar.addEvent(table, "mousedown", Calendar.tableMouseDown);

	var div = Calendar.createElement("div");
	this.element = div;
	
	div.className = "calendar";
	if (this.isPopup) {
		div.style.position = "absolute";
		div.style.display = "none";
		div.style.zIndex = 12000;
		
		
	}
	div.appendChild(table);

	var thead = Calendar.createElement("thead", table);
	var cell = null;
	var row = null;

	var cal = this;
	var hh = function (text, cs, navtype) {
		cell = Calendar.createElement("td", row);
		cell.colSpan = cs;
		cell.className = "button";
		if (navtype != 0 && Math.abs(navtype) <= 2)
			cell.className += " nav";
		Calendar._add_evs(cell);
		cell.calendar = cal;
		cell.navtype = navtype;
		if (text.substr(0, 1) != "&") {
			cell.appendChild(document.createTextNode(text));
		}
		else {
			// FIXME: dirty hack for entities
			cell.innerHTML = text;
		}
		return cell;
	};

	row = Calendar.createElement("tr", thead);
	var title_length = 6;
	(this.isPopup) && --title_length;
	(this.weekNumbers) && ++title_length;

	hh("?", 1, 400).ttip = Calendar._TT["INFO"];
	this.title = hh("", title_length, 300);
	this.title.className = "title";
	if (this.isPopup) {
		this.title.ttip = Calendar._TT["DRAG_TO_MOVE"];
		this.title.style.cursor = "move";
		hh("&#x00d7;", 1, 200).ttip = Calendar._TT["CLOSE"];
	}

	row = Calendar.createElement("tr", thead);
	row.className = "headrow";

	this._nav_py = hh("&#x00ab;", 1, -2);
	this._nav_py.ttip = Calendar._TT["PREV_YEAR"];

	this._nav_pm = hh("&#x2039;", 1, -1);
	this._nav_pm.ttip = Calendar._TT["PREV_MONTH"];

	this._nav_now = hh(Calendar._TT["TODAY"], this.weekNumbers ? 4 : 3, 0);
	this._nav_now.ttip = Calendar._TT["GO_TODAY"];

	this._nav_nm = hh("&#x203a;", 1, 1);
	this._nav_nm.ttip = Calendar._TT["NEXT_MONTH"];

	this._nav_ny = hh("&#x00bb;", 1, 2);
	this._nav_ny.ttip = Calendar._TT["NEXT_YEAR"];

	// day names
	row = Calendar.createElement("tr", thead);
	row.className = "daynames";
	if (this.weekNumbers) {
		cell = Calendar.createElement("td", row);
		cell.className = "name wn";
		cell.appendChild(document.createTextNode(Calendar._TT["WK"]));
	}
	for (var i = 7; i > 0; --i) {
		cell = Calendar.createElement("td", row);
		cell.appendChild(document.createTextNode(""));
		if (!i) {
			cell.navtype = 100;
			cell.calendar = this;
			Calendar._add_evs(cell);
		}
	}
	this.firstdayname = (this.weekNumbers) ? row.firstChild.nextSibling : row.firstChild;
	this._displayWeekdays();

	var tbody = Calendar.createElement("tbody", table);
	this.tbody = tbody;

	for (i = 6; i > 0; --i) {
		row = Calendar.createElement("tr", tbody);
		if (this.weekNumbers) {
			cell = Calendar.createElement("td", row);
			cell.appendChild(document.createTextNode(""));
		}
		for (var j = 7; j > 0; --j) {
			cell = Calendar.createElement("td", row);
			cell.appendChild(document.createTextNode(""));
			cell.calendar = this;
			Calendar._add_evs(cell);
		}
	}

	if (this.showsTime) {
		row = Calendar.createElement("tr", tbody);
		row.className = "time";

		cell = Calendar.createElement("td", row);
		cell.className = "time";
		cell.colSpan = 2;
		cell.innerHTML = Calendar._TT["TIME"] || "&nbsp;";

		cell = Calendar.createElement("td", row);
		cell.className = "time";
		cell.colSpan = this.weekNumbers ? 4 : 3;

		(function(){
			function makeTimePart(className, init, range_start, range_end) {
				var part = Calendar.createElement("span", cell);
				part.className = className;
				part.appendChild(document.createTextNode(init));
				part.calendar = cal;
				part.ttip = Calendar._TT["TIME_PART"];
				part.navtype = 50;
				part._range = [];
				if (typeof range_start != "number")
					part._range = range_start;
				else {
					for (var i = range_start; i <= range_end; ++i) {
						var txt;
						if (i < 10 && range_end >= 10) txt = '0' + i;
						else txt = '' + i;
						part._range[part._range.length] = txt;
					}
				}
				Calendar._add_evs(part);
				return part;
			};
			var hrs = cal.date.getHours();
			var mins = cal.date.getMinutes();
			var t12 = !cal.time24;
			var pm = (hrs > 12);
			if (t12 && pm) hrs -= 12;
			var H = makeTimePart("hour", hrs, t12 ? 1 : 0, t12 ? 12 : 23);
			var span = Calendar.createElement("span", cell);
			span.appendChild(document.createTextNode(":"));
			span.className = "colon";
			var M = makeTimePart("minute", mins, 0, 59);
			var AP = null;
			cell = Calendar.createElement("td", row);
			cell.className = "time";
			cell.colSpan = 2;
			if (t12)
				AP = makeTimePart("ampm", pm ? "pm" : "am", ["am", "pm"]);
			else
				cell.innerHTML = "&nbsp;";

			cal.onSetTime = function() {
				var hrs = this.date.getHours();
				var mins = this.date.getMinutes();
				var pm = (hrs > 12);
				if (pm && t12) hrs -= 12;
				H.firstChild.data = (hrs < 10) ? ("0" + hrs) : hrs;
				M.firstChild.data = (mins < 10) ? ("0" + mins) : mins;
				if (t12)
					AP.firstChild.data = pm ? "pm" : "am";
			};

			cal.onUpdateTime = function() {
				var date = this.date;
				var h = parseInt(H.firstChild.data, 10);
				if (t12) {
					if (/pm/i.test(AP.firstChild.data) && h < 12)
						h += 12;
					else if (/am/i.test(AP.firstChild.data) && h == 12)
						h = 0;
				}
				var d = date.getDate();
				var m = date.getMonth();
				var y = date.getFullYear();
				date.setHours(h);
				date.setMinutes(parseInt(M.firstChild.data, 10));
				date.setFullYear(y);
				date.setMonth(m);
				date.setDate(d);
				this.dateClicked = false;
				this.callHandler();
			};
		})();
	} else {
		this.onSetTime = this.onUpdateTime = function() {};
	}

	var tfoot = Calendar.createElement("tfoot", table);

	row = Calendar.createElement("tr", tfoot);
	row.className = "footrow";

	cell = hh(Calendar._TT["SEL_DATE"], this.weekNumbers ? 8 : 7, 300);
	cell.className = "ttip";
	if (this.isPopup) {
		cell.ttip = Calendar._TT["DRAG_TO_MOVE"];
		cell.style.cursor = "move";
	}
	this.tooltips = cell;

	div = Calendar.createElement("div", this.element);
	this.monthsCombo = div;
	div.className = "combo";
	for (i = 0; i < Calendar._MN.length; ++i) {
		var mn = Calendar.createElement("div");
		mn.className = Calendar.is_ie ? "label-IEfix" : "label";
		mn.month = i;
		mn.appendChild(document.createTextNode(Calendar._SMN[i]));
		div.appendChild(mn);
	}

	div = Calendar.createElement("div", this.element);
	this.yearsCombo = div;
	div.className = "combo";
	for (i = 12; i > 0; --i) {
		var yr = Calendar.createElement("div");
		yr.className = Calendar.is_ie ? "label-IEfix" : "label";
		yr.appendChild(document.createTextNode(""));
		div.appendChild(yr);
	}

	this._init(this.firstDayOfWeek, this.date);
	parent.appendChild(this.element);
};

/** keyboard navigation, only for popup calendars */
Calendar._keyEvent = function(ev) {
	if (!window.calendar) {
		return false;
	}
	(Calendar.is_ie) && (ev = window.event);
	var cal = window.calendar;
	var act = (Calendar.is_ie || ev.type == "keypress");
	if (ev.ctrlKey) {
		switch (ev.keyCode) {
		    case 37: // KEY left
			act && Calendar.cellClick(cal._nav_pm);
			break;
		    case 38: // KEY up
			act && Calendar.cellClick(cal._nav_py);
			break;
		    case 39: // KEY right
			act && Calendar.cellClick(cal._nav_nm);
			break;
		    case 40: // KEY down
			act && Calendar.cellClick(cal._nav_ny);
			break;
		    default:
			return false;
		}
	} else switch (ev.keyCode) {
	    case 32: // KEY space (now)
		Calendar.cellClick(cal._nav_now);
		break;
	    case 27: // KEY esc
		act && cal.callCloseHandler();
		break;
	    case 37: // KEY left
	    case 38: // KEY up
	    case 39: // KEY right
	    case 40: // KEY down
		if (act) {
			var date = cal.date.getDate() - 1;
			var el = cal.currentDateEl;
			var ne = null;
			var prev = (ev.keyCode == 37) || (ev.keyCode == 38);
			switch (ev.keyCode) {
			    case 37: // KEY left
				(--date >= 0) && (ne = cal.ar_days[date]);
				break;
			    case 38: // KEY up
				date -= 7;
				(date >= 0) && (ne = cal.ar_days[date]);
				break;
			    case 39: // KEY right
				(++date < cal.ar_days.length) && (ne = cal.ar_days[date]);
				break;
			    case 40: // KEY down
				date += 7;
				(date < cal.ar_days.length) && (ne = cal.ar_days[date]);
				break;
			}
			if (!ne) {
				if (prev) {
					Calendar.cellClick(cal._nav_pm);
				} else {
					Calendar.cellClick(cal._nav_nm);
				}
				date = (prev) ? cal.date.getMonthDays() : 1;
				el = cal.currentDateEl;
				ne = cal.ar_days[date - 1];
			}
			Calendar.removeClass(el, "selected");
			Calendar.addClass(ne, "selected");
			cal.date = new Date(ne.caldate);
			cal.callHandler();
			cal.currentDateEl = ne;
		}
		break;
	    case 13: // KEY enter
		if (act) {
			cal.callHandler();
			cal.hide();
		}
		break;
	    default:
		return false;
	}
	return Calendar.stopEvent(ev);
};

/**
 *  (RE)Initializes the calendar to the given date and firstDayOfWeek
 */
Calendar.prototype._init = function (firstDayOfWeek, date) {
	if(typeof Calendar.dateToday == 'undefined') var today = new Date();
	else var today = new Date(parseFloat(Calendar.dateToday));
	
	this.table.style.visibility = "hidden";
	var year = date.getFullYear();
	if (year < this.minYear) {
		year = this.minYear;
		date.setFullYear(year);
	} else if (year > this.maxYear) {
		year = this.maxYear;
		date.setFullYear(year);
	}
	this.firstDayOfWeek = firstDayOfWeek;
	this.date = new Date(date);
	var month = date.getMonth();
	var mday = date.getDate();
	var no_days = date.getMonthDays();

	// calendar voodoo for computing the first day that would actually be
	// displayed in the calendar, even if it's from the previous month.
	// WARNING: this is magic. ;-)
	date.setDate(1);
	var day1 = (date.getDay() - this.firstDayOfWeek) % 7;
	if (day1 < 0)
		day1 += 7;
	// sb: workaround for google chrome bug. changed "-day1" to "-day1 || 0"
	date.setDate(-day1 || 0);
	date.setDate(date.getDate() + 1);

	var row = this.tbody.firstChild;
	var MN = Calendar._SMN[month];
	var ar_days = new Array();
	var weekend = Calendar._TT["WEEKEND"];
	for (var i = 0; i < 6; ++i, row = row.nextSibling) {
		var cell = row.firstChild;
		if (this.weekNumbers) {
			cell.className = "day wn";
			cell.firstChild.data = date.getWeekNumber();
			cell = cell.nextSibling;
		}
		row.className = "daysrow";
		var hasdays = false;
		for (var j = 0; j < 7; ++j, cell = cell.nextSibling, date.setDate(date.getDate() + 1)) {
			var iday = date.getDate();
			var wday = date.getDay();
			cell.className = "day";
			var current_month = (date.getMonth() == month);
			if (!current_month) {
				if (this.showsOtherMonths) {
					cell.className += " othermonth";
					cell.otherMonth = true;
				} else {
					cell.className = "emptycell";
					cell.innerHTML = "&nbsp;";
					cell.disabled = true;
					continue;
				}
			} else {
				cell.otherMonth = false;
				hasdays = true;
			}
			cell.disabled = false;
			cell.firstChild.data = iday;
			if (typeof this.getDateStatus == "function") {
				var status = this.getDateStatus(date, year, month, iday);
				if (status === true) {
					cell.className += " disabled";
					cell.disabled = true;
				} else {
					if (/disabled/i.test(status))
						cell.disabled = true;
					cell.className += " " + status;
				}
			}
			if (!cell.disabled) {
				ar_days[ar_days.length] = cell;
				cell.caldate = new Date(date);
				cell.ttip = "_";
				if (current_month && iday == mday) {
					cell.className += " selected";
					this.currentDateEl = cell;
				}
				if (date.getFullYear() == today.getFullYear() &&
				    date.getMonth() == today.getMonth() &&
				    iday == today.getDate()) {
					cell.className += " today";
					cell.ttip += Calendar._TT["PART_TODAY"];
				}
				if (weekend.indexOf(wday.toString()) != -1) {
					cell.className += cell.otherMonth ? " oweekend" : " weekend";
				}
			}
		}
		if (!(hasdays || this.showsOtherMonths))
			row.className = "emptyrow";
	}
	this.ar_days = ar_days;
	this.title.firstChild.data = Calendar._MN[month] + ", " + year;
	this.onSetTime();
	this.table.style.visibility = "visible";
	// PROFILE
	// this.tooltips.firstChild.data = "Generated in " + ((new Date()) - today) + " ms";
};

/**
 *  Calls _init function above for going to a certain date (but only if the
 *  date is different than the currently selected one).
 */
Calendar.prototype.setDate = function (date) {
	if (!date.equalsTo(this.date)) {
		this._init(this.firstDayOfWeek, date);
	}
};

/**
 *  Refreshes the calendar.  Useful if the "disabledHandler" function is
 *  dynamic, meaning that the list of disabled date can change at runtime.
 *  Just * call this function if you think that the list of disabled dates
 *  should * change.
 */
Calendar.prototype.refresh = function () {
	this._init(this.firstDayOfWeek, this.date);
};

/** Modifies the "firstDayOfWeek" parameter (pass 0 for Synday, 1 for Monday, etc.). */
Calendar.prototype.setFirstDayOfWeek = function (firstDayOfWeek) {
	this._init(firstDayOfWeek, this.date);
	this._displayWeekdays();
};

/**
 *  Allows customization of what dates are enabled.  The "unaryFunction"
 *  parameter must be a function object that receives the date (as a JS Date
 *  object) and returns a boolean value.  If the returned value is true then
 *  the passed date will be marked as disabled.
 */
Calendar.prototype.setDateStatusHandler = Calendar.prototype.setDisabledHandler = function (unaryFunction) {
	this.getDateStatus = unaryFunction;
};

/** Customization of allowed year range for the calendar. */
Calendar.prototype.setRange = function (a, z) {
	this.minYear = a;
	this.maxYear = z;
};

/** Calls the first user handler (selectedHandler). */
Calendar.prototype.callHandler = function () {
	if (this.onSelected) {
		this.onSelected(this, this.date.print(this.dateFormat));
	}
};

/** Calls the second user handler (closeHandler). */
Calendar.prototype.callCloseHandler = function () {
	if (this.onClose) {
		this.onClose(this);
	}
	this.hideShowCovered();
};

/** Removes the calendar object from the DOM tree and destroys it. */
Calendar.prototype.destroy = function () {
	var el = this.element.parentNode;
	el.removeChild(this.element);
	Calendar._C = null;
	window.calendar = null;
};

/**
 *  Moves the calendar element to a different section in the DOM tree (changes
 *  its parent).
 */
Calendar.prototype.reparent = function (new_parent) {
	var el = this.element;
	el.parentNode.removeChild(el);
	new_parent.appendChild(el);
};

// This gets called when the user presses a mouse button anywhere in the
// document, if the calendar is shown.  If the click was outside the open
// calendar this function closes it.
Calendar._checkCalendar = function(ev) {
	if (!window.calendar) {
		return false;
	}
	var el = Calendar.is_ie ? Calendar.getElement(ev) : Calendar.getTargetElement(ev);
	for (; el != null && el != calendar.element; el = el.parentNode);
	if (el == null) {
		// calls closeHandler which should hide the calendar.
		window.calendar.callCloseHandler();
		return Calendar.stopEvent(ev);
	}
};

/** Shows the calendar. */
Calendar.prototype.show = function () {
	if(this.inputField != null && !this.inputField.readOnly)
	{
		var rows = this.table.getElementsByTagName("tr");
		for (var i = rows.length; i > 0;) {
			var row = rows[--i];
			Calendar.removeClass(row, "rowhilite");
			var cells = row.getElementsByTagName("td");
			for (var j = cells.length; j > 0;) {
				var cell = cells[--j];
				Calendar.removeClass(cell, "hilite");
				Calendar.removeClass(cell, "active");
			}
		}
		
		this.element.style.display = "block";
		this.hidden = false;
		if (this.isPopup) {
			window.calendar = this;
			Calendar.addEvent(document, "keydown", Calendar._keyEvent);
			Calendar.addEvent(document, "keypress", Calendar._keyEvent);
			Calendar.addEvent(document, "mousedown", Calendar._checkCalendar);
		}
		this.hideShowCovered();
	}
};

/**
 *  Hides the calendar.  Also removes any "hilite" from the class of any TD
 *  element.
 */
Calendar.prototype.hide = function () {
	if (this.isPopup) {
		Calendar.removeEvent(document, "keydown", Calendar._keyEvent);
		Calendar.removeEvent(document, "keypress", Calendar._keyEvent);
		Calendar.removeEvent(document, "mousedown", Calendar._checkCalendar);
	}
	this.element.style.display = "none";
	this.hidden = true;
	this.hideShowCovered();
};

/**
 *  Shows the calendar at a given absolute position (beware that, depending on
 *  the calendar element style -- position property -- this might be relative
 *  to the parent's containing rectangle).
 */
Calendar.prototype.showAt = function (x, y) {
	var s = this.element.style;
	s.left = x + "px";
	s.top = y + "px";
	this.show();
};

/** Shows the calendar near a given element. */
Calendar.prototype.showAtElement = function (el, opts) {
	var self = this;
	var p = Calendar.getAbsolutePos(el);
	if (!opts || typeof opts != "string") {
		this.showAt(p.x, p.y + el.offsetHeight);
		return true;
	}
	function fixPosition(box) {
		if (box.x < 0)
			box.x = 0;
		if (box.y < 0)
			box.y = 0;
		var cp = document.createElement("div");
		var s = cp.style;
		s.position = "absolute";
		s.right = s.bottom = s.width = s.height = "0px";
		document.body.appendChild(cp);
		var br = Calendar.getAbsolutePos(cp);
		document.body.removeChild(cp);
		if (Calendar.is_ie) {
			br.y += Math.max(document.body.scrollTop, document.documentElement.scrollTop);
			br.x += Math.max(document.body.scrollLeft, document.documentElement.scrollLeft);
		} else {
			br.y += window.scrollY;
			br.x += window.scrollX;
		}
		var tmp = box.x + box.width - br.x;
		if (tmp > 0) box.x -= tmp;
		tmp = box.y + box.height - br.y;
		if (tmp > 0) box.y -= tmp;
	};
	this.element.style.display = "block";
	Calendar.continuation_for_the_khtml_browser = function() {
		var w = self.element.offsetWidth;
		var h = self.element.offsetHeight;
		self.element.style.display = "none";
		var valign = opts.substr(0, 1);
		var halign = "l";
		if (opts.length > 1) {
			halign = opts.substr(1, 1);
		}
		// vertical alignment
		switch (valign) {
		    case "T": p.y -= h; break;
		    case "B": p.y += el.offsetHeight; break;
		    case "C": p.y += (el.offsetHeight - h) / 2; break;
		    case "t": p.y += el.offsetHeight - h; break;
		    case "b": break; // already there
		}
		// horizontal alignment
		switch (halign) {
		    case "L": p.x -= w; break;
		    case "R": p.x += el.offsetWidth; break;
		    case "C": p.x += (el.offsetWidth - w) / 2; break;
		    case "r": p.x += el.offsetWidth - w; break;
		    case "l": break; // already there
		}
		p.width = w;
		p.height = h + 40;
		self.monthsCombo.style.display = "none";
		fixPosition(p);
		self.showAt(p.x, p.y);
	};
	if (Calendar.is_khtml)
		setTimeout("Calendar.continuation_for_the_khtml_browser()", 10);
	else
		Calendar.continuation_for_the_khtml_browser();
};

/** Customizes the date format. */
Calendar.prototype.setDateFormat = function (str) {
	this.dateFormat = str;
};

/** Customizes the tooltip date format. */
Calendar.prototype.setTtDateFormat = function (str) {
	this.ttDateFormat = str;
};

/**
 *  Tries to identify the date represented in a string.  If successful it also
 *  calls this.setDate which moves the calendar to the given date.
 */
Calendar.prototype.parseDate = function (str, fmt) {
	var y = 0;
	var m = -1;
	var d = 0;
	var a = str.split(/\W+/);
	if (!fmt) {
		fmt = this.dateFormat;
	}
	var b = fmt.match(/%./g);
	var i = 0, j = 0;
	var hr = 0;
	var min = 0;
	for (i = 0; i < a.length; ++i) {
		if (!a[i])
			continue;
		switch (b[i]) {
		    case "%d":
		    case "%e":
			d = parseInt(a[i], 10);
			break;

		    case "%m":
			m = parseInt(a[i], 10) - 1;
			break;

		    case "%Y":
		    case "%y":
			y = parseInt(a[i], 10);
			(y < 100) && (y += (y > 29) ? 1900 : 2000);
			break;

		    case "%b":
		    case "%B":
			for (j = 0; j < 12; ++j) {
				if (Calendar._MN[j].substr(0, a[i].length).toLowerCase() == a[i].toLowerCase()) { m = j; break; }
			}
			break;

		    case "%H":
		    case "%I":
		    case "%k":
		    case "%l":
			hr = parseInt(a[i], 10);
			break;

		    case "%P":
		    case "%p":
			if (/pm/i.test(a[i]) && hr < 12)
				hr += 12;
			break;

		    case "%M":
			min = parseInt(a[i], 10);
			break;
		}
	}
	if (y != 0 && m != -1 && d != 0) {
		this.setDate(new Date(y, m, d, hr, min, 0));
		return;
	}
	y = 0; m = -1; d = 0;
	for (i = 0; i < a.length; ++i) {
		if (a[i].search(/[a-zA-Z]+/) != -1) {
			var t = -1;
			for (j = 0; j < 12; ++j) {
				if (Calendar._MN[j].substr(0, a[i].length).toLowerCase() == a[i].toLowerCase()) { t = j; break; }
			}
			if (t != -1) {
				if (m != -1) {
					d = m+1;
				}
				m = t;
			}
		} else if (parseInt(a[i], 10) <= 12 && m == -1) {
			m = a[i]-1;
		} else if (parseInt(a[i], 10) > 31 && y == 0) {
			y = parseInt(a[i], 10);
			(y < 100) && (y += (y > 29) ? 1900 : 2000);
		} else if (d == 0) {
			d = a[i];
		}
	}
	if (y == 0) {
		var today = new Date();
		y = today.getFullYear();
	}
	if (m != -1 && d != 0) {
		this.setDate(new Date(y, m, d, hr, min, 0));
	}
};

Calendar.prototype.hideShowCovered = function () {
    if (!Calendar.is_ie) {
      // This is no longer needed for newer browsers, IE is left in here for the sake of IE5
      return;
    }
	var self = this;
	Calendar.continuation_for_the_khtml_browser = function() {
		function getVisib(obj){
			var value = obj.style.visibility;
			if (!value) {
				if (document.defaultView && typeof (document.defaultView.getComputedStyle) == "function") { // Gecko, W3C
					if (!Calendar.is_khtml)
						value = document.defaultView.
							getComputedStyle(obj, "").getPropertyValue("visibility");
					else
						value = '';
				} else if (obj.currentStyle) { // IE
					value = obj.currentStyle.visibility;
				} else
					value = '';
			}
			return value;
		};

		var tags = new Array("applet", "iframe", "select");
		var el = self.element;

		var p = Calendar.getAbsolutePos(el);
		var EX1 = p.x;
		var EX2 = el.offsetWidth + EX1;
		var EY1 = p.y;
		var EY2 = el.offsetHeight + EY1;

		for (var k = tags.length; k > 0; ) {
			var ar = document.getElementsByTagName(tags[--k]);
			var cc = null;

			for (var i = ar.length; i > 0;) {
				cc = ar[--i];

				p = Calendar.getAbsolutePos(cc);
				var CX1 = p.x;
				var CX2 = cc.offsetWidth + CX1;
				var CY1 = p.y;
				var CY2 = cc.offsetHeight + CY1;

				if (self.hidden || (CX1 > EX2) || (CX2 < EX1) || (CY1 > EY2) || (CY2 < EY1)) {
					if (!cc.__msh_save_visibility) {
						cc.__msh_save_visibility = getVisib(cc);
					}
					cc.style.visibility = cc.__msh_save_visibility;
				} else {
					if (!cc.__msh_save_visibility) {
						cc.__msh_save_visibility = getVisib(cc);
					}
					cc.style.visibility = "hidden";
				}
			}
		}
	};
	if (Calendar.is_khtml)
		setTimeout("Calendar.continuation_for_the_khtml_browser()", 10);
	else
		Calendar.continuation_for_the_khtml_browser();
};

/** Internal function; it displays the bar with the names of the weekday. */
Calendar.prototype._displayWeekdays = function () {
	var fdow = this.firstDayOfWeek;
	var cell = this.firstdayname;
	var weekend = Calendar._TT["WEEKEND"];
	for (var i = 0; i < 7; ++i) {
		cell.className = "day name";
		var realday = (i + fdow) % 7;
		if (i) {
			cell.ttip = Calendar._TT["DAY_FIRST"].replace("%s", Calendar._DN[realday]);
			cell.navtype = 100;
			cell.calendar = this;
			cell.fdow = realday;
			Calendar._add_evs(cell);
		}
		if (weekend.indexOf(realday.toString()) != -1) {
			Calendar.addClass(cell, "weekend");
		}
		cell.firstChild.data = Calendar._SDN[(i + fdow) % 7];
		cell = cell.nextSibling;
	}
};

/** Internal function.  Hides all combo boxes that might be displayed. */
Calendar.prototype._hideCombos = function () {
	this.monthsCombo.style.display = "none";
	this.yearsCombo.style.display = "none";
};

/** Internal function.  Starts dragging the element. */
Calendar.prototype._dragStart = function (ev) {
	if (this.dragging) {
		return;
	}
	this.dragging = true;
	var posX;
	var posY;
	if (Calendar.is_ie) {
		posY = window.event.clientY + document.body.scrollTop;
		posX = window.event.clientX + document.body.scrollLeft;
	} else {
		posY = ev.clientY + window.scrollY;
		posX = ev.clientX + window.scrollX;
	}
	var st = this.element.style;
	this.xOffs = posX - parseInt(st.left);
	this.yOffs = posY - parseInt(st.top);
	with (Calendar) {
		addEvent(document, "mousemove", calDragIt);
		addEvent(document, "mouseup", calDragEnd);
	}
};

// BEGIN: DATE OBJECT PATCHES

/** Adds the number of days array to the Date object. */
Date._MD = new Array(31,28,31,30,31,30,31,31,30,31,30,31);

/** Constants used for time computations */
Date.SECOND = 1000 /* milliseconds */;
Date.MINUTE = 60 * Date.SECOND;
Date.HOUR   = 60 * Date.MINUTE;
Date.DAY    = 24 * Date.HOUR;
Date.WEEK   =  7 * Date.DAY;

/** Returns the number of days in the current month */
Date.prototype.getMonthDays = function(month) {
	var year = this.getFullYear();
	if (typeof month == "undefined") {
		month = this.getMonth();
	}
	if (((0 == (year%4)) && ( (0 != (year%100)) || (0 == (year%400)))) && month == 1) {
		return 29;
	} else {
		return Date._MD[month];
	}
};

/** Returns the number of day in the year. */
Date.prototype.getDayOfYear = function() {
	var now = new Date(this.getFullYear(), this.getMonth(), this.getDate(), 0, 0, 0);
	var then = new Date(this.getFullYear(), 0, 0, 0, 0, 0);
	var time = now - then;
	return Math.floor(time / Date.DAY);
};

/** Returns the number of the week in year, as defined in ISO 8601. */
Date.prototype.getWeekNumber = function() {
	var d = new Date(this.getFullYear(), this.getMonth(), this.getDate(), 0, 0, 0);
	var DoW = d.getDay();
	d.setDate(d.getDate() - (DoW + 6) % 7 + 3); // Nearest Thu
	var ms = d.valueOf(); // GMT
	d.setMonth(0);
	d.setDate(4); // Thu in Week 1
	return Math.round((ms - d.valueOf()) / (7 * 864e5)) + 1;
};

/** Checks dates equality (ignores time) */
Date.prototype.equalsTo = function(date) {
	return ((this.getFullYear() == date.getFullYear()) &&
		(this.getMonth() == date.getMonth()) &&
		(this.getDate() == date.getDate()) &&
		(this.getHours() == date.getHours()) &&
		(this.getMinutes() == date.getMinutes()));
};

/** Prints the date in a string according to the given format. */
Date.prototype.print = function (str) {
	var m = this.getMonth();
	var d = this.getDate();
	var y = this.getFullYear();
	var wn = this.getWeekNumber();
	var w = this.getDay();
	var s = {};
	var hr = this.getHours();
	var pm = (hr >= 12);
	var ir = (pm) ? (hr - 12) : hr;
	var dy = this.getDayOfYear();
	if (ir == 0)
		ir = 12;
	var min = this.getMinutes();
	var sec = this.getSeconds();
	s["%a"] = Calendar._SDN[w]; // abbreviated weekday name [FIXME: I18N]
	s["%A"] = Calendar._DN[w]; // full weekday name
	s["%b"] = Calendar._SMN[m]; // abbreviated month name [FIXME: I18N]
	s["%B"] = Calendar._MN[m]; // full month name
	// FIXME: %c : preferred date and time representation for the current locale
	s["%C"] = 1 + Math.floor(y / 100); // the century number
	s["%d"] = (d < 10) ? ("0" + d) : d; // the day of the month (range 01 to 31)
	s["%e"] = d; // the day of the month (range 1 to 31)
	// FIXME: %D : american date style: %m/%d/%y
	// FIXME: %E, %F, %G, %g, %h (man strftime)
	s["%H"] = (hr < 10) ? ("0" + hr) : hr; // hour, range 00 to 23 (24h format)
	s["%I"] = (ir < 10) ? ("0" + ir) : ir; // hour, range 01 to 12 (12h format)
	s["%j"] = (dy < 100) ? ((dy < 10) ? ("00" + dy) : ("0" + dy)) : dy; // day of the year (range 001 to 366)
	s["%k"] = hr;		// hour, range 0 to 23 (24h format)
	s["%l"] = ir;		// hour, range 1 to 12 (12h format)
	s["%m"] = (m < 9) ? ("0" + (1+m)) : (1+m); // month, range 01 to 12
	s["%M"] = (min < 10) ? ("0" + min) : min; // minute, range 00 to 59
	s["%n"] = "\n";		// a newline character
	s["%p"] = pm ? "PM" : "AM";
	s["%P"] = pm ? "pm" : "am";
	// FIXME: %r : the time in am/pm notation %I:%M:%S %p
	// FIXME: %R : the time in 24-hour notation %H:%M
	s["%s"] = Math.floor(this.getTime() / 1000);
	s["%S"] = (sec < 10) ? ("0" + sec) : sec; // seconds, range 00 to 59
	s["%t"] = "\t";		// a tab character
	// FIXME: %T : the time in 24-hour notation (%H:%M:%S)
	s["%U"] = s["%W"] = s["%V"] = (wn < 10) ? ("0" + wn) : wn;
	s["%u"] = w + 1;	// the day of the week (range 1 to 7, 1 = MON)
	s["%w"] = w;		// the day of the week (range 0 to 6, 0 = SUN)
	// FIXME: %x : preferred date representation for the current locale without the time
	// FIXME: %X : preferred time representation for the current locale without the date
	s["%y"] = ('' + y).substr(2, 2); // year without the century (range 00 to 99)
	s["%Y"] = y;		// year with the century
	s["%%"] = "%";		// a literal '%' character

	var re = /%./g;
	var isSafari=navigator.userAgent.toLowerCase().indexOf ("safari")!=-1;
	if (!Calendar.is_ie5 && !isSafari)
		return str.replace(re, function (par) { return s[par] || par; })

	var a = str.match(re);
	for (var i = 0; i < a.length; i++) {
		var tmp = s[a[i]];
		if (tmp) {
			re = new RegExp(a[i], 'g');
			str = str.replace(re, tmp);
		}
	}

	return str;
};

Date.prototype.__msh_oldSetFullYear = Date.prototype.setFullYear;
Date.prototype.setFullYear = function(y) {
	var d = new Date(this);
	d.__msh_oldSetFullYear(y);
	if (d.getMonth() != this.getMonth())
		this.setDate(28);
	this.__msh_oldSetFullYear(y);
};

// END: DATE OBJECT PATCHES


// global object that remembers the calendar
window.calendar = null;
// End of File jscalendar/calendar.js
                                
/*

Modification information for LGPL compliance

r56990 - 2010-06-16 13:05:36 -0700 (Wed, 16 Jun 2010) - kjing - snapshot "Mango" svn branch to a new one for GitHub sync

r56989 - 2010-06-16 13:01:33 -0700 (Wed, 16 Jun 2010) - kjing - defunt "Mango" svn dev branch before github cutover

r55980 - 2010-04-19 13:31:28 -0700 (Mon, 19 Apr 2010) - kjing - create Mango (6.1) based on windex

r51719 - 2009-10-22 10:18:00 -0700 (Thu, 22 Oct 2009) - mitani - Converted to Build 3  tags and updated the build system 

r51634 - 2009-10-19 13:32:22 -0700 (Mon, 19 Oct 2009) - mitani - Windex is the branch for Sugar Sales 1.0 development

r50375 - 2009-08-24 18:07:43 -0700 (Mon, 24 Aug 2009) - dwong - branch kobe2 from tokyo r50372

r42807 - 2008-12-29 11:16:59 -0800 (Mon, 29 Dec 2008) - dwong - Branch from trunk/sugarcrm r42806 to branches/tokyo/sugarcrm

r24077 - 2007-07-06 14:12:57 -0700 (Fri, 06 Jul 2007) - clee - Updates to match new date + time field conversions.

r550 - 2004-09-03 15:39:17 -0700 (Fri, 03 Sep 2004) - jostrow - Calendar feature


*/

// ** I18N

// Calendar EN language
// Author: Mihai Bazon, <mishoo@infoiasi.ro>
// Encoding: any
// Distributed under the same terms as the calendar itself.

// For translators: please use UTF-8 if possible.  We strongly believe that
// Unicode is the answer to a real internationalized world.  Also please
// include your contact information in the header, as can be seen above.

// full day names
Calendar._DN = new Array
("Sunday",
 "Monday",
 "Tuesday",
 "Wednesday",
 "Thursday",
 "Friday",
 "Saturday",
 "Sunday");

// Please note that the following array of short day names (and the same goes
// for short month names, _SMN) isn't absolutely necessary.  We give it here
// for exemplification on how one can customize the short day names, but if
// they are simply the first N letters of the full name you can simply say:
//
//   Calendar._SDN_len = N; // short day name length
//   Calendar._SMN_len = N; // short month name length
//
// If N = 3 then this is not needed either since we assume a value of 3 if not
// present, to be compatible with translation files that were written before
// this feature.

// short day names
Calendar._SDN = new Array
("Sun",
 "Mon",
 "Tue",
 "Wed",
 "Thu",
 "Fri",
 "Sat",
 "Sun");

// full month names
Calendar._MN = new Array
("January",
 "February",
 "March",
 "April",
 "May",
 "June",
 "July",
 "August",
 "September",
 "October",
 "November",
 "December");

// short month names
Calendar._SMN = new Array
("Jan",
 "Feb",
 "Mar",
 "Apr",
 "May",
 "Jun",
 "Jul",
 "Aug",
 "Sep",
 "Oct",
 "Nov",
 "Dec");

// tooltips
Calendar._TT = {};
Calendar._TT["INFO"] = "About the calendar";

Calendar._TT["ABOUT"] =
"DHTML Date/Time Selector\n" +
"(c) dynarch.com 2002-2003\n" + // don't translate this this ;-)
"For latest version visit: http://dynarch.com/mishoo/calendar.epl\n" +
"Distributed under GNU LGPL.  See http://gnu.org/licenses/lgpl.html for details." +
"\n\n" +
"Date selection:\n" +
"- Use the \xab, \xbb buttons to select year\n" +
"- Use the " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " buttons to select month\n" +
"- Hold mouse button on any of the above buttons for faster selection.";
Calendar._TT["ABOUT_TIME"] = "\n\n" +
"Time selection:\n" +
"- Click on any of the time parts to increase it\n" +
"- or Shift-click to decrease it\n" +
"- or click and drag for faster selection.";

Calendar._TT["PREV_YEAR"] = "Prev. year (hold for menu)";
Calendar._TT["PREV_MONTH"] = "Prev. month (hold for menu)";
Calendar._TT["GO_TODAY"] = "Go Today";
Calendar._TT["NEXT_MONTH"] = "Next month (hold for menu)";
Calendar._TT["NEXT_YEAR"] = "Next year (hold for menu)";
Calendar._TT["SEL_DATE"] = "Select date and time";
Calendar._TT["DRAG_TO_MOVE"] = "Drag to move";
Calendar._TT["PART_TODAY"] = " (today)";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] = "Display %s first";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] = "0,6";

Calendar._TT["CLOSE"] = "Close";
Calendar._TT["TODAY"] = "Today";
//Calendar._TT["TIME_PART"] = "(Shift-)Click or drag to change value";
Calendar._TT["TIME_PART"] = "Drag mouse or use arrows to change";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%Y-%m-%d";
Calendar._TT["TT_DATE_FORMAT"] = "%a, %b %e";

Calendar._TT["WK"] = "wk";
Calendar._TT["TIME"] = "Time:";
// End of File jscalendar/lang/calendar-en.js
                                
/*

Modification information for LGPL compliance

r56990 - 2010-06-16 13:05:36 -0700 (Wed, 16 Jun 2010) - kjing - snapshot "Mango" svn branch to a new one for GitHub sync

r56989 - 2010-06-16 13:01:33 -0700 (Wed, 16 Jun 2010) - kjing - defunt "Mango" svn dev branch before github cutover

r55980 - 2010-04-19 13:31:28 -0700 (Mon, 19 Apr 2010) - kjing - create Mango (6.1) based on windex

r51719 - 2009-10-22 10:18:00 -0700 (Thu, 22 Oct 2009) - mitani - Converted to Build 3  tags and updated the build system 

r51634 - 2009-10-19 13:32:22 -0700 (Mon, 19 Oct 2009) - mitani - Windex is the branch for Sugar Sales 1.0 development

r50375 - 2009-08-24 18:07:43 -0700 (Mon, 24 Aug 2009) - dwong - branch kobe2 from tokyo r50372

r42807 - 2008-12-29 11:16:59 -0800 (Mon, 29 Dec 2008) - dwong - Branch from trunk/sugarcrm r42806 to branches/tokyo/sugarcrm

r27708 - 2007-10-05 15:45:31 -0700 (Fri, 05 Oct 2007) - julian - Fix for bug #13416: jscalendar does not honor 'First Day of Week' value in I18N files (CONTRIBUTED CODE)

r25053 - 2007-08-03 09:24:58 -0700 (Fri, 03 Aug 2007) - clee - Fix to accomodate for ifFormat parameter that is used by modules when calling Calendar.setup code in Javascript.

r24533 - 2007-07-23 01:35:36 -0700 (Mon, 23 Jul 2007) - clee - Added fixes for IE javascript errors where calendar selection causes other form fields to be disabled/readonly.


*/

/*  Copyright Mihai Bazon, 2002, 2003  |  http://dynarch.com/mishoo/
 * ---------------------------------------------------------------------------
 *
 * The DHTML Calendar
 *
 * Details and latest version at:
 * http://dynarch.com/mishoo/calendar.epl
 *
 * This script is distributed under the GNU Lesser General Public License.
 * Read the entire license text here: http://www.gnu.org/licenses/lgpl.html
 *
 * This file defines helper functions for setting up the calendar.  They are
 * intended to help non-programmers get a working calendar on their site
 * quickly.  This script should not be seen as part of the calendar.  It just
 * shows you what one can do with the calendar, while in the same time
 * providing a quick and simple method for setting it up.  If you need
 * exhaustive customization of the calendar creation process feel free to
 * modify this code to suit your needs (this is recommended and much better
 * than modifying calendar.js itself).
 */

// $Id$

/**
 *  This function "patches" an input field (or other element) to use a calendar
 *  widget for date selection.
 *
 *  The "params" is a single object that can have the following properties:
 *
 *    prop. name   | description
 *  -------------------------------------------------------------------------------------------------
 *   inputField    | the ID of an input field to store the date
 *   displayArea   | the ID of a DIV or other element to show the date
 *   button        | ID of a button or other element that will trigger the calendar
 *   eventName     | event that will trigger the calendar, without the "on" prefix (default: "click")
 *   ifFormat      | date format that will be stored in the input field
 *   daFormat      | the date format that will be used to display the date in displayArea
 *   singleClick   | (true/false) wether the calendar is in single click mode or not (default: true)
 *   firstDay      | numeric: 0 to 6.  "0" means display Sunday first, "1" means display Monday first, etc.
 *   align         | alignment (default: "Br"); if you don't know what's this see the calendar documentation
 *   range         | array with 2 elements.  Default: [1900, 2999] -- the range of years available
 *   weekNumbers   | (true/false) if it's true (default) the calendar will display week numbers
 *   flat          | null or element ID; if not null the calendar will be a flat calendar having the parent with the given ID
 *   flatCallback  | function that receives a JS Date object and returns an URL to point the browser to (for flat calendar)
 *   disableFunc   | function that receives a JS Date object and should return true if that date has to be disabled in the calendar
 *   onSelect      | function that gets called when a date is selected.  You don't _have_ to supply this (the default is generally okay)
 *   onClose       | function that gets called when the calendar is closed.  [default]
 *   onUpdate      | function that gets called after the date is updated in the input field.  Receives a reference to the calendar.
 *   date          | the date that the calendar will be initially displayed to
 *   showsTime     | default: false; if true the calendar will include a time selector
 *   timeFormat    | the time format; can be "12" or "24", default is "12"
 *   electric      | if true (default) then given fields/date areas are updated for each move; otherwise they're updated only on close
 *   step          | configures the step of the years in drop-down boxes; default: 2
 *   position      | configures the calendar absolute position; default: null
 *   cache         | if "true" (but default: "false") it will reuse the same calendar object, where possible
 *   showOthers    | if "true" (but default: "false") it will show days from other months too
 *
 *  None of them is required, they all have default values.  However, if you
 *  pass none of "inputField", "displayArea" or "button" you'll get a warning
 *  saying "nothing to setup".
 */

Calendar.setup = function (params) {
	function param_default(pname, def) { if (typeof params[pname] == "undefined") { params[pname] = def; } };

	// ADDED: RMR
	param_default("inputFieldObj",     null);
	param_default("displayAreaObj",    null);
	param_default("buttonObj",         null);
	// END
	param_default("inputField",     null);
	param_default("displayArea",    null);
	param_default("button",         null);
	param_default("eventName",      "click");
	param_default("ifFormat",       "%Y/%m/%d");
	param_default("daFormat",       "%Y/%m/%d");
	param_default("singleClick",    true);
	param_default("disableFunc",    null);
	param_default("dateStatusFunc", params["disableFunc"]);	// takes precedence if both are defined
	param_default("firstDay",       isNaN(Calendar._FD)? 0 : Calendar._FD); // defaults to "Sunday" first. Uses Calendar._FD as default value.	
	param_default("align",          "Br");
	param_default("range",          [1900, 2999]);
	param_default("weekNumbers",    true);
	param_default("flat",           null);
	param_default("flatCallback",   null);
	param_default("onSelect",       null);
	param_default("onClose",        null);
	param_default("onOpen",        null);
	param_default("onUpdate",       null);
	param_default("date",           null);
	param_default("showsTime",      false);
	param_default("timeFormat",     "24");
	param_default("electric",       true);
	param_default("step",           2);
	param_default("position",       null);
	param_default("cache",          false);
	param_default("showOthers",     false);
	
	var tmp = ["inputField", "displayArea", "button"];
	for (var i in tmp) 
	{
		// ADDED: RMR
		if ( params[tmp[i]+'Obj'] == null 
			&& typeof params[tmp[i]] == "string")
		{
			params[tmp[i]] = document.getElementById(params[tmp[i]]);
		}
		else 
		{
			params[tmp[i]] = params[tmp[i]+'Obj'];
		}
		// END
	}
	if (!(params.flat || params.inputField || params.displayArea || params.button)) {
//		alert("Calendar.setup:\n  Nothing to setup (no fields found).  Please check your code");
		return false;
	}

	function onSelect(cal) {

		var p = cal.params;
		var update = (cal.dateClicked || p.electric);
		if (update && p.flat) {
			if (typeof p.flatCallback == "function")
				p.flatCallback(cal);
			else
				alert("No flatCallback given -- doing nothing.");
			return false;
		}
		if (update && p.inputField) {
			//p.inputField.value = cal.date.print(p.ifFormat);
		    val = cal.date.print(p.daFormat);
		    val = val.substring(0,10);
			p.inputField.value = val;

			if (typeof p.inputField.onchange == "function")
				p.inputField.onchange();
		}
		if (update && p.displayArea)
			p.displayArea.innerHTML = cal.date.print(p.daFormat);
		if (update && p.singleClick && cal.dateClicked)
			cal.callCloseHandler();
		if (update && typeof p.onUpdate == "function")
			p.onUpdate(cal);
	};
	if (params.flat != null) {

		if (typeof params.flat == "string")
			params.flat = document.getElementById(params.flat);
		if (!params.flat) {
			alert("Calendar.setup:\n  Flat specified but can't find parent.");
			return false;
		}
		var cal = new Calendar(params.firstDay, params.date, params.onSelect || onSelect);
		cal.showsTime = params.showsTime;
		cal.time24 = (params.timeFormat == "24");
		cal.params = params;
		cal.weekNumbers = params.weekNumbers;
		cal.setRange(params.range[0], params.range[1]);
		cal.setDateStatusHandler(params.dateStatusFunc);
		cal.create(params.flat);
		cal.show();
		return false;
	}

	var triggerEl = params.button || params.displayArea || params.inputField;
	triggerEl["on" + params.eventName] = function() {
		if(params.onOpen){
			params.onOpen();
		}
		var dateEl = params.inputField || params.displayArea;
		var dateFmt = ((typeof params.ifFormat != "undefined") && params.ifFormat != "%Y/%m/%d") ? params.ifFormat : params.daFormat;
        params.daFormat = dateFmt;
        
		if(dateFmt.indexOf(" ") > -1) {
		   dateFmt = dateFmt.substring(0, dateFmt.indexOf(" "));
        }
		
		var mustCreate = false;
		var cal = window.calendar;
		if (!(cal && params.cache)) {
			window.calendar = cal = new Calendar(params.firstDay,
							     params.date,
							     params.onSelect || onSelect,
							     params.onClose || function(cal) { cal.hide(); }, 
							     params.inputField);
			cal.showsTime = params.showsTime;
			cal.time24 = (params.timeFormat == "24");
			cal.weekNumbers = params.weekNumbers;
			mustCreate = true;
		} else {
			if (params.date)
				cal.setDate(params.date);
			cal.hide();
		}
		cal.showsOtherMonths = params.showOthers;
		cal.yearStep = params.step;
		cal.setRange(params.range[0], params.range[1]);
		cal.params = params;
		cal.setDateStatusHandler(params.dateStatusFunc);
		cal.setDateFormat(dateFmt);
		if (mustCreate)
			cal.create();
		cal.parseDate(dateEl.value || dateEl.innerHTML);
		cal.refresh();
		if (!params.position)
			cal.showAtElement(params.button || params.displayArea || params.inputField, params.align);
		else
			cal.showAt(params.position[0], params.position[1]);
		return false;
	};
};
// End of File jscalendar/calendar-setup_3.js
                                
JSON = YAHOO.lang.JSON;

SUGAR.quickCompose= {};

SUGAR.quickCompose = function() {
	return {
		
		parentPanel : null,
		dceMenuPanel : null,
		options: null,
		loadingMessgPanl : null,
		frameLoaded : false,
		resourcesLoaded: false,
		tinyLoaded : false,
		
		/**
		 * Get the required compose package in an ajax call required for 
		 * the quick compose.
		 * @method initComposePackage
		 * @param {Array} c Options containing compose package and full return url.
		 * @return {} none
		 **/
		initComposePackage: function(c)
		{
		    //Init fix for YUI 2.7.0 datatable sort.
	        SUGAR.email2.addressBook.initFixForDatatableSort();
	        
		    //JS resources must have been loaded if we reach this step.
		    SUGAR.quickCompose.resourcesLoaded = true;
            var callback = 
	        { 
	           success: function(o) 
	           {
	               var responseData = YAHOO.lang.JSON.parse(o.responseText);
	               //Create and insert the necessary script tag
	               var scriptTag = document.createElement('script');
	               scriptTag.id = 'quickComposeScript';
                   scriptTag.setAttribute('type','text/javascript');
                   
                   if(YAHOO.env.ua.ie > 0) //IE hack
                   		scriptTag.text = responseData.jsData;
                   else  //Everybody else
                   		scriptTag.appendChild(document.createTextNode(responseData.jsData));
                   
                   document.getElementsByTagName("head")[0].appendChild(scriptTag);
                   
                   //Create and insert the necessary div elements and html markup
	               var divTag = document.createElement("div");
	               divTag.innerHTML = responseData.divData;
	               divTag.id = 'quickCompose';
	               YAHOO.util.Dom.insertBefore(divTag, 'footer');
	               
	               //Set the flag that we loaded the compose package.
	               SUGAR.quickCompose.frameLoaded = true;
	               //Init the UI
                   SUGAR.quickCompose.initUI(c.data);
	           }
	       } 
	       
	       if(!SUGAR.quickCompose.frameLoaded)
		      YAHOO.util.Connect.asyncRequest('GET', 'index.php?entryPoint=GenerateQuickComposeFrame', callback, null);   
		   else
		      SUGAR.quickCompose.initUI(c.data);
		       
		},
		/**
		 * Initalize the UI for the quick compose
		 * the quick compose.
		 * @method initComposePackage
		 * @param {Array} options Options containing compose package and full return url.
		 * @return {} none
		 **/
		initUI: function(options)
		{
			var SQ = SUGAR.quickCompose;
			this.options = options;
		     
			//Hide the loading div
			loadingMessgPanl.hide();
			
    		dce_mode = (typeof this.dceMenuPanel != 'undefined' && this.dceMenuPanel != null) ? true : false;			
			
			//Destroy the previous quick compose panel to get a clean slate
    		if (SQ.parentPanel != null)
    		{
    			//First clean up the tinyMCE instance
    			tinyMCE.execCommand('mceRemoveControl', false, SUGAR.email2.tinyInstances.currentHtmleditor);
    			SUGAR.email2.tinyInstances[SUGAR.email2.tinyInstances.currentHtmleditor] = null;
    			SUGAR.email2.tinyInstances.currentHtmleditor = "";
    			SQ.parentPanel.destroy();
    			SQ.parentPanel = null;
    		}
    		
			theme = SUGAR.themes.theme_name;
	
			//The quick compose utalizes the EmailUI compose functionality which allows for multiple compose
			//tabs.  Quick compose always has only one compose screen with an index of 0.
			var idx = 0;  

		    //Get template engine with template
    		if (!SE.composeLayout.composeTemplate) 
    			SE.composeLayout.composeTemplate = new YAHOO.SUGAR.Template(SE.templates['compose']);
    		
    		panel_modal = dce_mode ? false : true;
    		panel_width = '880px';
			panel_constrain = dce_mode ? false : true;
    		panel_height = dce_mode ? '450px' : '400px';
    		panel_shadow = dce_mode ? false : true;
    		panel_draggable = dce_mode ? false : true;
    		panel_resize = dce_mode ? false : true;
    		panel_close = dce_mode ? false : true;
    		
        	SQ.parentPanel = new YAHOO.widget.Panel("container1", {
                modal: panel_modal,
				visible: true,
            	constraintoviewport: panel_constrain,
                width	: panel_width,
                height : panel_height,
                shadow	: panel_shadow,
                draggable : panel_draggable,
				resize: panel_resize,
				close: panel_close
            });
		
        	if(!dce_mode) {
        		SQ.parentPanel.setHeader( SUGAR.language.get('app_strings','LBL_EMAIL_QUICK_COMPOSE')) ;
        	}
        	
            SQ.parentPanel.setBody("<div class='email'><div id='htmleditordiv" + idx + "'></div></div>");
			
			var composePanel = SE.composeLayout.getQuickComposeLayout(SQ.parentPanel,this.options);
			
			if(!dce_mode) {			
				var resize = new YAHOO.util.Resize('container1', { 
                    handles: ['br'], 
                    autoRatio: false, 
                    minWidth: 400, 
                    minHeight: 350, 
                    status: false
                });
                
                resize.on('resize', function(args) { 
                    var panelHeight = args.height; 
                    this.cfg.setProperty("height", panelHeight + "px");
					var layout = SE.composeLayout[SE.composeLayout.currentInstanceId];
					layout.set("height", panelHeight - 50);
					layout.resize(true);
					SE.composeLayout.resizeEditor(SE.composeLayout.currentInstanceId);
                }, SQ.parentPanel, true);
			}
			
			YAHOO.util.Dom.setStyle("container1", "z-index", 1);
			
			if (!SQ.tinyLoaded)
			{
				//TinyMCE bug, since we are loading the js file dynamically we need to let tiny know that the
				//dom event has fired.  
				tinymce.dom.Event.domLoaded = true;

				tinyMCE.init({
			 		 convert_urls : false,
			         theme_advanced_toolbar_align : tinyConfig.theme_advanced_toolbar_align,
			         width: tinyConfig.width,
			         theme: tinyConfig.theme,
			         theme_advanced_toolbar_location : tinyConfig.theme_advanced_toolbar_location,
			         theme_advanced_buttons1 : tinyConfig.theme_advanced_buttons1,
			         theme_advanced_buttons2 : tinyConfig.theme_advanced_buttons2,
			         theme_advanced_buttons3 : tinyConfig.theme_advanced_buttons3,
			         plugins : tinyConfig.plugins,
			         elements : tinyConfig.elements,
			         language : tinyConfig.language,
			         extended_valid_elements : tinyConfig.extended_valid_elements,
			         mode: tinyConfig.mode,
			         strict_loading_mode : true
		    	 });
				SQ.tinyLoaded = true;
			}
			
			SQ.parentPanel.show();
			
			//Re-declare the close function to handle appropriattely.
			SUGAR.email2.composeLayout.forceCloseCompose = function(o){SUGAR.quickCompose.parentPanel.hide(); }
				
			
			
			if(!dce_mode) {
				SQ.parentPanel.center();
			}
		},
		/**
		 * Display a loading pannel and start retrieving the quick compose requirements.
		 * @method init
		 * @param {Array} o Options containing compose package and full return url.
		 * @return {} none
		 **/
		init: function(o) {

			  if(typeof o.menu_id != 'undefined') {
			     this.dceMenuPanel = o.menu_id;
			  } else {
			     this.dceMenuPanel = null;
			  }
			 
              loadingMessgPanl = new YAHOO.widget.SimpleDialog('loading', {
        			width: '200px',
        			close: true,
        			modal: true,
        			visible:  true,
        			fixedcenter: true,
        	        constraintoviewport: true,
        	        draggable: false
		      });		    	 
			 
              loadingMessgPanl.setHeader(SUGAR.language.get('app_strings','LBL_EMAIL_PERFORMING_TASK'));
		      loadingMessgPanl.setBody(SUGAR.language.get('app_strings','LBL_EMAIL_ONE_MOMENT'));
		      loadingMessgPanl.render(document.body);
		      loadingMessgPanl.show();
                 
		      //If JS files havn't been loaded, perform the load.
		      if(! SUGAR.quickCompose.resourcesLoaded )
		          this.loadResources(o);
		      else
		          this.initUI(o);
		},
		/**
		 * Pull in all the required js files.
		 * @method loadResources
		 * @param {Array} o Options containing compose package and full return url.
		 * @return {} none
		 **/
		loadResources: function(o)
		{
			//IE Bug fix for TinyMCE when pulling in the js file dynamically.
		   	window.skipTinyMCEInitPhase = true;
		    var require = ["layout", "element", "tabview", "menu","cookie","tinymce","securejson","sugarwidgets","sugarquickcompose","sugarquickcomposecss"];
			var loader = new YAHOO.util.YUILoader({
				    require : require,
				    loadOptional: true,
				    skin: { base: 'blank', defaultSkin: '' },
				    data: o,
				    onSuccess: this.initComposePackage,
				    allowRollup: true,
				    base: "include/javascript/yui/build/"
				});
				
				//TiinyMCE cannot be added into the sugar_grp_quickcomp file as it breaks the build, needs to be loaded 
				//seperately.
				loader.addModule({
				    name :"tinymce",
				    type : "js",
				    varName: "TinyMCE",
				    fullpath: "include/javascript/tiny_mce/tiny_mce.js"
				});
				
				loader.addModule({
				    name :"securejson",
				    type : "js",
				    varName: "JSON",
				    fullpath: "include/JSON.js"
				});
				
				//Load the Sugar widgets with dependancies on the yui library.
				loader.addModule({
				    name :"sugarwidgets",
				    type : "js",
				    fullpath: "include/javascript/sugarwidgets/SugarYUIWidgets.js",
				    varName: "YAHOO.SUGAR",
				    requires: ["datatable", "dragdrop", "treeview", "tabview"]
				});
			
				//Load the main components for the quick create compose screen.
				loader.addModule({
				    name :"sugarquickcompose",
				    type : "js",
				    varName: "SUGAR.email2.complexLayout",
				    requires: ["layout", "sugarwidgets", "tinymce"],
				    fullpath: "include/javascript/sugar_grp_quickcomp.js"
				});
				
				//Load the css needed for the quickCompose.
				loader.addModule({
				    name :"sugarquickcomposecss",
				    type : "css",
				    fullpath: "modules/Emails/EmailUI.css"
				});
				
				loader.insert();
	    }
	};
}();// End of File include/javascript/quickCompose.js
                                
/*
Copyright (c) 2009, Yahoo! Inc. All rights reserved.
Code licensed under the BSD License:
http://developer.yahoo.net/yui/license.txt
version: 2.8.0r4
*/
if(typeof YAHOO=="undefined"||!YAHOO){var YAHOO={};}YAHOO.namespace=function(){var A=arguments,E=null,C,B,D;for(C=0;C<A.length;C=C+1){D=(""+A[C]).split(".");E=YAHOO;for(B=(D[0]=="YAHOO")?1:0;B<D.length;B=B+1){E[D[B]]=E[D[B]]||{};E=E[D[B]];}}return E;};YAHOO.log=function(D,A,C){var B=YAHOO.widget.Logger;if(B&&B.log){return B.log(D,A,C);}else{return false;}};YAHOO.register=function(A,E,D){var I=YAHOO.env.modules,B,H,G,F,C;if(!I[A]){I[A]={versions:[],builds:[]};}B=I[A];H=D.version;G=D.build;F=YAHOO.env.listeners;B.name=A;B.version=H;B.build=G;B.versions.push(H);B.builds.push(G);B.mainClass=E;for(C=0;C<F.length;C=C+1){F[C](B);}if(E){E.VERSION=H;E.BUILD=G;}else{YAHOO.log("mainClass is undefined for module "+A,"warn");}};YAHOO.env=YAHOO.env||{modules:[],listeners:[]};YAHOO.env.getVersion=function(A){return YAHOO.env.modules[A]||null;};YAHOO.env.ua=function(){var D=function(H){var I=0;return parseFloat(H.replace(/\./g,function(){return(I++==1)?"":".";}));},G=navigator,F={ie:0,opera:0,gecko:0,webkit:0,mobile:null,air:0,caja:G.cajaVersion,secure:false,os:null},C=navigator&&navigator.userAgent,E=window&&window.location,B=E&&E.href,A;F.secure=B&&(B.toLowerCase().indexOf("https")===0);if(C){if((/windows|win32/i).test(C)){F.os="windows";}else{if((/macintosh/i).test(C)){F.os="macintosh";}}if((/KHTML/).test(C)){F.webkit=1;}A=C.match(/AppleWebKit\/([^\s]*)/);if(A&&A[1]){F.webkit=D(A[1]);if(/ Mobile\//.test(C)){F.mobile="Apple";}else{A=C.match(/NokiaN[^\/]*/);if(A){F.mobile=A[0];}}A=C.match(/AdobeAIR\/([^\s]*)/);if(A){F.air=A[0];}}if(!F.webkit){A=C.match(/Opera[\s\/]([^\s]*)/);if(A&&A[1]){F.opera=D(A[1]);A=C.match(/Opera Mini[^;]*/);if(A){F.mobile=A[0];}}else{A=C.match(/MSIE\s([^;]*)/);if(A&&A[1]){F.ie=D(A[1]);}else{A=C.match(/Gecko\/([^\s]*)/);if(A){F.gecko=1;A=C.match(/rv:([^\s\)]*)/);if(A&&A[1]){F.gecko=D(A[1]);}}}}}}return F;}();(function(){YAHOO.namespace("util","widget","example");if("undefined"!==typeof YAHOO_config){var B=YAHOO_config.listener,A=YAHOO.env.listeners,D=true,C;if(B){for(C=0;C<A.length;C++){if(A[C]==B){D=false;break;}}if(D){A.push(B);}}}})();YAHOO.lang=YAHOO.lang||{};(function(){var B=YAHOO.lang,A=Object.prototype,H="[object Array]",C="[object Function]",G="[object Object]",E=[],F=["toString","valueOf"],D={isArray:function(I){return A.toString.apply(I)===H;},isBoolean:function(I){return typeof I==="boolean";},isFunction:function(I){return(typeof I==="function")||A.toString.apply(I)===C;},isNull:function(I){return I===null;},isNumber:function(I){return typeof I==="number"&&isFinite(I);},isObject:function(I){return(I&&(typeof I==="object"||B.isFunction(I)))||false;},isString:function(I){return typeof I==="string";},isUndefined:function(I){return typeof I==="undefined";},_IEEnumFix:(YAHOO.env.ua.ie)?function(K,J){var I,M,L;for(I=0;I<F.length;I=I+1){M=F[I];L=J[M];if(B.isFunction(L)&&L!=A[M]){K[M]=L;}}}:function(){},extend:function(L,M,K){if(!M||!L){throw new Error("extend failed, please check that "+"all dependencies are included.");}var J=function(){},I;J.prototype=M.prototype;L.prototype=new J();L.prototype.constructor=L;L.superclass=M.prototype;if(M.prototype.constructor==A.constructor){M.prototype.constructor=M;}if(K){for(I in K){if(B.hasOwnProperty(K,I)){L.prototype[I]=K[I];}}B._IEEnumFix(L.prototype,K);}},augmentObject:function(M,L){if(!L||!M){throw new Error("Absorb failed, verify dependencies.");}var I=arguments,K,N,J=I[2];if(J&&J!==true){for(K=2;K<I.length;K=K+1){M[I[K]]=L[I[K]];}}else{for(N in L){if(J||!(N in M)){M[N]=L[N];}}B._IEEnumFix(M,L);}},augmentProto:function(L,K){if(!K||!L){throw new Error("Augment failed, verify dependencies.");}var I=[L.prototype,K.prototype],J;for(J=2;J<arguments.length;J=J+1){I.push(arguments[J]);}B.augmentObject.apply(this,I);},dump:function(I,N){var K,M,P=[],Q="{...}",J="f(){...}",O=", ",L=" => ";if(!B.isObject(I)){return I+"";}else{if(I instanceof Date||("nodeType" in I&&"tagName" in I)){return I;}else{if(B.isFunction(I)){return J;}}}N=(B.isNumber(N))?N:3;if(B.isArray(I)){P.push("[");for(K=0,M=I.length;K<M;K=K+1){if(B.isObject(I[K])){P.push((N>0)?B.dump(I[K],N-1):Q);}else{P.push(I[K]);}P.push(O);}if(P.length>1){P.pop();}P.push("]");}else{P.push("{");for(K in I){if(B.hasOwnProperty(I,K)){P.push(K+L);if(B.isObject(I[K])){P.push((N>0)?B.dump(I[K],N-1):Q);}else{P.push(I[K]);}P.push(O);}}if(P.length>1){P.pop();}P.push("}");}return P.join("");},substitute:function(Y,J,R){var N,M,L,U,V,X,T=[],K,O="dump",S=" ",I="{",W="}",Q,P;for(;;){N=Y.lastIndexOf(I);if(N<0){break;}M=Y.indexOf(W,N);if(N+1>=M){break;}K=Y.substring(N+1,M);U=K;X=null;L=U.indexOf(S);if(L>-1){X=U.substring(L+1);U=U.substring(0,L);}V=J[U];if(R){V=R(U,V,X);}if(B.isObject(V)){if(B.isArray(V)){V=B.dump(V,parseInt(X,10));}else{X=X||"";Q=X.indexOf(O);if(Q>-1){X=X.substring(4);}P=V.toString();if(P===G||Q>-1){V=B.dump(V,parseInt(X,10));}else{V=P;}}}else{if(!B.isString(V)&&!B.isNumber(V)){V="~-"+T.length+"-~";T[T.length]=K;}}Y=Y.substring(0,N)+V+Y.substring(M+1);}for(N=T.length-1;N>=0;N=N-1){Y=Y.replace(new RegExp("~-"+N+"-~"),"{"+T[N]+"}","g");}return Y;},trim:function(I){try{return I.replace(/^\s+|\s+$/g,"");}catch(J){return I;}},merge:function(){var L={},J=arguments,I=J.length,K;for(K=0;K<I;K=K+1){B.augmentObject(L,J[K],true);}return L;},later:function(P,J,Q,L,M){P=P||0;J=J||{};var K=Q,O=L,N,I;if(B.isString(Q)){K=J[Q];}if(!K){throw new TypeError("method undefined");}if(O&&!B.isArray(O)){O=[L];}N=function(){K.apply(J,O||E);};I=(M)?setInterval(N,P):setTimeout(N,P);return{interval:M,cancel:function(){if(this.interval){clearInterval(I);}else{clearTimeout(I);}}};},isValue:function(I){return(B.isObject(I)||B.isString(I)||B.isNumber(I)||B.isBoolean(I));}};B.hasOwnProperty=(A.hasOwnProperty)?function(I,J){return I&&I.hasOwnProperty(J);}:function(I,J){return !B.isUndefined(I[J])&&I.constructor.prototype[J]!==I[J];};D.augmentObject(B,D,true);YAHOO.util.Lang=B;B.augment=B.augmentProto;YAHOO.augment=B.augmentProto;YAHOO.extend=B.extend;})();YAHOO.register("yahoo",YAHOO,{version:"2.8.0r4",build:"2449"});
YAHOO.util.Get=function(){var M={},L=0,R=0,E=false,N=YAHOO.env.ua,S=YAHOO.lang;var J=function(W,T,X){var U=X||window,Y=U.document,Z=Y.createElement(W);for(var V in T){if(T[V]&&YAHOO.lang.hasOwnProperty(T,V)){Z.setAttribute(V,T[V]);}}return Z;};var I=function(U,V,T){var W={id:"yui__dyn_"+(R++),type:"text/css",rel:"stylesheet",href:U};if(T){S.augmentObject(W,T);}return J("link",W,V);};var P=function(U,V,T){var W={id:"yui__dyn_"+(R++),type:"text/javascript",src:U};if(T){S.augmentObject(W,T);}return J("script",W,V);};var A=function(T,U){return{tId:T.tId,win:T.win,data:T.data,nodes:T.nodes,msg:U,purge:function(){D(this.tId);}};};var B=function(T,W){var U=M[W],V=(S.isString(T))?U.win.document.getElementById(T):T;if(!V){Q(W,"target node not found: "+T);}return V;};var Q=function(W,V){var T=M[W];if(T.onFailure){var U=T.scope||T.win;T.onFailure.call(U,A(T,V));}};var C=function(W){var T=M[W];T.finished=true;if(T.aborted){var V="transaction "+W+" was aborted";Q(W,V);return;}if(T.onSuccess){var U=T.scope||T.win;T.onSuccess.call(U,A(T));}};var O=function(V){var T=M[V];if(T.onTimeout){var U=T.scope||T;T.onTimeout.call(U,A(T));}};var G=function(V,Z){var U=M[V];if(U.timer){U.timer.cancel();}if(U.aborted){var X="transaction "+V+" was aborted";Q(V,X);return;}if(Z){U.url.shift();if(U.varName){U.varName.shift();}}else{U.url=(S.isString(U.url))?[U.url]:U.url;if(U.varName){U.varName=(S.isString(U.varName))?[U.varName]:U.varName;}}var c=U.win,b=c.document,a=b.getElementsByTagName("head")[0],W;if(U.url.length===0){if(U.type==="script"&&N.webkit&&N.webkit<420&&!U.finalpass&&!U.varName){var Y=P(null,U.win,U.attributes);Y.innerHTML='YAHOO.util.Get._finalize("'+V+'");';U.nodes.push(Y);a.appendChild(Y);}else{C(V);}return;}var T=U.url[0];if(!T){U.url.shift();return G(V);}if(U.timeout){U.timer=S.later(U.timeout,U,O,V);}if(U.type==="script"){W=P(T,c,U.attributes);}else{W=I(T,c,U.attributes);}F(U.type,W,V,T,c,U.url.length);U.nodes.push(W);if(U.insertBefore){var e=B(U.insertBefore,V);if(e){e.parentNode.insertBefore(W,e);}}else{a.appendChild(W);}if((N.webkit||N.gecko)&&U.type==="css"){G(V,T);}};var K=function(){if(E){return;}E=true;for(var T in M){var U=M[T];if(U.autopurge&&U.finished){D(U.tId);delete M[T];}}E=false;};var D=function(Z){if(M[Z]){var T=M[Z],U=T.nodes,X=U.length,c=T.win.document,a=c.getElementsByTagName("head")[0],V,Y,W,b;if(T.insertBefore){V=B(T.insertBefore,Z);if(V){a=V.parentNode;}}for(Y=0;Y<X;Y=Y+1){W=U[Y];if(W.clearAttributes){W.clearAttributes();}else{for(b in W){delete W[b];}}a.removeChild(W);}T.nodes=[];}};var H=function(U,T,V){var X="q"+(L++);V=V||{};if(L%YAHOO.util.Get.PURGE_THRESH===0){K();}M[X]=S.merge(V,{tId:X,type:U,url:T,finished:false,aborted:false,nodes:[]});var W=M[X];W.win=W.win||window;W.scope=W.scope||W.win;W.autopurge=("autopurge" in W)?W.autopurge:(U==="script")?true:false;if(V.charset){W.attributes=W.attributes||{};W.attributes.charset=V.charset;}S.later(0,W,G,X);return{tId:X};};var F=function(c,X,W,U,Y,Z,b){var a=b||G;if(N.ie){X.onreadystatechange=function(){var d=this.readyState;if("loaded"===d||"complete"===d){X.onreadystatechange=null;a(W,U);}};}else{if(N.webkit){if(c==="script"){if(N.webkit>=420){X.addEventListener("load",function(){a(W,U);});}else{var T=M[W];if(T.varName){var V=YAHOO.util.Get.POLL_FREQ;T.maxattempts=YAHOO.util.Get.TIMEOUT/V;T.attempts=0;T._cache=T.varName[0].split(".");T.timer=S.later(V,T,function(j){var f=this._cache,e=f.length,d=this.win,g;for(g=0;g<e;g=g+1){d=d[f[g]];if(!d){this.attempts++;if(this.attempts++>this.maxattempts){var h="Over retry limit, giving up";T.timer.cancel();Q(W,h);}else{}return;}}T.timer.cancel();a(W,U);},null,true);}else{S.later(YAHOO.util.Get.POLL_FREQ,null,a,[W,U]);}}}}else{X.onload=function(){a(W,U);};}}};return{POLL_FREQ:10,PURGE_THRESH:20,TIMEOUT:2000,_finalize:function(T){S.later(0,null,C,T);},abort:function(U){var V=(S.isString(U))?U:U.tId;var T=M[V];if(T){T.aborted=true;}},script:function(T,U){return H("script",T,U);},css:function(T,U){return H("css",T,U);}};}();YAHOO.register("get",YAHOO.util.Get,{version:"2.8.0r4",build:"2449"});(function(){var Y=YAHOO,util=Y.util,lang=Y.lang,env=Y.env,PROV="_provides",SUPER="_supersedes",REQ="expanded",AFTER="_after";var YUI={dupsAllowed:{"yahoo":true,"get":true},info:{"root":"2.8.0r4/build/","base":"http://yui.yahooapis.com/2.8.0r4/build/","comboBase":"http://yui.yahooapis.com/combo?","skin":{"defaultSkin":"sam","base":"assets/skins/","path":"skin.css","after":["reset","fonts","grids","base"],"rollup":3},dupsAllowed:["yahoo","get"],"moduleInfo":{"animation":{"type":"js","path":"animation/animation-min.js","requires":["dom","event"]},"autocomplete":{"type":"js","path":"autocomplete/autocomplete-min.js","requires":["dom","event","datasource"],"optional":["connection","animation"],"skinnable":true},"base":{"type":"css","path":"base/base-min.css","after":["reset","fonts","grids"]},"button":{"type":"js","path":"button/button-min.js","requires":["element"],"optional":["menu"],"skinnable":true},"calendar":{"type":"js","path":"calendar/calendar-min.js","requires":["event","dom"],supersedes:["datemeth"],"skinnable":true},"carousel":{"type":"js","path":"carousel/carousel-min.js","requires":["element"],"optional":["animation"],"skinnable":true},"charts":{"type":"js","path":"charts/charts-min.js","requires":["element","json","datasource","swf"]},"colorpicker":{"type":"js","path":"colorpicker/colorpicker-min.js","requires":["slider","element"],"optional":["animation"],"skinnable":true},"connection":{"type":"js","path":"connection/connection-min.js","requires":["event"],"supersedes":["connectioncore"]},"connectioncore":{"type":"js","path":"connection/connection_core-min.js","requires":["event"],"pkg":"connection"},"container":{"type":"js","path":"container/container-min.js","requires":["dom","event"],"optional":["dragdrop","animation","connection"],"supersedes":["containercore"],"skinnable":true},"containercore":{"type":"js","path":"container/container_core-min.js","requires":["dom","event"],"pkg":"container"},"cookie":{"type":"js","path":"cookie/cookie-min.js","requires":["yahoo"]},"datasource":{"type":"js","path":"datasource/datasource-min.js","requires":["event"],"optional":["connection"]},"datatable":{"type":"js","path":"datatable/datatable-min.js","requires":["element","datasource"],"optional":["calendar","dragdrop","paginator"],"skinnable":true},datemath:{"type":"js","path":"datemath/datemath-min.js","requires":["yahoo"]},"dom":{"type":"js","path":"dom/dom-min.js","requires":["yahoo"]},"dragdrop":{"type":"js","path":"dragdrop/dragdrop-min.js","requires":["dom","event"]},"editor":{"type":"js","path":"editor/editor-min.js","requires":["menu","element","button"],"optional":["animation","dragdrop"],"supersedes":["simpleeditor"],"skinnable":true},"element":{"type":"js","path":"element/element-min.js","requires":["dom","event"],"optional":["event-mouseenter","event-delegate"]},"element-delegate":{"type":"js","path":"element-delegate/element-delegate-min.js","requires":["element"]},"event":{"type":"js","path":"event/event-min.js","requires":["yahoo"]},"event-simulate":{"type":"js","path":"event-simulate/event-simulate-min.js","requires":["event"]},"event-delegate":{"type":"js","path":"event-delegate/event-delegate-min.js","requires":["event"],"optional":["selector"]},"event-mouseenter":{"type":"js","path":"event-mouseenter/event-mouseenter-min.js","requires":["dom","event"]},"fonts":{"type":"css","path":"fonts/fonts-min.css"},"get":{"type":"js","path":"get/get-min.js","requires":["yahoo"]},"grids":{"type":"css","path":"grids/grids-min.css","requires":["fonts"],"optional":["reset"]},"history":{"type":"js","path":"history/history-min.js","requires":["event"]},"imagecropper":{"type":"js","path":"imagecropper/imagecropper-min.js","requires":["dragdrop","element","resize"],"skinnable":true},"imageloader":{"type":"js","path":"imageloader/imageloader-min.js","requires":["event","dom"]},"json":{"type":"js","path":"json/json-min.js","requires":["yahoo"]},"layout":{"type":"js","path":"layout/layout-min.js","requires":["element"],"optional":["animation","dragdrop","resize","selector"],"skinnable":true},"logger":{"type":"js","path":"logger/logger-min.js","requires":["event","dom"],"optional":["dragdrop"],"skinnable":true},"menu":{"type":"js","path":"menu/menu-min.js","requires":["containercore"],"skinnable":true},"paginator":{"type":"js","path":"paginator/paginator-min.js","requires":["element"],"skinnable":true},"profiler":{"type":"js","path":"profiler/profiler-min.js","requires":["yahoo"]},"profilerviewer":{"type":"js","path":"profilerviewer/profilerviewer-min.js","requires":["profiler","yuiloader","element"],"skinnable":true},"progressbar":{"type":"js","path":"progressbar/progressbar-min.js","requires":["element"],"optional":["animation"],"skinnable":true},"reset":{"type":"css","path":"reset/reset-min.css"},"reset-fonts-grids":{"type":"css","path":"reset-fonts-grids/reset-fonts-grids.css","supersedes":["reset","fonts","grids","reset-fonts"],"rollup":4},"reset-fonts":{"type":"css","path":"reset-fonts/reset-fonts.css","supersedes":["reset","fonts"],"rollup":2},"resize":{"type":"js","path":"resize/resize-min.js","requires":["dragdrop","element"],"optional":["animation"],"skinnable":true},"selector":{"type":"js","path":"selector/selector-min.js","requires":["yahoo","dom"]},"simpleeditor":{"type":"js","path":"editor/simpleeditor-min.js","requires":["element"],"optional":["containercore","menu","button","animation","dragdrop"],"skinnable":true,"pkg":"editor"},"slider":{"type":"js","path":"slider/slider-min.js","requires":["dragdrop"],"optional":["animation"],"skinnable":true},"storage":{"type":"js","path":"storage/storage-min.js","requires":["yahoo","event","cookie"],"optional":["swfstore"]},"stylesheet":{"type":"js","path":"stylesheet/stylesheet-min.js","requires":["yahoo"]},"swf":{"type":"js","path":"swf/swf-min.js","requires":["element"],"supersedes":["swfdetect"]},"swfdetect":{"type":"js","path":"swfdetect/swfdetect-min.js","requires":["yahoo"]},"swfstore":{"type":"js","path":"swfstore/swfstore-min.js","requires":["element","cookie","swf"]},"tabview":{"type":"js","path":"tabview/tabview-min.js","requires":["element"],"optional":["connection"],"skinnable":true},"treeview":{"type":"js","path":"treeview/treeview-min.js","requires":["event","dom"],"optional":["json","animation","calendar"],"skinnable":true},"uploader":{"type":"js","path":"uploader/uploader-min.js","requires":["element"]},"utilities":{"type":"js","path":"utilities/utilities.js","supersedes":["yahoo","event","dragdrop","animation","dom","connection","element","yahoo-dom-event","get","yuiloader","yuiloader-dom-event"],"rollup":8},"yahoo":{"type":"js","path":"yahoo/yahoo-min.js"},"yahoo-dom-event":{"type":"js","path":"yahoo-dom-event/yahoo-dom-event.js","supersedes":["yahoo","event","dom"],"rollup":3},"yuiloader":{"type":"js","path":"yuiloader/yuiloader-min.js","supersedes":["yahoo","get"]},"yuiloader-dom-event":{"type":"js","path":"yuiloader-dom-event/yuiloader-dom-event.js","supersedes":["yahoo","dom","event","get","yuiloader","yahoo-dom-event"],"rollup":5},"yuitest":{"type":"js","path":"yuitest/yuitest-min.js","requires":["logger"],"optional":["event-simulate"],"skinnable":true}}},ObjectUtil:{appendArray:function(o,a){if(a){for(var i=0;
i<a.length;i=i+1){o[a[i]]=true;}}},keys:function(o,ordered){var a=[],i;for(i in o){if(lang.hasOwnProperty(o,i)){a.push(i);}}return a;}},ArrayUtil:{appendArray:function(a1,a2){Array.prototype.push.apply(a1,a2);},indexOf:function(a,val){for(var i=0;i<a.length;i=i+1){if(a[i]===val){return i;}}return -1;},toObject:function(a){var o={};for(var i=0;i<a.length;i=i+1){o[a[i]]=true;}return o;},uniq:function(a){return YUI.ObjectUtil.keys(YUI.ArrayUtil.toObject(a));}}};YAHOO.util.YUILoader=function(o){this._internalCallback=null;this._useYahooListener=false;this.onSuccess=null;this.onFailure=Y.log;this.onProgress=null;this.onTimeout=null;this.scope=this;this.data=null;this.insertBefore=null;this.charset=null;this.varName=null;this.base=YUI.info.base;this.comboBase=YUI.info.comboBase;this.combine=false;this.root=YUI.info.root;this.timeout=0;this.ignore=null;this.force=null;this.allowRollup=true;this.filter=null;this.required={};this.moduleInfo=lang.merge(YUI.info.moduleInfo);this.rollups=null;this.loadOptional=false;this.sorted=[];this.loaded={};this.dirty=true;this.inserted={};var self=this;env.listeners.push(function(m){if(self._useYahooListener){self.loadNext(m.name);}});this.skin=lang.merge(YUI.info.skin);this._config(o);};Y.util.YUILoader.prototype={FILTERS:{RAW:{"searchExp":"-min\\.js","replaceStr":".js"},DEBUG:{"searchExp":"-min\\.js","replaceStr":"-debug.js"}},SKIN_PREFIX:"skin-",_config:function(o){if(o){for(var i in o){if(lang.hasOwnProperty(o,i)){if(i=="require"){this.require(o[i]);}else{this[i]=o[i];}}}}var f=this.filter;if(lang.isString(f)){f=f.toUpperCase();if(f==="DEBUG"){this.require("logger");}if(!Y.widget.LogWriter){Y.widget.LogWriter=function(){return Y;};}this.filter=this.FILTERS[f];}},addModule:function(o){if(!o||!o.name||!o.type||(!o.path&&!o.fullpath)){return false;}o.ext=("ext" in o)?o.ext:true;o.requires=o.requires||[];this.moduleInfo[o.name]=o;this.dirty=true;return true;},require:function(what){var a=(typeof what==="string")?arguments:what;this.dirty=true;YUI.ObjectUtil.appendArray(this.required,a);},_addSkin:function(skin,mod){var name=this.formatSkin(skin),info=this.moduleInfo,sinf=this.skin,ext=info[mod]&&info[mod].ext;if(!info[name]){this.addModule({"name":name,"type":"css","path":sinf.base+skin+"/"+sinf.path,"after":sinf.after,"rollup":sinf.rollup,"ext":ext});}if(mod){name=this.formatSkin(skin,mod);if(!info[name]){var mdef=info[mod],pkg=mdef.pkg||mod;this.addModule({"name":name,"type":"css","after":sinf.after,"path":pkg+"/"+sinf.base+skin+"/"+mod+".css","ext":ext});}}return name;},getRequires:function(mod){if(!mod){return[];}if(!this.dirty&&mod.expanded){return mod.expanded;}mod.requires=mod.requires||[];var i,d=[],r=mod.requires,o=mod.optional,info=this.moduleInfo,m;for(i=0;i<r.length;i=i+1){d.push(r[i]);m=info[r[i]];YUI.ArrayUtil.appendArray(d,this.getRequires(m));}if(o&&this.loadOptional){for(i=0;i<o.length;i=i+1){d.push(o[i]);YUI.ArrayUtil.appendArray(d,this.getRequires(info[o[i]]));}}mod.expanded=YUI.ArrayUtil.uniq(d);return mod.expanded;},getProvides:function(name,notMe){var addMe=!(notMe),ckey=(addMe)?PROV:SUPER,m=this.moduleInfo[name],o={};if(!m){return o;}if(m[ckey]){return m[ckey];}var s=m.supersedes,done={},me=this;var add=function(mm){if(!done[mm]){done[mm]=true;lang.augmentObject(o,me.getProvides(mm));}};if(s){for(var i=0;i<s.length;i=i+1){add(s[i]);}}m[SUPER]=o;m[PROV]=lang.merge(o);m[PROV][name]=true;return m[ckey];},calculate:function(o){if(o||this.dirty){this._config(o);this._setup();this._explode();if(this.allowRollup){this._rollup();}this._reduce();this._sort();this.dirty=false;}},_setup:function(){var info=this.moduleInfo,name,i,j;for(name in info){if(lang.hasOwnProperty(info,name)){var m=info[name];if(m&&m.skinnable){var o=this.skin.overrides,smod;if(o&&o[name]){for(i=0;i<o[name].length;i=i+1){smod=this._addSkin(o[name][i],name);}}else{smod=this._addSkin(this.skin.defaultSkin,name);}m.requires.push(smod);}}}var l=lang.merge(this.inserted);if(!this._sandbox){l=lang.merge(l,env.modules);}if(this.ignore){YUI.ObjectUtil.appendArray(l,this.ignore);}if(this.force){for(i=0;i<this.force.length;i=i+1){if(this.force[i] in l){delete l[this.force[i]];}}}for(j in l){if(lang.hasOwnProperty(l,j)){lang.augmentObject(l,this.getProvides(j));}}this.loaded=l;},_explode:function(){var r=this.required,i,mod;for(i in r){if(lang.hasOwnProperty(r,i)){mod=this.moduleInfo[i];if(mod){var req=this.getRequires(mod);if(req){YUI.ObjectUtil.appendArray(r,req);}}}}},_skin:function(){},formatSkin:function(skin,mod){var s=this.SKIN_PREFIX+skin;if(mod){s=s+"-"+mod;}return s;},parseSkin:function(mod){if(mod.indexOf(this.SKIN_PREFIX)===0){var a=mod.split("-");return{skin:a[1],module:a[2]};}return null;},_rollup:function(){var i,j,m,s,rollups={},r=this.required,roll,info=this.moduleInfo;if(this.dirty||!this.rollups){for(i in info){if(lang.hasOwnProperty(info,i)){m=info[i];if(m&&m.rollup){rollups[i]=m;}}}this.rollups=rollups;}for(;;){var rolled=false;for(i in rollups){if(!r[i]&&!this.loaded[i]){m=info[i];s=m.supersedes;roll=false;if(!m.rollup){continue;}var skin=(m.ext)?false:this.parseSkin(i),c=0;if(skin){for(j in r){if(lang.hasOwnProperty(r,j)){if(i!==j&&this.parseSkin(j)){c++;roll=(c>=m.rollup);if(roll){break;}}}}}else{for(j=0;j<s.length;j=j+1){if(this.loaded[s[j]]&&(!YUI.dupsAllowed[s[j]])){roll=false;break;}else{if(r[s[j]]){c++;roll=(c>=m.rollup);if(roll){break;}}}}}if(roll){r[i]=true;rolled=true;this.getRequires(m);}}}if(!rolled){break;}}},_reduce:function(){var i,j,s,m,r=this.required;for(i in r){if(i in this.loaded){delete r[i];}else{var skinDef=this.parseSkin(i);if(skinDef){if(!skinDef.module){var skin_pre=this.SKIN_PREFIX+skinDef.skin;for(j in r){if(lang.hasOwnProperty(r,j)){m=this.moduleInfo[j];var ext=m&&m.ext;if(!ext&&j!==i&&j.indexOf(skin_pre)>-1){delete r[j];}}}}}else{m=this.moduleInfo[i];s=m&&m.supersedes;if(s){for(j=0;j<s.length;j=j+1){if(s[j] in r){delete r[s[j]];}}}}}}},_onFailure:function(msg){YAHOO.log("Failure","info","loader");var f=this.onFailure;if(f){f.call(this.scope,{msg:"failure: "+msg,data:this.data,success:false});
}},_onTimeout:function(){YAHOO.log("Timeout","info","loader");var f=this.onTimeout;if(f){f.call(this.scope,{msg:"timeout",data:this.data,success:false});}},_sort:function(){var s=[],info=this.moduleInfo,loaded=this.loaded,checkOptional=!this.loadOptional,me=this;var requires=function(aa,bb){var mm=info[aa];if(loaded[bb]||!mm){return false;}var ii,rr=mm.expanded,after=mm.after,other=info[bb],optional=mm.optional;if(rr&&YUI.ArrayUtil.indexOf(rr,bb)>-1){return true;}if(after&&YUI.ArrayUtil.indexOf(after,bb)>-1){return true;}if(checkOptional&&optional&&YUI.ArrayUtil.indexOf(optional,bb)>-1){return true;}var ss=info[bb]&&info[bb].supersedes;if(ss){for(ii=0;ii<ss.length;ii=ii+1){if(requires(aa,ss[ii])){return true;}}}if(mm.ext&&mm.type=="css"&&!other.ext&&other.type=="css"){return true;}return false;};for(var i in this.required){if(lang.hasOwnProperty(this.required,i)){s.push(i);}}var p=0;for(;;){var l=s.length,a,b,j,k,moved=false;for(j=p;j<l;j=j+1){a=s[j];for(k=j+1;k<l;k=k+1){if(requires(a,s[k])){b=s.splice(k,1);s.splice(j,0,b[0]);moved=true;break;}}if(moved){break;}else{p=p+1;}}if(!moved){break;}}this.sorted=s;},toString:function(){var o={type:"YUILoader",base:this.base,filter:this.filter,required:this.required,loaded:this.loaded,inserted:this.inserted};lang.dump(o,1);},_combine:function(){this._combining=[];var self=this,s=this.sorted,len=s.length,js=this.comboBase,css=this.comboBase,target,startLen=js.length,i,m,type=this.loadType;YAHOO.log("type "+type);for(i=0;i<len;i=i+1){m=this.moduleInfo[s[i]];if(m&&!m.ext&&(!type||type===m.type)){target=this.root+m.path;target+="&";if(m.type=="js"){js+=target;}else{css+=target;}this._combining.push(s[i]);}}if(this._combining.length){YAHOO.log("Attempting to combine: "+this._combining,"info","loader");var callback=function(o){var c=this._combining,len=c.length,i,m;for(i=0;i<len;i=i+1){this.inserted[c[i]]=true;}this.loadNext(o.data);},loadScript=function(){if(js.length>startLen){YAHOO.util.Get.script(self._filter(js),{data:self._loading,onSuccess:callback,onFailure:self._onFailure,onTimeout:self._onTimeout,insertBefore:self.insertBefore,charset:self.charset,timeout:self.timeout,scope:self});}};if(css.length>startLen){YAHOO.util.Get.css(this._filter(css),{data:this._loading,onSuccess:loadScript,onFailure:this._onFailure,onTimeout:this._onTimeout,insertBefore:this.insertBefore,charset:this.charset,timeout:this.timeout,scope:self});}else{loadScript();}return;}else{this.loadNext(this._loading);}},insert:function(o,type){this.calculate(o);this._loading=true;this.loadType=type;if(this.combine){return this._combine();}if(!type){var self=this;this._internalCallback=function(){self._internalCallback=null;self.insert(null,"js");};this.insert(null,"css");return;}this.loadNext();},sandbox:function(o,type){this._config(o);if(!this.onSuccess){throw new Error("You must supply an onSuccess handler for your sandbox");}this._sandbox=true;var self=this;if(!type||type!=="js"){this._internalCallback=function(){self._internalCallback=null;self.sandbox(null,"js");};this.insert(null,"css");return;}if(!util.Connect){var ld=new YAHOO.util.YUILoader();ld.insert({base:this.base,filter:this.filter,require:"connection",insertBefore:this.insertBefore,charset:this.charset,onSuccess:function(){this.sandbox(null,"js");},scope:this},"js");return;}this._scriptText=[];this._loadCount=0;this._stopCount=this.sorted.length;this._xhr=[];this.calculate();var s=this.sorted,l=s.length,i,m,url;for(i=0;i<l;i=i+1){m=this.moduleInfo[s[i]];if(!m){this._onFailure("undefined module "+m);for(var j=0;j<this._xhr.length;j=j+1){this._xhr[j].abort();}return;}if(m.type!=="js"){this._loadCount++;continue;}url=m.fullpath;url=(url)?this._filter(url):this._url(m.path);var xhrData={success:function(o){var idx=o.argument[0],name=o.argument[2];this._scriptText[idx]=o.responseText;if(this.onProgress){this.onProgress.call(this.scope,{name:name,scriptText:o.responseText,xhrResponse:o,data:this.data});}this._loadCount++;if(this._loadCount>=this._stopCount){var v=this.varName||"YAHOO";var t="(function() {\n";var b="\nreturn "+v+";\n})();";var ref=eval(t+this._scriptText.join("\n")+b);this._pushEvents(ref);if(ref){this.onSuccess.call(this.scope,{reference:ref,data:this.data});}else{this._onFailure.call(this.varName+" reference failure");}}},failure:function(o){this.onFailure.call(this.scope,{msg:"XHR failure",xhrResponse:o,data:this.data});},scope:this,argument:[i,url,s[i]]};this._xhr.push(util.Connect.asyncRequest("GET",url,xhrData));}},loadNext:function(mname){if(!this._loading){return;}if(mname){if(mname!==this._loading){return;}this.inserted[mname]=true;if(this.onProgress){this.onProgress.call(this.scope,{name:mname,data:this.data});}}var s=this.sorted,len=s.length,i,m;for(i=0;i<len;i=i+1){if(s[i] in this.inserted){continue;}if(s[i]===this._loading){return;}m=this.moduleInfo[s[i]];if(!m){this.onFailure.call(this.scope,{msg:"undefined module "+m,data:this.data});return;}if(!this.loadType||this.loadType===m.type){this._loading=s[i];var fn=(m.type==="css")?util.Get.css:util.Get.script,url=m.fullpath,self=this,c=function(o){self.loadNext(o.data);};url=(url)?this._filter(url):this._url(m.path);if(env.ua.webkit&&env.ua.webkit<420&&m.type==="js"&&!m.varName){c=null;this._useYahooListener=true;}fn(url,{data:s[i],onSuccess:c,onFailure:this._onFailure,onTimeout:this._onTimeout,insertBefore:this.insertBefore,charset:this.charset,timeout:this.timeout,varName:m.varName,scope:self});return;}}this._loading=null;if(this._internalCallback){var f=this._internalCallback;this._internalCallback=null;f.call(this);}else{if(this.onSuccess){this._pushEvents();this.onSuccess.call(this.scope,{data:this.data});}}},_pushEvents:function(ref){var r=ref||YAHOO;if(r.util&&r.util.Event){r.util.Event._load();}},_filter:function(str){var f=this.filter;return(f)?str.replace(new RegExp(f.searchExp,"g"),f.replaceStr):str;},_url:function(path){return this._filter((this.base||"")+path);}};})();YAHOO.register("yuiloader",YAHOO.util.YUILoader,{version:"2.8.0r4",build:"2449"});
// End of File include/javascript/yui/build/yuiloader/yuiloader-min.js
                                
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Sales Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/eula/sugarcrm-sales-subscription-agreement.html
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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/**
 * The current namespaces.
 */
if ( typeof(SUGAR) == 'undefined' ) SUGAR = {};
if ( typeof(SUGAR.util) == 'undefined' ) SUGAR.util = {};
if ( typeof(SUGAR.expressions) == 'undefined' ) SUGAR.expressions= {};


/**
 * Constructs an Expression object given the parameters.
 */
SUGAR.expressions.Expression = function() {
};


/**
 * Designates an infinite number of parameters.
 */
SUGAR.expressions.Expression.INFINITY = -1;

/**
 * The various types supported by Expression.
 */
SUGAR.expressions.Expression.STRING_TYPE   	= "string";
SUGAR.expressions.Expression.NUMERIC_TYPE  	= "number";
SUGAR.expressions.Expression.DATE_TYPE 	 	= "date";
SUGAR.expressions.Expression.TIME_TYPE 	 	= "time";
SUGAR.expressions.Expression.BOOLEAN_TYPE 	= "boolean";
SUGAR.expressions.Expression.ENUM_TYPE 	 	= "enum";
SUGAR.expressions.Expression.GENERIC_TYPE  	= "generic";

/**
 * The two boolean values.
 */
SUGAR.expressions.Expression.TRUE  = "true";
SUGAR.expressions.Expression.FALSE = "false";

SUGAR.expressions.NumericConstants = {
	pi: 3.14159265,
	e: 2.718281828459045
}


/**
 * Initializes the Expression object.
 */
SUGAR.expressions.Expression.prototype.init = function(params) {
	// check if the parameter is an array with only one value
	if ( params instanceof Array && params.length == 1 ) {
		this.params = params[0];
	}

	// if params is an array or just a constant
	else {
		this.params = params;
	}

	// validate the parameters
	this.validateParameters();
};

/**
 * Returns the parameter list for this Expression.
 */
SUGAR.expressions.Expression.prototype.getParameters = function() {
	return this.params;
};

/**
 * Validates the parameters and throws an Exception if invalid.
 */
SUGAR.expressions.Expression.prototype.validateParameters = function() {
	var params = this.getParameters();
	var count  = this.getParamCount();
	var types  = this.getParameterTypes();

	/* parameter and type validation */

	// make sure count is a number
	if ( typeof(count) != 'number' ) {
		throw (this.getClass() + ": Number of paramters required must be a number");
	}

	// make sure types is a array or a string
	if ( typeof(types) != 'string' && ! (types instanceof Array) ) {
		throw (this.getClass() + ": Parameter types must be valid and match the parameter count");
	}

	// make sure sizeof types is equal to parameter count
	if ( types instanceof Array && count != SUGAR.expressions.Expression.INFINITY && count != types.length ) {
		throw (this.getClass() + ": Parameter types must be valid and match the parameter count");
	}

	// make sure types is valid
	if ( typeof(types) == 'string' ) {
		if ( SUGAR.expressions.Expression.TYPE_MAP[types] == null ) {
			throw (this.getClass() + ": Invalid type requirement '" + types + "'");
		}
	} else {
		for ( var i = 0; i < types.length; i++ ) {
			if ( typeof( SUGAR.expressions.Expression.TYPE_MAP[types[i]]) == 'undefined' ) {
				throw (this.getClass() + ": Invalid type requirement '" + types[i] + "'");
			}
		}
	}

	/* parameter and count validation */

	// if we want 0 params and we got 0 params, forget it
	if ( count == 0 && typeof(params) == 'undefined' )	{	return; }

	// if we want a single param, validate that param
	if ( count == 1 && this.isProperType(params, types) ) {	return; }

	// we require multiple but params only has 1
	if ( count > 1 && ! (params instanceof Array) ) {
		throw (this.getClass() + ": Requires exactly " + count + " parameter(s)");
	}

	// we require only 1 and params has multiple
	if ( count == 1 && params instanceof Array ) {
		throw (this.getClass() + ": Requires exactly 1 parameter");
	}

	// check parameter count
	if ( count != SUGAR.expressions.Expression.INFINITY && params instanceof Array && params.length != count ) {
		throw (this.getClass() + ": Requires exactly " + count + " parameter(s)");
	}

	// if a generic type is specified
	if ( typeof(types) == 'string' ) {
		// only a single parameter
		if ( ! (params instanceof Array) ) {
			if ( this.isProperType( params, types ) ) {
				return;
			}
			throw (this.getClass() + ": Parameter must be of type '" + types + "'");
		}

		// multiple parameters
		for ( var i = 0 ; i < params.length ; i ++ ) {
			if ( ! this.isProperType( params[i], types ) ) {
				throw (this.getClass() + ": All parameters must be of type '" + types + "'");
			}
		}
	}

	// if strict type constraints are specified
	else {
		// only a single parameter
		if ( ! (params instanceof Array) ) {
			if ( this.isProperType( params, types[0] ) ) {
				return;
			}
			throw (this.getClass() + ": Parameter must be of type '" + types[0] + "'");
		}

		// improper type
		for ( var i = 0 ; i < types.length ; i++ ) {
			if ( ! this.isProperType( params[i], types[i] ) ) {
				throw (this.getClass() + ": The parameter at index " + i + " must be of type " + types[i] );
			}
		}
	}
};

/**
 * Returns the exact number of parameters needed.
 */
SUGAR.expressions.Expression.prototype.getParamCount = function() {
	return SUGAR.expressions.Expression.INFINITY;
};

/**
 * Enforces the parameter types.
 */
SUGAR.expressions.Expression.prototype.isProperType = function(variable, type) {
	var see = SUGAR.expressions.Expression;
	if ( type instanceof Array ) {
		return false;
	}

	// retrieve the class
	var c = see.TYPE_MAP[type];

	// check if type is empty
	if ( typeof(c) == 'undefined' || c == null || c == '' ) {
		return false;
	}

	// check if it's an instance of type or a generic that could map to any (unknown type)
	var isInstance = variable instanceof c || variable instanceof see.TYPE_MAP.generic;

	// now check for generics
	switch(type) {
		case see.STRING_TYPE:
			return ( isInstance || typeof(variable) == 'string' || typeof(variable) == 'number' || variable instanceof 				see.TYPE_MAP[see.NUMERIC_TYPE]);
			break;
		case see.NUMERIC_TYPE:
			return ( isInstance || typeof(variable) == 'number' || SUGAR.expressions.isNumeric(variable));
			break;
		case see.BOOLEAN_TYPE:
			if ( variable instanceof see ) {
				variable = variable.evaluate();
			}
			return ( isInstance || variable == see.TRUE || variable == see.FALSE );
			break;
		case see.GENERIC_TYPE:
			return true;
			break;
	}

	// just return whether it is an instance or not
	return isInstance;
};

/** ABSTRACT METHODS **/

/**
 * Evaluates this expression and returns the resulting value.
 */
SUGAR.expressions.Expression.prototype.evaluate = function() {
	// does nothing .. needs to be overridden
};

/**
 * Gets the name of the expression passed in.
 * @param {Object} exp
 */
SUGAR.expressions.Expression.prototype.getClass = function(exp) {
	for (var i in SUGAR.FunctionMap){
		if (typeof SUGAR.FunctionMap[i] == "function" && SUGAR.FunctionMap[i].prototype 
			&& this instanceof SUGAR.FunctionMap[i])
			return i;
	}
	return false;
};

/**
 * Returns the type each parameter should be.
 */
SUGAR.expressions.Expression.prototype.getParameterTypes = function() {
	// does nothing .. needs to be overridden
};



/** GENERIC TYPE EXPRESSIONS **/
SUGAR.GenericExpression = function(params) {

};
SUGAR.util.extend(SUGAR.GenericExpression, SUGAR.expressions.Expression, {
	/**
	 * All parameters have to be a number by default.
	 */
	getParameterTypes: function() {
		return SUGAR.expressions.Expression.GENERIC_TYPE;
	}
});


/**
 * Construct a new NumericExpression.
 */
SUGAR.NumericExpression = function(params) {
	//this.init(params);
};
SUGAR.util.extend(SUGAR.NumericExpression, SUGAR.expressions.Expression, {
	/**
	 * All parameters have to be a number by default.
	 */
	getParameterTypes: function() {
		return SUGAR.expressions.Expression.NUMERIC_TYPE;
	}
});


/**
 * Construct a new StringExpression.
 */
SUGAR.StringExpression = function(params) {
	//this.init(params);
};
SUGAR.util.extend(SUGAR.StringExpression, SUGAR.expressions.Expression, {
	/**
	 * All parameters have to be a string by default.
	 */
	getParameterTypes: function() {
		return SUGAR.expressions.Expression.STRING_TYPE;
	}
});


/**
 * Construct a new BooleanExpression.
 */
SUGAR.BooleanExpression = function(params) {
	//this.init(params);
};
SUGAR.util.extend(SUGAR.BooleanExpression, SUGAR.expressions.Expression, {
	/**
	 * All parameters have to be a sugar-boolean by default.
	 */
	getParameterTypes: function() {
		return SUGAR.expressions.Expression.BOOLEAN_TYPE;
	}
});


/**
 * Construct a new EnumExpression.
 */
SUGAR.EnumExpression = function(params) {
	//this.init(params);
};
SUGAR.util.extend(SUGAR.EnumExpression, SUGAR.expressions.Expression, {
	/**
	 * All parameters have to be an enumeration by default.
	 */
	getParameterTypes: function() {
		return SUGAR.expressions.Expression.ENUM_TYPE;
	}
});


/**
 * Construct a new DateExpression.
 */
SUGAR.DateExpression = function(params) {
	//this.init(params);
};
SUGAR.util.extend(SUGAR.DateExpression, SUGAR.expressions.Expression, {
	/**
	 * All parameters have to be date by default.
	 */
	getParameterTypes: function() {
		return SUGAR.expressions.Expression.DATE_TYPE;
	}
});


/**
 * Construct a new TimeExpression.
 */
SUGAR.TimeExpression = function(params) {
	//this.init(params);
};
SUGAR.util.extend(SUGAR.TimeExpression, SUGAR.expressions.Expression, {
	/**
	 * All parameters have to be time by default.
	 */
	getParameterTypes: function() {
		return SUGAR.expressions.Expression.TIME_TYPE;
	}
});




/**
 * The type to object map for dynamic expression
 * creation and type validation.
 */
SUGAR.expressions.Expression.TYPE_MAP	= {
		"number" 	: SUGAR.NumericExpression,
		"string" 	: SUGAR.StringExpression,
		"date" 		: SUGAR.DateExpression,
		"time" 		: SUGAR.TimeExpression,
		"boolean" 	: SUGAR.BooleanExpression,
		"enum" 		: SUGAR.EnumExpression,
		"generic" 	: SUGAR.GenericExpression
};



/**
 * Construct a new ConstantExpression.
 */
SUGAR.ConstantExpression = function(params) {
	this.init(params);
}

SUGAR.util.extend(SUGAR.ConstantExpression, SUGAR.NumericExpression, {
	evaluate: function() {
		return this.getParameters();
	},
	getParamCount: function() {
		return 1;
	}
});

/**
 * Construct a new StringLiteralExpression.
 */
SUGAR.StringLiteralExpression = function(params) {
	this.init(params);
}
SUGAR.util.extend(SUGAR.StringLiteralExpression, SUGAR.StringExpression, {
	evaluate: function() {
		return this.getParameters();
	},
	getParamCount: function() {
		return 1;
	}
});

/**
 * Construct a new FalseExpression.
 */
SUGAR.FalseExpression = function(params) {
	this.init(params);
}
SUGAR.util.extend(SUGAR.FalseExpression, SUGAR.BooleanExpression, {
	evaluate: function() {
		return SUGAR.expressions.Expression.FALSE;
	},
	getParamCount: function() {
		return 0;
	}
});

/**
 * Construct a new TrueExpression.
 */
SUGAR.TrueExpression = function(params) {
	this.init(params);
}
SUGAR.util.extend(SUGAR.TrueExpression, SUGAR.BooleanExpression, {
	evaluate: function() {
		return SUGAR.expressions.Expression.TRUE;
	},
	getParamCount: function() {
		return 0;
	}
});

/**
 * The ExpressionParser object.
 */
SUGAR.expressions.ExpressionParser = function() {
	// nothing
};

SUGAR.expressions.ExpressionParser.prototype.validate = function(expr)
{
	if ( typeof(expr) != 'string' )	throw "ExpressionParser requires a string expression.";
	// check if its a constant and return a constant expression
	var fixed = this.toConstant(expr);

	if ( fixed != null && typeof(fixed) != 'undefined' )
		return true;

	// VALIDATE: expression format
	if ((/^[\w\-]+\(.*\)$/).exec(expr) == null) {
		throw ("Syntax Error (Expression Format Incorrect '" + expr + "' )");
	}

	// if no open-paren '(' found
	if ( expr.indexOf('(') < 0 )
		throw ("Syntax Error (No opening paranthesis found)");

	return true;
}

SUGAR.expressions.ExpressionParser.prototype.tokenize = function(expr)
{
	var fixed = this.toConstant(expr);
	if ( fixed != null && typeof(fixed) != 'undefined' )
	{
		return {
			type: "constant",
			returnType : this.getType(fixed)
		}
	}

	if(/^[$]\w+$/.test(expr))
	{
		return {
			type:"variable",
			name:YAHOO.lang.trim(expr).substr(1)
		}
	}

	// EXTRACT: Function
	var open_paren_loc = expr.indexOf('(');
	if (open_paren_loc < 1)
		throw (expr + ": Syntax Error");

	// get the function
	var func = expr.substring(0, open_paren_loc);

	// EXTRACT: Parameters
	var params = expr.substring(open_paren_loc + 1, expr.length-1);

	// now parse the individual parameters recursively
	var level  = 0;
	var length = params.length;
	var argument = "";
	var args = new Array();

	// flags
	var currChar		= null;
	var lastCharRead	= null;
	var justReadString	= false;		// did i just read in a string
	var isInQuotes 		= false;		// am i currently reading in a string
	var isPrevCharBK 	= false;		// is my previous character a backslash

	for ( var i = 0 ; i <= length ; i++ ) {
		// store the last character read
		lastCharRead = currChar;

		// the last parameter
		if ( i == length ) {
			argument = YAHOO.lang.trim(argument);
			if (argument != "")
				args[args.length] = this.tokenize(argument);
			break;
		}

		// set isprevcharbk
		isPrevCharBK = ( lastCharRead == '\\' );

		// get the charAt index i
		currChar = params.charAt(i);

		// if i am in quotes, then keep reading
		if ( isInQuotes && currChar != '"' && !isPrevCharBK ) {
			argument += currChar;
			continue;
		}

		// check for quotes
		if ( currChar == '"' && !isPrevCharBK && level == 0 )
		{
			// if i am ending a quote, then make sure nothing follows
			if ( isInQuotes ) {
				// only spaces may follow the end of a string
				var end_reg = params.indexOf(",", i);
				if ( end_reg < 0 )	end_reg = params.length-1;
				var start_reg = ( i < length - 1 ? i+1 : length - 1);

				var temp = params.substring(start_reg , end_reg );
				if ( (/^\s*$/).exec(temp) == null )
					throw (func + ": Syntax Error (Improperly Terminated String '" + temp + "')" + (start_reg) + " " + end_reg);
			}

			// negate if i am in quotes
			isInQuotes = !isInQuotes;
		}

		// check parantheses open/close
		if ( currChar == '(' ) {
			level++;
		} else if ( currChar == ')' ) {
			level--;
		}

		// argument splitting
		else if ( currChar == ',' && level == 0 ) {
			argument = YAHOO.lang.trim(argument);
			if (argument == "")
				throw ("Syntax Error: Unexpected ','");
				args[args.length] = this.tokenize(argument);
			argument = "";
			continue;
		}

		// construct the next argument
		argument += currChar;
	}

	// now check to make sure all the quotes opened were closed
	if ( isInQuotes )	throw ("Syntax Error (Unterminated String Literal)");

	// now check to make sure all the parantheses opened were closed
	if ( level != 0 )	throw ("Syntax Error (Incorrectly Matched Parantheses)");

	// require and return the appropriate expression object
	return {
		type: "function",
		name: YAHOO.lang.trim(func),
		args: args
	}
}


SUGAR.expressions.ExpressionParser.prototype.getType = function(variable) {
	var see = SUGAR.expressions.Expression;

	for(var type in see.TYPE_MAP)
	{
		if (variable instanceof see.TYPE_MAP[type])
		{
			return type;
		}
	}

	return false;
};

/**
 * Evaluate a given string expression and return an Expression
 * object.
 */
SUGAR.expressions.ExpressionParser.prototype.evaluate = function(expr)
{
	// make sure it is only parsing strings
	if ( typeof(expr) != 'string' )	throw "ExpressionParser requires a string expression.";

	// trim spaces, left and right
	expr = expr.replace(/^\s+|\s+$|\n/g,"");

	// check if its a constant and return a constant expression
	var fixed = this.toConstant(expr);
	if ( fixed != null && typeof(fixed) != 'undefined' )
		return fixed;

	// VALIDATE: expression format
	if ((/^[\w\-]+\(.*\)$/).exec(expr) == null) {
		throw ("Syntax Error (Expression Format Incorrect '" + expr + "' )");
		debugger; 
	}

	// EXTRACT: Function
	var open_paren_loc = expr.indexOf('(');

	// if no open-paren '(' found
	if ( open_paren_loc < 0 )
		throw ("Syntax Error (No opening paranthesis found)");

	// get the function
	var func = expr.substring(0, open_paren_loc);

	if ( SUGAR.FunctionMap[func] == null )
		throw (func + ": No such function defined");

	// EXTRACT: Parameters
	var params = expr.substring(open_paren_loc + 1, expr.length-1);

	// now parse the individual parameters recursively
	var level  = 0;
	var length = params.length;
	var argument = "";
	var args = new Array();

	// flags
	var currChar		= null;
	var lastCharRead	= null;
	var justReadString	= false;		// did i just read in a string
	var isInQuotes 		= false;		// am i currently reading in a string
	var isPrevCharBK 	= false;		// is my previous character a backslash

	for ( var i = 0 ; i <= length ; i++ ) {
		// store the last character read
		lastCharRead = currChar;

		// the last parameter
		if ( i == length ) {
			args[args.length] = this.evaluate(argument);
			break;
		}

		// set isprevcharbk
		isPrevCharBK = ( lastCharRead == '\\' );

		// get the charAt index i
		currChar = params.charAt(i);

		// if i am in quotes, then keep reading
		if ( isInQuotes && currChar != '"' && !isPrevCharBK ) {
			argument += currChar;
			continue;
		}

		// check for quotes
		if ( currChar == '"' && !isPrevCharBK && level == 0 )
		{
			// if i am ending a quote, then make sure nothing follows
			if ( isInQuotes ) {
				// only spaces may follow the end of a string
				var end_reg = params.indexOf(",", i);
				if ( end_reg < 0 )	end_reg = params.length-1;
				var start_reg = ( i < length - 1 ? i+1 : length - 1);

				var temp = params.substring(start_reg , end_reg );
				if ( (/^\s*$/).exec(temp) == null )
					throw (func + ": Syntax Error (Improperly Terminated String '" + temp + "')" + (start_reg) + " " + end_reg);
			}

			// negate if i am in quotes
			isInQuotes = !isInQuotes;
		}

		// check parantheses open/close
		if ( currChar == '(' ) {
			level++;
		} else if ( currChar == ')' ) {
			level--;
		}

		// argument splitting
		else if ( currChar == ',' && level == 0 ) {
			args[args.length] = this.evaluate(argument);
			argument = "";
			continue;
		}

		// construct the next argument
		argument += currChar;
	}

	// now check to make sure all the quotes opened were closed
	if ( isInQuotes )	throw ("Syntax Error (Unterminated String Literal)");

	// now check to make sure all the parantheses opened were closed
	if ( level != 0 )	throw ("Syntax Error (Incorrectly Matched Parantheses)");

	// require and return the appropriate expression object
	return new SUGAR.FunctionMap[func](args);
}



/**
 * Takes in a string and returns a ConstantExpression if the
 * string can be converted to a constant.
 */
SUGAR.expressions.ExpressionParser.prototype.toConstant = function(expr) {
	// a raw numeric constant
	if ( (/^(\-)?[0-9]+(\.[0-9]+)?$/).exec(expr) != null ) {
		return new SUGAR.ConstantExpression( parseFloat(expr) );
	}

	// a pre defined numeric constant
	var fixed = SUGAR.expressions.NumericConstants[expr];
	if ( fixed != null && typeof(fixed) != 'undefined' )
		return new SUGAR.ConstantExpression( parseFloat(fixed) );

	// a constant string literal
	if ( (/^".*"$/).exec(expr) != null ) {
		expr = expr.substring(1, expr.length-1);		// remove start/end quotes
		return new SUGAR.StringLiteralExpression( expr );
	}

	// a boolean
	if ( expr == "true" ) {
		return new SUGAR.TrueExpression();
	} else if ( expr == "false" ) {
		return new SUGAR.FalseExpression();
	}

	// a date
	if ( (/^(0[0-9]|1[0-2])\/([0-2][0-9]|3[0-1])\/[0-3][0-9]{3,3}$/).exec(expr) != null ) {
		var day   = parseFloat(expr.substring(0, 2));
		var month = parseFloat(expr.substring(3, 2));
		var year  = parseFloat(expr.substring(6, 4));
		return new SUGAR.DateExpression([day, month, year]);
	}

	// a time
	if ( (/^([0-1][0-9]|2[0-4]):[0-5][0-9]:[0-5][0-9]$/).exec(expr) != null ) {
		var hour   = parseFloat(expr.substring(0, 2));
		var minute = parseFloat(expr.substring(3, 2));
		var second = parseFloat(expr.substring(6, 2));
		return new SUGAR.TimeExpression([hour, minute, second]);
	}

	// neither
	return null;
};

SUGAR.expressions.isNumeric = function(str) {
    if(typeof(str) != 'number' && typeof(str) != 'string')
        return false;
    var SE = SUGAR.expressions;
    var numRegex = new RegExp("^(\\-)?[0-9\\,]+(\\.[0-9]+)?$");
    str = SE.unFormatNumber(str);
    return numRegex.exec(str) != null;

};

SUGAR.expressions.unFormatNumber = function(num) {
    var SE = SUGAR.expressions;
    var ts = "," , ds= ".";
    if (SE.userPrefs) {
        ts = SE.userPrefs.num_grp_sep;
        ds = SE.userPrefs.dec_sep;
    };
    num = SE.replaceAll(num, ts, "");
    num = SE.replaceAll(num, ds, ".");

    return num;
};

SUGAR.expressions.replaceAll = function(haystack, needle, rpl) {
    if (needle == rpl || haystack == "" || needle == "") return haystack;
    var str = haystack;
    while ( str.indexOf(needle) > -1 ) {
        str = str.replace(needle, rpl);
    }
    return str;
};

/**
 * 
 */
 
SUGAR.util.DateUtils = {
	/**
 	 * Converts a date string to a new format.
 	 * If no new format is passed in, the date is returned as a Unix timestamp.
 	 * If no old format is passed in, the old format is guessed.
 	 * @param {Object} date String representing a date.
 	 * @param {Object} newFormat Optional: Format date should be returned in.
 	 * @param {Object} oldFormat Optional: Current format of the date string.
 	 */
	convert : function(date, newFormat, oldFormat) {
		if (oldFormat == null || oldFormat == "") {
			oldFormat = SUGAR.util.DateUtils.guessFormat(date);
		}
		if (oldFormat == false) {
			return false;
		}
		var jsDate = new Date("Jan 1, 1970 00:00:00");
		var part = "";
		var dateRemain = date;
		oldFormat = oldFormat + " "; // Trailing space to read as last separator.
		for (var c in oldFormat) {
			c = oldFormat[c];
			if (c == ':' || c == '/' || c == '-' || c == '.' || c == " " || c == 'a' || c == "A") {
				var i = dateRemain.indexOf(c);
				if (i == -1) i = dateRemain.length;
				var v = dateRemain.substring(0, i);
				dateRemain = dateRemain.substring(i+1);
				switch (part) {
					case 'm':
						jsDate.setMonth(v - 1); break;
					case 'd':
						jsDate.setDate(v); break;
					case 'Y':
						jsDate.setYear(v); break;
					case 'h':
						//Read time, assume minutes are at end of date string (we do not accept seconds)
						var timeformat = oldFormat.substring(oldFormat.length - 5);
						if (timeformat.toLowerCase == "i a " || timeformat.toLowerCase == c + "ia ") {
							if (dateRemain.substring(dateRemain.length - 3).toLowerCase == 'pm') {
								v = v * 1;
								if (v < 12) {
									v += 12;
								}
							}
						}
					case 'H':
						jsDate.setHours(v);
						break;
					case 'i':
						v = v.substring(0, 2);
						jsDate.setMinutes(v); break;
				}
				part = "";
			} else {
				part = c;
			}
		}
		return jsDate.toString();
	},
	guessFormat: function(date) {
		if (typeof date != "string")
			return false;
		//Is there a time
		var time = "";
		if (date.indexOf(" ") != -1) {
			time = date.substring(date.indexOf(" ") + 1, date.length);
			date = date.substring(0, date.indexOf(" "));
		}

		//First detect if the date contains "-" or "/"
		var dateSep = "/";
		if (date.indexOf("/") != -1){}
		else if (date.indexOf("-") != -1)
		{
			dateSep = "-";
		} 
		else if (date.indexOf(".") != -1)
		{
			dateSep = ".";
		}
		else 
		{
		 	return false;
		}
		var dateParts = date.split(dateSep);
		var dateFormat = "";
		var jsDate = new Date("Jan 1, 1970 00:00:00");
		if (dateParts[0].length == 4)
		{
			dateFormat = "Y" + dateSep + "m" + dateSep + "d";
		}
		else if (dateParts[2].length == 4)
		{
			dateFormat = "m" + dateSep + "d" + dateSep + "Y";
		}
		else 
		{
			return false;
		}

		//Detect the Time format
		if (time != "")
		{
			var timeFormat = "";
			var timeSep = ":";
			if (time.indexOf(".") == 2) {
				timeSep = ".";
			}
			if (time.indexOf(" ") != -1) {
				var timeParts = time.split(" ");
				if (timeParts[1] == "am" || timeParts[1] == "pm") {
					return dateFormat + " h" + timeSep + "i a";
				} else if (timeParts[1] == "AM" || timeParts[1] == "PM") {
					return dateFormat + " h" + timeSep + "i A";
				}	
			}
			else 
			{
				var timeEnd = time.substring(time.length - 2, time.length);
				if (timeEnd == "AM" || timeEnd == "PM") {
					return dateFormat + " h" + timeSep + "iA";
				}
				if (timeEnd == "am" || timeEnd == "pm") {
					return dateFormat + " h" + timeSep + "iA";
				}

				return dateFormat + " H" + timeSep + "i";
			}
		}

		return dateFormat;
	}
 }// End of File include/Expressions/javascript/expressions.js
                                
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Sales Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/eula/sugarcrm-sales-subscription-agreement.html
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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


/**
 * This JavaScript file provides an entire framework for the new
 * SUGAR Calculated Fields/Dependent Dropdowns implementation.
 * This is integrated heavily with the SUGAR Expressions engine
 * which does the actual input validation and expression
 * calculations behind the scenes.
 *
 * @import sugar_expressions.php
 * @import formvalidation.js  (RequireDependency function)
 * @import yui-dom-event.js	    (although, we could do without this in the future)
 */
 
// namespace
if ( typeof(SUGAR.forms) == 'undefined' )	SUGAR.forms = {};
if ( typeof(SUGAR.forms.animation) == 'undefined') SUGAR.forms.animation = {};

/**
 * @STATIC
 * The main assignment handler which maintains a registry of the
 * current variables in use and the appropriate fields they map to.
 * It can assign values to variables and retrieve the values of
 * variables. Also, it animates the updated fields if necessary
 * to indicate a change in value to the user.
 */
SUGAR.forms.AssignmentHandler = function() {
	// pass ...
}

/**
 * @STATIC
 * This flag determines whether animations are turned on/off.
 */
SUGAR.forms.AssignmentHandler.ANIMATE = true;

/**
 * @STATIC
 * This array maps variables to their respective element id's.
 */
SUGAR.forms.AssignmentHandler.VARIABLE_MAP = {};

/**
 * @STATIC
 * This array contains the list of locked variables. (For Detection of Circular References)
 */
SUGAR.forms.AssignmentHandler.LOCKS = {};



/**
 * @STATIC
 * Register a variable with the handler.
 */
SUGAR.forms.AssignmentHandler.register = function(variable) {
	if ( variable instanceof Array ) {
		for ( var i = 0; i < variable.length; i++ ) {
			SUGAR.forms.AssignmentHandler.VARIABLE_MAP[variable[i]] = document.getElementById(variable[i]);
		}
	} else {
		SUGAR.forms.AssignmentHandler.VARIABLE_MAP[variable] = document.getElementById(variable);
	}
}


/**
 * @STATIC
 * Register form fields with the handler.
 */
SUGAR.forms.AssignmentHandler.registerFields = function(flds) {
	if ( typeof(flds) != 'object' ) return;
	var form = document.forms[flds.form];
	var names = flds.fields;
	if ( typeof(form) == 'undefined' ) return;
	for ( var i = 0; i < names.length; i++ ) {
		var el = form[names[i]];
		if ( el != null )	SUGAR.forms.AssignmentHandler.VARIABLE_MAP[el.id] = el;
	}
}

/**
 * @STATIC
 * Register all the fields in a form
 */
SUGAR.forms.AssignmentHandler.registerForm = function(f) {
	var form = document.forms[f];
	if ( typeof(form) == 'undefined' ) return;
	for ( var i = 0; i < form.length; i++ ) {
		var el = form[i];
		if ( el != null && el.value != null && el.id != null && el.id != "")
			SUGAR.forms.AssignmentHandler.VARIABLE_MAP[el.id] = el;
	}
}

SUGAR.forms.AssignmentHandler.registerView = function(view, startEl) {
	var Dom = YAHOO.util.Dom;
	if (Dom.get(view) != null && Dom.get(view).tagName == "FORM") {
		return SUGAR.forms.AssignmentHandler.registerForm(view);
	}
	var nodes = YAHOO.util.Selector.query("." + view + ".view [id]", startEl);
	for (var i in nodes) {
		if (nodes[i].id != "")
			SUGAR.forms.AssignmentHandler.VARIABLE_MAP[nodes[i].id] = nodes[i];
	}
}


/**
 * @STATIC
 * Register a form field with the handler.
 */
SUGAR.forms.AssignmentHandler.registerField = function(formname, field) {
	SUGAR.forms.AssignmentHandler.registerFields({form:formname,fields:new Array(field)});
}

/**
 * @STATIC
 * Retrieve the value of a variable.
 */
SUGAR.forms.AssignmentHandler.getValue = function(variable) {
	var field = SUGAR.forms.AssignmentHandler.getElement(variable);
	if ( field == null || field.tagName == null) 	return null;

	// special select case for IE6
	if ( field.tagName.toLowerCase() == "select" ) {
		if(field.selectedIndex == -1) {
			return null;
		} else {
			return field.options[field.selectedIndex].value;
		}
	}
	
	if(field.tagName.toLowerCase() == "input" && field.type.toLowerCase() == "checkbox") {
			return field.checked?SUGAR.expressions.Expression.TRUE:SUGAR.expressions.Expression.FALSE;
	}
	
	if (field.value !== null && typeof(field.value) != "undefined")
		return field.value;
	
	return field.innerHTML;
}


/**
 * @STATIC
 * Retrieve the element behind a variable.
 */
SUGAR.forms.AssignmentHandler.getElement = function(variable) {
	// retrieve the variable
	var field = SUGAR.forms.AssignmentHandler.VARIABLE_MAP[variable];
		
	if ( field == null )	
		field = YAHOO.util.Dom.get(variable);

	return field;
}

/**
 * @STATIC
 * Assign a value to a variable.
 */
SUGAR.forms.AssignmentHandler.assign = function(variable, value, flash)
{
	if (typeof flash == "undefined")
		flash = true;
	// retrieve the variable
	var field = SUGAR.forms.AssignmentHandler.getElement(variable);
	
	if ( field == null )	
		return null;

	// now check if this field is locked
	if ( SUGAR.forms.AssignmentHandler.LOCKS[variable] != null ) {
		throw ("Circular Reference Detected");
	}

	//Detect field types and add error handling.
	if (YAHOO.util.Dom.hasClass(field, "imageUploader"))
	{
		var img = Dom.get("img_" + field.id);
		img.src = value;
		img.style.visibility = "";
	} 
	else {
		field.value = value;
	}
	
	// animate
	if ( SUGAR.forms.AssignmentHandler.ANIMATE && flash)
		SUGAR.forms.FlashField(field);

	// lock this variable
	SUGAR.forms.AssignmentHandler.LOCKS[variable] = true;

	// fire onchange
	var listeners = YAHOO.util.Event.getListeners(field, 'change');
	if (listeners != null) {
		for (var i = 0; i < listeners.length; i++) {
			var l = listeners[i];
			l.fn(null, l.obj);
		}
	}

	// unlock this variable
	SUGAR.forms.AssignmentHandler.LOCKS[variable] = null;
}

SUGAR.forms.AssignmentHandler.showError = function(variable, error)
{
	// retrieve the variable
	var field = SUGAR.forms.AssignmentHandler.getElement(variable);
	
	if ( field == null )	
		return null;
	
	add_error_style(field.form.name, field, error, false);
}

SUGAR.forms.AssignmentHandler.clearError = function(variable)
{
	// retrieve the variable
	var field = SUGAR.forms.AssignmentHandler.getElement(variable);
	
	if ( field == null )	
		return;
	
	for(var i in inputsWithErrors)
	{
		if (inputsWithErrors[i] == field)
		{
			if ( field.parentNode.className.indexOf('x-form-field-wrap') != -1 ) 
            {
				field.parentNode.parentNode.removeChild(field.parentNode.parentNode.lastChild);
            }
            else 
            {
            	field.parentNode.removeChild(field.parentNode.lastChild);
            }
			delete inputsWithErrors[i];
			return;
		}
	}
}

/**
 * @STATIC
 * Change the style attributes of the given variable.
 */
SUGAR.forms.AssignmentHandler.setStyle = function(variable, styles)
{
	// retrieve the variable
	var field = SUGAR.forms.AssignmentHandler.getElement(variable);
	if ( field == null )	return null;

	// set the styles
	for ( var property in styles ) {
		YAHOO.util.Dom.setStyle(field, property + "", styles[property]);
	}
}





/**
 * @STATIC
 * The Default expression parser.
 */
SUGAR.forms.DefaultExpressionParser = new SUGAR.expressions.ExpressionParser();

/**
 * @STATIC
 * Parses expressions given a variable map.<br>
 */
SUGAR.forms.evalVariableExpression = function(expression, varmap)
{
	var SE = SUGAR.expressions;
	// perform range replaces
	expression = SUGAR.forms._performRangeReplace(expression);

	var handler = SUGAR.forms.AssignmentHandler;

	// resort to the master variable map if not defined
	if ( typeof(varmap) == 'undefined' )
	{
		varmap = new Array();
		for ( v in handler.VARIABLE_MAP) {
			if (v != "") {
				varmap[varmap.length] = v;
			}
		}
	}

	if ( expression == SE.Expression.TRUE || expression == SE.Expression.FALSE )
		return SUGAR.forms.DefaultExpressionParser.evaluate(expression);

	var vars = SUGAR.forms.getFieldsFromExpression(expression);
	for (var i in vars)
	{
		var v = vars[i];
		var value = handler.getValue(v);
		if (value == null)
			throw "Unable to find field: " + v;
		
		value = value.replace(/\n/g, "");
		var regex = new RegExp("\\$" + v, "g");

		if ((/^(\s*)$/).exec(value) != null || value === "") {
			expression = expression.replace(regex, '""');
		}
		// test if value is a number or boolean
		else if ( SE.isNumeric(value) ) {
            expression = expression.replace(regex, SE.unFormatNumber(value));
		}
		// assume string
		else {
			expression = expression.replace(regex, '"' + value + '"');
		}
	}

	return SUGAR.forms.DefaultExpressionParser.evaluate(expression);
}

/**
 * Replaces range expressions with their values.
 * eg. '%a[1,10]' => '$a1,$a2,$a3,...,$a10'
 */
SUGAR.forms._performRangeReplace = function(expression)
{
	this.generateRange = function(prefix, start, end) {
		var str = "";
		var i = parseInt(start);
		if ( typeof(end) == 'undefined' )
			while ( SUGAR.forms.AssignmentHandler.getElement(prefix + '' + i) != null )
				str += '$' + prefix + '' + (i++) + ',';
		else
			for ( ; i <= end ; i ++ ) {
				var t = prefix + '' + i;
				if ( SUGAR.forms.AssignmentHandler.getElement(t) != null )
					str += '$' + t + ',';
			}
		return str.substring(0, str.length-1);
	}

	this.valueReplace = function(val) {
		if ( !(/^\$.*$/).test(val) )	return val;
		return SUGAR.forms.AssignmentHandler.getValue(val.substring(1));
	}

	// flags
	var isInQuotes = false;
	var prev;
	var inRange;

	// go character by character
	for ( var i = 0 ;  ; i ++ ) {
		// due to fluctuating expression length
		if ( i == expression.length ) break;

		var ch = expression.charAt(i);

		if ( ch == '"' && prev != '\\' )	isInQuotes = !isInQuotes;

		if ( !isInQuotes && ch == '%' ) {
			inRange = true;

			// perform the replace
			var loc_start = expression.indexOf( '[' , i+1 );
			var loc_comma = expression.indexOf(',', loc_start );
			var loc_end   = expression.indexOf(']', loc_start );

			// invalid expression syntax?
			if ( loc_start < 0 || loc_end < 0 )	throw ("Invalid range syntax");

			// construct the pieces
			var prefix = expression.substring( i+1 , loc_start );
			var start, end;

			// optional param is there
			if ( loc_comma > -1 && loc_comma < loc_end ) {
				start = expression.substring( loc_start+1, loc_comma );
				end = expression.substring( loc_comma + 1, loc_end );
			} else {
				start = expression.substring( loc_start+1, loc_end );
			}

			// optional param is there
			if ( loc_comma > -1 && loc_comma < loc_end )	end = expression.substring( loc_comma + 1, loc_end );

			// construct the range
			var result = this.generateRange(prefix, this.valueReplace(start), this.valueReplace(end));
			//var result = this.generateRange(prefix, start, end);

			// now perform the replace
			if ( typeof(end) == 'undefined' )
				expression = expression.replace('%'+prefix+'['+start+']', result);
			else
				expression = expression.replace('%'+prefix+'['+start+','+end+']', result);

			// skip on
			i = i + result.length - 1;
		}

		prev = ch;
	}

	return expression;
}

SUGAR.forms.getFieldsFromExpression = function(expression)
{
	var re = /[^$]*?\$(\w+)[^$]*?/g, 
		matches = [], 
		result;
	while (result = re.exec(expression))
	{
		matches.push(result[result.length-1]);
	}
	return matches;
}




/**
 * @STATIC
 * Creates a new trigger with the given variables, condition, dependencies, and a flag
 * which indicates whether or not to execute this trigger when the window loads.
 */
SUGAR.forms.createTrigger = function(variables, condition, triggeronload)
{
	var trigger = new SUGAR.forms.Trigger(variables, condition);
	if ( triggeronload ) {
		var empty = false;
		for (var i in variables)
		{
			if (SUGAR.forms.AssignmentHandler.getValue(variables[i]) == "")
				empty = true;
		}
		if (!empty)
			YAHOO.util.Event.addListener(window, "load", SUGAR.forms.Trigger.trigger, trigger);
	}

	return trigger;
}





/**
 * A dependency is an object representation of a variable being dependent
 * on other variables. For example A being the sum of B and C where A is
 * 'dependent' on B and C.
 */
SUGAR.forms.Dependency = function(trigger, actions, falseActions, testOnLoad)
{
	this.actions = actions;
	this.falseActions = falseActions;
	trigger.setDependency(this);
	this.trigger = trigger;
	if (testOnLoad) {
		var vars = trigger.variables;
		var empty = false;
		for (var i in vars)
		{
			if (SUGAR.forms.AssignmentHandler.getValue(vars[i]) == "")
				empty = true;
		}
		if (!empty)
			SUGAR.forms.Trigger.fire("", trigger);
	}
}


/**
 * Triggers this dependency to be re-evaluated again.
 */
SUGAR.forms.Dependency.prototype.fire = function(undo)
{
	try {
		var actions = this.actions;
		if (undo && this.falseActions != null)
			actions = this.falseActions;
		
		if (actions instanceof SUGAR.forms.AbstractAction) {
			actions.exec();
		} else {
			for (var i in actions) {
				var action = actions[i];
				if (typeof action.exec == "function")
					action.exec();
			}
		}
	} catch (e) {
		if (!SUGAR.isIE && console && console.log){ 
			console.log('ERROR: ' + e);
		}
		return;
	}
};


SUGAR.forms.AbstractAction = function(target) {
	this.target = target;
};

SUGAR.forms.AbstractAction.prototype.exec = function()
{
	
}

/**
 * This object resembles a trigger where a change in any of the specified
 * variables triggers the dependencies to be re-evaluated again.
 */
SUGAR.forms.Trigger = function(variables, condition) {
	this.variables	  = variables;
	this.condition 	  = condition;
	this.dependency = { };
	this._attachListeners();
}

/**
 * Attaches a 'change' listener to all the fields that cause
 * the condition to be re-evaluated again.
 */
SUGAR.forms.Trigger.prototype._attachListeners = function() {
	var handler = SUGAR.forms.AssignmentHandler;
	if ( ! (this.variables instanceof Array) ) {
		var el = handler.getElement(this.variables);
		if (!el) return;
		
		if (el.type && el.type.toUpperCase() == "CHECKBOX")
		{
			YAHOO.util.Event.addListener(el, "click", SUGAR.forms.Trigger.fire, this);
		} else {
			YAHOO.util.Event.addListener(el, "change", SUGAR.forms.Trigger.fire, this);
		}
		return;
	}
	for ( var i = 0; i < this.variables.length; i++){
		var el = handler.getElement(this.variables[i]);
		if (!el) continue;
		if (el.type && el.type.toUpperCase() == "CHECKBOX")
		{
			YAHOO.util.Event.addListener(el, "click", SUGAR.forms.Trigger.fire, this);
		} else {
			YAHOO.util.Event.addListener(el, "change", SUGAR.forms.Trigger.fire, this);
		}
	}
}

/**
 * Attaches a 'change' listener to all the fields that cause
 * the condition to be re-evaluated again.
 */
SUGAR.forms.Trigger.prototype.setDependency = function(dep) {
	this.dependency = dep;
}

/**
 * @STATIC
 * This is the function that is called when a 'change' event
 * is triggered. If the condition is true, then it triggers
 * all the dependencies.
 */
SUGAR.forms.Trigger.fire = function(e, obj)
{
	// eval the condition
	var eval;
	var val;
	try {
		eval = SUGAR.forms.evalVariableExpression(obj.condition);
	} catch (e) {
		if (!SUGAR.isIE && console && console.log){ 
			console.log('ERROR:' + e + "; in Condition: " + obj.condition);
		}
	}

	// evaluate the result
	if ( typeof(eval) != 'undefined' )
		val = eval.evaluate();

	// if the condition is met
	if ( val == SUGAR.expressions.Expression.TRUE ) {
		// single dependency
		if (obj.dependency instanceof SUGAR.forms.Dependency ) {
			obj.dependency.fire(false);
			return;
		}
	} else if ( val == SUGAR.expressions.Expression.FALSE ) {
		// single dependency
		if (obj.dependency instanceof SUGAR.forms.Dependency ) {
			obj.dependency.fire(true);
			return;
		}
	}
}

SUGAR.forms.flashInProgress = {};
/**
 * @STATIC
 * Animates a field when by changing it's background color to
 * a shade of light red and back.
 */
SUGAR.forms.FlashField = function(field, to_color) {
    if ( typeof(field) == 'undefined')     return;

    if (SUGAR.forms.flashInProgress[field.id])
    	return;
    SUGAR.forms.flashInProgress[field.id] = true;
    // store the original background color
    var original = field.style.backgroundColor;

    // default bg-color to white
    if ( typeof(original) == 'undefined' || original == '' ) {
        original = '#FFFFFF';
    }

    // default to_color
    if ( typeof(to_color) == 'undefined' )
        var to_color = '#FF8F8F';

    // Create a new ColorAnim instance
    var oButtonAnim = new YAHOO.util.ColorAnim(field, { backgroundColor: { to: to_color } }, 0.2);

    oButtonAnim.onComplete.subscribe(function () {
        if ( this.attributes.backgroundColor.to == to_color ) {
            this.attributes.backgroundColor.to = original;
            this.animate();
        } else {
        	field.style.backgroundColor = original;
        	SUGAR.forms.flashInProgress[field.id] = false;
        }
    });
    
    //Flash tabs for fields that are not visible. 
    var tabsId = field.form.getAttribute("name") + "_tabs";
    if(typeof (window[tabsId]) != "undefined") {
        var tabView = window[tabsId];
        var parentDiv = YAHOO.util.Dom.getAncestorByTagName(field, "div");
        if ( tabView.get ) {
            var tabs = tabView.get("tabs");
            for (var i in tabs) {
                if (i != tabView.get("activeIndex") && (tabs[i].get("contentEl") == parentDiv 
                		|| YAHOO.util.Dom.isAncestor(tabs[i].get("contentEl"), field)))
                {
                	var label = tabs[i].get("labelEl");
                	
                	if(SUGAR.forms.flashInProgress[label.parentNode.id])
                		return;
                	
                	var tabAnim = new YAHOO.util.ColorAnim(label, { color: { to: '#F00' } }, 0.2);
                	tabAnim.origColor = Dom.getStyle(label, "color");
                	tabAnim.onComplete.subscribe(function () {
                		if (this.attributes.color.to == '#F00') {
                			this.attributes.color.to = this.origColor;
                			this.animate();
                        } else {
                        	SUGAR.forms.flashInProgress[label.parentNode.id] = false;
                        }
                    });
                	SUGAR.forms.flashInProgress[label.parentNode.id] = true;
                	tabAnim.animate();
                }
            }
        }
	} 

    oButtonAnim.animate();
}

// End of File include/Expressions/javascript/dependency.js
                                
