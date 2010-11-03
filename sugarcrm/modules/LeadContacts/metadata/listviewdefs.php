<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2007 SugarCRM, Inc.; All Rights Reserved.
 */




$listViewDefs['LeadContacts'] =
array (
  'FULL_NAME' => 
  array (
    'name' => 'full_name',
    'rname' => 'full_name',
    'vname' => 'LBL_NAME',
    'type' => 'name',
    'fields' => 
    array (
      0 => 'first_name',
      1 => 'last_name',
    ),
    'sort_on' => 'last_name',
    'source' => 'non-db',
    'group' => 'last_name',
    'len' => '510',
    'db_concat_fields' => 
    array (
      0 => 'first_name',
      1 => 'last_name',
    ),
    'width' => '10%',
    'link' => true,
    'label' => 'LBL_NAME',
    'related_fields' => 
    array (
      0 => 'first_name',
      1 => 'last_name',
    ),
    'orderBy' => 'last_name',
    'default' => true,
  ),
  'TITLE' => 
  array (
    'width' => '10%',
    'label' => 'LBL_TITLE',
    'default' => true,
  ),
  'LEADACCOUNT_NAME' => 
  array (
    'width' => '34%', 
    'label' => 'LBL_LEADACCOUNT_NAME', 
    'module' => 'LeadAccounts',
    'id' => 'LEADACCOUNT_ID',
    'link' => true,
    'default' => true,
    'sortable' =>  true,
    'ACLTag' => 'LEADACCOUNT',
    'related_fields' => array('leadaccount_id')),
  'SCORE' => 
  array (
    'width' => '10%',
    'label' => 'LBL_SCORE',
    'default' => true,
  ),
  'TEAM_NAME' => 
  array (
    'width' => '10%',
    'label' => 'LBL_TEAM',
    'default' => true,
  ),
  'ASSIGNED_USER_NAME' => 
  array (
    'width' => '10%',
    'label' => 'LBL_ASSIGNED_TO_NAME',
    'default' => true,
  ),
  'CREATED_BY_NAME' => 
  array (
    'width' => '10%',
    'label' => 'LBL_CREATED',
    'default' => false,
  ),
  'MODIFIED_BY_NAME' => 
  array (
    'width' => '10%',
    'label' => 'LBL_MODIFIED_NAME',
    'default' => false,
  ),
  'DATE_MODIFIED' => 
  array (
    'width' => '10%',
    'label' => 'LBL_DATE_MODIFIED',
    'default' => false,
  ),
  'DATE_ENTERED' => 
  array (
    'width' => '10%',
    'label' => 'LBL_DATE_ENTERED',
    'default' => false,
  ),
  'DEPARTMENT' => 
  array (
    'width' => '10%',
    'label' => 'LBL_DEPARTMENT',
    'default' => false,
  ),
  'PHONE_FAX' => 
  array (
    'width' => '10%',
    'label' => 'LBL_FAX_PHONE',
    'default' => false,
  ),
  'PHONE_WORK' => 
  array (
    'width' => '10%',
    'label' => 'LBL_OFFICE_PHONE',
    'default' => false,
  ),
  'PHONE_OTHER' => 
  array (
    'width' => '10%',
    'label' => 'LBL_OTHER_PHONE',
    'default' => false,
  ),
  'PHONE_HOME' => 
  array (
    'width' => '10%',
    'label' => 'LBL_HOME_PHONE',
    'default' => false,
  ),
  'ASSISTANT_PHONE' => 
  array (
    'width' => '10%',
    'label' => 'LBL_ASSISTANT_PHONE',
    'default' => false,
  ),
  'CONVERTED' => 
  array (
    'width' => '10%',
    'label' => 'LBL_CONVERTED',
    'default' => true,
  ),
  'ASSISTANT' => 
  array (
    'width' => '10%',
    'label' => 'LBL_ASSISTANT',
    'default' => false,
  ),
  'PHONE_MOBILE' => 
  array (
    'width' => '10%',
    'label' => 'LBL_MOBILE_PHONE',
    'default' => false,
  ),
);
