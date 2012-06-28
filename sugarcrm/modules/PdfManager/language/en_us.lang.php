<?php
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

$mod_strings = array (
  'LBL_TEAM' => 'Teams',
  'LBL_TEAMS' => 'Teams',
  'LBL_TEAM_ID' => 'Team Id',
  'LBL_ASSIGNED_TO_ID' => 'Assigned User Id',
  'LBL_ASSIGNED_TO_NAME' => 'Assigned to',
  'LBL_ID' => 'ID',
  'LBL_DATE_ENTERED' => 'Date Created',
  'LBL_DATE_MODIFIED' => 'Date Modified',
  'LBL_MODIFIED' => 'Modified By',
  'LBL_MODIFIED_ID' => 'Modified By Id',
  'LBL_MODIFIED_NAME' => 'Modified By Name',
  'LBL_CREATED' => 'Created By',
  'LBL_CREATED_ID' => 'Created By Id',
  'LBL_DESCRIPTION' => 'Description',
  'LBL_DELETED' => 'Deleted',
  'LBL_NAME' => 'Name',
  'LBL_CREATED_USER' => 'Created by User',
  'LBL_MODIFIED_USER' => 'Modified by User',
  'LBL_LIST_NAME' => 'Name',
  'LBL_LIST_FORM_TITLE' => 'PDF Template List',
  'LBL_MODULE_NAME' => 'PdfManager',
  'LBL_MODULE_TITLE' => 'PdfManager',
  'LBL_HOMEPAGE_TITLE' => 'My PDF Templates',
  'LNK_NEW_RECORD' => 'Create PDF Template',
  'LNK_LIST' => 'View PDF Templates',
  'LNK_REPORT_CONFIG' => 'Report PDF Template',
  'LNK_IMPORT_PDFMANAGER' => 'Import PDF Templates',
  'LBL_SEARCH_FORM_TITLE' => 'Search PDF Manager',
  'LBL_HISTORY_SUBPANEL_TITLE' => 'View History',
  'LBL_ACTIVITIES_SUBPANEL_TITLE' => 'Activities',
  'LBL_PDFMANAGER_SUBPANEL_TITLE' => 'PdfManager',
  'LBL_NEW_FORM_TITLE' => 'New PDF Template',
  'LBL_BASE_MODULE' => 'Module',
  'LBL_PUBLISHED' => 'Published',
  'LBL_FIELD' => 'Field',
  'LBL_BODY_HTML' => 'Template',
  'LBL_EDITVIEW_PANEL1' => 'PDF Document Properties',
  'LBL_TITLE' => 'Title',
  'LBL_SUBJECT' => 'Subject',
  'LBL_KEYWORDS' => 'Keyword(s)',
  'LBL_AUTHOR' => 'Author',
  'LBL_PUBLISHED_POPUP_HELP' => 'Publish a template to make it available to users.',
  'LBL_BASE_MODULE_POPUP_HELP' => 'Select a module for which this template will be available.',
  'LBL_FIELD_POPUP_HELP' => 'Select a field to insert the variable for the field value. To select fields of a parent module, first select the module in the Links area at the bottom of the Fields list in the first dropdown, then select the field in the second dropdown.',
  'LBL_BODY_HTML_POPUP_HELP' => 'Create the template using the HTML editor. After saving the template, you will be able to view a preview of the PDF version of the template.',
  'LBL_BODY_HTML_POPUP_QUOTES_HELP' => 'Create the template using the HTML editor. After saving the template, you will be able to view a preview of the PDF version of the template.<br><br>To edit the loop used to create the Product line items, click the "HTML" button in the editor to access the code.  The code is contained within <!--START_BUNDLE_LOOP-->, <!--START_PRODUCT_LOOP-->, <!--END_PRODUCT_LOOP--> and <!--END_BUNDLE_LOOP-->.',
  'LBL_KEYWORDS_POPUP_HELP' => 'Associate Keywords with the document, generally in the form "keyword1 keyword2..."',
  'LBL_BTN_INSERT' => 'Insert',
  'LBL_FIELDS_LIST' => 'Fields',
  'LBL_LINK_LIST' => 'Links',
  'LBL_PREVIEW' => 'Preview',
  'LBL_ALERT_SWITCH_BASE_MODULE' => 'WARNING: If you really want to change the main module, all previous fields added to template must be deleted.',  
  'LBL_EMAIL_PDF_DEFAULT_DESCRIPTION' => 'Here is the file you requested (You can change this text)'
);

