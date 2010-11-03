<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * Generic ListView class
 *
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-professional-eula.html
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
 */


require_once('include/ListView/ListView.php');

class LQListView extends ListView
{

	var $data_present = false;


/**initializes ListView
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
*/
 function LQListView(){

	parent::ListView();

}
/**sets how many records should be displayed per page in the list view
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
*/

 function processListViewTwo($seed, $xTemplateSection, $html_varName){
	if(!isset($this->xTemplate))
		$this->createXTemplate();
	$isSugarBean = is_subclass_of($seed, "SugarBean");
	$list = null;
	if($isSugarBean){

		$list =	$this->processSugarBean($xTemplateSection, $html_varName, $seed);

	}else{
		$list =$seed;
	}
	
//BEGIN SUGARINTERNAL CUSTOMIZATIONS -jgreen

	if(!empty($list)){
		$this->data_present = true;
	}
	
	
//END SUGARINTERNAL CUSTOMIZATIONS - jgreen	
	
	if ( $this->is_dynamic )
	{
		$this->processHeaderDynamic($xTemplateSection,$html_varName);

		$this->processListRows($list,$xTemplateSection, $html_varName);
	}
	else
	{
		$this->processSortArrows($html_varName);

		if ($isSugarBean) {
			$seed->parse_additional_headers($this->xTemplate, $xTemplateSection);
		}
		$this->xTemplateAssign('CHECKALL', "<img src='include/images/blank.gif' width=\"1\" height=\"1\" al=\"\">");

		// Process the  order by before processing the pro_nav.  The pro_nav requires the order by values to be set
		$this->processOrderBy($html_varName);


		if($this->xTemplate->exists('main.pro_nav'))
			$this->xTemplate->parse('main.pro_nav');


		$this->processListRows($list,$xTemplateSection, $html_varName);
	}

	if($this->display_header_and_footer){
		$this->getAdditionalHeader();
		if ( ! empty($this->header_title) )
		{
			echo get_form_header( $this->header_title, $this->header_text, false);
		}
	}
	$this->xTemplate->out($xTemplateSection);
	
	if($isSugarBean )
		//echo "</td></tr>\n</table>\n";

	if(isset($_SESSION['validation'])){
		print base64_decode('PGEgaHJlZj0naHR0cDovL3d3dy5zdWdhcmNybS5jb20nPlBPV0VSRUQmbmJzcDtCWSZuYnNwO1NVR0FSQ1JNPC9hPg==');
}

//end function processlistview2
}
//end class
}
?>
