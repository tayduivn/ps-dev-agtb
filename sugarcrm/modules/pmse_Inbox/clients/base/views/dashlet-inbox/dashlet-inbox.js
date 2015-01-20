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
({
//    extendsFrom: 'TabbedDashletView',
    extendsFrom: 'HistoryView',

    /**
     * {@inheritDoc}
     *
     * @property {Number} _defaultSettings.limit Maximum number of records to
     *   load per request, defaults to '10'.
     * @property {String} _defaultSettings.visibility Records visibility
     *   regarding current user, supported values are 'user' and 'group',
     *   defaults to 'user'.
     */
    _defaultSettings: {
        date:'true',
        limit: 10,
        visibility: 'user'
    },
//    /**
//     * {@inheritDoc}
//     *
//     * Store current date state in settings.
//     */
//    initDashlet: function() {
//        this._super('initDashlet');
//        if (!this.meta.last_state) {
//            this.meta.last_state = {
//                id: this.dashModel.get('id') + ':' + this.name,
//                defaults: {}
//            };
//        }
//        this.settings.on('change:date', function(model, value) {
//            var specificDateKey = app.user.lastState.key('date', this);
//            app.user.lastState.set(specificDateKey, value);
//        }, this);
//        this.settings.set('date', this.getDate());
//        this.tbodyTag = 'ul[data-action="pagination-body"]';
//    },

    /**
     * {@inheritDoc}
     */
    initialize: function(options) {
        options.meta = options.meta || {};
        options.meta.template = 'tabbed-dashlet';

        this.plugins = _.union(this.plugins, [
            'LinkedModel'
        ]);

        this._super('initialize', [options]);


    },

    /**
     * {@inheritDoc}
     */
    _initEvents: function() {
//        this._super('_initEvents');
        //dashlet-inbox:participate-record:fire
////        this.on('dashlet-processes:designer:fire', this.designer, this);
////        this.on('dashlet-processes:delete-record:fire', this.deleteRecord, this);
////        this.on('dashlet-processes:disable-record:fire', this.disableRecord, this);
////        this.on('dashlet-processes:enable-record:fire', this.enableRecord, this);
//        return this;
        this.events = _.extend(this.events, {
            'click [data-action=date-switcher]': 'dateSwitcher',
            'click [data-action=participate-switcher]': 'participateSwitcher'
        });
        this._super('_initEvents');
//        this.on('planned-activities:close-record:fire', this.heldActivity, this);

//        this.before('render:rows', function(data) {
//            this.updateInvitation(this.collection, data);
//            return false;
//        }, this);

        return this;
    },
    participateSwitcher: function() {
        alert('participate');
    },
    /**
     * {@inheritDoc}
     */
//    tabSwitcher: function(event) {
//        var tab = this.tabs[this.settings.get('activeTab')];
//        if (tab.invitations) {
//            tab.invitations.dataFetched = false;
//        }
//
//        this._super('tabSwitcher', [event]);
//    },

    /**
     * Event handler for date switcher.
     *
     * @param {Event} event Click event.
     */
    dateSwitcher: function(event) {
        var date = this.$(event.currentTarget).val();
        if (date === this.getDate()) {
            return;
        }
        //alert('switcher '+date);date devuelve true o false del botton
        this.settings.set('date', date);
        this.layout.loadData();
    },

    /**
     * Get current date state.
     * Returns default value if can't find in last state or settings.
     *
     * @return {String} Date state.
     */
    getDate: function() {
        var date = app.user.lastState.get(
            app.user.lastState.key('date', this),
            this
        );
        return date || this.settings.get('date') || this._defaultSettings.date;
    },
    /**
     * {@inheritDoc}
     *
     * On load of new data, make sure we reload invitations related data, if
     * it is defined for the current tab.
     */
    loadData: function(options) {
        if (this.disposed || this.meta.config) {
            return;
        }
        var tab = this.tabs[this.settings.get('activeTab')];
        if (tab.invitations) {
            tab.invitations.dataFetched = false;
        }
        this._super('loadData', [options]);
    },


    /**
     * {@inheritDoc}
     *
     * FIXME: This should be removed when metadata supports date operators to
     * allow one to define relative dates for date filters.
     */
    _initTabs: function() {
        // FIXME: this should be replaced with this._super('_initTabs'); which
        // is currently throwing an error with the following message: "Attempt
        // to call different parent method from child method"
        app.view.invokeParent(this, {
            type: 'view',
            name: 'tabbed-dashlet',
            method: '_initTabs',
            platform: 'base'
        });
        //filters
//        _.each(this.tabs, function(tab) {
//            if (!tab.invitation_actions) {
//                return;
//            }
//            tab.invitations = this._createInvitationsCollection(tab);
//        }, this);
//
//        return this;
  },
    /**
     * {@inheritDoc}
     */
    _getFilters: function(index) {

//        var today = app.date().format('YYYY-MM-DD'),
            tab = this.tabs[index],
            filter = {},
            filters = [],
            defaultFilters = {
                'true': {$lte: 'true'},
                'false': {$gt: 'false'}
            };

        filter[tab.filter_applied_to] = defaultFilters[this.getDate()];

        filters.push(filter);

        return filters;
    },
//    _initTabs: function() {
//        this.tabs = [];
//        _.each(this.dashletConfig.tabs, function(tab, index) {
//            if (tab.active) {
//                this.settings.set('activeTab', index);
//            }
//            var collection = this._createCollection(tab);
//            if (_.isNull(collection)) {
//                return;
//            }
//            collection.on('add', this.bindCollectionAdd, this);
//            collection.on('reset', this.bindCollectionReset, this);
//
//            this.tabs[index] = tab;
//            this.tabs[index].collection = collection;
//            this.tabs[index].relate = _.isObject(collection.link);
//            alert(collection.link);
//            //this.tabs[index].record_date = tab.record_date || 'date_entered';
//            this.tabs[index].include_child_items = tab.include_child_items || true;
//        }, this);
//        return this;
//    },
    //----------------------------------------FIN

    /**
     * Updating in fields delete removed
     * @return {Function} complete callback
     * @private
     */
    _getRemoveRecord: function() {
        return _.bind(function(model){
            if (this.disposed) {
                return;
            }
            this.collection.remove(model);
            this.render();
            this.context.trigger("tabbed-dashlet:refresh", model.module);
        }, this);
    },
    /**
     * Method view alert in process with text modify
     * show and hide alert
     */
    _refresh: function(model, status) {
        app.alert.show(model.id + ':refresh', {
            level:"process",
            title: status,
            autoclose: false
        });
        return _.bind(function(model){
            var options = {};
            this.layout.reloadDashlet(options);
            app.alert.dismiss(model.id + ':refresh');
        }, this);
    },

    /**
     * {@inheritDoc}
     *
     * New model related properties are injected into each model:
     *
     * - {Boolean} overdue True if record is prior to now.
     * - {String} picture_url Picture url for model's assigned user.
     */
    _renderHtml: function() {
        var self = this;
        if (this.meta.config) {
            this._super('_renderHtml');
            return;
        }

        var tab = this.tabs[this.settings.get('activeTab')];
        
        if (tab.overdue_badge) {
            this.overdueBadge = tab.overdue_badge;
        }
        _.each(this.collection.models, function(model){
            var pictureUrl = App.api.buildFileURL({
                module: 'Users',
                id: model.get('assigned_user_id'),
                field: 'picture'
            });
            var ShowCaseUrl = 'pmse_Inbox/' +  model.get('id2') + '/layout/show-case/' +  model.get('flow_id');
            var ShowCaseUrlBwc = App.bwc.buildRoute('pmse_Inbox', '', 'showCase', {id:model.get('flow_id')});
            var SugarModule = model.get('cas_sugar_module');
            if (app.metadata.getModule(SugarModule).isBwcEnabled) {
                model.set('show_case_url', ShowCaseUrlBwc);
            } else {
                model.set('show_case_url', ShowCaseUrl);
            }
            model.set('picture_url', pictureUrl);
        }, this);
        this._super('_renderHtml');
    }
})
