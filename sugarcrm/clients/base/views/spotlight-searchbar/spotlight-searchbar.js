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
 * @class View.Views.Base.SpotlightSearchbarView
 * @alias SUGAR.App.view.views.BaseSpotlightSearchbarView
 * @extends View.View
 */
({
    className: 'spotlight-searchbar',
    events: {
        'keyup input': 'throttledSearch',
        'click [data-action=configure]': 'initConfig'
    },

    /**
     * @inheritDoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        app.events.on('app:sync:complete', this.initLibrary, this);
        this.lastTerm = '';
    },

    initConfig: function(evt) {
        this.layout.toggle();
        this.layout.trigger('spotlight:config');
    },

    initLibrary: function() {
        this.library = [];
        this.addToInternalLibrary(this.getModuleLinks());
        this.addToInternalLibrary(this.getModuleCreateLinks());
        this.addToInternalLibrary([
            {
                module: 'Accounts',
                label: 'Ac',
                name: 'Chandler Logistics Inc',
                route: '#Accounts/f1c7a996-e0d1-d59b-258e-54bec38d95da'
            },
            {
                module: 'Contacts',
                label: 'Co',
                name: 'Octavia Stella',
                route: '#Contacts/f0bbaf0b-a38f-a037-caf6-54bec3fe5ce5'
            }
        ]);
        var options = {
            keys: ['module', 'name'],
            threshold: '0.1'
        };
        this.fuse = new Fuse(this.library, options);
    },

    addToInternalLibrary: function(items) {
        this.library = this.library.concat(items);
    },

    getModuleLinks: function() {
        var moduleList = app.metadata.getModuleNames({filter: 'display_tab'});
        return _.map(moduleList, function(module) {
            return {
                module: module,
                label: module.substr(0, 2),
                name: app.lang.getModuleName(module, {plural: true}),
                route: '#' + module
            }
        });
    },

    getModuleCreateLinks: function() {
        var moduleList = app.metadata.getModuleNames({filter: 'display_tab', access: 'create'});
        return _.map(moduleList, function(module) {
            return {
                module: module,
                label: module.substr(0, 2),
                name: app.lang.get('LNK_CREATE', module),
                route: '#' + module + '/create'
            }
        });
    },

    applyQuickSearch: function() {
        var term = this.$('input').val();
        if (term === this.lastTerm) {
            return;
        }
        var results = [];
        if (!_.isEmpty(term)) {
            results = this.fuse.search(term);
            results = results.slice(0, 6);
        }
        this.layout.trigger('spotlight:results', results);
        this.lastTerm = term;
    },

    throttledSearch: _.debounce(function(event) {
        this.applyQuickSearch();
    }, 200)

})
