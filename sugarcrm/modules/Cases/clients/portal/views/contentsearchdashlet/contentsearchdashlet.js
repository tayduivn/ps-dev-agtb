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
* @class View.Views.Portal.ContentsearchdashletView
* @alias SUGAR.App.view.views.PortalContentsearchdashletView
* @extends View.View
*/
({
    plugins: ['Dashlet'],

    events: {
        'click [data-action="create-case"]': 'initCaseCreation',
        'keyup [data-action="search"]': 'searchCases'
    },

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this.caseDeflection = this.isCaseDeflectionEnabled();
        this.greeting = app.lang.get('LBL_CONTENT_SEARCH_DASHLET_GREETING', this.module, {
            username: app.user.get('full_name')
        });
        this._super('initialize', [options]);
    },

    /**
     * Checks if case deflection is enabled. In case it is enabled the dashlet
     * will render a search bar for the users, if not it will render a message
     * with the case creation button.
     *
     * @return {boolean} True if case deflection is enabled.
     */
    isCaseDeflectionEnabled: function() {
        return _.isUndefined(app.config.caseDeflection) ||
            app.config.caseDeflection === 'enabled';
    },

    /**
     * Will display the case creation drawer from where the users are able to create a new case.
     */
    initCaseCreation: function() {
        app.drawer.open({
            layout: 'create',
            context: {
                create: true,
                module: 'Cases'
            }
        });
    },

    /**
     * Will make a search for Knowledge Base records matching the given search keywords.
     */
    searchCases: _.debounce(function(event) {
        var searchTerm = event.target.value;
        var resultsWrapper = this.$el.find('.search-results-wrapper');
        var resultsPanel = resultsWrapper.find('.search-results');

        if (searchTerm) {
            resultsWrapper.removeClass('hide');
        } else {
            resultsWrapper.addClass('hide');
        }
        resultsPanel.html(searchTerm);
    }, 200)
})
