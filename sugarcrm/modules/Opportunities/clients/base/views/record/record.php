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

//BEGIN SUGARCRM flav=pro && flav!=ent ONLY
// PRO/CORP only fields
$fields = array(
    array(
        'name' => 'account_name',
        'related_fields' => array(
            'account_id'
        )
    ),
    array(
        'name' => 'date_closed',
    ),
    array(
        'name' => 'sales_stage',
        'required' => true,
    ),

    'probability',
    array(
        'name' => 'amount',
        'type' => 'currency',
        'label' => 'LBL_LIST_AMOUNT',
        'related_fields' => array(
            'amount',
            'currency_id',
            'base_rate',
        ),
        'required' => true,
        'currency_field' => 'currency_id',
        'base_rate_field' => 'base_rate',
    ),
    array(
        'name' => '',
        'view' => 'detail',
        'readonly' => true,
    ),
    array(
        'name' => 'best_case',
        'type' => 'currency',
        'label' => 'LBL_BEST_CASE',
        'related_fields' => array(
            'best_case',
            'currency_id',
            'base_rate',
        ),
        'currency_field' => 'currency_id',
        'base_rate_field' => 'base_rate',
    ),
    array(
        'name' => 'worst_case',
        'type' => 'currency',
        'label' => 'LBL_WORST_CASE',
        'related_fields' => array(
            'worst_case',
            'currency_id',
            'base_rate',
        ),
        'currency_field' => 'currency_id',
        'base_rate_field' => 'base_rate',
    ),
);

$fieldsHidden = array(
    'next_step',
    'opportunity_type',
    'lead_source',
    'campaign_name',
    array(
        'name' => 'description',
        'span' => 12,
    ),
    'assigned_user_name',
    'team_name',
    array(
        'name' => 'date_entered_by',
        'readonly' => true,
        'type' => 'fieldset',
        'label' => 'LBL_DATE_ENTERED',
        'fields' => array(
            array(
                'name' => 'date_entered',
            ),
            array(
                'type' => 'label',
                'default_value' => 'LBL_BY'
            ),
            array(
                'name' => 'created_by_name',
            ),
        ),
    ),
    array(
        'name' => 'date_modified_by',
        'readonly' => true,
        'type' => 'fieldset',
        'label' => 'LBL_DATE_MODIFIED',
        'fields' => array(
            array(
                'name' => 'date_modified',
            ),
            array(
                'type' => 'label',
                'default_value' => 'LBL_BY',
            ),
            array(
                'name' => 'modified_by_name',
            ),
        ),
    ),
);
//END SUGARCRM flav=pro && flav!=ent ONLY

//BEGIN SUGARCRM flav=ent ONLY
// ENT/ULT only fields
$fields = array(
    array(
        'name' => 'account_name',
        'related_fields' => array(
            'account_id'
        )
    ),
    array(
        'name' => 'date_closed',
        'readonly' => true,
    ),
    'sales_status',
    array(
        'name' => 'amount',
        'type' => 'currency',
        'label' => 'LBL_LIST_AMOUNT',
        'related_fields' => array(
            'amount',
            'currency_id',
            'base_rate',
        ),
        'readonly' => true,
        'currency_field' => 'currency_id',
        'base_rate_field' => 'base_rate',
    ),
    array(
        'name' => 'best_case',
        'type' => 'currency',
        'label' => 'LBL_BEST_CASE',
        'related_fields' => array(
            'best_case',
            'currency_id',
            'base_rate',
        ),
        'readonly' => true,
        'currency_field' => 'currency_id',
        'base_rate_field' => 'base_rate',
    ),
    array(
        'name' => 'worst_case',
        'type' => 'currency',
        'label' => 'LBL_WORST_CASE',
        'related_fields' => array(
            'worst_case',
            'currency_id',
            'base_rate',
        ),
        'readonly' => true,
        'currency_field' => 'currency_id',
        'base_rate_field' => 'base_rate',
    ),
    'opportunity_type',
    'assigned_user_name',
);

$fieldsHidden = array(
    'next_step',
    array(
        'name' => 'description',
        'span' => 12,
    ),
    'lead_source',
    'campaign_name',
    array(
        'name' => 'date_entered_by',
        'readonly' => true,
        'type' => 'fieldset',
        'label' => 'LBL_DATE_ENTERED',
        'fields' => array(
            array(
                'name' => 'date_entered',
            ),
            array(
                'type' => 'label',
                'default_value' => 'LBL_BY'
            ),
            array(
                'name' => 'created_by_name',
            ),
        ),
    ),
    'team_name',
    array(
        'name' => 'date_modified_by',
        'readonly' => true,
        'type' => 'fieldset',
        'label' => 'LBL_DATE_MODIFIED',
        'fields' => array(
            array(
                'name' => 'date_modified',
            ),
            array(
                'type' => 'label',
                'default_value' => 'LBL_BY',
            ),
            array(
                'name' => 'modified_by_name',
            ),
        ),
    ),
);
//END SUGARCRM flav=ent ONLY

$viewdefs['Opportunities']['base']['view']['record'] = array(
    'panels' => array(
        array(
            'name' => 'panel_header',
            'header' => true,
            'fields' => array(
                'name',
                array(
                    'type' => 'favorite',
                    'readonly' => true,
                ),
                array(
                    'type' => 'follow',
                    'readonly' => true,
                ),
            ),
        ),
        array(
            'name' => 'panel_body',
            'label' => 'LBL_PANEL_2',
            'columns' => 2,
            'labels' => true,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => $fields,
        ),
        array(
            'name' => 'panel_hidden',
            'hide' => true,
            'labelsOnTop' => true,
            'placeholders' => true,
            'columns' => 2,
            'fields' => $fieldsHidden,
        ),
    ),
);
