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
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

({
    extendsFrom: 'RecordView',

    /**
     * Holds a reference to the alert this view triggers
     */
    alert: undefined,

//BEGIN SUGARCRM flav!=ent ONLY

    /**
     * Holds a reference to the alert this view triggers
     */
    cancelClicked: function () {
        /**
         * todo: this is a sad way to work around some problems with sugarlogic and revertAttributes
         * but it makes things work now. Probability listens for Sales Stage to change and then by
         * SugarLogic, it updates probability when sales_stage changes. When the user clicks cancel,
         * it goes to revertAttributes() which sets the model back how it was, but when you try to
         * navigate away, it picks up those new changes as unsaved changes to your model, and tries to
         * falsely warn the user. This sets the model back to those changed attributes (causing them to
         * show up in this.model.changed) then calls the parent cancelClicked function which does the
         * exact same thing, but that time, since the model was already set, it doesn't see anything in
         * this.model.changed, so it doesn't warn the user.
         */
        var changedAttributes = this.model.changedAttributes(this.model.getSyncedAttributes());
        this.model.set(changedAttributes);
        this._super("cancelClicked");
    },
//END SUGARCRM flav=!ent ONLY

//BEGIN SUGARCRM flav=ent ONLY
    /**
     * {@inheritdoc}
     * @param options
     */
    initialize: function(options) {
        this.plugins = _.union(this.plugins, ['LinkedModel']);
        this.once('init', function() {
            var rlis = this.model.getRelatedCollection('revenuelineitems');
            rlis.once('reset', function(collection) {
                // check if the RLI collection is empty
                // and make sure there isn't another RLI warning on the page
                if (collection.length === 0 && $('#createRLI').length === 0) {
                    this.showRLIWarningMessage(this.model.module);
                }
            }, this);
            rlis.fetch({ relate: true });
        }, this);
        this._super("initialize", [options]);
    },

    /**
     * @inheritdoc
     */
    cancelClicked: function () {
        /**
         * todo: this is a sad way to work around some problems with sugarlogic and revertAttributes
         * but it makes things work now. Probability listens for Sales Stage to change and then by
         * SugarLogic, it updates probability when sales_stage changes. When the user clicks cancel,
         * it goes to revertAttributes() which sets the model back how it was, but when you try to
         * navigate away, it picks up those new changes as unsaved changes to your model, and tries to
         * falsely warn the user. This sets the model back to those changed attributes (causing them to
         * show up in this.model.changed) then calls the parent cancelClicked function which does the
         * exact same thing, but that time, since the model was already set, it doesn't see anything in
         * this.model.changed, so it doesn't warn the user.
         */
        var changedAttributes = this.model.changedAttributes(this.model.getSyncedAttributes());
        this.model.set(changedAttributes);
        this._super("cancelClicked");
    },

    /**
     * Display the warning message about missing RLIs
     * @param string module     The module that we are currently on.
     */
    showRLIWarningMessage: function(module) {
        // add a callback to close the alert if users navigate from the page
        app.routing.before('route', this.dismissAlert, undefined, this);

        var message = app.lang.get('TPL_RLI_CREATE', 'Opportunities')
            + '  <a href="javascript:void(0);" id="createRLI">'
            + app.lang.get('TPL_RLI_CREATE_LINK_TEXT', 'Opportunities') + '</a>';

        this.alert = app.alert.show('opp-rli-create', {
            level: 'warning',
            autoClose: false,
            title: app.lang.get('LBL_ALERT_TITLE_WARNING') + ':',
            messages: message,
            onLinkClick: _.bind(function() {
                this.openRLICreate();
            }, this),
            onClose: _.bind(function() {
                app.routing.offBefore('route', this.dismissAlert, this);
            }, this)
        });
    },

    /**
     * Handle dismissing the RLI create alert
     */
    dismissAlert: function() {
        // close RLI warning alert
        app.alert.dismiss('opp-rli-create');
        // remove before route event listener
        app.routing.offBefore('route', this.dismissAlert, this);
    },

    /**
     * Open a new Drawer with the RLI Create Form
     */
    openRLICreate: function() {
        // close RLI warning alert
        this.dismissAlert();

        var model = this.createLinkModel(this.createdModel || this.model, 'revenuelineitems');

        app.drawer.open({
            layout: 'create-actions',
            context: {
                create: true,
                module: model.module,
                model: model
            }
        }, _.bind(this.rliCreateClose, this));
    },

    /**
     * Callback for when the create drawer closes
     * @param model
     */
    rliCreateClose: function(model) {
        if (!model) {
            return;
        }

        var ctx = this.listContext || this.context;

        ctx.resetLoadFlag();
        ctx.set('skipFetch', false);
        ctx.loadData();

        // find the child collection for the RLI subpanel
        // if we find one and it has the loadData method, call that method to
        // force the subpanel to load the data.
        var rli_ctx = _.find(ctx.children, function(child) {
            return (child.get('module') == 'RevenueLineItems');
        }, this);
        if (!_.isUndefined(rli_ctx) && _.isFunction(rli_ctx.loadData)) {
            rli_ctx.loadData();
        }
    }
//END SUGARCRM flav=ent ONLY
})
