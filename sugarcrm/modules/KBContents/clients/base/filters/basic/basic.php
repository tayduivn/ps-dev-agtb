<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
$viewdefs['KBContents']['base']['filter']['basic'] = array(
    'create' => true,
    'quicksearch_field' => array('name'),
    'quicksearch_priority' => 1,
    'filters' => array(
        array(
            'id' => 'all_records',
            'name' => 'LBL_LISTVIEW_FILTER_ALL',
            'filter_definition' => array(),
            'editable' => false,
        ),
        array(
            'id' => 'draft',
            'name' => 'LBL_KBSTATUS_DRAFT',
            'filter_definition' => array(
                array(
                    'status' => array('$equals' => KBContent::ST_DRAFT),
                ),
            ),
            'editable' => false,
        ),
        array(
            'id' => 'expired',
            'name' => 'LBL_KBSTATUS_EXPIRED',
            'filter_definition' => array(
                array(
                    'status' => array('$equals' => KBContent::ST_EXPIRED),
                ),
            ),
            'editable' => false,
        ),
        array(
            'id' => 'in-review',
            'name' => 'LBL_KBSTATUS_INREVIEW',
            'filter_definition' => array(
                array(
                    'status' => array('$equals' => KBContent::ST_IN_REVIEW),
                ),
            ),
            'editable' => false,
        ),
        array(
            'id' => 'published-in',
            'name' => 'LBL_KBSTATUS_PUBLISHED_IN',
            'filter_definition' => array(
                array(
                    'status' => array('$equals' => KBContent::ST_PUBLISHED_IN),
                ),
            ),
            'editable' => false,
        ),
        array(
            'id' => 'published-ex',
            'name' => 'LBL_KBSTATUS_PUBLISHED_EX',
            'filter_definition' => array(
                array(
                    'status' => array('$equals' => KBContent::ST_PUBLISHED_EX),
                ),
            ),
            'editable' => false,
        ),
        array(
            'id' => 'recently_viewed',
            'name' => 'LBL_RECENTLY_VIEWED',
            'filter_definition' => array(
                '$tracker' => '-7 day',
            ),
            'editable' => false,
        ),
        array(
            'id' => 'recently_created',
            'name' => 'LBL_NEW_RECORDS',
            'filter_definition' => array(
                'date_entered' => array(
                    '$dateRange' => 'last_7_days',
                ),
            ),
            'editable' => false,
        ),
    ),
);
