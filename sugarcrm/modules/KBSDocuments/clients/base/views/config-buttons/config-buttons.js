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
 * Copyright  2004-2014 SugarCRM Inc.  All rights reserved.
 */

({
    events: {
        'click a[name=cancel_button]': 'cancelClicked'
    },
    /**
     * {@inheritdoc}
     */
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.context.on('config:saved', this.cancelClicked, this);
        return;
    },

    /**
     * Cancels the config setup process and redirects back
     */
    cancelClicked: function () {
        // If we're inside a drawer and Forecasts is setup
        if (app.drawer) {
            app.drawer.close(this.context, this.context.get('model'));
        }
        this.dispose();
    },

    _dispose: function () {
        this.context.off('button:cancel_button:click', this.cancelClicked, this);
        this.context.off('config:saved', this.cancelClicked, this);
        app.view.View.prototype._dispose.call(this);
    }

})
