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
 * @class View.Views.Base.SelectionHeaderpaneView
 * @alias SUGAR.App.view.views.BaseSelectionHeaderpaneView
 * @extends View.Views.Base.HeaderpaneView
 */
({
    extendsFrom: 'HeaderpaneView',

    initialize: function(options) {
        var moduleMeta = app.metadata.getModule(options.module),
            isBwcEnabled = (moduleMeta && moduleMeta.isBwcEnabled),
            buttonsToRemove = [],
            additionalEvents = {};

        if (isBwcEnabled) {
            buttonsToRemove.push('create_button');
        } else {
            additionalEvents['click .btn[name=create_button]'] = 'createAndSelect';
            this.events = _.extend({}, this.events, additionalEvents);
        }

        this.isMultiLink = options.context.has('recLink');
        if (!this.isMultiLink) {
            buttonsToRemove.push('link_button');
        }

        options = this._removeButtons(options, buttonsToRemove);
        this._super('initialize', [options]);
    },

    _renderHtml: function() {
        var titleTemplate = Handlebars.compile(app.lang.getAppString('LBL_SEARCH_AND_SELECT')),
            moduleName = app.lang.get('LBL_MODULE_NAME', this.module);
        this.title = titleTemplate({module: moduleName});
        this._super('_renderHtml');

        this.layout.on('selection:closedrawer:fire', function() {
            app.drawer.close();
        }, this);

        if (this.isMultiLink) {
            this.layout.on('selection:link:fire', function() {
                this.context.trigger('selection-list:link:multi');
            });
        }
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
     * Remove the specified buttons from the options metadata
     *
     * @param {object} options
     * @param {array} buttons
     * @return {*}
     * @private
     */
    _removeButtons: function(options, buttons) {
        if (options && options.meta && options.meta.buttons) {
            options.meta.buttons = _.filter(options.meta.buttons, function(button) {
                return !_.contains(buttons, button.name);
            });
        }

        return options;
    }
})
