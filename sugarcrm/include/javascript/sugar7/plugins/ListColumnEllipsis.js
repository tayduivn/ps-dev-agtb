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
(function (app) {
    app.events.on('app:init', function () {
        app.plugins.register('ListColumnEllipsis', ['view'], {

            events: {
                'click [data-field-toggle]': 'toggleColumn'
            },
            /**
             * Toggle the 'visible' state of an available field
             * @param {Object} event jquery event object
             */
            toggleColumn: function (event) {
                var column = $(event.currentTarget).data('fieldToggle');

                // SP-845 (must have atleast one column selected)
                // User should not be able to deselect if only one column available
                if (this.isLastColumnVisible(column)) {
                    event.stopPropagation();
                    return;
                }
                this._toggleColumn(column);
                this.render();
                this._reopenFieldsDropdown(event);
            },
            /**
             * Toggle selected field.
             *
             * @param {String} column The column name.
             * @protected
             */
            _toggleColumn: function(column) {
                var changedColumn = {};
                // Search _fields.options for match on column and toggle it's selected property
                var f = this._fields._byId[column];
                if (f) {
                    f.selected = !f.selected;
                    changedColumn = f;
                }
                this._fields.visible = _.where(this._fields.all, { selected: true });

                // Trigger an event to let the view know to save/update current
                // state
                this.trigger('list:save:laststate');

                // trigger an event to let the view that this is mixed-into that a column has been toggled
                this.trigger('list:toggle:column', column, changedColumn.selected, changedColumn);
            },

            /**
             * Determines if only one column is visible, and if the `column` being toggled is the last visible one.
             * @param {String} column The column's `name`
             * @return {Boolean} True if one column left and trying to toggle same column; false otherwise
             * @protected
             */
            isLastColumnVisible: function(column) {
                if (this._fields.visible.length === 1) {
                    // See if we're trying to toggle the last checked column
                    var f = this._fields._byId[column];
                    return f && f.selected;
                }
                return false;//so we don't return object
            },
            /**
             * Reopens fields dropdown and stopPropagation to keep fields dropdown opened
             * @param {Object} event The original event
             * @protected
             */
            _reopenFieldsDropdown: function(event) {
                this.$('[data-action="fields-toggle"]').dropdown('toggle');
                event.stopPropagation();
            },
            onAttach: function (component, plugin) {
                this.before('render', function () {
                    var lastActionColumn = _.last(this.rightColumns);
                    if (lastActionColumn) {
                        lastActionColumn.isColumnDropdown = true;
                    }
                }, null, this);
            }
        });
    });
})(SUGAR.App);
