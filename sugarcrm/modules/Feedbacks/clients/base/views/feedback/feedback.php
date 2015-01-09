<?php
$viewdefs['Feedbacks']['base']['view']['feedback'] = array(
    'panels' => array(
        array(
            'label' => 'LBL_PANEL_CSAT',
            'fields' => array(
                array(
                    'name' => 'feedback_csat',
                    'type' => 'rating',
                    'rate' => 5,
                    'required' => true,
                    'template' => 'edit',
                    'css_class' => 'field-rating',
                ),
            ),
        ),
        array(
            'fields' => array(
                array(
                    'name' => 'feedback_text',
                    'type' => 'textarea',
                    'template' => 'edit',
                    'required' => true,
                    'css_class' => 'feedback-text',
                    'placeholder' => 'LBL_FEEDBACK_TEXT_PLACEHOLDER'
                ),
            ),
        ),
    ),
);
