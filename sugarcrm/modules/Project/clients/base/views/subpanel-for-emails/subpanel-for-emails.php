<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (\“MSA\”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
$viewdefs['Project']['base']['view']['subpanel-for-emails'] = array(
  'panels' =>
  array(
    array(
      'name' => 'panel_header',
      'label' => 'LBL_PANEL_1',
      'fields' =>
      array(
        array(
          'label' => 'LBL_LIST_NAME',
          'enabled' => true,
          'default' => true,
          'name' => 'name',
          'link' => true,
        ),
        array(
          'target_record_key' => 'assigned_user_id',
          'target_module' => 'Users',
          'label' => 'LBL_LIST_ASSIGNED_USER_ID',
          'enabled' => true,
          'default' => true,
          'name' => 'assigned_user_name',
          'sortable' => false,
        ),
        array(
          'label' => 'LBL_DATE_START',
          'enabled' => true,
          'default' => true,
          'name' => 'estimated_start_date',
        ),
        array(
          'label' => 'LBL_DATE_END',
          'enabled' => true,
          'default' => true,
          'name' => 'estimated_end_date',
        ),
      ),
    ),
  ),
);
