<?php
$viewdefs['Opportunities']['portal']['view']['edit'] = array(
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
                    'module' => 'Opportunities',
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
                        'name' => 'name',
                        'label' => 'Name',
                    ),
                    1 =>
                    array(
                        'name' => 'amount',
                        'label' => 'Opportunity Amount',
                    ),
                    2 =>
                    array(
                        'name' => 'opportunity_type',
                        'label' => 'Opp. Type',
                    ),
                    3 =>
                    array(
                        'name' => 'lead_source',
                        'label' => 'Lead Source',
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
