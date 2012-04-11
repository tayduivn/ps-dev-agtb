<?php
$viewdefs['Cases']['portal']['view']['edit'] = array(
        'buttons' =>
        array(
            0 =>
            array(
                'name' => 'save_button',
                'type' => 'button',
                'label' => 'Save',
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
                'label' => 'Cancel',
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
                        'label' => 'Case Number',
                        'class' => 'foo',
                    ),
                    1 =>
                    array(
                        'name' => 'name',
                        'label' => 'Name',
                    ),
                    2 =>
                    array(
                        'name' => 'status',
                        'label' => 'Status',
                    ),
                    3 =>
                    array(
                        'name' => 'description',
                        'label' => 'Description',
                    ),
                    4 =>
                    array(
                        'name' => 'date_modified',
                        'label' => 'Modifed Date',
                    ),
                    5 =>
                    array(
                        'name' => 'assigned_user_name',
                        'label' => 'Assigned User Name',
                    ),
                ),
            ),
        ),
);
