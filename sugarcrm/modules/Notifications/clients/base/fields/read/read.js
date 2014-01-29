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
({
    events: {
        'click [data-action=toggle]': 'toggleIsRead',
        'mouseover [data-action=toggle]': 'toggleMouse',
        'mouseout [data-action=toggle]': 'toggleMouse'
    },

    plugins: ['Tooltip'],

    /**
     * {@inheritDoc}
     *
     * The read field is always a readonly field.
     *
     * If `mark_as_read` option is enabled on metadata it means we should
     * automatically mark the notification as read.
     *
     */
    initialize: function(options) {
        options.def.readonly = true;

        this._super('initialize', [options]);

        if (options.def && options.def.mark_as_read) {
            this.markAs(true);
        }
    },

    /**
     * Event handler for mouse events.
     *
     * @param {Event} event Mouse over / mouse out.
     */
    toggleMouse: function(event) {
        var $target= this.$(event.currentTarget),
            isRead = this.model.get('is_read');

        if (!isRead) {
            return;
        }

        var label = event.type === 'mouseover' ? 'LBL_UNREAD' : 'LBL_READ';
        $target.html(app.lang.get(label, this.module));
        $target.toggleClass('label-inverse', event.type === 'mouseover');
    },

    /**
     * Toggle notification `is_read` flag.
     */
    toggleIsRead: function() {
        this.markAs(!this.model.get('is_read'));
    },

    /**
     * Mark notification as read/unread.
     *
     * @param {Boolean} read `True` marks notification as read, `false` as
     *   unread.
     */
    markAs: function(read) {
        if (read === this.model.get('is_read')) {
            return;
        }

        this.model.save({is_read: !!read}, {
            success: _.bind(function() {
                if (!this.disposed) {
                    this.render();
                }
            }, this)
        });
    }
})
