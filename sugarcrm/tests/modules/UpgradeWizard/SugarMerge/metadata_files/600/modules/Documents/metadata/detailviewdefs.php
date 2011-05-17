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
 * by SugarCRM are Copyright (C) 2004-2009 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
$viewdefs['Documents']['DetailView'] = array(
'templateMeta' => array('maxColumns' => '2',
                        'form' => array('hidden'=>array('<input type="hidden" name="old_id" value="{$fields.document_revision_id.value}">')), 
                        'widths' => array(
                                        array('label' => '10', 'field' => '30'), 
                                        array('label' => '10', 'field' => '30')
                                        ),
                        ),
'panels' =>array (
  
  array (
    
    array (
      'name' => 'document_name',
      'label' => 'LBL_DOC_NAME',
    ),
    
    array (
      'name' => 'revision',
      'label' => 'LBL_DOC_VERSION',
    ),
  ),
  
  array (
    
    array (
      'name' => 'is_template',
      'label' => 'LBL_DET_IS_TEMPLATE',
    ),
    
    array (
      'name' => 'template_type',
      'label' => 'LBL_DET_TEMPLATE_TYPE',
    ),
  ),
  
  array (
      'category_id',
      'subcategory_id',
  ),
  
  array (
	  'status',

	  'team_name',

  ),
  
  array (
      'last_rev_created_name',
      'last_rev_create_date',
  ),
  
  array (
      'active_date',
      'exp_date',
  ),
  
  array (
    'related_doc_name',
    'related_doc_rev_number',
  ),
  
  array (
    
    array (
      'name' => 'description',
      'label' => 'LBL_DOC_DESCRIPTION',
    ),
  ),
  
  array (
    
    array (
      'name' => 'filename',
      'displayParams' => array('link'=>'filename', 'id'=>'document_revision_id'), 
    ),

  ),
)


   
);
?>
