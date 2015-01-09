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
 * @class View.Views.Base.SelectionHeaderpaneView
 * @alias SUGAR.App.view.views.BaseSelectionHeaderpaneView
 * @extends View.Views.Base.HeaderpaneView
 */
({
    extendsFrom: 'HeaderpaneView',

    initialize: function(options) {
        var moduleMeta = app.metadata.getModule(options.module),
            isBwcEnabled = (moduleMeta && moduleMeta.isBwcEnabled),
            multiSelectFilter = options.context.get('isMultiSelect'),
            buttonsToRemove = [],
            additionalEvents = {};

        if (isBwcEnabled || multiSelectFilter) {
            buttonsToRemove.push('create_button');
        } else {
            additionalEvents['click [name=create_button]'] = 'createAndSelect';
            this.events = _.extend({}, this.events, additionalEvents);
        }

        this.isMultiLink = options.context.has('recLink');
        if (!this.isMultiLink) {
            buttonsToRemove.push('link_button');
        }
        if (!multiSelectFilter) {
            buttonsToRemove.push('select_button');
        }

        this._super('initialize', [options]);
        this._removeButtons(buttonsToRemove);
    },

    /**
     * @inheritDoc
     */
    _renderHtml: function() {
        this._super('_renderHtml');

        this.layout.on('selection:closedrawer:fire', _.once(_.bind(function() {
            this.$el.off();
            app.drawer.close();
        }, this)));

        if (this.isMultiLink) {
            this.layout.on('selection:link:fire', function() {
                this.context.trigger('selection-list:link:multi');
            });
        }
    },

    /**
     * @inheritDoc
     */
    _formatTitle: function(title) {
        var moduleName = app.lang.get('LBL_MODULE_NAME', this.module);
        return app.lang.get(title, this.module, {module: moduleName});
    },

    /**
     * Open create inline modal with no dupe check
     * On save, set the selection model which will close the selection-list inline modal
     */
    createAndSelect: function() {
        app.drawer.open({
            layout: 'create-nodupecheck',
            context: {
                module: this.module,
                create: true
            }
        }, _.bind(function(context, model) {
            if (model) {
                this.context.trigger('selection-list:select', model);
            }
        }, this));
    },

    /**
     * Removes buttons from main_dropdown
     *
     * @param {Array} buttons The names of the buttons to remove.
     * @private
     */
    _removeButtons: function(buttons) {
        if (!buttons.length) {
            return;
        }

        var mainDropdown = _.find(this.meta.buttons, function(button) {
            return button.name === 'main_dropdown';
        });

        if (!mainDropdown) {
            return;
        }

        mainDropdown.buttons = _.filter(mainDropdown.buttons, function(button) {
            return !_.contains(buttons, button.name);
        });
    }
})
