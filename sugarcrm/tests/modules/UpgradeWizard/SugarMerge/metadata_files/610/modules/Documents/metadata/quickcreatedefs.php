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

$viewdefs['Documents']['QuickCreate'] = array(
    'templateMeta' => array('form' => array('enctype'=>'multipart/form-data',
                                            'hidden'=>array('<input type="hidden" name="old_id" value="{$fields.document_revision_id.value}">',
                                                            '<input type="hidden" name="parent_id" value="{$smarty.request.parent_id}">',
                                                            '<input type="hidden" name="parent_type" value="{$smarty.request.parent_type}">',)),
                                            
                            'maxColumns' => '2', 
                            'widths' => array(
                                            array('label' => '10', 'field' => '30'), 
                                            array('label' => '10', 'field' => '30')
                                            ),
                            'includes' => 
                              array (
                                array('file' => 'include/javascript/popup_parent_helper.js'),
                                array('file' => 'cache/include/javascript/sugar_grp_jsolait.js'),
                                array('file' => 'modules/Documents/documents.js'),
                              ),
),
 'panels' =>array (
  'default' => 
  array (
    
    array (

      array('name'=>'uploadfile', 
            'customCode' => '<input type="hidden" name="escaped_document_name"><input name="uploadfile" type="file" size="30" maxlength="" onchange="setvalue(this);" value="{$fields.filename.value}">{$fields.filename.value}',
            'displayParams'=>array('required'=>true),
            ),
      'status_id',            
    ),
    
    array (
      'document_name',
      array('name'=>'revision',
            'customCode' => '<input name="revision" type="text" value="{$fields.revision.value}">'
           ),    
    ),    
    
    array (
        array (
          'name' => 'template_type',
          'label' => 'LBL_DET_TEMPLATE_TYPE',
        ),
    	array (
          'name' => 'is_template',
          'label' => 'LBL_DET_IS_TEMPLATE',
        ),
    ),
    
    array (
       array('name'=>'active_date','displayParams'=>array('required'=>true)),
       'category_id',
    ),
    
    array (
      'exp_date',
      'subcategory_id',
    ),    
    
    array (
      array('name'=>'team_name','displayParams'=>array('required'=>true)),
     
    ),

    array (
      array('name'=>'description', 'displayParams'=>array('rows'=>10, 'cols'=>120)),
    ),
  ),
)

);
?>