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
//BEGIN SUGARCRM flav=pro && flav!=ent ONLY

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
        app.view.invokeParent(this, {type: 'view', name: 'record', method: 'cancelClicked'});
    },
//END SUGARCRM flav=pro && flav=!ent ONLY

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
                if (collection.length === 0) {
                    this.showRLIWarningMessage(this.model.module);
                }
            }, this);
            rlis.fetch({ relate: true });
        }, this);
        app.view.invokeParent(this, {type: 'view', name: 'record', method: 'initialize', args: [options]});
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
        app.view.invokeParent(this, {type: 'view', name: 'record', method: 'cancelClicked'});
    },

    /**
     * Display the warning message about missing RLIs
     * @param string module     The module that we are currently on.
     */
    showRLIWarningMessage: function(module) {
        app.routing.before('route', this.dismissAlert, undefined, this);

        var alert = app.alert.show('opp-rli-create', {
            level: 'warning',
            autoClose: false,
            title: app.lang.get('LBL_ALERT_TITLE_WARNING') + ':',
            messages: Handlebars.compile(app.lang.get('TPL_RLI_CREATE', 'Opportunities'))()
        });
        alert.$el.find('a[href]').on('click.open', _.bind(function() {
            // remove the event handler
            alert.$el.find('a[href]').off('click.open');
            this.openRLICreate();
        }, this));
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
