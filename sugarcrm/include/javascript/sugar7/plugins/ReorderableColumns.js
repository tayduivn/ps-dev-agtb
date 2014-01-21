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

(function(app) {
    app.events.on('app:init', function() {

        /**
         * This plugin makes list view columns reorderable by a simple drag
         * and drop of the column header.
         *
         * It is only supported by `flex-list` views.
         */
        app.plugins.register('ReorderableColumns', ['view'], {

            /**
             * @property {String} CSS selector for the draggable columns.
             */
            _listDragColumnSelector: 'th[data-fieldname]',

            /**
             * @property {String} CSS selector for actual draggable items.
             */
            _listDragItemSelector: 'th[data-fieldname] [data-draggable="true"]',

            /**
             * @property {Array} Store the current visible columns order.
             */
            _listDragColumn: [],

            /**
             * This method will make the columns draggable and the placeholders
             * droppable.
             *
             * @private
             */
            _makeColumnReorderable: _.debounce(function() {

                if (!this.$('table').hasClass('reorderable-columns')) {
                    app.logger.error('ReorderablePlugins expects the table to have .draggable-columns class ' +
                        'in order to work.');
                    return;
                }

                this._listDragColumn = _.map(this.$(this._listDragColumnSelector), function(column) {
                    return $(column).data('fieldname');
                });

                // Make columns draggable.
                this.$(this._listDragItemSelector).draggable({
                    revert: 'invalid',
                    axis: 'x',
                    stop: _.bind(function(event, ui) {
                        if (ui.helper._renderView && !this.disposed) {
                            this.render();
                        }
                    }, this)
                });

                // Make placeholders droppable.
                this.$('.th-droppable-placeholder').droppable({
                    accept: this._listDragItemSelector,
                    hoverClass: 'th-droppable-placeholder-highlight',
                    tolerance: 'touch',
                    drop: _.bind(this._onColumnDrop, this)
                });
            }, 200),

            /**
             * When a column is dropped into a placeholder, we first verify that
             * the item has moved.
             *
             * If it has actually moved, we take the full list of columns, and
             * move the item. Then we have to reset the catalog of visible.
             * Once this is done, local storage is updated and we render the
             * view.
             *
             * @param {Event} event The event that triggered this method.
             * @param {Object} ui jQuery UI object returned by droppable plugin.
             * @private
             */
            _onColumnDrop: function(event, ui) {
                var $draggedItem = $(ui.draggable),
                    $droppedInItem = $(event.target),
                    draggedIndex,
                    droppedInIndex;

                draggedIndex = $draggedItem
                    .closest('th')
                    .find('.th-droppable-placeholder:first')
                    .data('droppableindex');

                droppedInIndex = $droppedInItem.data('droppableindex');

                if (!this._hasOrderChanged(draggedIndex, droppedInIndex)) {
                    $draggedItem.draggable('option', 'revert', true);
                    return;
                }

                var newOrder = this._calculateNewOrder($draggedItem, $droppedInItem);
                // Trigger an event to let the view know to reorder the catalog
                // of fields.
                this.trigger('list:reorder:columns', this._fields, newOrder);

                // Trigger an event to let the view know to save/update current
                // state.
                this.trigger('list:save:laststate');

                // Will render the view on draggable `stop` event.
                ui.draggable._renderView = true;
            },

            /**
             * Takes the list of visible fields, move the item, and verify if
             * the order has changed.
             *
             * @param {jQuery.Element} $draggedItem The element being dragged.
             * @param {jQuery.Element} $droppedInItem The placeholder where the
             *                         element is dropped.
             * @return {boolean} TRUE if order has changed, FALSE otherwise.
             * @private
             */
            _hasOrderChanged: function(draggedIndex, droppedInIndex) {
                var initialOrder,
                    visibleOrder;

                initialOrder = _.clone(this._listDragColumn);
                visibleOrder = _.moveIndex(this._listDragColumn, draggedIndex, droppedInIndex);
                return !_.isEqual(visibleOrder, initialOrder);
            },

            /**
             * Takes the full list of fields (including hidden fields), move the
             * item, and returns the list.
             *
             * @param {jQuery.Element} $draggedItem The element being dragged.
             * @param {jQuery.Element} $droppedInItem The placeholder where the
             *                         element is dropped.
             * @return {Array} The full list freshly ordered.
             * @private
             */
            _calculateNewOrder: function($draggedItem, $droppedInItem) {
                var globalOrder,
                    draggedIndex,
                    droppedInIndex;

                globalOrder = _.pluck(this._fields.options, 'name');
                draggedIndex = _.indexOf(globalOrder, $draggedItem.closest('th').data('fieldname'));
                droppedInIndex = _.indexOf(globalOrder, $droppedInItem.closest('th').data('fieldname'));

                // Special case for the last column, we want to move after, not
                // before.
                if ($droppedInItem.hasClass('th-droppable-placeholder-last')) {
                    droppedInIndex++;
                }

                return _.moveIndex(globalOrder, draggedIndex, droppedInIndex);
            },

            /**
             * {@inheritDoc}
             *
             * On render makes the list view columns reorderable.
             */
            onAttach: function(component, plugin) {
                this.on('render', this._makeColumnReorderable, null, this);
            }
        });
    });
})(SUGAR.App);
