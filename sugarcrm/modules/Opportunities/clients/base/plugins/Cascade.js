/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

(function(app) {
    app.events.on('app:init', function() {
        /**
         * The Cascade plugin is used for Opportunity fields
         */
        app.plugins.register('Cascade', ['field'], {
            baseFieldName: null,
            field: null,
            model: null,

            /**
             * Set the appropriate field attribute for Opps+RLIs to handle
             * rendering the checkbox.
             *
             * Wrap "setMode" as it handles changing the field from detail to
             * edit modes. It now will also handle binding enable/disable
             * listeners to the checkbox.
             *
             * Listen to model.change events on this field, and set our model's
             * _cascade attribute.
             * @param component
             * @param plugin
             */
            onAttach: function(component, plugin) {
                this.baseFieldName = component.options.def.name;
                this.field = component;
                this.model = this.field.options.model;

                var oppConfig = app.metadata.getModule('Opportunities', 'config');
                if (!oppConfig || oppConfig.opps_view_by !== 'RevenueLineItems') {
                    this.field.rlisEnabled = false;
                    return;
                }
                this.field.rlisEnabled = true;

                component.setMode = _.wrap(component.setMode, _.bind(function(setMode, args) {
                    setMode.call(component, args);
                    this.handleModeChange(args);
                }, this));

                if (this.field.options.view.action === 'edit') {
                    this.field.on('render', this.bindEditActions, this);
                }

                this.model.on('change:' + this.baseFieldName, this.setCascadeValue, this);
            },

            /**
             * If we're in "edit" mode, bind our event listeners to the checkbox.
             *
             * Otherwise, make sure the field is enabled so clicking it or
             * entering edit mode will display the checkbox.
             * @param toTemplate
             */
            handleModeChange: function(toTemplate) {
                if (!this.field.$el) {
                    return;
                }
                var action = toTemplate || this.field.action || this.field.view.action || 'detail';
                if (action === 'edit') {
                    this.bindEditActions();
                } else {
                    this.field.setDisabled(false, {trigger: true});
                }
            },

            /**
             * Bind a "click" listener to the checkbox. This is done using
             * jQuery because this checkbox exists only in our template and not
             * as a field on our model.
             */
            bindEditActions: function() {
                var checkbox = this.field.$el.children('input[type=checkbox]');
                var self = this;
                checkbox.click(function() {
                    if (this.checked === false) {
                        self.field.setDisabled(true, {trigger: true});
                        $('.' + self.baseFieldName + '_should_cascade').prop('checked', false);
                        self.resetModelValue();
                    } else {
                        self.field.setDisabled(false, {trigger: true});
                    }
                    // If the field has been enabled/disabled, it has also been
                    // re-rendered. This re-rendering removes the DOM element
                    // to which we bound our "click" listener, so we need to bind
                    // it to the element that exists now.
                    self.bindEditActions();
                });
            },

            /**
             * Util function to reset model to synced values and stop any cascades.
             * Used when un-checking the checkbox in edit mode.
             */
            resetModelValue: function() {
                this.model.set(this.baseFieldName, this.model.getSynced(this.baseFieldName));
                this.model.set(this.baseFieldName + '_cascade', '');
            },

            /**
             * Called on model.change events for our field. This sets the model
             * property needed to cause cascading changes.
             */
            setCascadeValue: function() {
                this.model.set(this.baseFieldName + '_cascade', this.model.get(this.baseFieldName));
            }
        });
    });
})(SUGAR.App);
