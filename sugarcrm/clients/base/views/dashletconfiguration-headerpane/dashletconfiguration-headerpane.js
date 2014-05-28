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
 * @class View.Views.Base.DashletconfigurationHeaderpaneView
 * @alias SUGAR.App.view.views.BaseDashletconfigurationHeaderpaneView
 * @extends View.View
 */
({
    plugins: ['Editable', 'ErrorDecoration'],

    events: {
        "click a[name=cancel_button]": "close",
        "click a[name=save_button]":   "save"
    },

    /**
     * Store the translated i18n label.
     * @type {String} Translated dashlet's title label.
     * @private
     */
    _translatedLabel: null,

    /**
     * {@inheritDoc}
     * Binds the listener for the before `save` event.
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.before('save', function(model) {
            return this.layout.triggerBefore('dashletconfig:save', model);
        }, this);
    },

    /**
     * {@inheritDoc}
     * Compare with the previous attributes and translated dashlet's label
     * in order to warn unsaved changes.
     *
     * @return {Boolean} true if the dashlet setting contains changes.
     */
    hasUnsavedChanges: function() {
        var previousAttributes = _.extend(this.model.previousAttributes(), {
            label: this._translatedLabel
        });
        return !_.isEmpty(this.model.changedAttributes(previousAttributes));
    },

    /**
     * Triggers a `save` event before `app.drawer.close()` is called, in case
     * any processing needs to be done on the model before it is saved.
     *
     * @return {Boolean} `false` if the `dashletconfig:save` event returns false.
     */
    save: function() {
        if (this.triggerBefore('save', this.model) === false) {
            return false;
        }

        var fields = {};
        _.each(this.meta.panels[0].fields, function(field) {
            fields[field.name] = field;
        });

        this.model.doValidate(fields, _.bind(function(isValid) {
            if (isValid) {
                app.drawer.close(this.model);
            }
        }, this));
    },

    close: function() {
        app.drawer.close();
    },

    /**
     * {@inheritdoc}
     *
     * Translate model label before render using model attributes.
     */
    _renderHtml: function() {
        var label;
        this.model = this.layout.context.get('model');
        label = app.lang.get(
            this.model.get('label'),
            this.model.get('module') || this.module,
            this.model.attributes
        );
        this._translatedLabel = label;
        this.model.set('label', label, {silent: true});
        app.view.View.prototype._renderHtml.call(this);
    }
})
