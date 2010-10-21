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
 * $Id: view.edit.php 
 * Description: This file is used to override the default Meta-data EditView behavior
 * to provide customization specific to the Calls module.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
require_once('include/MVC/View/views/view.detail.php');

class DocumentsViewExtdoc extends ViewDetail 
{
	var $options = array('show_header' => false, 'show_title' => false, 'show_subpanels' => false, 'show_search' => true, 'show_footer' => false, 'show_javascript' => false, 'view_print' => false,);

 	public function display(){
 		if(!empty($_REQUEST['form_id'])){
 			$name_field = !empty($_REQUEST['name_field'])?$_REQUEST['name_field']: 'filename';
 			$form_id = $_REQUEST['form_id'];
 		
			echo "<script>
			function fillSelect(filename) {
				var oForm = document.forms['$form_id'];
				oForm.elements[\"filename\"].value = filename;
				oForm.elements[\"doc_id\"].value = filename;
				DCMenu.closeTopOverlay();
			}
			</script>";
		}else{
			echo "<script>
			function fillSelect(filename) {
				window.open('https://apps.lotuslive.com/files/filer2/home.do#files.do%3FsubContent%3DfileDetails.do%3FfileId%3D36A40110D5BC11DF8278B49A0A050301', 'download');
			}
			</script>";	
		}


		echo "
		<table class='dcSearch'>
			<tr>
			<td>
			<input type='text' id='dcSearch' name='dcSearch'>
			</td>
			<td>
			<input type='submit' name='submit' class='dcSubmit' value='Search Documents'>
			</td>
			</tr>
		</table>
		
		<table width='500' class='dcListView' cellpadding='0' cellspacing='0'>
		<tr>
			<th>Type</th>
			<th>Name</th>
			<th>Last Modified</th>
			<th>Owner</th>
		</tr>
		<tr>
			<td class='type' width='20'><img src='themes/default/images/xls_image_inline.gif'></td>
			<td class='name'><a href='javascript: fillSelect(\"sales.xls\")'>Sales Matrix</a></td>
			<td class='lastModified'>12/12/10 05:30:00</td>
			<td class='owner'>Majed Itani</td>
		</tr>
		<tr>
			<td class='type' width='20'><img src='themes/default/images/pdf_image_inline.gif'></td>
			<td class='name' nowrap><a href='javascript: fillSelect(\"Sugar vs sfdc_Overview_08-31-2010.pdf\")'>Sugar vs sfdc_Overview_08-31-2010.pdf</a></td>
			<td class='lastModified'>10/11/10 05:30:00</td>
			<td class='owner' nowrap>Jan Sysmans</td>
		</tr>
		<tr>
			<td class='type' width='20'><img src='themes/default/images/doc_image_inline.gif'></td>
			<td class='name'><a href='javascript: fillSelect(\"letter.doc\")'>Company Letter Head</a></td>
			<td class='lastModified'>02/11/10 05:30:00</td>
			<td class='owner'>Roger Smith</td>
		</tr>
		</table>";

 	}
}
