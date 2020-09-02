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
            this.layout.on('voice:incoming', this.handleIncomingCall, this);
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
        var context = this.context.getChildContext({forceNew: true, module: 'Dashboards'});
        var dashboard = app.view.createLayout({
            type: 'omnichannel-dashboard',
            context: context
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
        var self = this;
        var index = _.indexOf(this.contactIds, contactId);
        if (index !== -1) {
            var dashboard = this._components[index];
            var _remove = function() {
                self._removeDashboard(index);
            };
            if (!dashboard.triggerBefore('omni-dashboard:close', {callback: _remove})) {
                self._showClearButton(index, contactId);
                return;
            }
            _remove();
        }
    },

    /**
     * Show 'Clear' button on a dashboard.
     * @param {number} index - Current index of dashboard
     * @param {string} contactId - Id of connect Contact associated with dashboard
     */
    _showClearButton: function(index, contactId) {
        var self = this;
        var _remove = function() {
            self._clearButtonClicked(contactId);
        };
        var dashboard = this._components[index];
        var tabbedDashboard = dashboard._getTabbedDashboard();
        var $button = tabbedDashboard.$el.find('a[name=clear]');
        if ($button) {
            $button.removeClass('hidden');
            tabbedDashboard.context.on('button:clear_button:click', function() {
                // check if there are any unsaved changes before removing
                if (!dashboard.triggerBefore('omni-dashboard:close', {callback: _remove})) {
                    return;
                }

                _remove();
            });
        }
    },

    /**
     * Remove a contact's dashboard by index.
     * @param {number} index
     */
    _removeDashboard: function(index) {
        var dashboard = this._components[index];
        if (dashboard) {
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
     * Close appropriate dashboard for contactId when user click's Clear button
     * @param {string} contactId
     * @private
     */
    _clearButtonClicked: function(contactId) {
        var index = _.indexOf(this.contactIds, contactId);
        if (index > -1) {
            this._removeDashboard(index);
        }
    },

    /**
     * Remove all dashboards.
     */
    removeAllDashboards: function() {
        var self = this;
        if (this._components.length < 1) {
            this.layout.close();
            return;
        }
        _.each(this._components, function(dashboard, index) {
            var _remove = function() {
                self._removeDashboard(index);
                if (self.contactIds.length < 1) {
                    self.layout.close();
                    self.zIndex = 1;
                }
            };
            if (!dashboard.triggerBefore('omni-dashboard:close', {callback: _remove})) {
                self._showClearButton(index);
                return;
            }
            _remove();
        });
    },

    /**
     *
     * @param contactId
     * @return {View.Layout|null}
     */
    getDashboard: function(contactId) {
        var index = _.indexOf(this.contactIds, contactId);
        if (index !== -1) {
            return this._components[index];
        }
        return null;
    },

    /**
     * Search for contact by phone number. If we find exactly one match, set the
     * contact model on the appropriate dashboard.
     *
     * @param {Object} contact - connect-streams Contact object
     */
    handleIncomingCall: function(contact) {
        var connection = contact.getInitialConnection();
        var endpoint = connection.getEndpoint();
        var maxNum = app.config && app.config.maxSearchQueryResult ? app.config.maxSearchQueryResult : 5;
        var searchParams = {
            q: endpoint.phoneNumber,
            fields: 'phone_home, phone_mobile, phone_work, phone_other, assistant_phone',
            module_list: 'Contacts',
            max_num: maxNum
        };
        var successCallback = _.bind(function(data) {
            var self = this;
            setTimeout(function() {
                self._setContactModel(contact, data);
            }, 1000);
        }, this);
        app.api.search(searchParams, {success: successCallback});
    },

    /**
     * Sets contact model for dashboard in a particular tab. This function is
     * the success callback used in the search API on incoming calls.
     *
     * @param {Object} contact - connect-streams Contact object
     * @param {Object} data - data returned by Search api
     * @private
     */
    _setContactModel: function(contact, data) {
        var index = _.indexOf(this.contactIds, contact.contactId);

        // If we have a dashboard for this contact, and our search returned
        // exactly one record, set that record on the contact tab of the
        // appropriate dashboard
        if (index !== -1 && _.isArray(data.records) && data.records.length === 1) {
            var contactBean = app.data.createBean('Contacts', _.first(data.records));
            this._components[index].setModel(1, contactBean);
            this._components[index].switchTab(1);
        }
    }
})
