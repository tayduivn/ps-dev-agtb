<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional Subscription
 * Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

$viewdefs['Notes']['EditView'] = array(
    'templateMeta' => array('form' => array('enctype'=> 'multipart/form-data',
                                            'headerTpl'=>'modules/Notes/tpls/EditViewHeader.tpl',
                                            ),
							'maxColumns' => '2', 
                            'widths' => array(
                                            array('label' => '10', 'field' => '30'), 
                                            array('label' => '10', 'field' => '30')
                                            ),
'javascript' => '<script type="text/javascript" src="include/javascript/dashlets.js?s={$SUGAR_VERSION}&c={$JS_CUSTOM_VERSION}"></script>
<script>
function deleteAttachmentCallBack(text) 
	{literal} { {/literal} 
	if(text == \'true\') {literal} { {/literal} 
		document.getElementById(\'new_attachment\').style.display = \'\';
		ajaxStatus.hideStatus();
		document.getElementById(\'old_attachment\').innerHTML = \'\'; 
	{literal} } {/literal} else {literal} { {/literal} 
		document.getElementById(\'new_attachment\').style.display = \'none\';
		ajaxStatus.flashStatus(SUGAR.language.get(\'Notes\', \'ERR_REMOVING_ATTACHMENT\'), 2000); 
	{literal} } {/literal}  
{literal} } {/literal} 
</script>
<script>toggle_portal_flag(); function toggle_portal_flag()  {literal} { {/literal} {$TOGGLE_JS} {literal} } {/literal} </script>',
),
	'panels' =>array (
  		'lbl_note_information' => array (
  					array ('contact_name','parent_name'),
	    			array (
                        array('name'=>'name', 'displayParams'=>array('size'=>60)),''
                    ),
					array ( 
						array (
	        				'name' => 'filename',
	        				'customCode' => '<span id=\'new_attachment\' style=\'display:{if !empty($fields.filename.value)}none{/if}\'>
        									 <input name="uploadfile" tabindex="3" type="file" size="60"/>
        									 </span>
											 <span id=\'old_attachment\' style=\'display:{if empty($fields.filename.value)}none{/if}\'>
		 									 <input type=\'hidden\' name=\'deleteAttachment\' value=\'0\'>
		 									 {$fields.filename.value}<input type=\'hidden\' name=\'old_filename\' value=\'{$fields.filename.value}\'/><input type=\'hidden\' name=\'old_id\' value=\'{$fields.id.value}\'/>
											 <input type=\'button\' class=\'button\' value=\'{$APP.LBL_REMOVE}\' onclick=\'ajaxStatus.showStatus(SUGAR.language.get("Notes", "LBL_REMOVING_ATTACHMENT"));this.form.deleteAttachment.value=1;this.form.action.value="EditView";SUGAR.dashlets.postForm(this.form, deleteAttachmentCallBack);this.form.deleteAttachment.value=0;this.form.action.value="";\' >       
											 </span>',
	      				),
					    array('name'=>'portal_flag',
					          'displayParams'=>array('required'=>false),
					          'customLabel'=>'{if ($PORTAL_ENABLED)}{sugar_translate label="LBL_PORTAL_FLAG" module="Notes"}{/if}',
					          'customCode'=>' {if ($PORTAL_ENABLED)}
											  {if $fields.portal_flag.value == "1"}
											  {assign var="checked" value="CHECKED"}
											  {else}
											  {assign var="checked" value=""}
											  {/if}
											  <input type="hidden" name="{$fields.portal_flag.name}" value="0"> 
											  <input type="checkbox" name="{$fields.portal_flag.name}" value="1" tabindex="1" {$checked}>
					        		          {/if}',
					    ),
	    			),
	    			array (
                        array('name' => 'description', 'label' => 'LBL_NOTE_STATUS'),
                    ),          
                             
  		),
  		
  	  	
	  'LBL_PANEL_ASSIGNMENT' => array(
	    array(
		    array ('name' => 'assigned_user_name','label' => 'LBL_ASSIGNED_TO'),
		    array('name'=>'team_name'), 
	    ),
	  ),  	
	)
);
?>