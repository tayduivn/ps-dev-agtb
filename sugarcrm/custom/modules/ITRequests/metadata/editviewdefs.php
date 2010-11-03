<?php
$viewdefs ['ITRequests'] =
        array(
            'EditView' =>
            array(
                'templateMeta' =>
                array(
                    'maxColumns' => '2',
                    'form' =>
                    array(
                        'headerTpl' => 'modules/ITRequests/tpls/header.tpl',
                    ),
                    'widths' =>
                    array(
                        0 =>
                        array(
                            'label' => '15',
                            'field' => '30',
                        ),
                        1 =>
                        array(
                            'label' => '15',
                            'field' => '30',
                        ),
                    ),
                    'useTabs' => false,
                ),
                'panels' =>
                array(
                    'default' =>
                    array(
                        0 =>
                        array(
                            0 =>
                            array(
                                'name' => 'itrequest_number',
                                'customCode' => '{if $fields.itrequest_number.value != ""}{$fields.itrequest_number.value}{else}New ITRequest{/if}',
                            ),
                        ),
                        1 =>
                        array(
                            0 =>
                            array(
                                'name' => 'priority',
                                'comment' => 'The priority of the itrequest',
                                'label' => 'LBL_PRIORITY',
                            ),
                        ),
                        2 =>
                        array(
                            0 =>
                            array(
                                'name' => 'department_c',
                                'studio' => 'visible',
                                'label' => 'LBL_DEPARTMENT',
                            ),
                        ),
                        3 =>
                        array(
                            0 =>
                            array(
                                'name' => 'department_category_c',
                                'studio' => 'visible',
                                'label' => 'LBL_DEPARTMENT_CATEGORY',
                            ),
                        ),
                        4 =>
                        array(
                            0 =>
                            array(
                                'name' => 'name',
                                'displayParams' =>
                                array(
                                    'size' => 100,
                                    'required' => true,
                                ),
                            ),
                        ),
                        5 =>
                        array(
                            0 =>
                            array(
                                'name' => 'description',
                                'displayParams' =>
                                array(
                                    'rows' => 12,
                                    'cols' => 120,
                                ),
                            ),
                        ),
                        6 =>
                        array(
                            0 =>
                            array(
                                'name' => 'resolution',
                                'customCode' => '{if $NEWITR=="false"}{$fields.resolution.value|nl2br}{if $fields.resolution.value != ""}<br /><br />{/if}<h4>Enter New Work Log</h4><textarea name="new_log" cols="120" rows="6"></textarea>{/if}',
                            ),
                        ),
                    ),
                    'lbl_editview_panel1' =>
                    array(
                        0 =>
                        array(
                            0 =>
                            array(
                                'name' => 'status',
                                'comment' => 'The status of the itrequest',
                                'label' => 'LBL_STATUS',
                            ),
                            1 =>
                            array(
                                'name' => 'target_date',
                                'comment' => 'This is the targeted completion date for the request',
                                'label' => 'LBL_TARGET_DATE',
                            ),

                        ),
                        1 =>
                        array(
                            0 =>
                            array(
                                'name' => 'escalation_c',
                                'label' => 'LBL_ESCALATION',
                            ),
                            1 =>
                            array(
                                'name' => 'assigned_user_name',
                            ),

                        ),
                        2 =>
                        array(
                            0 =>
                            array(
                                'name' => 'development_time',
                                'comment' => 'This is the number of hours required to complete the request.',
                                'label' => 'LBL_DEVELOPMENT_TIME',
                            ),
                            1 =>
                            array(
                                'name' => 'team_name',
                                'displayParams' =>
                                array(
                                    'display' => true,
                                ),
                            ),
                        ),
                        3 =>
                        array(
                            0 =>array(
                                'name' => 'project_c',
                                'studio' => 'visible',
                                'label' => 'LBL_PROJECT',
                            ),
                        )
                    ),
                ),
            ),
        );
?>
