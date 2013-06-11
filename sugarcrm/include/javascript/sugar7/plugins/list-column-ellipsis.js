/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

(function (app) {
    app.events.on("app:init", function () {
        app.plugins.register('list-column-ellipsis', ['view'], {

            events: {
                'click [data-field-toggle]': 'toggleColumn'
            },
            /**
             * Toggle the 'visible' state of an available field
             * @param {Object} event jquery event object
             */
            toggleColumn: function (event) {
                var isLastColumnVisible,
                    column = $(event.currentTarget).data('fieldToggle');

                // SP-845 (must have atleast one column selected)
                // User should not be able to deselect if only one column available
                isLastColumnVisible = this.isLastColumnVisible(column);
                if (!isLastColumnVisible) {
                    this._toggleColumn(column);
                }
                this.render();
                this._reopenFieldsDropdown(event);
            },
            /**
             * Sets `this._fields.available` and `this._fields.visible` properties, and toggles selected field
             * @param {String} column The column's `name`
             * @protected
             */
            _toggleColumn: function(column) {
                // Clear out _fields
                this._fields.visible = [];
                this._fields.available = [];
                // Search _fields.options for match on column and toggle it's selected property
                _.each(this._fields.options, function (fieldMeta) {
                    if (fieldMeta.name === column) {
                        fieldMeta.selected = !fieldMeta.selected;
                    }
                    // If column was found and toggled selected push to `visible` else `available`
                    if (fieldMeta.selected) {
                        this._fields.visible.push(fieldMeta);
                    } else {
                        this._fields.available.push(fieldMeta);
                    }
                }, this);
            },
            /**
             * Determines if only one column is visible, and if the `column` being toggled is the last visible one.
             * @param {String} column The column's `name`
             * @return {Boolean} True if one column left and trying to toggle same column; false otherwise
             * @protected
             */
            isLastColumnVisible: function(column) {
                var isLastColumnVisible = false;
                if (this._fields.visible.length === 1) {
                    // See if we're trying to toggle the last checked column
                    isLastColumnVisible = _.find(this._fields.visible, function(fmeta) {
                        // Column selected is the last visible one
                        return fmeta.name === column;
                    });
                }
                return isLastColumnVisible ? true : false;//so we don't return object
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
