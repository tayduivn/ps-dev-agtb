/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/**
 * @class View.Fields.Base.CreateAddOnField
 * @alias SUGAR.App.view.fields.BaseCreateAddOnField
 * @extends View.Fields.Base.RowactionField
 */
({
    extendsFrom: 'RowactionField',
    events: {
        'click [data-action=existing_opportunity]': 'existingOpportunityClicked',
        'click [data-action=new_opportunity]': 'newOpportunityClicked',
        'click [data-action=link]': 'toggleAddOns'
    },

    _render: function() {
        if (this.parent && this.parent.model && this.parent.model.get('service')) {
            this._super('_render');
            this.$('[data-action=existing_opportunity]').hide();
            this.$('[data-action=new_opportunity]').hide();
            this.$('.dropdown-inset').hide();
        }
    },

    /**
     * Toggles new and existing opportunity buttons
     * @param {Event} evt
     */
    toggleAddOns: function(evt) {
        if (evt) {
            evt.preventDefault();
            evt.stopPropagation();
        }
        this.$('[data-action=existing_opportunity]').toggle();
        this.$('[data-action=new_opportunity]').toggle();
        this.$('.dropdown-inset').toggle();
    },

    /**
     * Handles Existing Opportunity button being clicked
     * @param {Event} evt
     */
    existingOpportunityClicked: function(evt) {
        var opportunityModel = app.data.createBean('Opportunities');
        var revenueLineItemModel = app.data.createBean('RevenueLineItems');
        var parentModel = this.parent.model;
        var filterOptions = new app.utils.FilterOptions()
            .config({
                initial_filter: 'filterOpportunityTemplate',
                initial_filter_label: 'LBL_FILTER_OPPORTUNITY_TEMPLATE',
                filter_populate: {
                    account_id: parentModel.get('account_id')
                }
            }).format();

        // Open existing opportunities
        app.drawer.open({
            layout: 'selection-list',
            context: {
                module: 'Opportunities',
                filterOptions: filterOptions,
                parent: this.context,
            }
        }, _.bind(this.selectExistingOpportunityDrawerCallback, this));
    },

    /**
     * Handles New Opportunity button being clicked
     * @param {Event} evt
     */
    newOpportunityClicked: function(evt) {
        var opportunityModel = app.data.createBean('Opportunities');
        var parentModel = this.parent.model;
        var addOnToData = {
            add_on_to_id: parentModel.get('id'),
            add_on_to_name: parentModel.get('name'),
            service: '1'
        };
        opportunityModel.set({
            account_id: parentModel.get('account_id'),
            account_name: parentModel.get('account_name'),
        });

        app.drawer.open({
            layout: 'create',
            context: {
                create: true,
                module: 'Opportunities',
                model: opportunityModel,
                addOnToData: addOnToData
            }
        },  _.bind(this.refreshRLISubpanel, this));

    },

    /**
     * Open new RevenueLineItem drawer when an opportunity is selected
     * @param {Object} model
     */
    selectExistingOpportunityDrawerCallback: function(model) {
        if (!model || _.isEmpty(model.id)) {
            return;
        }
        var revenueLineItemModel = app.data.createBean('RevenueLineItems');
        var parentModel = this.parent.model;
        // set up RLI to open when opportunity is selected
        revenueLineItemModel.set({
            add_on_to_id: parentModel.get('id'),
            add_on_to_name: parentModel.get('name'),
            service: '1',
            opportunity_name: model.name,
            opportunity_id: model.id
        });

        //open Revenue Line Items create view
        app.drawer.open({
            layout: 'create',
            context: {
                create: true,
                module: 'RevenueLineItems',
                model: revenueLineItemModel
            }
        }, _.bind(this.refreshRLISubpanel, this));
    },

    refreshRLISubpanel: function(model) {
        if (!model) {
            return;
        }
        var ctx = this.listContext || this.context;
        ctx.reloadData({recursive: false});
        // Refresh RevenueLineItems subpanel when drawer is closed
        if (!_.isUndefined(ctx.children)) {
            _.each(ctx.children, function(child) {
                if (_.contains(['RevenueLineItems'], child.get('module'))) {
                    child.reloadData({recursive: false});
                }
            });
        }
    },

    /**
     * @inheritdoc
     * Check access.
     */
    hasAccess: function() {
        var pliViewAccess = app.acl.hasAccess('view', 'PurchasedLineItems');
        var rliCreateAccess = app.acl.hasAccess('create', 'RevenueLineItems');
        var oppCreateAccess = app.acl.hasAccess('create', 'Opportunities');
        var oppConfig = app.metadata.getModule('Opportunities', 'config');
        var rlisTurnedOn = oppConfig && oppConfig.opps_view_by === 'RevenueLineItems';
        return pliViewAccess && rliCreateAccess &&
            oppCreateAccess && rlisTurnedOn && this._super('hasAccess');
    }
})
