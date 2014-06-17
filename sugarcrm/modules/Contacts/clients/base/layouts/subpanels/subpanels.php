<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
$viewdefs['Contacts']['base']['layout']['subpanels'] = array (
  'components' => array (
      array(
          'layout' => 'subpanel',
          'label' => 'LBL_CALLS_SUBPANEL_TITLE',
          'context' => array(
              'link' => 'calls',
          ),
      ),
      array(
          'layout' => 'subpanel',
          'label' => 'LBL_MEETINGS_SUBPANEL_TITLE',
          'context' => array(
              'link' => 'meetings',
          ),
      ),
      array(
          'layout' => 'subpanel',
          'label' => 'LBL_TASKS_SUBPANEL_TITLE',
          'context' => array(
              'link' => 'all_tasks',
          ),
      ),
      array(
          'layout' => 'subpanel',
          'label' => 'LBL_NOTES_SUBPANEL_TITLE',
          'context' => array(
              'link' => 'notes',
          ),
      ),
    array (
      'layout' => "subpanel",
      'label' => 'LBL_LEADS_SUBPANEL_TITLE',
      'context' => array (
        'link' => 'leads',
      ),
    ),
    array (
      'layout' => "subpanel",
      'label' => 'LBL_OPPORTUNITIES_SUBPANEL_TITLE',
      'context' => array (
        'link' => 'opportunities',
      ),
    ),
    array(
        'layout' => 'subpanel',
        'label' => 'LBL_EMAILS_SUBPANEL_TITLE',
        'context' => array (
            'link' => 'archived_emails',
        ),
    ),
    array (
      'layout' => "subpanel",
      'label' => 'LBL_CASES_SUBPANEL_TITLE',
      'context' => array (
        'link' => 'cases',
      ),
    ),
    array (
      'layout' => "subpanel",
      'label' => 'LBL_BUGS_SUBPANEL_TITLE',
      'context' => array (
        'link' => 'bugs',
      ),
    ),
    array (
      'layout' => 'subpanel',
      'label' => 'LBL_DIRECT_REPORTS_SUBPANEL_TITLE',
      'override_subpanel_list_view' => 'subpanel-for-contacts',
      'context' => array (
        'link' => 'direct_reports',
      ),
    ),
    array (
      'layout' => "subpanel",
      'label' => 'LBL_DOCUMENTS_SUBPANEL_TITLE',
      'context' => array (
        'link' => 'documents',
      ),
    ),
    array (
      'layout' => 'subpanel',
      'label' => 'LBL_QUOTES_SUBPANEL_TITLE',
        'context' => array (
          'link' => 'quotes',
          'collectionOptions' => array(
            'params' => array(
              'ignore_role' => 1,
            ),
          ),
        ),
    ),
    array (
      'layout' => 'subpanel',
      'label' => 'LBL_CONTRACTS_SUBPANEL_TITLE',
      'context' => array (
        'link' => 'contracts',
      ),
    ),
    array (
      'layout' => 'subpanel',
      'label' => 'LBL_PRODUCTS_SUBPANEL_TITLE',
      'context' => array (
        'link' => 'products',
      ),
    ),
    array (
      'layout' => 'subpanel',
      'label' => 'LBL_CAMPAIGN_LIST_SUBPANEL_TITLE',
      'context' => array (
        'link' => 'campaigns',
      ),
    ),
  ),
  'type' => 'subpanels',
  'span' => 12,
);
