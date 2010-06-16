<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: Invoice.php 50983 2009-09-21 20:45:37Z ajay $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/




require('modules/Quotes/config.php');
require_once('include/pdf/class.ezpdf.php');
require_once('include/Sugarpdf/sugarpdf_config.php');

global $mod_strings, $app_strings, $app_list_strings;
global $current_user, $currentModule, $action, $record, $focus, $locale;

//turn off all error reporting so that PHP warnings don't munge the PDF code
error_reporting(E_ALL);
set_time_limit(1800);

$GLOBALS['log']->info("Quote layout view: Invoice");

// prepare bean for export
$focus = $locale->prepBeanForExport($focus);
// cn: bug 8587 handle strings for export
$mod_strings		= $locale->translateStringPack($mod_strings, $locale->getExportCharset());
$app_strings		= $locale->translateStringPack($app_strings, $locale->getExportCharset());
$app_list_strings	= $locale->translateStringPack($app_list_strings, $locale->getExportCharset());

$font = "include/fonts/Helvetica";
$pdf = new Cezpdf(array(0,0,598,842));
// wp: must substitute chr(2) for the euro character symbol because it is not part of the
// standard ISO set. see [ http://www.ros.co.nz/pdf/faq.php#16 ]
$euro_diff = array(2=>'Euro');
$pdf->selectFont($font, array('differences'=>$euro_diff));


$euro_diff = array(2=>'Euro');
$pdf->selectFont($font, array());


//adds line to top and bottom of pages
$all = $pdf->openObject();
$pdf->saveState();
$pdf->setStrokeColor(0,0,0,1);
$pdf->line(20,40,578,40);
$pdf->line(20,822,578,822);
$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');

//retrieve the sales person's first name
global $beanFiles;
require_once($beanFiles['User']);
$rep = new User;
$rep->retrieve($focus->assigned_user_id);


global $timedate;

$imgFileName = K_PATH_CUSTOM_IMAGES.PDF_HEADER_LOGO;
if (@getimagesize($imgFileName) === FALSE) {
    $imgFileName = K_PATH_IMAGES.PDF_HEADER_LOGO;
}

$pdf->addJpegFromFile($imgFileName,20,750,300);
$quote[0]['TITLE'] = $mod_strings['LBL_PDF_INVOICE_NUMBER'];
$quote[1]['TITLE'] = $mod_strings['LBL_PDF_QUOTE_DATE'];
$quote[2]['TITLE'] = $mod_strings['LBL_PURCHASE_ORDER_NUM'];
$quote[3]['TITLE'] = $mod_strings['LBL_PAYMENT_TERMS'];
$quote[0]['VALUE'] = format_number_display($focus->quote_num,$focus->system_id);
$quote[1]['VALUE'] = $timedate->to_display_date(date($GLOBALS['timedate']->dbDayFormat, time()), false);
$quote[2]['VALUE'] = $focus->purchase_order_num;
$quote[3]['VALUE'] = $focus->payment_terms;

$options['showLines'] = 0;
$options['shaded'] = 0;
$options['titleFontSize'] = 12;
$options['shadeHeadings'] = 1;
$options['headCol'] = array(.7,.3,.3);
$options['headTextCol'] = array(0,0,.4);
$options['xPos'] = 400;
$options['xOrientation'] = 'right';
$options['showHeadings'] = 0;
$options['shaded'] = 4;
$options['rowGap'] = 0;

$pdf->ezTable($quote,'' ,$mod_strings['LBL_PDF_INVOICE_TITLE'] ,$options);

$addressB[0][$mod_strings['LBL_PDF_BILLING_ADDRESS']]  = $focus->billing_contact_name;
$addressB[1][$mod_strings['LBL_PDF_BILLING_ADDRESS']]  = $focus->billing_account_name;
$addressB[2][$mod_strings['LBL_PDF_BILLING_ADDRESS']]  = $focus->billing_address_street;
if(!empty($focus->billing_address_city) || !empty($focus->billing_address_state) || !empty($focus->billing_address_postalcode)) {
	$addressB[3][$mod_strings['LBL_PDF_BILLING_ADDRESS']]  = "$focus->billing_address_city, $focus->billing_address_state  $focus->billing_address_postalcode";
}
else {
	$addressB[3][$mod_strings['LBL_PDF_BILLING_ADDRESS']]  = '';
}

