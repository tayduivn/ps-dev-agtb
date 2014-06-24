/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
/**
 * @class View.Fields.Base.EditablelistbuttonField
 * @alias SUGAR.App.view.fields.BaseEditablelistbuttonField
 * @extends View.Fields.Base.ButtonField
 */
({
    events: {
        'click [name=inline-save]' : 'saveClicked',
        'click [name=inline-cancel]' : 'cancelClicked'
    },
    extendsFrom: 'ButtonField',
    initialize: function(options) {
        this._super("initialize", [options]);
        if(this.name === 'inline-save') {
            this.model.off("change", null, this);
            this.model.on("change", function() {
                this.changed = true;
            }, this);
        }
    },
    _loadTemplate: function() {
        app.view.Field.prototype._loadTemplate.call(this);
        if(this.view.action === 'list' && _.indexOf(['edit', 'disabled'], this.action) >= 0 ) {
            this.template = app.template.getField('button', 'edit', this.module, 'edit');
        } else {
            this.template = app.template.empty;
        }
    },
    /**
     * Called whenever validation completes on the model being edited
     * @param {boolean} isValid TRUE if model is valid
     * @private
     */
    _validationComplete : function(isValid){
        if (!isValid) {
            this.setDisabled(false);
            return;
        }
        if (!this.changed) {
            this.cancelEdit();
            return;
        }

        var self = this,
            successCallback = function() {
                self._save();
            };

        async.forEachSeries(this.view.rowFields[this.model.id], function(view, callback) {
            app.file.checkFileFieldsAndProcessUpload(view, {
                success: function(response) {
                    if (response.record && response.record.date_modified) {
                        self.model.set('date_modified', response.record.date_modified);
                    }
                    callback.call();
                }
            }, {deleteIfFails: false }, true);
        }, successCallback);
    },

    _save: function() {
        var self = this,
            successCallback = function(model) {
                self.changed = false;
                self.view.toggleRow(model.id, false);
                self._refreshListView();
            },
            options = {
                success: successCallback,
                error: function(error) {
                    if (error.status === 409) {
                        app.utils.resolve409Conflict(error, self.model, function(model, isDatabaseData) {
                            if (model) {
                                if (isDatabaseData) {
                                    successCallback(model);
                                } else {
                                    self._save();
                                }
                            }
                        });
                    }
                },
                complete: function() {
                    // remove this model from the list if it has been unlinked
                    if (self.model.get('_unlinked')) {
                        self.collection.remove(self.model, { silent: true });
                        self.collection.trigger('reset');
                        self.view.render();
                    } else {
                        self.setDisabled(false);
                    }
                },
                lastModified: self.model.get('date_modified'),
                //Show alerts for this request
                showAlerts: {
                    'process': true,
                    'success': {
                        messages: app.lang.get('LBL_RECORD_SAVED', self.module)
                    }
                },
                relate: this.model.link ? true : false
            };

        options = _.extend({
            params: {
                // tell the API to return only relevant fields
                fields: this.context.get("fields")
            }
        }, options, this.getCustomSaveOptions(options));

        this.model.save({}, options);
    },

    getCustomSaveOptions: function(options) {
        return {};
    },

    saveModel: function() {
        this.setDisabled(true);
        var fieldsToValidate = this.view.getFields(this.module, this.model);
        this.model.doValidate(fieldsToValidate, _.bind(this._validationComplete, this));
    },
    cancelEdit: function() {
        if (this.isDisabled()) {
            this.setDisabled(false);
        }
        this.changed = false;
        this.model.revertAttributes();
        this.view.clearValidationErrors();
        this.view.toggleRow(this.model.id, false);

        // trigger a cancel event across the parent context so listening components
        // know the changes made in this row are being reverted
        if(this.context.parent) {
            this.context.parent.trigger('editablelist:cancel', this.model);
        }
    },
    saveClicked: function(evt) {
        if (!$(evt.currentTarget).hasClass('disabled')) {
            this.saveModel();
        }
    },
    cancelClicked: function(evt) {
        this.cancelEdit();
    },
    /**
     * On model save success, this function gets called to refresh the list
     * view.
     *
     * {@link View.Fields.Base.FavoriteField} is using about the same method.
     * @private
     */
    _refreshListView: function() {
        var filterPanelLayout = this.view;
        //Try to find the filterpanel layout
        while (filterPanelLayout && filterPanelLayout.name!=='filterpanel') {
            filterPanelLayout = filterPanelLayout.layout;
        }
        //If filterpanel layout found and not disposed, then pick the value from the quicksearch input and
        //trigger the filtering
        if (filterPanelLayout && !filterPanelLayout.disposed && this.collection) {
            filterPanelLayout.applyLastFilter(this.collection);
        }
    }
})
