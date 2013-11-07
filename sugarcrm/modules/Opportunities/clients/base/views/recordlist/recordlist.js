/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

({
    extendsFrom: 'RecordlistView',

    /**
     * Flag for if the before route listener is attached or not
     */
    hasRouteListener: false,

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.hasRouteListener = true;
        app.routing.before('route', this.dismissAlert, undefined, this);
    },

    /**
     * Handle dismissing the RLI create alert
     */
    dismissAlert: function() {
        // close RLI warning alert
        app.alert.dismiss('opp-rli-create');
        // remove before route event listener
        app.routing.offBefore('route', this.dismissAlert, this);
        this.hasRouteListener = false;
    },

    /**
     * @inheritdoc
     */
    _dispose: function() {
        if(this.hasRouteListener) {
            this.hasRouteListener = false;
            // remove before route event listener
            app.routing.offBefore('route', this.dismissAlert, this);
        }
        this._super('_dispose', []);
    }
})
