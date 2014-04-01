<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
$viewdefs['KBSContents']['base']['layout']['subpanels'] = array (
  'components' => array (
      array(
          'layout' => 'subpanel',
          'label' => 'LBL_LOCALIZATIONS_SUBPANEL_TITLE',
          'override_subpanel_list_view' => 'subpanel-for-localizations',
          'override_paneltop_view' => 'panel-top-for-localizations',
          'context' => array(
              'link' => 'localizations',
          ),
      ),
      array(
          'layout' => 'subpanel',
          'label' => 'LBL_REVISIONS_SUBPANEL_TITLE',
          'override_subpanel_list_view' => 'subpanel-for-revisions',
          'override_paneltop_view' => 'panel-top-for-revisions',
          'context' => array(
              'link' => 'revisions',
          ),
      ),
  ),
  'type' => 'subpanels',
  'span' => 12,
);
