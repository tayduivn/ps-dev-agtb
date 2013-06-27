<?php

/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

$viewdefs['Accounts']['base']['layout']['record-dashboard'] = array (
  'metadata' => 
  array (
    'components' => 
    array (
      array (
        'rows' => 
        array (
          array (
            array (
              'view' => 
              array (
                'name' => 'opportunity-metrics',
                'label' => 'Opportunitity Metrics',
              ),
              'width' => 12,
            ),
          ),
          array (
            array (
              'view' => 
              array (
                'name' => 'casessummary',
                'label' => 'Cases Summary',
              ),
              'width' => 12,
            ),
          ),
          array (
            array (
              'view' => 
              array (
                'name' => 'news',
                'label' => 'News Feed',
              ),
              'width' => 12,
            ),
          ),
          array (
            array (
              'view' => 
              array (
                'name' => 'interactions',
                'label' => 'Interactions',
                'filter_duration' => '7',
              ),
              'width' => 12,
            ),
          ),
        ),
        'width' => 12,
      ),
    ),
  ),
  'name' => 'My Dashboard',
);

