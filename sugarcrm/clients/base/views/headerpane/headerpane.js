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
 * @class View.Views.Base.HeaderpaneView
 * @alias SUGAR.App.view.views.BaseHeaderpaneView
 * @extends View.View
 */
({
    /**
     * @inheritDoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        this.meta = _.extend({}, app.metadata.getView(null, 'headerpane'), this.meta);

        /**
         * The label used for the title. This is the raw label.
         *
         * @deprecated 7.5 and will be removed in 7.7. We recommend to set the
         * title by defining a `fields` array, containing a field named `title`,
         * in the metadata. You should not define the title with `meta.title`.
         * Note that you can extend {@link #formatTitle} if the string used is a
         * template and you wish to pass a context.
         *
         * @type {string}
         * @private
         */
        this._title = this.meta.title;

        this.context.on('headerpane:title', function(title) {
            this._title = title;
            if (!this.disposed) this.render();
        }, this);

        //shortcut keys
        app.shortcuts.register({
            id: 'Headerpane:Cancel',
            keys: ['esc','ctrl+alt+l'],
            component: this,
            description: 'LBL_SHORTCUT_CLOSE_DRAWER',
            callOnFocus: true,
            handler: function() {
                var $cancelButton = this.$('a[name=cancel_button]'),
                    $closeButton = this.$('a[name=close]');

                if ($cancelButton.is(':visible') && !$cancelButton.hasClass('disabled')) {
                    $cancelButton.click();
                } else if ($closeButton.is(':visible') && !$closeButton.hasClass('disabled')) {
                    $closeButton.click();
                }
            }
        });
        app.shortcuts.register({
            id: 'Headerpane:Save',
            keys: ['ctrl+s','ctrl+alt+a'],
            component: this,
            description: 'LBL_SHORTCUT_RECORD_SAVE',
            callOnFocus: true,
            handler: function() {
                var $saveButton = this.$('a[name=save_button]');
                if ($saveButton.is(':visible') && !$saveButton.hasClass('disabled')) {
                    $saveButton.click();
                }
            }
        });
    },

    /**
     * @inheritDoc
     */
    _renderHtml: function() {
        /**
         * The title being rendered in the headerpane. This is the formatted
         * label.
         *
         * @deprecated 7.5 and will be removed in 7.7. We recommend to set the
         * title by defining a `fields` array, containing a field named `title`,
         * in the metadata. You should not define the title with `meta.title`.
         * Note that you can extend {@link #formatTitle} if the string used is a
         * template and you wish to pass a context.
         *
         * @type {string}
         */
        this.title = !_.isUndefined(this._title) ? this._formatTitle(this._title) : this.title;
        this.meta.fields = _.map(this.meta.fields, function(field) {
            if (field.name === 'title') {
                field['formatted_value'] = this.title || this._formatTitle(field['default_value']);
            }
            return field;
        }, this);
        this._super('_renderHtml');
    },

    /**
     * Formats the title before being rendered.
     *
     * @param {string} title The unformatted title.
     * @return {string} The formatted title.
     * @protected
     */
    _formatTitle: function(title) {
        if (!title) {
            return '';
        }
        return app.lang.get(title, this.module);
    }
})