$addressB[4][$mod_strings['LBL_PDF_BILLING_ADDRESS']]  = $focus->billing_address_country;
$addressS[0][$mod_strings['LBL_PDF_SHIPPING_ADDRESS']]  = $focus->shipping_contact_name;
$addressS[1][$mod_strings['LBL_PDF_SHIPPING_ADDRESS']]  = $focus->shipping_account_name;
$addressS[2][$mod_strings['LBL_PDF_SHIPPING_ADDRESS']]  = $focus->shipping_address_street;
if(!empty($focus->shipping_address_city) || !empty($focus->shipping_address_state) || !empty($focus->shipping_address_postalcode)) {
	$addressS[3][$mod_strings['LBL_PDF_SHIPPING_ADDRESS']]  = "$focus->shipping_address_city, $focus->shipping_address_state  $focus->shipping_address_postalcode";
}
else {
	$addressS[3][$mod_strings['LBL_PDF_SHIPPING_ADDRESS']]  = '';
}
$addressS[4][$mod_strings['LBL_PDF_SHIPPING_ADDRESS']]  = $focus->shipping_address_country;

//ezTable $options is an associative array which can contain:
//'showLines'=> 0,1,2, default is 1 (1->show the borders, 0->no borders, 2-> show borders AND lines between rows.)
//'showHeadings' => 0 or 1
//'shaded'=> 0,1,2, default is 1 (1->alternate lines are shaded, 0->no shading, 2->both sets are shaded)
//'shadeCol' => (r,g,b) array, defining the colour of the shading, default is (0.8,0.8,0.8)
//'shadeCol2' => (r,g,b) array, defining the colour of the shading of the second set, default is (0.7,0.7,0.7), used when 'shaded' is set to 2.
//'fontSize' => 10
//'textCol' => (r,g,b) array, text colour
//'titleFontSize' => 12
//'rowGap' => 2 , the space between the text and the row lines on each row 7 of 41 http://ros.co.nz/pdf - http://www.sourceforge.net/projects/pdf-php
//'colGap' => 5 , the space between the text and the column lines in each column
//'lineCol' => (r,g,b) array, defining the colour of the lines, default, black.
//'xPos' => 'left','right','center','centre',or coordinate, reference coordinate in the x-direction
//'xOrientation' => 'left','right','center','centre', position of the table w.r.t 'xPos'. This entry is to be used in conjunction with 'xPos' to give control over the lateral position of the table.
//'width' => <number>, the exact width of the table, the cell widths will be adjusted to give the table this width.
//'maxWidth' => <number>, the maximum width of the table, the cell widths will only be adjusted if the table width is going to be greater than this.

//$options['width'] = 300;
$pdf->ezSetDY(-35);
$lastY = $pdf->y ;
$options['shaded'] = 0;
$options['titleFontSize'] = 12;
$options['shadeHeadings'] = 1;
$options['headCol'] = array(.3,.3,.3);
$options['headTextCol'] = array(1,1,1);
$options['xPos'] = 25;
$options['xOrientation'] = 'right';
$options['showHeadings'] = 1;
$options['shaded'] = 0;
$pdf->ezTable($addressB,'' ,'' ,$options);
$pdf->ezSetY($lastY);
$options['xPos'] = 25 + $pdf->lastWidth;
$pdf->ezTable($addressS,'' ,'' ,$options);


$currency = new Currency();
////	settings
$format_number_array = array(
	'currency_symbol' => true,
	'type' => 'pdf',
	'currency_id' => $focus->currency_id,
	'charset_convert' => true, /* UTF-8 uses different bytes for Euro and Pounds */
);
$currency->retrieve($focus->currency_id);
//kbrill Bug#11569 - When Quotes are printed as Proposals or Invoices, multiple product groups are out of order from the original quote
//$product_bundle_list = $focus->get_product_bundles();
$product_bundle_list = $focus->get_linked_beans('product_bundles','ProductBundle');



