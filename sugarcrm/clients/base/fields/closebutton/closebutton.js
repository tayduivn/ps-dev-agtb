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
 * @class View.Fields.Base.CloseButtonField
 * @alias SUGAR.App.view.fields.BaseCloseButtonField
 * @extends View.Fields.Base.Rowaction
 */
({
    extendsFrom: 'RowactionField',

    closedStatus: 'Completed', //status indicating that the record is closed or complete

    /**
     * Setup event handlers.
     * @inheritdoc
     * @param options
     */
    initialize: function(options) {
        this.events = _.extend({}, this.events, options.def.events, {
            'click [name="record-close"]': 'closeClicked',
            'click [name="record-close-new"]': 'closeNewClicked'
        });

        this._super("initialize", [options]);
        this.type = 'rowaction';
    },

    /**
     * Handle record close event.
     * @param event
     */
    closeClicked: function(event) {
        this._close(false);
    },

    /**
     * Handle record close and create new event.
     * @param event
     */
    closeNewClicked: function(event) {
        this._close(true);
    },

    /**
     * Should not show button to close a record if the record is already closed.
     * @inheritdoc
     * @returns {Boolean} true if it has aclAccess and status is not closed
     */
    hasAccess: function() {
        var acl = this._super("hasAccess");
        return acl && this.model.get('status') !== this.closedStatus;
    },

    /**
     * Close the record by setting the appropriate status on the record.
     * @param {boolean} createNew - Open a new drawer to create a record after close.
     * @private
     */
    _close: function (createNew) {
        var self = this;

        this.model.set('status', this.closedStatus);
        this.model.save({}, {
            success: function () {
                self.showSuccessMessage();
                if (createNew) {
                    self.openDrawerToCreateNewRecord();
                }
            },
            error: function (error) {
                self.showErrorMessage();
                app.logger.error('Record failed to close. ' + error);

                // we didn't save, revert!
                self.model.revertAttributes();
            }
        });
    },

    /**
     * Open a drawer to create a new record.
     */
    openDrawerToCreateNewRecord: function() {
        var self = this,
            module = app.metadata.getModule(this.model.module),
            prefill = app.data.createBean(this.model.module);

        prefill.copy(this.model);

        if (module.fields.status && module.fields.status['default']) {
            prefill.set('status', module.fields.status['default']);
        } else {
            prefill.unset('status');
        }

        app.drawer.open({
            layout: 'create-actions',
            context: {
                create: true,
                model: prefill
            }
        }, function() {
            if (self.parent) {
                self.parent.render();
            } else {
                self.render();
            }
        });
    },

    /**
     * Display a success message.
     */
    showSuccessMessage: function() {},

    /**
     * Display an error message.
     */
    showErrorMessage: function() {
        app.alert.show('close_record_error', {
            level: 'error',
            title: app.lang.getAppString('ERR_AJAX_LOAD')
        });
    },

    /**
     * Re-render the field when the status on the record changes.
     */
    bindDataChange: function () {
        if (this.model) {
            this.model.on("change:status", this.render, this);
        }
    }
})
