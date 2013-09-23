//FILE SUGARCRM flav=ent ONLY
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
    extendsFrom: 'RecordView',

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
     * Display the warning message about missing RLIs
     * @param string module     The module that we are currently on.
     */
    showRLIWarningMessage: function(module) {
        var alert = app.alert.show('opp-rli-create', {
            level: 'warning',
            autoClose: false,
            title: app.lang.get('LBL_ALERT_TITLE_WARNING') + ':',
            messages: app.lang.get('TPL_RLI_CREATE', module)
        });
        alert.$el.find('a[href]').on('click.open', _.bind(function() {
            // remove the event handler
            alert.$el.find('a[href]').off('click.open');
            this.openRLICreate();
        }, this));
    },

    /**
     * Open a new Drawer with the RLI Create Form
     */
    openRLICreate: function() {
        // close RLI warning alert
        app.alert.dismiss('opp-rli-create');

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

})
