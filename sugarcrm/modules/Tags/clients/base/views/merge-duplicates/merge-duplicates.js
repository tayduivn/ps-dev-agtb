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
 * View for merge duplicates.
 *
 * @class View.Views.Base.MergeDuplicatesView
 * @alias SUGAR.App.view.views.BaseMergeDuplicatesView
 * @extends View.Views.Base.ListView
 */
({
    /**
     * Handler for save merged records event.
     *
     * Shows confirmation message and calls
     * {@link View.Views.BaseMergeDuplicatesView#_savePrimary} on confirm.
     */
    triggerSave: function() {
        var alternativeModels = _.without(this.collection.models, this.primaryRecord);
        var alternativeModelNames = [];

        _.each(alternativeModels, function(model) {
            alternativeModelNames.push(app.utils.getRecordName(model));
        }, this);

        this.clearValidationErrors(this.getFieldNames());

        app.alert.show('merge_confirmation', {
            level: 'confirmation',
            messages: app.lang.get('LBL_MERGE_DUPLICATES_CONFIRM') + ' ' +
                      alternativeModelNames.join(', ') + '. ' +
                      app.lang.get('LBL_MERGE_DUPLICATES_PROCEED'),
            onConfirm: _.bind(this.duplicateNameCheck, this)
        });
    },

    /**
     * Checks to see if there is another record with a duplicate name
     *
     */
    duplicateNameCheck: function() {
        var self = this;
        var primaryRecordName = this.primaryRecord.get('name');
        var tagCollection = app.data.createBeanCollection('Tags');

        tagCollection.filterDef = {
            'filter': [{'name_lower': {'$equals': primaryRecordName.toLowerCase()}}]
        };

        //fetch records that have the same name as the primaryRecord name
        tagCollection.fetch({
            success: function(tags) {
                //throw a warning if the primaryRecord name is in the dupCollection
                // and it is not one of the merged records
                if (tags.length > 0 && _.isEmpty(_.intersection(_.keys(self.rowFields), _.pluck(tags.models, 'id')))) {
                    app.alert.show('tag_duplicate', {
                        level: 'warning',
                        messages: app.lang.get('LBL_EDIT_DUPLICATE_FOUND', 'Tags')
                    });
                } else {
                    self._savePrimary();
                }
            }
        });
    },

    /**
     * Saves primary record and triggers `mergeduplicates:primary:saved` event on success.
     * Before saving triggers also `duplicate:unformat:field` event.
     *
     * @private
     */
    _savePrimary: function() {
        var self = this;
        var fields = this.getFieldNames().filter(function(field) {
                return app.acl.hasAccessToModel('edit', this.primaryRecord, field);
            }, this);

        this.primaryRecord.trigger('duplicate:unformat:field');

        this.primaryRecord.save({}, {
            fieldsToValidate: fields,
            success: function() {
                // Trigger format fields again, because they can come different
                // from the server (e.g: only teams checked will be in the
                // response, and we still want to display unchecked teams on the
                // view)
                self.primaryRecord.trigger('duplicate:format:field');
                self.primaryRecord.trigger('mergeduplicates:primary:saved');
            },
            error: function(error) {
                if (error.status === 409) {
                    app.utils.resolve409Conflict(error, self.primaryRecord, function(model, isDatabaseData) {
                        if (model) {
                            if (isDatabaseData) {
                                self.resetRadioSelection(model.id);
                            } else {
                                self._savePrimary();
                            }
                        }
                    });
                }
            },
            lastModified: this.primaryRecord.get('date_modified'),
            showAlerts: true,
            viewed: true,
            params: {verifiedUnique: true}
        });
    }
})
