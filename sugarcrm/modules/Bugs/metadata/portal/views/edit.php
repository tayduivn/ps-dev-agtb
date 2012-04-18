<?php
$viewdefs['Bugs']['portal']['view']['edit'] = array(
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
                'events' =>
                 array(
                    'click' => 'function(){ window.history.back(); }',
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
                        'name' => 'bug_number',
                        'label' => 'Bug Number',
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
                ),
            ),
        ),
);
