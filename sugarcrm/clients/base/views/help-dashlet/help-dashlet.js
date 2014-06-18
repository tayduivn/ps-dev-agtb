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
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 */
({
    plugins: ['Dashlet'],

    /**
     * Holds the Object returned by app.help.get()
     * <pre><code>
     * {
     *    title: '',
     *    body: '',
     *    more_help: ''
     * }
     * </code></pre>
     * @type {Object}
     */
    helpObject: undefined,

    /**
     * {@inheritdoc}
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        var helpUrl = {
                more_info_url: this.createMoreHelpLink(),
                more_info_url_close: '</a>'
            },
            ctx = this.context && this.context.parent || this.context,
            layout = (this.meta.preview) ? 'preview' : ctx.get('layout');

        this.helpObject = app.help.get(ctx.get('module'), layout, helpUrl);

        // if title is empty for some reason, use the dashlet label as the fallback
        if (_.isEmpty(this.helpObject.title)) {
            this.helpObject.title = app.lang.get(this.meta.label);
        }
    },

    /**
     * {@inheritdoc}
     */
    initDashlet: function() {
        this.settings.set({
            label: this.helpObject.title
        });
    },

    /**
     * @inheritdoc
     */
    getLabel: function() {
        return this.helpObject.title;
    },

    /**
     * Collects server version, language, module, and route and returns an HTML link to be used
     * in the template
     *
     * @returns {String} The HTML a-tag for the More Help link
     */
    createMoreHelpLink: function() {
        var serverInfo = app.metadata.getServerInfo(),
            lang = app.lang.getLanguage(),
            module = app.controller.context.get('module'),
            route = this.context.get('layout');

        if (route == 'records') {
            route = 'list';
        }

        var url = 'http://www.sugarcrm.com/crm/product_doc.php?edition=' + serverInfo.flavor
            + '&version=' + serverInfo.version + '&lang=' + lang + '&module=' + module + '&route=' + route;
        if (route == 'bwc') {
            var action = window.location.hash.match(/#bwc.*action=(\w*)/i);
            if (action && !_.isUndefined(action[1])) {
                url += '&action=' + action[1];
            }
        }

        return '<a href="' + url + '" target="_blank">';
    },

    /**
     * {@inheritdoc}
     *
     * Overriding to pass this.helpObject as the template model to use,
     * and this.options in case templateOptions get passed down
     */
    _renderHtml: function() {
        this._super('_renderHtml', [this.helpObject, this.options]);
    }
})
