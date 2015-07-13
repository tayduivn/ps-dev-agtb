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
({
    extendsFrom: 'MassupdateView',
    
    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this.plugins = _.union(this.plugins || [], ['CommittedDeleteWarning', 'KBContent']);
        this._super('initialize', [options]);
    },

    /**
     * @inheritdoc
     */
    saveClicked: function(evt) {
        var massUpdateModels = this.getMassUpdateModel(this.module).models,
            fieldsToValidate = this._getFieldsToValidate(),
            emptyValues = [];

        this._doValidateMassUpdate(massUpdateModels, fieldsToValidate, _.bind(function(fields, errors) {
            if (_.isEmpty(errors)) {
                this.trigger('massupdate:validation:complete', {
                    errors: errors,
                    emptyValues: emptyValues
                });
                if(this.$('.btn[name=update_button]').hasClass('disabled') === false) {
                    this.save();
                }
            } else {
                this.handleValidationError(errors);
            }
        }, this));
    },

    /**
     * Custom MassUpdate validation.
     *
     * @param {Object} models
     * @param {Object} fields
     * @param {Function} callback
     * @private
     */
    _doValidateMassUpdate: function(models, fields, callback) {
        var self = this,
            value = this.model.get(fields[0].name),
            errors = {};

        if (fields[0].name === 'status') {
            _.each(models, function(model) {
                switch (value) {
                    case 'published':
                        self._doValidateExpDateField(model, fields, errors, function(model, fields, errors){
                            var fieldName = 'exp_date';
                            if (!_.isUndefined(errors[fieldName])){
                                errors[fields[0].name] = {'expDateLow': true};
                            }
                        });
                        break;
                    case 'approved':
                        self._doValidateActiveDateField(model, fields, errors, function(model, fields, errors){
                            var fieldName = 'active_date';
                            if (!_.isUndefined(errors[fieldName])){
                                errors[fields[0].name] = {'activeDateLow': true};
                            }
                        });
                        break;
                }
            });
        }

        callback(fields, errors);
    }
})
