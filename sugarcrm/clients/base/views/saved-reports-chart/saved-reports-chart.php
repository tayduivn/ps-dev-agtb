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

$viewdefs['base']['view']['saved-reports-chart'] = array(
    'dashlets' => array(
        array(
            'label' => 'LBL_DASHLET_SAVED_REPORTS_CHART',
            'description' => 'LBL_DASHLET_SAVED_REPORTS_CHART_DESC',
            'config' => array(

            ),
            'preview' => array(

            ),
        )
    ),
    'dashlet_config_panels' => array(
        array(
            'name' => 'panel_body',
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => array(
                array(
                    'name' => 'saved_report_id',
                    'label' => 'LBL_REPORT_SELECT',
                    'type' => 'enum',
                    'options' => array('' => ''),
                ),
                array(
                    'name' => 'auto_refresh',
                    'label' => 'LBL_REPORT_AUTO_REFRESH',
                    'type' => 'enum',
                    'options' => 'sugar7_dashlet_reports_auto_refresh_options'
                ),
                array(
                    'name' => 'chart_type',
                    'label' => 'Chart type',
                    'type' => 'enum',
                    'sort_alpha' => true,
                    'ordered' => true,
                    'searchBarThreshold' => -1,
                    'options' => 'd3_chart_types',
                ),

                array(
                    'name' => 'editReport',
                    'label' => 'LBL_REPORT_EDIT',
                    'type' => 'button',
                    'css_class' => 'btn-invisible btn-link btn-inline',
                    'dismiss_label' => true,
                ),

                array(
                    'name' => 'title_options',
                    'type' => 'fieldset',
                    'inline' => true,
                    'show_child_labels' => false,
                    'label' => 'Show title',
                    'toggle' => 'show_title',
                    'dependent' => 'report_title',
                    'fields' => array(
                        array(
                            'name' => 'show_title',
                            'type' => 'bool',
                            'default' => 0,
                            'css_class' => 'align-top',
                        ),
                        array(
                            'name' => 'report_title',
                        ),
                    ),
                ),

                array(
                    'name' => 'show_legend',
                    'label' => 'Show legend',
                    'type' => 'bool',
                    'default' => 1,
                ),

                array(
                    'name' => 'x_label_options',
                    'type' => 'fieldset',
                    'inline' => true,
                    'show_child_labels' => false,
                    'label' => 'Show x-axis label',
                    'toggle' => 'show_x_label',
                    'dependent' => 'x_axis_label',
                    'fields' => array(
                        array(
                            'name' => 'show_x_label',
                            'type' => 'bool',
                            'default' => 0,
                        ),
                        array(
                            'name' => 'x_axis_label',
                        ),
                    ),
                ),

                array(
                    'name' => 'tickDisplayMethods',
                    'type' => 'fieldset',
                    'inline' => true,
                    'show_child_labels' => true,
                    'label' => 'Tick display methods',
                    'css_class' => 'fieldset-wrap',
                    'fields' => array(
                        array(
                            'name' => 'wrapTicks',
                            'text' => 'Wrap ticks',
                            'type' => 'bool',
                            'default' => 1,
                        ),
                        array(
                            'name' => 'staggerTicks',
                            'text' => 'Stagger ticks',
                            'type' => 'bool',
                            'default' => 1,
                        ),
                        array(
                            'name' => 'rotateTicks',
                            'text' => 'Rotate ticks',
                            'type' => 'bool',
                            'default' => 1,
                        ),
                    ),
                ),

                array(
                    'name' => 'y_label_options',
                    'type' => 'fieldset',
                    'inline' => true,
                    'show_child_labels' => false,
                    'label' => 'Show y-axis label',
                    'toggle' => 'show_y_label',
                    'dependent' => 'y_axis_label',
                    'fields' => array(
                        array(
                            'name' => 'show_y_label',
                            'type' => 'bool',
                            'default' => 0,
                        ),
                        array(
                            'name' => 'y_axis_label',
                        ),
                    ),
                ),

                array(
                ),

                array(
                    'name' => 'showValues',
                    'label' => 'Bar chart value placement',
                    'type' => 'enum',
                    'default' => false,
                    'options' => 'd3_value_placement',
                ),

                array(
                    'name' => 'groupDisplayOptions',
                    'type' => 'fieldset',
                    'inline' => true,
                    'show_child_labels' => false,
                    'label' => 'Bar chart display options',
                    'css_class' => 'fieldset-wrap',
                    'fields' => array(
                        array(
                            'name' => 'allowScroll',
                            'text' => 'Allow scrolling',
                            'type' => 'bool',
                            'default' => 1,
                        ),
                        array(
                            'name' => 'stacked',
                            'text' => 'Stack data series',
                            'type' => 'bool',
                            'default' => 1,
                        ),
                        array(
                            'name' => 'hideEmptyGroups',
                            'text' => 'Hide empty groups',
                            'type' => 'bool',
                            'default' => 1,
                        ),
                    ),
                ),
            ),
        ),
    ),
    'chart' => array(
        'name' => 'chart',
        'label' => 'Chart',
        'type' => 'chart',
        'view' => 'detail'
    ),
);
