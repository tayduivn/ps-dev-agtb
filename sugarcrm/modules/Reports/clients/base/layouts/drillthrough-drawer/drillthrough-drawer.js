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
/**
 * @class View.Layouts.Base.Reports.DrillthroughDrawerLayout
 * @alias SUGAR.App.view.layouts.BaseReportsDrillthroughDrawerLayout
 * @extends View.Layout
 */
({
    plugins: ['ShortcutSession'],

    shortcuts: [
        'Sidebar:Toggle',
        'List:Headerpane:Create',
        'List:Select:Down',
        'List:Select:Up',
        'List:Scroll:Left',
        'List:Scroll:Right',
        'List:Select:Open',
        'List:Inline:Edit',
        'List:Delete',
        'List:Inline:Cancel',
        'List:Inline:Save',
        'List:Favorite',
        'List:Follow',
        'List:Preview',
        'List:Select',
        'SelectAll:Checkbox',
        'SelectAll:Dropdown',
        'Filter:Search',
        'Filter:Create',
        'Filter:Edit',
        'Filter:Show'
    ],

    /**
     * Override the default loadData method to allow for manually constructing
     * context for each component in layout. We are loading data from the
     * ReportAPI in public method updateList.
     *
     * @override
     */
    loadData: function() {
        this.updateList();
    },

    /**
     * Fetch report related records based on drawer context as defined in
     * saved-reports-chart dashlet or Report detail view with context containing
     * a filter definition based on a chart click event. This method will also
     * render the list component in layout after data is fetched.
     */
    updateList: function() {
        var chartModule = this.context.get('chartModule');
        var reportId = this.context.get('reportId');
        var filterDef = this.context.get('filterDef');
        var useSavedFilters = this.context.get('useSavedFilters') || false;
        var endpoint = function(method, model, options, callbacks) {
            var params = _.extend(options.params || {},
                {view: 'list', group_filters: filterDef, use_saved_filters: useSavedFilters});
            var url = app.api.buildURL('Reports', 'records', {id: reportId}, params);
            return app.api.call('read', url, null, callbacks);
        };
        var callbacks = {
            success: _.bind(function(data) {
                if (this.disposed) {
                    return;
                }
                this.context.trigger('refresh:count');
                this.context.trigger('refresh:drill:labels');
            }, this),
            error: function(o) {
                app.alert.show('listfromreport_loading', {
                    level: 'error',
                    messages: app.lang.get('ERROR_RETRIEVING_DRILLTHRU_DATA', 'Reports')
                });
            },
            complete: function(data) {
                app.alert.dismiss('listfromreport_loading');
            }
        };
        var collection = this.context.get('collection');
        collection.module = chartModule;
        collection.model = app.data.getBeanClass(chartModule);
        collection.setOption('endpoint', endpoint);
        collection.fetch(callbacks);
    }
})