if(is_array($product_bundle_list)){
	$ordered_bundle_list = array();
    for ($cnt = 0; $cnt < count($product_bundle_list); $cnt++) {
    $index = $product_bundle_list[$cnt]->get_index($focus->id);
    $ordered_bundle_list[(int)$index[0]['bundle_index']] = $product_bundle_list[$cnt];
    } //for
    ksort($ordered_bundle_list);
    
    foreach ($ordered_bundle_list as $product_bundle) {

		// cn: bug 8341 - prep product line-item for export
		$product_bundle = $locale->prepBeanForExport($product_bundle);

		$options['showHeadings'] = 1;
		if(isset($focus->show_line_nums) && $focus->show_line_nums == 1){
			$options['showRowCount']=1;
		}
		if(key_exists($product_bundle->bundle_stage, $in_total_group_stages)){
			$count = 0;
			$item = array();
			$product_list = $product_bundle->get_products();
			if (is_array($product_list)) {

				$bundle_list = $product_bundle->get_product_bundle_line_items();
				if (is_array($bundle_list)) {
					while (list($key, $line_item) = each ($bundle_list)) {
						// cn: bug 8341 - prep product line-item for export
						$line_item = $locale->prepBeanForExport($line_item);

						if ($line_item->object_name == "Product") {
							$item[$count][$mod_strings['LBL_PDF_ITEM_QUANTITY']] = format_number($line_item->quantity, 0, 0);
							$item[$count][$mod_strings['LBL_PDF_PART_NUMBER']] = $line_item->mft_part_num;
							$item[$count][$mod_strings['LBL_PDF_ITEM_PRODUCT']] = stripslashes($line_item->name) . "\n" . stripslashes($line_item->description);

							$item[$count][$mod_strings['LBL_PDF_ITEM_LIST_PRICE']] = format_number($line_item->list_usdollar, $locale->getPrecision(), $locale->getPrecision(), array_merge($format_number_array, array('convert' => true)));
							$item[$count][$mod_strings['LBL_PDF_ITEM_UNIT_PRICE']] = format_number($line_item->discount_usdollar, $locale->getPrecision(), $locale->getPrecision(), array_merge($format_number_array, array('convert' => true)));
							$item[$count][$mod_strings['LBL_PDF_ITEM_EXT_PRICE']] = format_number($line_item->discount_usdollar * $line_item->quantity, $locale->getPrecision(), $locale->getPrecision(), array_merge($format_number_array, array('convert' => true)));
						    if(format_number($product_bundle->deal_tot, $locale->getPrecision(), $locale->getPrecision())!= 0.00){
                                if($line_item->discount_select){
                                $item[$count][$mod_strings['LBL_PDF_ITEM_DISCOUNT']] = format_number($line_item->discount_amount, $locale->getPrecision(), $locale->getPrecision())."%";
                                }
                                else{
                                $item[$count][$mod_strings['LBL_PDF_ITEM_DISCOUNT']] = format_number($line_item->discount_amount, $locale->getPrecision(), $locale->getPrecision(), array_merge($format_number_array, array('convert' => true)));
                                }
                            }
							$count++;
						}
						else if ($line_item->object_name == "ProductBundleNote") {
							$item[$count][$mod_strings['LBL_PDF_ITEM_QUANTITY']] = "";
							$item[$count][$mod_strings['LBL_PDF_PART_NUMBER']] = "";
							$item[$count][$mod_strings['LBL_PDF_ITEM_PRODUCT']] = stripslashes($line_item->description);
							$item[$count][$mod_strings['LBL_PDF_ITEM_LIST_PRICE']] = "";
							$item[$count][$mod_strings['LBL_PDF_ITEM_UNIT_PRICE']] = "";
							$item[$count][$mod_strings['LBL_PDF_ITEM_EXT_PRICE']] = "";
							$item[$count][$mod_strings['LBL_PDF_ITEM_DISCOUNT']] = "";
                            $item[$count][$mod_strings['LBL_PDF_ITEM_SELECT_DISCOUNT']] = "";

							$count++;
						}
					}
				}
			}

			$options['cols'] = array(
				$mod_strings['LBL_PDF_ITEM_LIST_PRICE'] => array('justification' => 'right'),
				$mod_strings['LBL_PDF_ITEM_UNIT_PRICE'] => array('justification' => 'right'),
				$mod_strings['LBL_PDF_ITEM_EXT_PRICE'] => array('justification' => 'right'),
				$mod_strings['LBL_PDF_ITEM_DISCOUNT'] => array('justification' => 'right')
			);
			$options['width'] = 545;
			$options['xPos'] = 25;
			$pdf->ezSetDy(-20);
			$bundle_name_options = array('aleft' => 24);
			$pdf->ezText($product_bundle->name, 13, $bundle_name_options);
			$pdf->ezTable($item,'' ,'' ,$options);
			$pdf->ezSetDy(-20);
			if($pdf_group_subtotal){
				$total = array();
				$total[0]['BLANK'] = ' ';
				$total[1]['BLANK'] = ' ';
				$total[2]['BLANK'] = ' ';
				$total[3]['BLANK'] = ' ';
				$total[0]['TITLE'] =  $mod_strings['LBL_PDF_SUBTOTAL'];
				$total[0]['VALUE'] = format_number($product_bundle->subtotal, $locale->getPrecision(), $locale->getPrecision(), $format_number_array);
			    if(format_number($product_bundle->deal_tot, $locale->getPrecision(), $locale->getPrecision())!= 0.00){
                $total[1]['TITLE'] =  $mod_strings['LBL_PDF_DISCOUNT'];
                $total[1]['VALUE'] = format_number($product_bundle->deal_tot, $locale->getPrecision(), $locale->getPrecision(), $format_number_array);               
                $total[2]['TITLE'] =  $mod_strings['LBL_PDF_NEW_SUB'];
                $total[2]['VALUE'] = format_number($product_bundle->new_sub, $locale->getPrecision(), $locale->getPrecision(), $format_number_array);              
                $total[3]['TITLE'] = $mod_strings['LBL_PDF_TAX'];
                $total[3]['VALUE'] =  format_number($product_bundle->tax, $locale->getPrecision(), $locale->getPrecision(), $format_number_array);
                $total[4]['TITLE'] = $mod_strings['LBL_PDF_SHIPPING'];
                $total[4]['VALUE'] =  format_number($product_bundle->shipping, $locale->getPrecision(), $locale->getPrecision(), $format_number_array);
                $total[5]['TITLE'] = $mod_strings['LBL_PDF_TOTAL'];
                $total[5]['VALUE'] =  format_number($product_bundle->total, $locale->getPrecision(), $locale->getPrecision(), $format_number_array);
                }
                else{
                $total[1]['TITLE'] = $mod_strings['LBL_PDF_TAX'];
                $total[1]['VALUE'] =  format_number($product_bundle->tax, $locale->getPrecision(), $locale->getPrecision(), $format_number_array);
                $total[2]['TITLE'] = $mod_strings['LBL_PDF_SHIPPING'];
                $total[2]['VALUE'] =  format_number($product_bundle->shipping, $locale->getPrecision(), $locale->getPrecision(), $format_number_array);
                $total[3]['TITLE'] = $mod_strings['LBL_PDF_TOTAL'];
                $total[3]['VALUE'] =  format_number($product_bundle->total, $locale->getPrecision(), $locale->getPrecision(), $format_number_array);
                }
				$options['xPos'] = 25;
				$options['showHeadings'] = 0;
				$options['shaded'] = 0;
				$options['showRowCount'] = 0;
				$options['width'] = $pdf->lastWidth;
				$options['cols'] = array('BLANK' => array('width' => '390'), 'VALUE' => array('justification' => 'right'));



				$pdf->ezSetDy(-5);
				$pdf->line(25, $pdf->y , $pdf->lastWidth , $pdf->y);
				$pdf->ezSetDy(-5);
				$pdf->ezTable($total,'' ,'' ,$options);
			}
		}
	}
}

