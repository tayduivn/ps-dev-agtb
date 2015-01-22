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
 * @class View.Views.Base.SpotlightHeaderpaneView
 * @alias SUGAR.App.view.views.BaseSpotlightHeaderpaneView
 * @extends View.View
 */
({
    events: {
        'click a[name=save_button]':   'save'
    },

    /**
     * {@inheritDoc}
     * Binds the listener for the before `save` event.
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        // this.before('save', function(model) {
        //     return this.layout.triggerBefore('dashletconfig:save', model);
        // }, this);

        app.shortcuts.register('Dashlet:Config:Save', ['ctrl+s','ctrl+alt+a'], function() {
            var $saveButton = this.$('a[name=save_button]');
            if ($saveButton.is(':visible') && !$saveButton.hasClass('disabled')) {
                $saveButton.click();
            }
        }, this, true);
    },

    /**
     * @inheritDoc
     */
    hasUnsavedChanges: function() {
        // var previousAttributes = _.extend(this.model.previousAttributes(), {
        //     label: this._translatedLabel
        // });
        // return !_.isEmpty(this.model.changedAttributes(previousAttributes));
    },

    save: function() {
        // if (this.triggerBefore('save', this.model) === false) {
        //     return false;
        // }

        // var fields = {};
        // _.each(this.meta.panels[0].fields, function(field) {
        //     fields[field.name] = field;
        // });

        // this.model.doValidate(fields, _.bind(function(isValid) {
        //     if (isValid) {
        //         app.drawer.close(this.model);
        //     }
        // }, this));
        app.drawer.close();
    }
})
