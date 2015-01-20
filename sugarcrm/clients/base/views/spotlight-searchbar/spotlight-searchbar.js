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
        'keyup input': 'throttledSearch'
    },

    /**
     * @inheritDoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.library = [];
        this.addModuleLinks();
    },

    addModuleLinks: function() {
        var moduleList = app.metadata.getModuleNames({filter:'display_tab'});
        this.library.concat(_.map(moduleList, function(module) {
            return {
                module: module,
                label: module.substr(0, 2),
                name: app.lang.get('LBL_MODULE_NAME', module)
            }
        }));
    },

    applyQuickSearch: function() {
        var term = this.$('input').val();
        console.log('should search ' + term);
    },

    throttledSearch: _.debounce(function(event) {
        this.applyQuickSearch();
    }, 200)

})
