/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
/**
 * @class View.Views.Base.FooterActionsView
 * @alias SUGAR.App.view.views.BaseFooterActionsView
 * @extends View.View
 */
({
    // TODO remove the id links in footer and replace to data-attributes (SC-2580)
    events: {
        'click #tour': 'showTutorialClick',
        'click #feedback': 'feedback',
        'click #support': 'support',
        'click #help': 'help'
    },
    tagName: 'span',
    layoutName: '',

    /**
     * Array of layout names where the help button should be disabled
     */
    helpBtnDisabledLayouts: [
        'about',
        'first-login-wizard'
    ],

    handleViewChange: function(layout, params) {
        var module = params && params.module ? params.module : null;
        // should we disable the help button or not, this only happens when layout is 'bwc'
        this.layoutName = _.isObject(layout) ? layout.name : layout;
        this.disableHelpButton(this.shouldHelpBeDisabled());
        if (app.tutorial.hasTutorial(this.layoutName, module)) {
            this.enableTourButton();
            if (params.module === 'Home' && params.layout === 'record' && params.action === 'detail') {
                // first time in or upgrade, show tour
                var serverInfo = app.metadata.getServerInfo(),
                    currentKeyValue = serverInfo.build + '-' + serverInfo.flavor + '-' + serverInfo.version,
                    lastStateKey = app.user.lastState.key('toggle-show-tutorial', this),
                    lastKeyValue = app.user.lastState.get(lastStateKey);
                if (currentKeyValue !== lastKeyValue) {
                    // first time in, or first time after upgrade
                    app.user.lastState.set(lastStateKey, currentKeyValue);
                    this.showTutorial({showTooltip: true});
                }
            }
        } else {
            this.disableTourButton();
        }
    },
    handleRouteChange: function(route, params) {
        this.routeParams = {'route': route, 'params': params};
    },
    enableTourButton: function() {
        this.$('#tour').removeClass('disabled');
        this.events['click #tour'] = 'showTutorialClick';
        this.undelegateEvents();
        this.delegateEvents();
    },
    disableTourButton: function() {
        this.$('#tour').addClass('disabled');
        delete this.events['click #tour'];
        this.undelegateEvents();
        this.delegateEvents();
    },

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        app.events.on('app:view:change', this.handleViewChange, this);
        var self = this;
        app.utils.doWhen(function() {
            return !_.isUndefined(app.router);
        }, function() {
            self.listenTo(app.router, 'route', self.handleRouteChange);
        });

        app.events.on('app:help:shown', function() {
            this.toggleHelpButton(true);
        }, this);

        app.events.on('app:help:hidden', function() {
            this.toggleHelpButton(false);
        }, this);

        app.events.on('alert:cancel:clicked', function() {
            // re-check if help should be disabled or not and set accordingly
            this.disableHelpButton(this.shouldHelpBeDisabled());
        }, this);
    },

    /**
     * Checks any criteria to see if help button should be disabled
     * @return {boolean}
     */
    shouldHelpBeDisabled: function() {
        return (_.indexOf(this.helpBtnDisabledLayouts, this.layoutName) !== -1);
    },

    _renderHtml: function() {
        this.isAuthenticated = app.api.isAuthenticated();
        app.view.View.prototype._renderHtml.call(this);
    },
    feedback: function() {
        window.open('http://www.sugarcrm.com/sugar7survey', '_blank');
    },
    support: function() {
        window.open('http://support.sugarcrm.com', '_blank');
    },

    /**
     * Help Button Click Event Listener
     *
     * @param {Object} event        The Click Event
     */
    help: function(event) {
        if(this.layoutName === 'bwc') {
            this.bwcHelpClicked();
        } else {
            var button = $(event.currentTarget),
                buttonDisabled = button.hasClass('disabled'),
                buttonAppEvent = button.hasClass('active') ? 'app:help:hide' : 'app:help:show';

            if (!buttonDisabled) {
                // add the disabled so that way if it's clicked again, it won't triggered the events again,
                // this will get removed below
                button.addClass('disabled');
                // trigger the app event to show and hide the help dashboard
                app.events.trigger(buttonAppEvent);
            }
        }
    },

    /**
     * Disable the help button
     *
     * @param {boolean} [disable=true]      Should we disable it or enable it, if not passed will default to true
     */
    disableHelpButton: function(disable) {
        disable = _.isUndefined(disable) ? true : disable;

        var button = this.$('#help');
        if (button) {
            button.toggleClass('disabled', disable);
        }

        return disable;
    },

    /**
     * Utility Method to toggle the help button on and off.
     *
     * @param {Boolean} active      Set or remove the active state of the button
     * @param {Object} (button)     Button Object (optional), will be found if not passed in
     */
    toggleHelpButton: function(active, button) {
        if (_.isUndefined(button)) {
            button = this.$('#help');
        }

        if (button) {
            button.removeClass('disabled').toggleClass('active', active);
        }
    },

    /**
     * click event for show tour icon
     * @param {Object} e click event.
     */
    showTutorialClick: function(e) {
        this.showTutorial();
    },

    /**
     * show tour overlay
     * @param {Object} prefs preferences to preserve.
     */
    showTutorial: function(prefs) {
        app.tutorial.resetPrefs(prefs);
        app.tutorial.show(app.controller.context.get('layout'), {module: app.controller.context.get('module')});
    },

    /**
     * Calls the old Help Docs if in BWC mode
     */
    bwcHelpClicked: function() {
        var serverInfo = app.metadata.getServerInfo(),
            lang = app.lang.getLanguage(),
            module = app.controller.context.get('module'),
            route = this.routeParams.route,
            url = 'http://www.sugarcrm.com/crm/product_doc.php?edition=' + serverInfo.flavor + '&version=' + serverInfo.version + '&lang=' + lang + '&module=' + module + '&route=' + route;
        if (route == 'bwc') {
            var action = window.location.hash.match(/#bwc.*action=(\w*)/i);
            if (action && !_.isUndefined(action[1])) {
                url += '&action=' + action[1];
            }
        }
        app.logger.info("help URL: " + url);
        window.open(url);
    }
})
