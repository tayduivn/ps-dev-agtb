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

$listViewDefs['Touchpoints'] =
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
  'width' => '80%',
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
  'SCORE' => 
  array (
    'width' => '20%',
    'label' => 'LBL_SCORE',
    'default' => true,
  ),
  'SCRUBBED' => 
  array (
    'width' => '20%',
    'label' => 'LBL_SCRUBBED',
    'default' => true,
  ),
  'CAMPAIGN_ID' =>
  array (
    'width' => '20%',
    'label' => 'LBL_LIST_CAMPAIGN_NAME',
    'default' => true,
  ),
  'SCRUB_RESULT' => 
  array (
    'width' => '20%',
    'label' => 'LBL_SCRUB_RESULT',
    'default' => true,
    'customCode' => '<span {$SCRUBBED_STYLE}>{$SCRUB_RESULT}</span>',
  ),
  'ASSIGNED_USER_NAME' =>
  array (
    'width' => '10%',
    'label' => 'LBL_ASSIGNED_TO_NAME',
    'default' => true,
  ),
  'COMPANY_NAME' =>
  array (
    'width' => '20%',
    'label' => 'LBL_COMPANY_NAME',
    'default' => false,
  ),
  'TITLE' =>
  array (
    'width' => '20%',
    'label' => 'LBL_LIST_TITLE',
    'default' => false,
  ),
);
