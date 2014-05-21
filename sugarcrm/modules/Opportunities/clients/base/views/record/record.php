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
        'related_fields' => array(
            'date_closed_timestamp'
        )
    ),
    array(
        'name' => 'sales_stage',
    ),
    'probability',
    array(
        'name' => 'amount',
        'type' => 'currency',
        'label' => 'LBL_LIKELY',
        'related_fields' => array(
            'amount',
            'currency_id',
            'base_rate',
        ),
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
        'label' => 'LBL_BEST',
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
        'label' => 'LBL_WORST',
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
        'related_fields' => array(
            'date_closed_timestamp'
        )
    ),
    'sales_status',
    array(
        'name' => 'amount',
        'type' => 'currency',
        'label' => 'LBL_LIKELY',
        'related_fields' => array(
            'amount',
            'currency_id',
            'base_rate',
        ),
        'currency_field' => 'currency_id',
        'base_rate_field' => 'base_rate',
    ),
    array(
        'name' => 'best_case',
        'type' => 'currency',
        'label' => 'LBL_BEST',
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
        'label' => 'LBL_WORST',
        'related_fields' => array(
            'worst_case',
            'currency_id',
            'base_rate',
        ),
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
    'buttons' => array(
        array(
            'type' => 'button',
            'name' => 'cancel_button',
            'label' => 'LBL_CANCEL_BUTTON_LABEL',
            'css_class' => 'btn-invisible btn-link',
            'showOn' => 'edit',
        ),
        array(
            'type' => 'rowaction',
            'event' => 'button:save_button:click',
            'name' => 'save_button',
            'label' => 'LBL_SAVE_BUTTON_LABEL',
            'css_class' => 'btn btn-primary',
            'showOn' => 'edit',
            'acl_action' => 'edit',
        ),
        array(
            'type' => 'actiondropdown',
            'name' => 'main_dropdown',
            'primary' => true,
            'showOn' => 'view',
            'buttons' => array(
                array(
                    'type' => 'rowaction',
                    'event' => 'button:edit_button:click',
                    'name' => 'edit_button',
                    'label' => 'LBL_EDIT_BUTTON_LABEL',
                    'acl_action' => 'edit',
                ),
                array(
                    'type' => 'shareaction',
                    'name' => 'share',
                    'label' => 'LBL_RECORD_SHARE_BUTTON',
                    'acl_action' => 'view',
                ),
                array(
                    'type' => 'pdfaction',
                    'name' => 'download-pdf',
                    'label' => 'LBL_PDF_VIEW',
                    'action' => 'download',
                    'acl_action' => 'view',
                ),
                array(
                    'type' => 'pdfaction',
                    'name' => 'email-pdf',
                    'label' => 'LBL_PDF_EMAIL',
                    'action' => 'email',
                    'acl_action' => 'view',
                ),
                array(
                    'type' => 'divider',
                ),
                array(
                    'type' => 'rowaction',
                    'event' => 'button:find_duplicates_button:click',
                    'name' => 'find_duplicates_button',
                    'label' => 'LBL_DUP_MERGE',
                    'acl_action' => 'edit',
                ),
                array(
                    'type' => 'rowaction',
                    'event' => 'button:duplicate_button:click',
                    'name' => 'duplicate_button',
                    'label' => 'LBL_DUPLICATE_BUTTON_LABEL',
                    'acl_module' => 'Opportunities',
                    'acl_action' => 'create',
                ),
                array(
                    'type' => 'rowaction',
                    'event' => 'button:historical_summary_button:click',
                    'name' => 'historical_summary_button',
                    'label' => 'LBL_HISTORICAL_SUMMARY',
                    'acl_action' => 'view',
                ),
                array(
                    'type' => 'rowaction',
                    'event' => 'button:audit_button:click',
                    'name' => 'audit_button',
                    'label' => 'LNK_VIEW_CHANGE_LOG',
                    'acl_action' => 'view',
                ),
                array(
                    'type' => 'divider',
                ),
                array(
                    'type' => 'rowaction',
                    'event' => 'button:delete_button:click',
                    'name' => 'delete_button',
                    'label' => 'LBL_DELETE_BUTTON_LABEL',
                    'acl_action' => 'delete',
                ),
            ),
        ),
        array(
            'name' => 'sidebar_toggle',
            'type' => 'sidebartoggle',
        ),
    ),
    'panels' => array(
        array(
            'name' => 'panel_header',
            'header' => true,
            'fields' => array(
                array(
                    'name'          => 'picture',
                    'type'          => 'avatar',
                    'size'          => 'large',
                    'dismiss_label' => true,
                    'readonly'      => true,
                ),
                array(
                    'name' => 'name',
                    //BEGIN SUGARCRM flav=ent ONLY
                    'related_fields' => array(
                        'total_revenue_line_items',
                        'closed_revenue_line_items'
                    )
                    //END SUGARCRM flav=ent ONLY
                ),
                array(
                    'name' => 'favorite',
                    'label' => 'LBL_FAVORITE',
                    'type' => 'favorite',
                    'dismiss_label' => true,
                ),
                array(
                    'name' => 'follow',
                    'label'=> 'LBL_FOLLOW',
                    'type' => 'follow',
                    'readonly' => true,
                    'dismiss_label' => true,
                ),
            ),
        ),
        array(
            'name' => 'panel_body',
            'label' => 'LBL_RECORD_BODY',
            'columns' => 2,
            'labels' => true,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => $fields,
        ),
        array(
            'name' => 'panel_hidden',
            'label' => 'LBL_RECORD_SHOWMORE',
            'hide' => true,
            'labelsOnTop' => true,
            'placeholders' => true,
            'columns' => 2,
            'fields' => $fieldsHidden,
        ),
    ),
);
