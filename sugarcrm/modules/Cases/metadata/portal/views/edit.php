<?php
$viewdefs['Cases']['portal']['view']['edit'] = array(
        'buttons' =>
        array(
            0 =>
            array(
                'name' => 'save_button',
                'type' => 'button',
                'label' => 'LBL_SAVE_BUTTON_LABEL',
                'value' => 'save',
                'primary' => true,
                'events' =>
                array(
                    'click' => 'function(){ var self = this; this.model.save(null, {success:function(){self.app.navigate(self.context, self.model, \'detail\');}});}',
                ),
            ),
            1 =>
            array(
                'name' => 'cancel_button',
                'type' => 'button',
                'label' => 'LBL_CANCEL_BUTTON_LABEL',
                'value' => 'cancel',
                'route' =>
                array(
                    'action' => 'detail',
                    'module' => 'Cases',
                ),
                'primary' => false,
            ),
        ),
        'panels' =>
        array(
            0 =>
            array(
                'label' => 'Details',
                'fields' =>
                array(
                    0 =>
                    array(
                        'name' => 'case_number',
                        'label' => 'LBL_CASE_NUMBER',
                        'class' => 'foo',
                    ),
                    1 =>
                    array(
                        'name' => 'name',
                        'label' => 'LBL_SUBJECT',
                    ),
                    2 =>
                    array(
                        'name' => 'status',
                        'label' => 'LBL_LIST_STATUS',
                    ),
                    3 =>
                    array(
                        'name' => 'description',
                        'label' => 'LBL_DESCRIPTION',
                    ),
                    4 =>
                    array(
                        'name' => 'date_modified',
                        'label' => 'LBL_LAST_MODIFIED',
                    ),
                ),
            ),
        ),
);
