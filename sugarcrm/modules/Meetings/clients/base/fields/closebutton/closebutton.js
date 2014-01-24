/**
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
({
    events: {
        'click [name="record-close"]': 'closeClicked'
    },

    extendsFrom: 'RowactionField',

    /**
     * {@inheritDoc}
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.type = 'rowaction';
        this.closedStatus = 'Held';
    },

    closeClicked: function() {
        this._close();
    },

    /**
     * Override so we can have a custom hasAccess for closed status.
     *
     * @return {Boolean} true if it has aclAccess and status is not closed.
     */
    hasAccess: function() {
        return this._super('hasAccess') && this.model.get('status') !== this.closedStatus;
    },

    /**
     * Close a meeting.
     *
     * @private
     */
    _close: function() {
        var self = this;

        this.model.set('status', this.closedStatus);
        this.model.save({}, {
            success: function() {
                app.alert.show(
                    'close_meeting_success',
                    {level: 'success', autoClose: true, title: app.lang.get('LBL_MEETING_CLOSE_SUCCESS', self.module)}
                );
            },
            error: function(error) {
                app.alert.show(
                    'close_meeting_error',
                    {level: 'error', autoClose: true, title: app.lang.getAppString('ERR_AJAX_LOAD')}
                );
                app.logger.error('Failed to close a meeting. ' + error);

                // we didn't save, revert!
                self.model.revertAttributes();
            }
        });
    },

    /**
     * {@inheritDoc}
     */
    bindDataChange: function() {
        if (this.model) {
            this.model.on('change:status', this.render, this);
        }
    }
})