$mod_list_strings = array (
    'pdf_template_quote' => array (
        'name' => 'Quote',
        'description' => "This template is used to print Quote in PDF.",
        'template_name' => 'quote',        
        'body_html' => '
<table border="0" cellspacing="2">
<tbody>
<tr>
<td rowspan="4" width="180%"><img src="./themes/default/images/pdf_logo.jpg" alt="" /></td>
<td width="60%"><strong>Quote</strong></td>
<td width="60%">&nbsp;</td>
</tr>
<tr>
<td bgcolor="#DCDCDC" width="75%">Quote number:</td>
<td width="75%">{$fields.quote_num}</td>
</tr>
<tr>
<td bgcolor="#DCDCDC" width="75%">Sales Person:</td>
<td width="75%">{$fields.assigned_user_link.name}</td>
</tr>
<tr>
<td bgcolor="#DCDCDC" width="75%">Valid until:</td>
<td width="75%">{$fields.date_quote_expected_closed}</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>
<table style="width: 50%;" border="0" cellspacing="2">
<tbody>
<tr style="color: #ffffff;" bgcolor="#4B4B4B">
<td>Bill To</td>
<td>Ship To</td>
</tr>
<tr>
<td>{$fields.billing_contact_name}</td>
<td>{$fields.shipping_contact_name}</td>
</tr>
<tr>
<td>{$fields.billing_account_name}</td>
<td>{$fields.shipping_account_name}</td>
</tr>
<tr>
<td>{$fields.billing_address_street}</td>
<td>{$fields.shipping_address_street}</td>
</tr>
<tr>
<td>{$fields.billing_address_city}, {$fields.billing_address_state}, {$fields.billing_address_postalcode}</td>
<td>{$fields.shipping_address_city}, {$fields.shipping_address_state}, {$fields.shipping_address_postalcode}</td>
</tr>
<tr>
<td>{$fields.billing_address_country}</td>
<td>{$fields.shipping_address_country}</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>
<!--START_BUNDLE_LOOP-->
<h3>{$bundle.name}</h3>
<table style="width: 100%;" border="0">
<tbody>
<tr style="color: #ffffff;" bgcolor="#4B4B4B">
<td width="70%">Quantity</td>
<td width="175%">Part Number</td>
<td width="175%">Product</td>
<td width="70%">List Price</td>
<td width="70%">Unit Price</td>
<td width="70%">Ext. Price</td>
<td width="70%">Discount</td>
</tr>
<!--START_PRODUCT_LOOP-->
<tr>
<td width="70%">{$product.quantity}</td>
<td width="175%">{$product.mft_part_num}</td>
<td width="175%">{$product.name}</td>
<td align="right" width="70%">{$product.list_price}</td>
<td align="right" width="70%">{$product.discount_price}</td>
<td align="right" width="70%">{$product.ext_price}</td>
<td align="right" width="70%">{$product.discount_amount}</td>
</tr>
<!--END_PRODUCT_LOOP--></tbody>
</table>
<table>
<tbody>
<tr>
<td><hr /></td>
</tr>
</tbody>
</table>
<table style="width: 100%; margin: auto;" border="0">
<tbody>
<tr>
<td width="210%">&nbsp;</td>
<td width="45%">Subtotal:</td>
<td align="right" width="45%">{$bundle.subtotal}</td>
</tr>
<tr>
<td width="210%">&nbsp;</td>
<td width="45%">Discount:</td>
<td align="right" width="45%">{$bundle.deal_tot}</td>
</tr>
<tr>
<td width="210%">&nbsp;</td>
<td width="45%">Discounted Subtotal:</td>
<td align="right" width="45%">{$bundle.new_sub}</td>
</tr>
<tr>
<td width="210%">&nbsp;</td>
<td width="45%">Tax:</td>
<td align="right" width="45%">{$bundle.tax}</td>
</tr>
<tr>
<td width="210%">&nbsp;</td>
<td width="45%">Shipping:</td>
<td align="right" width="45%">{$bundle.shipping}</td>
</tr>
<tr>
<td width="210%">&nbsp;</td>
<td width="45%">Total</td>
<td align="right" width="45%">{$bundle.total}</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>
<p>&nbsp;</p>
<!--END_BUNDLE_LOOP-->
<p>&nbsp;</p>
<p>&nbsp;</p>
<table>
<tbody>
<tr>
<td><hr /></td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>
<table style="width: 100%; margin: auto;" border="0">
<tbody>
<tr>
<td width="200%">&nbsp;</td>
<td style="font-weight: bold;" colspan="2" align="center" width="150%"><b>Grand Total</b></td>
<td width="75%">&nbsp;</td>
<td align="right" width="75%">&nbsp;</td>
</tr>
<tr>
<td width="200%">&nbsp;</td>
<td width="75%">Currency:</td>
<td width="75%">{$fields.currency_iso}</td>
<td width="75%">Subtotal:</td>
<td align="right" width="75%">{$fields.subtotal}</td>
</tr>
<tr>
<td width="200%">&nbsp;</td>
<td width="75%">&nbsp;</td>
<td align="right" width="75%">&nbsp;</td>
<td width="75%">Discount:</td>
<td align="right" width="75%">{$fields.deal_tot}</td>
</tr>
<tr>
<td width="200%">&nbsp;</td>
<td width="75%">&nbsp;</td>
<td width="75%">&nbsp;</td>
<td width="75%">Discounted Subtotal:</td>
<td align="right" width="75%">{$fields.new_sub}</td>
</tr>
<tr>
<td width="200%">&nbsp;</td>
<td width="75%">Tax Rate:</td>
<td width="75%">{$fields.taxrate_value}</td>
<td width="75%">Tax:</td>
<td align="right" width="75%">{$fields.tax}</td>
</tr>
<tr>
<td width="200%">&nbsp;</td>
<td width="75%">Shipping Provider:</td>
<td width="75%">{$fields.shipper_name}</td>
<td width="75%">Shipping:</td>
<td align="right" width="75%">{$fields.shipping}</td>
</tr>
<tr>
<td width="200%">&nbsp;</td>
<td width="75%">&nbsp;</td>
<td width="75%">&nbsp;</td>
<td width="75%">Total</td>
<td align="right" width="75%">{$fields.total}</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>
<table>
<tbody>
<tr>
<td><hr /></td>
</tr>
</tbody>
</table>        
        ',
    ),

    'pdf_template_invoice' => array (
        'name' => 'Invoice',
        'description' => 'This template is used to print Invoice in PDF.',
        'template_name' => 'invoice',
        'body_html' => '
<table border="0" cellspacing="2">
<tbody>
<tr>
<td rowspan="4" width="180%"><img src="./themes/default/images/pdf_logo.jpg" alt="" /></td>
<td width="60%"><strong>Invoice</strong></td>
<td width="60%">&nbsp;</td>
</tr>
<tr>
<td bgcolor="#DCDCDC" width="75%">Invoice number:</td>
<td width="75%">{$fields.quote_num}</td>
</tr>
<tr>
<td bgcolor="#DCDCDC" width="75%">Sales Person:</td>
<td width="75%">{$fields.assigned_user_link.name}</td>
</tr>
<tr>
<td bgcolor="#DCDCDC" width="75%">Valid until:</td>
<td width="75%">{$fields.date_quote_expected_closed}</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>
<table style="width: 50%;" border="0" cellspacing="2">
<tbody>
<tr style="color: #ffffff;" bgcolor="#4B4B4B">
<td>Bill To</td>
<td>Ship To</td>
</tr>
<tr>
<td>{$fields.billing_contact_name}</td>
<td>{$fields.shipping_contact_name}</td>
</tr>
<tr>
<td>{$fields.billing_account_name}</td>
<td>{$fields.shipping_account_name}</td>
</tr>
<tr>
<td>{$fields.billing_address_street}</td>
<td>{$fields.shipping_address_street}</td>
</tr>
<tr>
<td>{$fields.billing_address_city}, {$fields.billing_address_state}, {$fields.billing_address_postalcode}</td>
<td>{$fields.shipping_address_city}, {$fields.shipping_address_state}, {$fields.shipping_address_postalcode}</td>
</tr>
<tr>
<td>{$fields.billing_address_country}</td>
<td>{$fields.shipping_address_country}</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>
<!--START_BUNDLE_LOOP-->
<h3>{$bundle.name}</h3>
<table style="width: 100%;" border="0">
<tbody>
<tr style="color: #ffffff;" bgcolor="#4B4B4B">
<td width="70%">Quantity</td>
<td width="175%">Part Number</td>
<td width="175%">Product</td>
<td width="70%">List Price</td>
<td width="70%">Unit Price</td>
<td width="70%">Ext. Price</td>
<td width="70%">Discount</td>
</tr>
<!--START_PRODUCT_LOOP-->
<tr>
<td width="70%">{$product.quantity}</td>
<td width="175%">{$product.mft_part_num}</td>
<td width="175%">{$product.name}</td>
<td align="right" width="70%">{$product.list_price}</td>
<td align="right" width="70%">{$product.discount_price}</td>
<td align="right" width="70%">{$product.ext_price}</td>
<td align="right" width="70%">{$product.discount_amount}</td>
</tr>
<!--END_PRODUCT_LOOP--></tbody>
</table>
<table>
<tbody>
<tr>
<td><hr /></td>
</tr>
</tbody>
</table>
<table style="width: 100%; margin: auto;" border="0">
<tbody>
<tr>
<td width="210%">&nbsp;</td>
<td width="45%">Subtotal:</td>
<td align="right" width="45%">{$bundle.subtotal}</td>
</tr>
<tr>
<td width="210%">&nbsp;</td>
<td width="45%">Discount:</td>
<td align="right" width="45%">{$bundle.deal_tot}</td>
</tr>
<tr>
<td width="210%">&nbsp;</td>
<td width="45%">Discounted Subtotal:</td>
<td align="right" width="45%">{$bundle.new_sub}</td>
</tr>
<tr>
<td width="210%">&nbsp;</td>
<td width="45%">Tax:</td>
<td align="right" width="45%">{$bundle.tax}</td>
</tr>
<tr>
<td width="210%">&nbsp;</td>
<td width="45%">Shipping:</td>
<td align="right" width="45%">{$bundle.shipping}</td>
</tr>
<tr>
<td width="210%">&nbsp;</td>
<td width="45%">Total</td>
<td align="right" width="45%">{$bundle.total}</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>
<p>&nbsp;</p>
<!--END_BUNDLE_LOOP-->
<p>&nbsp;</p>
<p>&nbsp;</p>
<table>
<tbody>
<tr>
<td><hr /></td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>
<table style="width: 100%; margin: auto;" border="0">
<tbody>
<tr>
<td width="200%">&nbsp;</td>
<td style="font-weight: bold;" colspan="2" align="center" width="150%"><b>Grand Total</b></td>
<td width="75%">&nbsp;</td>
<td align="right" width="75%">&nbsp;</td>
</tr>
<tr>
<td width="200%">&nbsp;</td>
<td width="75%">Currency:</td>
<td width="75%">{$fields.currency_iso}</td>
<td width="75%">Subtotal:</td>
<td align="right" width="75%">{$fields.subtotal}</td>
</tr>
<tr>
<td width="200%">&nbsp;</td>
<td width="75%">&nbsp;</td>
<td align="right" width="75%">&nbsp;</td>
<td width="75%">Discount:</td>
<td align="right" width="75%">{$fields.deal_tot}</td>
</tr>
<tr>
<td width="200%">&nbsp;</td>
<td width="75%">&nbsp;</td>
<td width="75%">&nbsp;</td>
<td width="75%">Discounted Subtotal:</td>
<td align="right" width="75%">{$fields.new_sub}</td>
</tr>
<tr>
<td width="200%">&nbsp;</td>
<td width="75%">Tax Rate:</td>
<td width="75%">{$fields.taxrate_value}</td>
<td width="75%">Tax:</td>
<td align="right" width="75%">{$fields.tax}</td>
</tr>
<tr>
<td width="200%">&nbsp;</td>
<td width="75%">Shipping Provider:</td>
<td width="75%">{$fields.shipper_name}</td>
<td width="75%">Shipping:</td>
<td align="right" width="75%">{$fields.shipping}</td>
</tr>
<tr>
<td width="200%">&nbsp;</td>
<td width="75%">&nbsp;</td>
<td width="75%">&nbsp;</td>
<td width="75%">Total</td>
<td align="right" width="75%">{$fields.total}</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>
<table>
<tbody>
<tr>
<td><hr /></td>
</tr>
</tbody>
</table>
        ',
    ),
);
