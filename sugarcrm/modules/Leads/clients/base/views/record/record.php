<?php
//FILE SUGARCRM flav=pro || flav=sales ONLY
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

$viewdefs['Leads']['base']['view']['record'] = array(
    'buttons' => array(
        array(
            'type'      => 'button',
            'name'      => 'cancel_button',
            'label'     => 'LBL_CANCEL_BUTTON_LABEL',
            'css_class' => 'btn-invisible btn-link',
            'showOn'    => 'edit',
        ),
        array(
            'type'    => 'actiondropdown',
            'name'    => 'main_dropdown',
            'primary' => true,
            'buttons' => array(
                array(
                    'type'    => 'rowaction',
                    'event'   => 'button:edit_button:click',
                    'name'    => 'edit_button',
                    'label'   => 'LBL_EDIT_BUTTON_LABEL',
                    'primary' => true,
                    'showOn'  => 'view',
                    'value'=>'edit'
                ),
                array(
                    'type'    => 'rowaction',
                    'event'   => 'button:save_button:click',
                    'name'    => 'save_button',
                    'label'   => 'LBL_SAVE_BUTTON_LABEL',
                    'primary' => true,
                    'showOn'  => 'edit',
                    'value'   => 'edit',
                    'value'=>'edit'
                ),
                array(
                    'type'   => 'rowaction',
                    'event'  => 'button:delete_button:click',
                    'name'   => 'delete_button',
                    'label'  => 'LBL_DELETE_BUTTON_LABEL',
                    'showOn' => 'view',
                    'value'  => 'delete',
                    'value'  =>'edit'
                ),
                array(
                    'type'   => 'rowaction',
                    'event'  => 'button:duplicate_button:click',
                    'name'   => 'duplicate_button',
                    'label'  => 'LBL_DUPLICATE_BUTTON_LABEL',
                    'showOn' => 'view',
                    'value'  => 'create',
                    'value'=>'edit'
                ),
                array(
                    'type'   => 'rowaction',
                    'event'  => 'button:find_duplicates_button:click',
                    'name'   => 'find_duplicates_button',
                    'label'  => 'LBL_DUP_MERGE',
                    'showOn' => 'view',
                    'value'  => 'list',
                ),
                array(
                    'type'   => 'rowaction',
                    'event'  => 'button:lead_convert_button:click',
                    'name'   => 'lead_convert_button',
                    'label'  => 'LBL_CONVERT_BUTTON_LABEL',
                    'showOn' => 'view',
                    'value'  => 'edit',
                ),
                //TODO: Commented out for SugarCon.  Will add back once actions are ready
               /* array(
                    'type'  => 'rowaction',
                    'event' => 'button:create_related_button:click',
                    'name'  => 'create_related_button',
                    'label' => 'LBL_CREATE_RELATED_RECORD',
                    'value' => 'create',
                ),
                array(
                    'type'  => 'rowaction',
                    'event' => 'button:link_related_button:click',
                    'name'  => 'link_related_button',
                    'label' => 'LBL_ASSOC_RELATED_RECORD',
                    'value' => 'edit',
                ),*/
                array(
                    'type'   => 'rowaction',
                    'event'  => 'button:manage_subscriptions:click',
                    'name'   => 'convert_button',
                    'label'  => 'LBL_MANAGE_SUBSCRIPTIONS',
                    'showOn' => 'view',
                    'value'  => 'edit'
                ),
                array(
                    'type' => 'rowaction',
                    'event' => 'button:change_log_button:click',
                    'name' => 'change_log_button',
                    'label' => 'LNK_VIEW_CHANGE_LOG',
                    'value' => 'view'
                ),
            ),
        ),
        array(
            'name' => 'sidebar_toggle',
            'type' => 'sidebartoggle',
        ),
    ),
    'panels'  => array(
        array(
            'name'   => 'panel_header',
            'header' => true,
            'fields' => array(
                array(
                    'name'          => 'picture',
                    'type'          => 'image',
                    'width'         => 42,
                    'height'        => 42,
                    'dismiss_label' => true,
                ),
                array(
                    'name'   => 'fieldset_name',
                    'type'   => 'fieldset-with-labels',
                    'fields' => array(
                        array(
                            'name'               => 'salutation',
                            'type'               => 'enum',
                            'enum_width'         => 'auto',
                            'searchBarThreshold' => 7,
                        ),
                        'first_name',
                        'last_name',
                    ),
                ),
                array(
                    'type'           => 'badge',
                    'readonly'       => true,
                    'related_fields' => array(
                        'converted',
                        'account_id',
                        'contact_id',
                        'contact_name',
                        'opportunity_id',
                        'opportunity_name'
                    ),
                ),
                array(
                    'type' => 'favorite',
                ),
            ),
        ),
        array(
            'name'         => 'panel_body',
            'label'        => 'LBL_PANEL_2',
            'columns'      => 2,
            'labels'       => true,
            'labelsOnTop'  => true,
            'placeholders' => true,
            'fields'       => array(
                'account_name',
                'website',
                'title',
                'email',
                'department',
                'phone_mobile',
                array(
                    'name'   => 'fieldset_primaryaddress',
                    'type'   => 'fieldset',
                    'label'  => 'LBL_PRIMARY_ADDRESS',
                    'fields' => array(
                        array(
                            'name'        => 'primary_address_street',
                            'placeholder' => 'LBL_STREET',
                        ),
                        array(
                            'name'        => 'primary_address_city',
                            'placeholder' => 'LBL_CITY',
                        ),
                        array(
                            'name'        => 'primary_address_state',
                            'placeholder' => 'LBL_STATE',
                        ),
                        array(
                            'name'        => 'primary_address_postalcode',
                            'placeholder' => 'LBL_POSTAL_CODE',
                        ),
                        array(
                            'name'        => 'primary_address_country',
                            'placeholder' => 'LBL_COUNTRY',
                        ),
                    ),
                ),
                'phone_work',
                'do_not_call',
                'phone_fax',
            ),
        ),
        array(
            'name'         => 'panel_hidden',
            'hide'         => true,
            'label'        => 'LBL_PANEL_3',
            'columns'      => 2,
            'labels'       => true,
            'labelsOnTop'  => true,
            'placeholders' => true,
            'fields'       => array(
                array(
                    'name' => 'description',
                    'span' => 12,
                ),
                'status',
                'status_description',
                'lead_source',
                'lead_source_description',
                'campaign_name',
                'opportunity_amount',
                array(
                    'name'   => 'fieldset_altaddress',
                    'type'   => 'fieldset',
                    'label'  => 'LBL_ALT_ADDRESS',
                    'span'   => 12,
                    'fields' => array(
                        array(
                            'name'        => 'alt_address_street',
                            'placeholder' => 'LBL_STREET',
                        ),
                        array(
                            'name'        => 'alt_address_city',
                            'placeholder' => 'LBL_CITY',
                        ),
                        array(
                            'name'        => 'alt_address_state',
                            'placeholder' => 'LBL_STATE',
                        ),
                        array(
                            'name'        => 'alt_address_postalcode',
                            'placeholder' => 'LBL_POSTAL_CODE',
                        ),
                        array(
                            'name'        => 'alt_address_country',
                            'placeholder' => 'LBL_COUNTRY',
                        ),
                        array(
                            'name'    => 'copy',
                            'label'   => 'NTC_COPY_PRIMARY_ADDRESS',
                            'type'    => 'copy',
                            'mapping' => array(
                                'primary_address_street'     => 'alt_address_street',
                                'primary_address_city'       => 'alt_address_city',
                                'primary_address_state'      => 'alt_address_state',
                                'primary_address_postalcode' => 'alt_address_postalcode',
                                'primary_address_country'    => 'alt_address_country',
                            ),
                        ),
                    ),
                ),
                'assigned_user_name',
                array(
                    'name' => 'date_entered_by',
                    'readonly' => true,
                    'type'     => 'fieldset',
                    'label'    => 'LBL_DATE_ENTERED',
                    'fields'   => array(
                        array(
                            'name' => 'date_entered',
                        ),
                        array(
                            'type'          => 'label',
                            'default_value' => 'LBL_BY'
                        ),
                        array(
                            'name' => 'created_by_name',
                        ),
                    ),
                ),
                //BEGIN SUGARCRM flav=pro ONLY
                array(
                    "type" => "teamset",
                    "name" => "team_name",
                ),
                //END SUGARCRM flav=pro ONLY
                array(
                    'name'     => 'date_modified_by',
                    'readonly' => true,
                    'type'     => 'fieldset',
                    'label'    => 'LBL_DATE_MODIFIED',
                    'fields'   => array(
                        array(
                            'name' => 'date_modified',
                        ),
                        array(
                            'type'          => 'label',
                            'default_value' => 'LBL_BY'
                        ),
                        array(
                            'name' => 'modified_by_name',
                        ),
                    ),
                ),
            ),
        ),
    ),
);
