
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

//FILE SUGARCRM flav=pro ONLY


SUGAR.PdfManager = {};

SUGAR.PdfManager.fieldInserted = false;
/**
 * Returns a list of fields for a module
 */
SUGAR.PdfManager.loadFields = function(moduleName, linkName) {

    if (SUGAR.PdfManager.fieldInserted && linkName.length == 0) {
        alert(SUGAR.language.get('PdfManager', 'LBL_ALERT_SWITCH_BASE_MODULE'));
    }

    if (linkName.length > 0 && linkName.indexOf('pdfManagerRelateLink_') == -1) {
        $('#subField').empty();
        $('#subField').hide();
        return true;
    }
    var url = "index.php?" + SUGAR.util.paramsToUrl({
        module : "PdfManager",
        action : "getFields",
        to_pdf : "1",
        sugar_body_only : "true",
        baseModule : moduleName,
        baseLink : linkName
    });

    var resp = http_fetch_sync(url);

    var field = YAHOO.util.Dom.get('field');

    if (field != null) {
        var inputTD = YAHOO.util.Dom.getAncestorByTagName(field, 'TD');
        if (resp.responseText.length > 0 && inputTD != null) {
            inputTD.innerHTML = resp.responseText;
            SUGAR.forms.AssignmentHandler.register('field', 'EditView');
        }
    }
}

/**
 * Push var to WYSIWYG
 */
SUGAR.PdfManager.insertField = function(selField, selSubField) {

    SUGAR.PdfManager.fieldInserted = true;

    var fieldName = "";

    if ( selField && selField.value != "") {
        fieldName += selField.value;

        if ( selSubField && selSubField.value != "") {
            fieldName += "."+selSubField.value;
        }
    }

    var cleanFieldName = fieldName.replace('pdfManagerRelateLink_', '');
	var inst = tinyMCE.getInstanceById("body_html");
	if (fieldName.length > 0 && inst) {
		inst.getWin().focus();
		inst.execCommand('mceInsertRawHTML', false, '{$fields.' + cleanFieldName + '}');
	}
}
