<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
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
 ********************************************************************************/
$viewdefs['DocumentRevisions']['EditView'] = array(
    'templateMeta' => array('form' => array('enctype'=>'multipart/form-data',
                                            'hidden'=>array('<input type="hidden" name="return_id" value="{$smarty.request.return_id}">'),
                                ),       
                            'maxColumns' => '2', 
                            'widths' => array(
                                array('label' => '10', 'field' => '30'), 
                                array('label' => '10', 'field' => '30')
                                ),
                            'javascript' => '<script type="text/javascript" src="include/javascript/popup_parent_helper.js?s={$SUGAR_VERSION}&c={$JS_CUSTOM_VERSION}"></script>
<script type="text/javascript" src="include/javascript/sugar_grp_jsolait.js"></script>
<script type="text/javascript" src="include/JSON.js?s={$SUGAR_VERSION}"></script>
<script type="text/javascript" src="modules/Documents/documents.js?s={$SUGAR_VERSION}&c={$JS_CUSTOM_VERSION}"></script>',
        ),
    'panels' =>array (
        '' => 
        array (
            array (
                array ( 'name' => 'document_name', 'type' => 'readonly' ),
                array ( 'name' => 'latest_revision', 'type' => 'readonly' ),
            ),
            array (
                'revision',
            ),
            
            array (
                'filename',
            	 //BEGIN SUGARCRM flav!=com ONLY
                'doc_type',
            	 //END SUGARCRM flav!=com ONLY
            ),
            
            array (
                array ( 'name' => 'change_log', 'size' => '126', 'maxlength' => '255' ),
            ),

        ),
    ),
);