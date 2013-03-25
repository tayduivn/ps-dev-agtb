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

                var column = $(event.currentTarget).data('fieldToggle');

                this._fields.visible = [];
                this._fields.available = [];

                _.each(this._fields.options, function (fieldMeta) {
                    if (fieldMeta.name === column) {
                        fieldMeta.selected = !fieldMeta.selected;
                    }
                    if (fieldMeta.selected) {

                        this._fields.visible.push(fieldMeta);
                    } else {
                        this._fields.available.push(fieldMeta);
                    }
                }, this);

                this.render();

                // reopen fields dropdown
                this.$('[data-action="fields-toggle"]').dropdown('toggle');
                // stopPropagation to keep the fields dropdown opened
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
