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
 * The container of Omnichannel console dashboards.
 *
 * @class View.Layouts.Base.OmnichannelDashboardSwicthLayout
 * @alias SUGAR.App.view.layouts.BaseOmnichannelDashboardSwitchLayout
 * @extends View.Layout
 */
({
    className: 'omni-dashboard-switch',

    /**
     * Contact Ids.
     * @property {Array}
     */
    contactIds: [],

    /**
     * z-index for next top dashboard.
     * @property {number}
     */
    zIndex: 1,

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        if (this.layout) {
            this.layout.on('ccp:terminated', this.removeAllDashboards, this);
            this.layout.on('contact:view', this.showDashboard, this);
            this.layout.on('contact:destroyed', this.removeDashboard, this);
        }
    },

    /**
     * Show a contact's dashboard. Create a new dashbord if it doesn't exist.
     * @param {string} contactId
     */
    showDashboard: function(contactId) {
        var index = _.indexOf(this.contactIds, contactId);
        if (index === -1) {
            this._createDashboard();
            this.contactIds.push(contactId);
        } else {
            var dashboard = this._components[index];
            // move to top
            dashboard.$el.css('z-index', this.zIndex++);
        }
        var console = this.layout;
        if (!console.isExpanded()) {
            console.toggle();
        }
    },

    /**
     * Create a new dashboard.
     * @private
     */
    _createDashboard: function() {
        var dashboard = app.view.createLayout({
            type: 'omnichannel-dashboard'
        });
        this._components.push(dashboard);
        this.$el.append(dashboard.$el);
        dashboard.$el.css('z-index', this.zIndex++);
        dashboard.initComponents();
        dashboard.loadData();
        dashboard.render();
    },

    /**
     * Remove a contact's dashboard.
     * @param {string} contactId
     */
    removeDashboard: function(contactId) {
        var index = _.indexOf(this.contactIds, contactId);
        if (index !== -1) {
            var dashboard = this._components[index];
            dashboard.dispose();
            this._components.splice(index, 1);
            this.contactIds.splice(index, 1);
        }
        if (this.contactIds.length < 1) {
            var console = this.layout;
            if (console.isExpanded()) {
                console.toggle();
            }
        }
    },

    /**
     * Remove all dashboards.
     */
    removeAllDashboards: function() {
        var console = this.layout;
        if (console.isExpanded()) {
            console.toggle();
        }
        this._disposeComponents();
        this.contactIds = [];
        this.zIndex = 1;
    }
})
