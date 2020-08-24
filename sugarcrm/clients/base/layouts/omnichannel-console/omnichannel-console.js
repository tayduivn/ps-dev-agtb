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
 * The layout for the Omnichannel console.
 *
 * @class View.Layouts.Base.OmnichannelConsoleLayout
 * @alias SUGAR.App.view.layouts.BaseOmnichannelConsoleLayout
 * @extends View.Layout
 */
({
    /**
     * Css class for this component.
     * @property {string}
     */
    className: 'omni-console',

    /**
     * Current state: 'opening', 'idle', 'closing', ''.
     * @property {string}
     */
    currentState: '',

    /**
     * Size of console with ccp only.
     * The ccp itself can be 200px to a maximum of 320px wide and 400px to 465px tall according to:
     * https://github.com/amazon-connect/amazon-connect-streams
     *
     * @property {Object}
     */
    ccpSize: {
        width: 320,
        height: 540
    },

    /**
     * Height of the console header.
     * @property {number}
     */
    headerHeight: 28,

    /**
     * Showing ccp only or all.
     * @property {boolean}
     */
    ccpOnly: true,

    /**
     * Event handlers.
     * @property {Object}
     */
    events: {
        'click [data-action=close]': 'close'
    },

    /**
     * The omnichannel dashboard switch component
     */
    omniDashboardSwitch: null,

    /**
     * The CCP component
     */
    ccpComponent: null,

    /**
     * Fields to NOT pre-fill in when quick-creating contacts/cases
     */
    qcBlackListFields: [
        'last_name',
        'name'
    ],

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        $(window).on('resize.omniConsole', _.bind(this._resize, this));
        app.router.on('route', this.closeImmediately, this);
        this._setSize();
    },

    /**
     * @inheritdoc
     */
    bindDataChange: function() {
        this._super('bindDataChange');

        // when the quickcreate dropdown is clicked, load the model data to context
        $('.btn.dropdown-toggle[track="click:quickCreate"]')
            .click(_.bind(this._addQuickcreateModelDataToContext, this));

        // when the quickcreate drawer is closed, perform the necessary steps
        var qcContext = this._getTopLevelContext();
        qcContext.on('quickcreate-drawer:closed', this._handleClosedQuickcreateDrawer, this);
    },

    /**
     * Add custom quickcreateModelData to the context so the quickcreate drawer can pre-populate
     * relevant data
     *
     * @private
     */
    _addQuickcreateModelDataToContext: function() {
        if (this.isOpen()) {
            var ccp = this._getCCPComponent();
            var context = this._getTopLevelContext();
            if (ccp.activeContact) {
                var contactInfo = _.omit(ccp.getContactInfo(ccp.activeContact), this.qcBlackListFields);
                context.set('quickcreateModelData',
                    _.extendOwn(
                        contactInfo,
                        this.getContactModelDataForQuickcreate(),
                        {
                            no_success_label_link: true,
                        }
                    )
                );
            }
        }
    },

    /**
     * Handle when the quickcreate drawer is closed, regardless if a new record
     * was created. If no new record was created, simply re-open the console
     *
     * @private
     */
    _handleClosedQuickcreateDrawer: function() {
        var dashboard = this._getOmnichannelDashboard();
        var context = this._getTopLevelContext();
        var qcModel = context.get('quickcreateCreatedModel');

        if (dashboard && !_.isEmpty(qcModel)) {
            var module = qcModel.get('_module');
            var tabIndex = dashboard.moduleTabIndex[module];

            // if a new Case was created, set the relevant model in the Contacts tab
            if (module === 'Cases') {
                var contactId = qcModel.get('primary_contact_id');

                if (contactId) {
                    var setContactModel = function(data) {
                        var model = app.data.createBean('Contacts', data);

                        dashboard.setModel(dashboard.moduleTabIndex.Contacts, model);
                    };

                    this.fetchModelData('Contacts', contactId, setContactModel);
                }
            }

            dashboard.setModel(tabIndex, qcModel);
            dashboard.switchTab(tabIndex);

            context.unset('quickcreateCreatedModel');
        }

        this.open(); // re-open the console
    },

    /**
     * Get and return the omnichannel dashboard switch component
     *
     * @return {View.Layout}
     * @private
     */
    _getOmnichannelDashboardSwitch: function() {
        if (!this.omniDashboardSwitch) {
            this.omniDashboardSwitch = this.getComponent('omnichannel-dashboard-switch');
        }

        return this.omniDashboardSwitch;
    },

    /**
     * Get and return the omnichannel dashboard for the active contact
     *
     * @return {View.Layout}
     * @private
     */
    _getOmnichannelDashboard: function() {
        var ccp = this._getCCPComponent();
        var contactId = ccp.getActiveContactId();

        return this._getOmnichannelDashboardSwitch().getDashboard(contactId);
    },

    /**
     * Get and return the CCP component
     *
     * @return {View.View}
     * @private
     */
    _getCCPComponent: function() {
        if (!this.ccpComponent) {
            this.ccpComponent = this.getComponent('omnichannel-ccp');
        }

        return this.ccpComponent;
    },

    /**
     * Get relevant model data from the selected Contact, if there is one selected
     *
     * @return {Object}
     */
    getContactModelDataForQuickcreate: function() {
        var dashboard = this._getOmnichannelDashboard();
        var tabModels = dashboard.tabModels;

        var data = {};

        // if there is no selected Contact, return empty
        if (!tabModels[dashboard.moduleTabIndex.Contacts]) {
            return data;
        }

        // these attributes will be deleted from data after retrieving them as the
        // model requires a different attribute name
        var modelAttributes = [
            'id',
            'name',
        ];

        var attributes = modelAttributes.concat([
            'account_id',
            'account_name',
        ]);

        var model = tabModels[dashboard.moduleTabIndex.Contacts];

        _.each(attributes, function(attr) {
            data[attr] = model.get(attr);
        });

        // update the attribute names so they're friendly for the new model
        data.primary_contact_id = data.id;
        data.primary_contact_name = data.name;

        // remove the attributes that were updated
        _.each(modelAttributes, function(attr) {
            delete data[attr];
        });

        return data;
    },

    /**
     * Fetch model data for the supplied module/id and call the callback,
     * if supplied
     *
     * @param {string} module
     * @param {string} id
     * @param callback
     */
    fetchModelData: function(module, id, callback) {
        var url = app.api.buildURL(module + '/' + id);

        app.api.call('read', url, null, {
            success: function(data) {
                if (callback) {
                    callback(data);
                }
            }
        });
    },

    /**
     * Open the console.
     */
    open: function() {
        // open the console if not yet
        if (!this.isOpen()) {
            this._setSize();
            this.currentState = 'opening';
            this.$el.show('slide', {direction: 'down'}, 300);
            this.currentState = 'idle';
            $main = app.$contentEl.children().first();
            $main.on('drawer:add.omniConsole', _.bind(this.closeImmediately, this));
            this.trigger('omniconsole:open');
        }
    },

    /**
     * Tell if the console is opened.
     * @return {boolean} True if open, false if not.
     */
    isOpen: function() {
        return this.currentState !== '';
    },

    /**
     * Tell if console is expanded with dashbaord.
     * @return {boolean} True if it's expanded, false otherwise
     */
    isExpanded: function() {
        return !this.ccpOnly;
    },

    /**
     * Close the console immediately.
     */
    closeImmediately: function() {
        this.$el.hide();
        this.currentState = '';
        this._offEvents();
    },

    /**
     * Expand/shrink console.
     */
    toggle: function() {
        if (this.isOpen()) {
            this.ccpOnly = !this.ccpOnly;
            this.$el.animate({
                'left': 0,
                'top': this._determineTop(),
                'right': this._determineRight(),
                'bottom': this._determineBottom()
            });
        }
    },

    /**
     * Close the console.
     */
    close: function() {
        if (this.isOpen()) {
            this.currentState = 'closing';
            this.$el.hide('slide', {direction: 'down'}, 300);
            this.currentState = '';
            this._offEvents();
        }
    },

    /**
     * Unsubscribe to events.
     * @private
     */
    _offEvents: function() {
        $main = app.$contentEl.children().first();
        $main.off('drawer:add.omniConsole', this.closeImmediately);
    },

    /**
     * Calculate the right of the console.
     * @return {number}
     * @private
     */
    _determineRight: function() {
        if (this.ccpOnly) {
            return $(window).width() - this.ccpSize.width - 6;
        }
        return 0;
    },

    /**
     * Calculate the height of the console.
     * @return {number}
     * @private
     */
    _determineBottom: function() {
        return $('footer').outerHeight();
    },

    /**
     * Calculate the top of the console.
     * @return {number}
     * @private
     */
    _determineTop: function() {
        var top = $('#header .navbar').outerHeight();
        var footerHeight = $('footer').outerHeight();
        var windowHeight = $(window).height();
        if (this.ccpOnly) {
            var ccpTop = windowHeight - footerHeight - this.ccpSize.height - this.headerHeight;
            // if ccpTop < top, ccp will be partially covered by megamenu
            if (ccpTop > top) {
                top = ccpTop;
            }
        }
        return top;
    },

    /**
     * Set the size of the console.
     * @param {boolean} ccpOnly true if showing ccp only, false otherwise
     * @private
     */
    _setSize: function(ccpOnly) {
        if (!_.isUndefined(ccpOnly)) {
            this.ccpOnly = ccpOnly;
        }
        this.$el.css({
            'left': 0,
            'top': this._determineTop(),
            'right': this._determineRight(),
            'bottom': this._determineBottom()
        });
    },

    /**
     * Resize the console.
     * @private
     */
    _resize: _.throttle(function() {
        if (this.disposed) {
            return;
        }
        // resize the console if it is opened
        if (this.currentState === 'idle') {
            this._setSize();
        }
    }, 30),

    /**
     * @inheritdoc
     */
    _dispose: function() {
        $(window).off('resize.omniConsole');
        app.router.off('route', null, this);
        this._super('_dispose');
    },

    /**
     * Get top-level context for setting Quick Create models
     *
     * @return {Object} context
     * @private
     */
    _getTopLevelContext: function() {
        var context = this.context;
        while (context.parent) {
            context = context.parent;
        }
        return context;
    },
})
