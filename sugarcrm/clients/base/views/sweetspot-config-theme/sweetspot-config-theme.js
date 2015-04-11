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
 * @class View.Views.Base.SweetspotConfigThemeView
 * @alias SUGAR.App.view.views.BaseSweetspotConfigThemeView
 * @extends View.View
 */
({
    className: 'container-fluid',

    events: {
        'click .theme': 'checkTheme'
    },

    /**
     * @inheritDoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this._bindEvents();
    },

    /**
     * Binds the events that this layout uses.
     *
     * @protected
     */
    _bindEvents: function() {
        this.context.on('sweetspot:ask:configs', this.generateConfig, this);
    },


    /**
     * @inheritDoc
     */
    _renderHtml: function() {
        this._super('_renderHtml');
        this.initTheme();
    },

    initTheme: function() {
        var prefs = app.user.getPreference('sweetspot');
        var theme = prefs && prefs.theme;

        this.checkTheme(null, theme);
    },

    checkTheme: function(evt, theme) {
        var $newTheme;
        if (evt) {
            var $label = this.$(evt.currentTarget);
            $newTheme = $label.find('input');
        } else {
            var themeId = theme ? '#' + theme : '#default';
            $newTheme = this.$(themeId);
        }

        // If it's already checked, exit.
        if ($newTheme.is(':checked')) {
            return;
        }

        // Clear checked state from radio inputs.
        var $radios = this.$('[type=radio]');
        $radios.removeAttr('checked');

        $newTheme.attr('checked', true);
    },

    /**
     * Saves the selected theme into user preferences.
     */
    generateConfig: function() {
        var $checked = this.$('[checked=checked]');
        var theme = $checked.val();

        if (theme === 'default') {
            return;
        }
        var data = this._formatForUserPrefs(theme);
        this.context.trigger('sweetspot:receive:configs', data);
    },

    /**
     * This method prepares the attributes payload for the call to
     * {@link Core.User#updatePreferences}.
     *
     * @protected
     * @param {string} theme The configured theme name.
     * @return {Object} The prepared configuration data.
     */
    _formatForUserPrefs: function(theme) {
        return {theme: theme};
    }
})