if(isset($focus->calc_grand_total) && $focus->calc_grand_total == 1) {
	$total = array();
	$total[0]['BLANK'] = '';
	$total[1]['BLANK'] = ' ';
	$total[2]['BLANK'] = ' ';
	$total[3]['BLANK'] = ' ';
	$total[0]['TITLE0'] = $mod_strings['LBL_PDF_CURRENCY'];
	$total[0]['VALUE0'] = $currency->iso4217;
	$total[0]['TITLE'] = $mod_strings['LBL_PDF_SUBTOTAL'];
	$total[0]['VALUE'] = format_number($focus->subtotal, $locale->getPrecision(), $locale->getPrecision(), $format_number_array);
    if(format_number($focus->deal_tot, $locale->getPrecision(), $locale->getPrecision())!=0.00){
    $total[1]['TITLE0'] = '';
    $total[1]['VALUE0'] ='';    
    $total[1]['TITLE'] = $mod_strings['LBL_PDF_DISCOUNT'];
    $total[1]['VALUE'] = format_number($focus->deal_tot, $locale->getPrecision(), $locale->getPrecision(), $format_number_array);
    $total[2]['TITLE0'] = '';
    $total[2]['VALUE0'] ='';
    $total[2]['TITLE'] = $mod_strings['LBL_PDF_NEW_SUB'];
    $total[2]['VALUE'] = format_number($focus->new_sub, $locale->getPrecision(), $locale->getPrecision(), $format_number_array);   
    $total[3]['TITLE0'] = $mod_strings['LBL_PDF_TAX_RATE'];
    $total[3]['VALUE0'] = format_number($focus->taxrate_value, $locale->getPrecision(), $locale->getPrecision(), array('percentage' => true));
    $total[3]['TITLE'] = $mod_strings['LBL_PDF_TAX'];
    $total[3]['VALUE'] =  format_number($focus->tax, $locale->getPrecision(), $locale->getPrecision(), $format_number_array);
    $total[4]['TITLE0'] = $mod_strings['LBL_PDF_SHIPPING_COMPANY'];
    $total[4]['VALUE0'] = $focus->shipper_name;
    $total[4]['TITLE'] = $mod_strings['LBL_PDF_SHIPPING'];
    $total[4]['VALUE'] =  format_number($focus->shipping, $locale->getPrecision(), $locale->getPrecision(), $format_number_array);
    $total[5]['TITLE0'] = '';
    $total[5]['VALUE0'] ='';
    $total[5]['TITLE'] = $mod_strings['LBL_PDF_TOTAL'];
    $total[5]['VALUE'] =  format_number($focus->total, $locale->getPrecision(), $locale->getPrecision(), $format_number_array);
    }
    else{  
    $total[1]['TITLE0'] = $mod_strings['LBL_PDF_TAX_RATE'];
    $total[1]['VALUE0'] = format_number($focus->taxrate_value, $locale->getPrecision(), $locale->getPrecision(), array('percentage' => true));
    $total[1]['TITLE'] = $mod_strings['LBL_PDF_TAX'];
    $total[1]['VALUE'] =  format_number($focus->tax, $locale->getPrecision(), $locale->getPrecision(), $format_number_array);
    $total[2]['TITLE0'] = $mod_strings['LBL_PDF_SHIPPING_COMPANY'];
    $total[2]['VALUE0'] = $focus->shipper_name;
    $total[2]['TITLE'] = $mod_strings['LBL_PDF_SHIPPING'];
    $total[2]['VALUE'] =  format_number($focus->shipping, $locale->getPrecision(), $locale->getPrecision(), $format_number_array);
    $total[3]['TITLE0'] = '';
    $total[3]['VALUE0'] ='';
    $total[3]['TITLE'] = $mod_strings['LBL_PDF_TOTAL'];
    $total[3]['VALUE'] =  format_number($focus->total, $locale->getPrecision(), $locale->getPrecision(), $format_number_array);
    }
	$options['xPos'] = 25;
	$options['showHeadings'] = 0;
	$options['shaded'] = 0;
	$options['showRowCount'] = 0;
	$options['cols'] = array(
		'BLANK' => array('width' => '200'),
		'VALUE0' => array('width' => '100'),
		'VALUE' => array('justification' => 'right')
	);
	$pdf->ezSetDy(-50);
	if($pdf->y < 125){
		$pdf->ezNewPage();
	}

	$pdf->line(25, $pdf->y , $pdf->lastWidth , $pdf->y);
	$pdf->ezSetDy(-5);
	$pdf->ezTable($total,'' ,$mod_strings['LBL_PDF_GRAND_TOTAL'] ,$options);
	$pdf->ezSetDy(-5);
	$pdf->line(25, $pdf->y , $pdf->lastWidth , $pdf->y);
}

