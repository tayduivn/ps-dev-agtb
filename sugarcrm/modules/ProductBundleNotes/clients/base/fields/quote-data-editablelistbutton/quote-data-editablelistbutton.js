/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/**
 * @class View.Fields.Base.ProductBundleNotes.EditablelistbuttonField
 * @alias SUGAR.App.view.fields.BaseProductBundleNotesEditablelistbuttonField
 * @extends View.Fields.Base.BaseEditablelistbuttonField
 */
({
    extendsFrom: 'BaseEditablelistbuttonField',

    /**
     * @inheritdoc
     */
    _render: function() {
        this._super('_render');

        if (this.tplName === 'edit') {
            this.$el.closest('.left-column-save-cancel').addClass('higher');
        } else {
            this.$el.closest('.left-column-save-cancel').removeClass('higher');
        }
    },

    /**
     * Overriding and not calling parent _loadTemplate as those are based off view/actions and we
     * specifically need it based off the modelView set by the parent layout for this row model
     *
     * @inheritdoc
     * @override
     */
    _loadTemplate: function() {
        this.tplName = this.model.modelView || 'list';

        if (this.view.action === 'list' && _.indexOf(['edit', 'disabled'], this.action) < 0) {
            this.template = app.template.empty;
        } else {
            this.template = app.template.getField(this.type, this.tplName, this.module);
        }
    },

    /**
     * @inheritdoc
     */
    cancelEdit: function() {
        if (this.isDisabled()) {
            this.setDisabled(false);
        }
        this.changed = false;
        this.model.revertAttributes();
        this.view.clearValidationErrors();

        // this is the only line I had to change
        this.view.toggleRow(this.model.module, this.model.id, false);

        // trigger a cancel event across the view context so listening components
        // know the changes made in this row are being reverted
        if (this.view.context) {
            this.view.context.trigger('editablelist:cancel:' + this.view.model.get('id'), this.model);
        }
    },

    /**
     * @inheritdoc
     */
    _save: function() {
        var self = this;
        var oldModelId = this.model.get('id');
        var successCallback = function(model) {
            self.changed = false;
            model.modelView = 'list';
            if (self.view.context) {
                self.view.context.trigger('editablelist:save:' + self.view.model.get('id'), model, oldModelId);
            }
        };
        var options = {
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
                    self.collection.remove(self.model, {silent: true});
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

        options = _.extend({}, options, this.getCustomSaveOptions(options));

        if (this.model._notSaved) {
            this.model.id = null;
            this.model.unset('id');
        }
        this.model.save({}, options);
    }
});
