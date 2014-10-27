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
/**
 * @class View.Views.Calls.PreviewView
 * @alias SUGAR.App.view.views.CallsPreviewView
 * @extends View.PreviewView
 */
({
    extendsFrom: 'PreviewView',

    /**
     * Modify view definition so that reminders field is flattened, the duration
     * field displays label, and the recurrence field is hidden.
     * @inheritdoc
     * @param {Object} meta - View definition to be modified for preview.
     * @returns {Object}
     * @private
     */
    _previewifyMetadata: function(meta){
        var metadata = this._super('_previewifyMetadata', [meta]);

        _.each(metadata.panels, function(panel) {
            _.each(panel.fields, function(field, index) {
                switch(field.name) {
                    case 'reminders':
                        panel.fields.splice(index, 1, field.fields[0], field.fields[1]);
                        break;
                    case 'duration':
                        field.dismiss_label = false;
                        break;
                    case 'recurrence':
                        panel.fields.splice(index, 1);
                        break;
                }
            });
        });

        return metadata;
    }
})
