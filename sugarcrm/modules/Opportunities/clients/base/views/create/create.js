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
/**
 * @class View.Views.Base.Opportunities.CreateView
 * @alias SUGAR.App.view.views.OpportunitiesCreateView
 * @extends View.Views.Base.CreateView
 */
({
    extendsFrom: 'CreateView',

    //BEGIN SUGARCRM flav=ent ONLY
    /**
     * Used by the alert openRLICreate method
     */
    createdModel: undefined,

    /**
     * Used by the openRLICreate method
     */
    listContext: undefined,

    /**
     * The original success message to call from the new one we set in the getCustomSaveOptions method
     */
    originalSuccess: undefined,

    /**
     * Holds a reference to the alert this view triggers
     */
    alert: undefined,

    //END SUGARCRM flav=ent ONLY

    /**
     * {@inheritDoc}
     */
    initialize: function(options) {
        //BEGIN SUGARCRM flav=ent ONLY
        this.plugins = _.union(this.plugins, ['LinkedModel']);
        //END SUGARCRM flav=ent ONLY
        this._super('initialize', [options]);
        app.utils.hideForecastCommitStageField(this.meta.panels);
    },

    //BEGIN SUGARCRM flav=ent ONLY

    /**
     * @override
     */
    getCustomSaveOptions: function(options) {
        if (app.metadata.getModule('Opportunities', 'config').opps_view_by === 'RevenueLineItems') {
            this.createdModel = this.model;
            // since we are in a drawer
            this.listContext = this.context.parent || this.context;
            this.originalSuccess = options.success;

            var success = _.bind(function(model) {
                this.originalSuccess(model);
                this._checkForRevenueLineItems(model, options);
            }, this);

            return {
                success: success
            };
        }
    },

    /**
     * Check for Revenue Line Items, if the user has edit access and non exist, then
     * display the RLI Warning Message.
     *
     * @param {{Data.Bean}} model
     * @param {{object}} options
     * @private
     */
    _checkForRevenueLineItems: function(model, options) {
        // lets make sure we have edit/create access to RLI's
        // if we do, lets make sure that the values where added
        if (app.acl.hasAccess('edit', 'RevenueLineItems')) {
            // check to see if we added RLIs during create
            var addedRLIs = model.get('revenuelineitems') || false;
            addedRLIs = (addedRLIs && addedRLIs.create && addedRLIs.create.length);
            if (!addedRLIs) {
                this.showRLIWarningMessage(this.listContext.get('module'));
            }
        }
    },

    /**
     * Display the warning message about missing RLIs
     */
    showRLIWarningMessage: function() {
        // add a callback to close the alert if users navigate from the page
        app.routing.before('route', this.dismissAlert, this);

        var message = app.lang.get('TPL_RLI_CREATE', 'Opportunities') +
            '  <a href="javascript:void(0);" id="createRLI">' +
            app.lang.get('TPL_RLI_CREATE_LINK_TEXT', 'Opportunities') + '</a>';

        this.alert = app.alert.show('opp-rli-create', {
            level: 'warning',
            autoClose: false,
            title: app.lang.get('LBL_ALERT_TITLE_WARNING') + ':',
            messages: message,
            onLinkClick: _.bind(function() {
                app.alert.dismiss('create-success');
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
    dismissAlert: function(data) {
        // if we are not navigating to the Opps list view, dismiss the alert
        if (data && !(data.args && data.args[0] === 'Opportunities' && data.route === 'list')) {
            app.alert.dismiss('opp-rli-create');
            // close RLI warning alert
            // remove before route event listener
            app.routing.offBefore('route', this.dismissAlert, this);
        }
    },

    /**
     * Open a new Drawer with the RLI Create Form
     */
    openRLICreate: function() {
        // close RLI warning alert
        this.dismissAlert(true);

        var model = this.createLinkModel(this.createdModel || this.model, 'revenuelineitems');

        app.drawer.open({
            layout: 'create',
            context: {
                create: true,
                module: model.module,
                model: model
            }
        }, _.bind(function(model) {
            if (!model) {
                return;
            }

            var ctx = this.listContext || this.context;

            ctx.reloadData({recursive: false});

            // reload opportunities and RLIs subpanels
            ctx.trigger('subpanel:reload', {links: ['opportunities', 'revenuelineitems']});
        }, this));
    },

    /**
     * @inheritdoc
     */
    _dispose: function() {
        if (this.alert) {
            this.alert.getCloseSelector().off('click');
        }

        this._super('_dispose', []);
    }
    //END SUGARCRM flav=ent ONLY
})
