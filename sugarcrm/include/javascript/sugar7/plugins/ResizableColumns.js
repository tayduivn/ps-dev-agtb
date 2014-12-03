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
         * This plugin makes list view columns resizable by the end user.
         *
         * It is only supported by the list views extending
         * {@link View.Views.Base.FlexListView}.
         */
        app.plugins.register('ResizableColumns', ['view'], {

            /**
             * CSS selector for the resizable columns.
             *
             * @property {string}
             * @private
             */
            _listResizableColumnSelector: 'thead tr:first-child th[data-fieldname]',

            /**
             * This method will make the columns resizable.
             *
             * The plugin will trigger `list:column:resize:save`, passing the
             * column widths, when the user resizes a column. The view can
             * eventually listen and save them to the cache.
             *
             * The plugin calls
             * {@link View.Views.Base.FlexListView#getCacheWidths} and will
             * restore the widths if it gets an array containing the column
             * widths.
             *
             * @private
             */
            _makeColumnResizable: function() {
                if (this.disposed) {
                    return;
                }

                var $table = this.$('table');
                $table.resizableColumns({
                    usePixels: true,
                    selector: this._listResizableColumnSelector,
                    minWidth: 1,

                    /**
                     * Sets to `null` because we use our own store through the
                     * events.
                     */
                    store: null,

                    /**
                     * Sets the column widths to the table headers.
                     *
                     * @param {Event} event The `column:resize:restore` event.
                     * @param {Array} columns The column widths.
                     */
                    restore: function(event, columns) {
                        var i = 0;
                        var resizableColumns = $table.data('resizableColumns');

                        resizableColumns.$tableHeaders.each(function(index, el) {
                            var $el, width;
                            $el = $(el);
                            width = columns[i++];
                            if ($el.attr('data-noresize') == null && width) {
                                return resizableColumns.setWidth($el[0], width);
                            }
                        });
                        resizableColumns.syncHandleWidths();
                    }
                });

                // Restore the cache widths.
                var cachedSizes = this.getCacheWidths();
                if (!_.isEmpty(cachedSizes)) {
                    $table.trigger('column:resize:restore', [cachedSizes]);
                }
                $(window).resize();

                // Triggers an event to tell the view to save changes.
                $table.on('column:resize:save', _.bind(function(event, columns) {
                    this.trigger('list:column:resize:save', columns);
                }, this));
            },

            /**
             * Unbinds the plugin and the events attached to the `<table>`.
             *
             * @private
             */
            _unbindResizableColumns: function() {
                if (this.disposed) {
                    return;
                }

                var $table = this.$('table');
                $table.off('column:resize:save');
                $table.resizableColumns('destroy');
            },

            /**
             * @inheritDoc
             *
             * Every time `render` is called, re-applies the plugin and cleans
             * up.
             */
            onAttach: function(component, plugin) {
                this.before('render', this._unbindResizableColumns);
                this.on('render', _.debounce(this._makeColumnResizable, 100));
            },

            /**
             * @inheritDoc
             *
             * Unbinds the plugin properly.
             */
            onDetach: function(component, plugin) {
                this._unbindResizableColumns();
            }
        });
    });
})(SUGAR.App);
