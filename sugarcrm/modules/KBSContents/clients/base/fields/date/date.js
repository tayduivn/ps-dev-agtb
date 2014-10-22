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
    extendsFrom: 'DateField',

    /**
     * {@inheritDoc}
     * Added validation tasks.
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        // TODO: This needs an API instead. Will be fixed by SC-3369.
        app.error.errorName2Keys['expDateLow'] = 'ERROR_EXP_DATE_LOW';
        app.error.errorName2Keys['activeDateApproveRequired'] = 'ERROR_ACTIVE_DATE_APPROVE_REQUIRED';

        this.model.addValidationTask('exp_date_publish', _.bind(this._doValidateExpDateField, this));
        this.model.addValidationTask('active_date_approve', _.bind(this._doValidateActiveDateField, this));
        this.model.on('validation:complete', _.bind(this._validationComplete, this));
    },

    /**
     * Custom validator for the "exp_date" field.
     * Show error when expiration date is lower than publishing.
     *
     * @param {Object} fields Hash of field definitions to validate.
     * @param {Object} errors Error validation errors.
     * @param {Function} callback Async.js waterfall callback.
     */
    _doValidateExpDateField: function(fields, errors, callback) {
        var fieldName = 'exp_date',
            expDate = this.model.get(fieldName),
            publishingDate = this.model.get('active_date'),
            status = this.model.get('status'),
            changed = this.model.changedAttributes(this.model.getSyncedAttributes());

        if (
            this._isPublishingStatus(status) &&
            (!changed.status || !this._isPublishingStatus(changed.status))
        ) {
            publishingDate = app.date().formatServer(true);
            this.model.set('active_date', publishingDate);
        }

        if (status !== 'expired' && expDate && publishingDate && app.date(expDate).isBefore(publishingDate)) {
            errors[fieldName] = errors[fieldName] || {};
            errors[fieldName].expDateLow = true;
        }

        callback(null, fields, errors);
    },

    /**
     * Custom validator for the "active_date" field.
     * Approved status requires publishing date.
     *
     * @param {Object} fields Hash of field definitions to validate.
     * @param {Object} errors Error validation errors.
     * @param {Function} callback Async.js waterfall callback.
     */
    _doValidateActiveDateField: function(fields, errors, callback) {
        var fieldName = 'active_date',
            status = this.model.get('status'),
            publishingDate = this.model.get(fieldName);

        if (!publishingDate && status == 'approved') {
            errors[fieldName] = errors[fieldName] || {};
            errors[fieldName].activeDateApproveRequired = true;
        }

        callback(null, fields, errors);
    },

    /**
     * Called whenever validation completes.
     * Change publishing and expiration dates to current on manual change.
     *
     * @param {Boolean} isValid
     */
    _validationComplete: function(isValid) {
        if (isValid) {
            var changed = this.model.changedAttributes(this.model.getSyncedAttributes());
            var current = this.model.get('status');

            if (current == 'expired') {
                this.model.set('exp_date', app.date().formatServer(true));
            } else if (
                this._isPublishingStatus(current) &&
                !(changed.status && this._isPublishingStatus(changed.status))
            ) {
                this.model.set('active_date', app.date().formatServer(true));
            }
        }
    },

    /**
     * Check if passed status is publishing status.
     *
     * @param {String} status Status field value.
     * @return {Boolean}
     */
    _isPublishingStatus: function(status) {
        return ['published', 'published-in', 'published-ex'].indexOf(status) !== -1;
    }

})
