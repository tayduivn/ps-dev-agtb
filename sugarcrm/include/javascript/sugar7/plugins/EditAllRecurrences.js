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
(function(app) {

    app.events.on('app:init', function() {

        /**
         * Edit All Recurrences plugin sets up a standard way to enable/disable
         * an 'All Recurrences' mode for record view. When the mode is off,
         * only the single event record is updated. When the mode is on, all
         * event records in the series are updated.
         * This plugin also handles switching from a child record in the series
         * to the parent record (since we require the parent record to control
         * the recurrence information).
         *
         * This plugin is built to enhance {@link View.Views.Base.RecordView}
         * and its descendants.
         */
        app.plugins.register('EditAllRecurrences', ['view'], {
            onAttach: function() {
                this.on('init', function() {
                    // listen for edit all recurrences event on the context
                    this.context.on('all_recurrences:edit', this.editAllRecurrences, this);
                });

                this.on('render', function() {
                    // TODO: This solution is temporary until SC-3244 which will provide a cleaner way into edit mode
                    // if context has all_recurrences flag, go directly into editing all
                    if (this.context.get('all_recurrences') === true && this.buttons.edit_recurrence_button) {
                        this.editAllRecurrences();
                        this.context.unset('all_recurrences');
                    } else {
                        this.toggleAllRecurrencesMode(false); // default to off
                    }
                });

                // override {@link View.Views.Base.RecordView#cancelClicked}
                // turn off all recurrences mode on cancel
                this.cancelClicked = _.wrap(this.cancelClicked, function(_super, event) {
                    _super.call(this, event);
                    this.toggleAllRecurrencesMode(false);
                });

                // override {@link View.Views.Base.RecordView#getCustomSaveOptions}
                this.getCustomSaveOptions = _.wrap(this.getCustomSaveOptions,
                    _.bind(function(_super, options) {
                        return _.extend(
                            _super.call(this, options),
                            this.addRecurrenceOptionsForSave(options)
                        );
                    }, this)
                );
            },

            /**
             * Puts the record view in edit mode for all event records in
             * the series. If launching from a child record, we switch over to
             * the parent record since that is what controls the recurrence
             * information.
             */
            editAllRecurrences: function() {
                var parentId = this.model.get('repeat_parent_id');
                if (!_.isEmpty(parentId) && parentId !== this.model.id) {
                    app.alert.show('recurrence_parent_route_confirmation', {
                        level: 'confirmation',
                        messages: 'LBL_CALENDAR_CONFIRM_ROUTE_TO_PARENT',
                        onConfirm: _.bind(function() {
                            this.editAllRecurrencesFromParent(parentId);
                        }, this)
                    });
                } else {
                    this.toggleAllRecurrencesMode(true);
                    this.context.trigger('button:edit_button:click');
                }
            },

            /**
             * Toggle edit all recurrences mode on/off
             *
             * @param {Boolean} enabled True turns edit all recurrence mode on
             */
            toggleAllRecurrencesMode: function(enabled) {
                // if no recurrence - should always be editable
                if (this.model.get('repeat_type') === '') {
                    enabled = true;
                }

                this.allRecurrencesMode = enabled;
                this.toggleEditRecurrenceFields(enabled);
            },

            /**
             * Toggle the the recurrence fields between edit/readonly
             *
             * @param {Boolean} editable
             */
            toggleEditRecurrenceFields: function(editable) {
                var getEditLink = function(fieldName) {
                    return this.$("span.record-edit-link-wrapper[data-name=" + fieldName + "]");
                };

                _.each([
                    'repeat_type',
                    'recurrence',
                    'repeat_interval',
                    'repeat_dow',
                    'repeat_until',
                    'repeat_count'
                ], function(field) {
                    if (editable) {
                        // allow recurrence fields to be editable
                        this.noEditFields = _.without(this.noEditFields, field);
                        getEditLink(field).show();
                    } else {
                        // make all recurrence fields read only
                        this.noEditFields.push(field);
                        getEditLink(field).hide();
                    }
                }, this);

                this.setEditableFields();
            },

            /**
             * Route to parent record in edit all recurrences mode
             */
            editAllRecurrencesFromParent: function(parentId) {
                var route = app.router.buildRoute(this.module, parentId, 'edit/all-recurrences');
                app.router.navigate('#' + route, {trigger: true});
            },

            /**
             * Set the `all_recurrences` to true if in all recurrences mode on save
             * This is used by the API to determine whether to update an individual
             * event or all recurrences of the event.
             *
             * @param {Object} options
             * @returns {Object} Options to be added on top of other save options
             */
            addRecurrenceOptionsForSave: function(options) {
                options = options || {};

                if (this.allRecurrencesMode) {
                    options.params = options.params || {};
                    options.params.all_recurrences = true;
                }

                return options;
            }
        });
    });
})(SUGAR.App);
