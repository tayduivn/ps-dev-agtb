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
 * @class View.Views.Base.LanguageActionsView
 * @alias SUGAR.App.view.views.BaseLanguageActionsView
 * @extends View.View
 */
({
    events: {
        'click #languageList .dropdown-menu a' : 'setLanguage'
    },
    tagName: "span",
    /**
     * @override
     * @param {Object} options
     */
    initialize: function(options) {
        app.events.on("app:sync:complete", this.render, this);
        app.events.on("app:login:success", this.render, this);
        app.events.on("app:logout", this.render, this);
        app.view.View.prototype.initialize.call(this, options);
        $(window).on('resize', _.debounce(_.bind(this.adjustMenuHeight, this), 100));
    },
    /**
     * @override
     * @private
     */
    _renderHtml: function() {
        this.isAuthenticated = app.api.isAuthenticated();
        this.currentLang = app.lang.getLanguage() || "en_us";
        this.languageList = this.formatLanguageList();
        app.view.View.prototype._renderHtml.call(this);
        this.$('[data-toggle="dropdown"]').dropdown();
        this.adjustMenuHeight();
    },
    /**
     * When a user selects a language in the dropdown, set this language.
     * Note that on login, user's preferred language will be updated to this language
     *
     * @param {Event} e
     */
    setLanguage: function(e) {
        var $li = this.$(e.currentTarget),
            langKey = $li.data("lang-key");
        app.alert.show('language', {level: 'warning', title: app.lang.getAppString('LBL_LOADING_LANGUAGE'), autoclose: false});
        app.lang.setLanguage(langKey, function() {
            app.alert.dismiss('language');
        });
    },
    adjustMenuHeight: function(){
        if (this.$('#languageList').length === 0) {
            return;
        }
        var linkButton = this.$('#languageList'),
            dropupMenu = this.$('#languageList .dropdown-menu.bottom-up'),
            linkBottomPosition = parseInt($('footer').height() - linkButton.height() - linkButton.position().top, 10),
            dropupOffset = parseInt(dropupMenu.css('bottom'), 10),
            borderTop = parseInt(dropupMenu.css('border-top-width'), 10),
            menuHeight = Math.round($(window).height() - borderTop - dropupOffset - linkBottomPosition);
        dropupMenu.css('max-height', menuHeight);
    },
    /**
     * Formats the language list for the template
     *
     * @returns {Array} of languages
     */
    formatLanguageList: function() {
        // Format the list of languages for the template
        var list = [],
            languages = app.lang.getAppListStrings('available_language_dom');

        _.each(languages, function(label, key) {
            if (key !== '') {
                list.push({ key: key, value: label });
            }
        });
        return list;
    },
    /**
     * @inheritdoc
     */
    _dispose: function() {
        $(window).off('resize');
        app.view.View.prototype._dispose.call(this);
    }
})