$filename = preg_replace("#[^A-Z0-9\-_\.]#i", "_", $focus->shipping_account_name);
if (!empty($focus->quote_num)) {
	$filename .= "_{$focus->quote_num}";
}
$filename = $mod_strings['LBL_INVOICE']."_{$filename}.pdf";
if(isset($_SERVER['HTTP_USER_AGENT']) && preg_match("/MSIE/", $_SERVER['HTTP_USER_AGENT'])) {
	//$filename = $locale->translateCharset($filename, $locale->getExportCharset());
	$filename = urlencode($filename);
} 
$stream_options = array(
	'Content-Disposition' => $filename,
	'compress'            => 1,
);

if (isset($_REQUEST['email_action']) && $_REQUEST['email_action']=="EmailLayout") {
	if (!is_array($stream_options)){
		$options=array();
	}
	if ( isset($options['compress']) && $options['compress']==0){
		$tmp = $pdf->output(1);
	}
	else {
		$tmp = $pdf->output();
	}

	$badoutput = ob_get_contents();
	if(strlen($badoutput) > 0) {
		ob_end_clean();
	}

	$fp = sugar_fopen($GLOBALS['sugar_config']['upload_dir'].$filename,'w');
	fwrite($fp, ltrim($tmp));
	fclose($fp);

	return $filename;

}
else{
	$pdf->stream($stream_options);
}
?>