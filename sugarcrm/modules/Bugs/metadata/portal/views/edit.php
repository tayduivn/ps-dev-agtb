<?php
$viewdefs['Bugs']['portal']['view']['edit'] =
    array(
        'buttons' =>
        array(
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
        'templateMeta' =>
        array(
            'maxColumns' => '2',
            'widths' =>
            array(
                array(
                    'label' => '10',
                    'field' => '30',
                ),
                array(
                    'label' => '10',
                    'field' => '30',
                ),
            ),
            'formId' => 'BugEditView',
            'formName' => 'BugEditView',
            'hiddenInputs' =>
            array(
                'module' => 'Bugs',
                'returnmodule' => 'Bugs',
                'returnaction' => 'DetailView',
                'action' => 'Save',
            ),
            'hiddenFields' =>
            array(
                array(
                    'name' => 'portal_viewable',
                    'operator' => '=',
                    'value' => '1',
                ),
            ),
            'useTabs' => false,
        ),
        'panels' =>
        array(
            array(
                'label' => 'default',
                'fields' =>
                array(
                    array(
                        'name' => 'bug_number',
                        'label' => 'Number',
                        'displayParams' => array(
                            'colspan' => 2,
                        ),
                        'readOnly' => true),
                    array(
                        'name' => 'priority',
                        'label' => 'Priority',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'product_category',
                        'label' => 'Category',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'status',
                        'label' => 'Status',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'type',
                        'label' => 'Type',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'resolution',
                        'label' => 'Resolution',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'date_entered',
                        'label' => 'Created',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'date_modified',
                        'label' => 'Modified',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'name',
                        'label' => 'Name',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'description',
                        'label' => 'Description',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                ),
            ),
        ),
    );
?>
