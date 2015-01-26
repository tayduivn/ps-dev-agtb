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
    plugins: ['JSTree', 'NestedSetCollection'],

    events: {
        'keyup [data-name=search]': '_keyHandler',
        'click [data-role=icon-remove]': function() {
            this.trigger('search:clear');
        }
    },

    /**
     * Default settings.
     *
     * @property {Object} _defaultSettings
     * @property {Boolean} _defaultSettings.showMenu Display menu or not.
     * @property {Number} _defaultSettings.liHeight Height (pixels) of row.
     */
    _defaultSettings: {
        showMenu: true,
        liHeight: 37
    },

    /**
     * Aggregated settings.
     * @property {Object} _settings
     */
    _settings: null,

    /**
     * List of overridden callbacks.
     * @property {Object} _callbacks
     */
    _callbacks: null,

    /**
     * Initialize _settings object.
     * @return {Object}
     * @private
     */
    _initSettings: function() {
        this._settings = {
            settings: _.extend({},
                this._defaultSettings,
                this.context.get('treeoptions') || {},
                this.def && this.def.settings || {}
            )};
        return this;
    },

    /**
     * Initialize _callbacks object.
     * @return {Object}
     * @private
     */
    _initCallbacks: function() {
        this._callbacks = _.extend({},
            this.context.get('treecallbacks') || {},
            this.def && this.def.callbacks || {}
        );
        return this;
    },

    /**
     * {@inheritDoc}
     */
    initialize: function(options) {
        this.on('search:clear', function() {
            var el = this.$el.find('input[data-name=search]');
            el.val('');
            this._toggleIconRemove(!_.isEmpty(el.val()));
            this.searchNodeHandler(el.val());
        }, this);
        this._super('initialize', [options]);
        this._initSettings();
        this._initCallbacks();
        this.layout.on('click:add_node_button', this.addNodeHandler, this);
    },

    /**
     * {@inheritDoc}
     */
    _dispose: function() {
        this.off('search:clear');
        this._super('_dispose');
    },

    /**
     * {@inheritDoc}
     * @example Call _renderTree function with the following parameters.
     * <pre><code>
     * this._renderTree($('.tree-block'), this._settings, {
     *      onToggle: this.jstreeToggle,
     *      onSelect: this.jstreeSelect
     * });
     * </code></pre>
     */
    _renderHtml: function(ctx, options) {
        this._super('_renderHtml', [ctx, options]);
        this._renderTree($('.tree-block'), this._settings, this._callbacks);
    },

    /**
     * Handle submit in search field.
     * @param {Event} event
     * @return {Boolean}
     * @private
     */
    _keyHandler: function(event) {
        this._toggleIconRemove(!_.isEmpty($(event.currentTarget).val()));
        if (event.keyCode != 13) return false;
        this.searchNodeHandler(event);
    },

    /**
     * Append or remove an icon to the search input so the user can clear the search easily.
     * @param {Boolean} addIt TRUE if you want to add it, FALSE to remove
     */
    _toggleIconRemove: function(addIt) {
        if (addIt && !this.$('i[data-role=icon-remove]')[0]) {
            this.$el.find('div[data-container=filter-view-search]').append('<i class="add-on fa fa-remove" data-role="icon-remove"></i>');
        } else if (!addIt) {
            this.$('i[data-role=icon-remove]').remove();
        }
    },

    /**
     * Custom add handler.
     */
    addNodeHandler: function() {
        this.addNode(app.lang.get('LBL_DEFAULT_TITLE', this.module), 'last', false, true);
    },

    /**
     * Custom search handler.
     * @param {Event} event
     */
    searchNodeHandler: function(event) {
        this.searchNode($(event.currentTarget).val());
    }
})
