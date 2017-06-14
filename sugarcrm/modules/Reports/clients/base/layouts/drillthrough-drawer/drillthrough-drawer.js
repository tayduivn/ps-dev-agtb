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
 * @extends View.View
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
     * Success callback function for api call
     * @param {Object} results
     */
    loadData: function() {
        this.updateList();
    },

    updateList: function() {
        var chartModule = this.context.get('chartModule');
        var reportId = this.context.get('reportId');
        var filterDef = this.context.get('filterDef');

        var url = app.api.buildURL('Reports', 'records', {id: reportId}, {filter: filterDef});

        var recordList = this.getComponent('sidebar')
                             .getComponent('main-pane')
                             .getComponent('drillthrough-list')
                             .getComponent('recordlist');

        var headerPane = this.getComponent('sidebar')
                             .getComponent('main-pane')
                             .getComponent('drillthrough-headerpane');

        app.api.call('read', url, null, {
            success: _.bind(function(data) {
                var collection = app.data.createBeanCollection(chartModule, data.records);
                var title = this._buildTitle(collection);

                this.context.trigger('headerpane:title', title);
                collection.dataFetched = true;

                recordList.collection = collection;
                recordList.context.set('collection', collection);
                recordList.context.set('dataView', 'list');
                recordList.context.trigger('change:collection');
                recordList.render();
            }, this),
            error: _.bind(function(o) {
                console.log('Error retrieving Report data. ' + o);
            }, this),
            complete: function(data) {
                app.alert.dismiss('listfromreport_loading');
            }
        });
    },

    _buildTitle: function(collection) {
        var chartModule = this.context.get('chartModule');
        var groupDefs = this.context.get('groupDefs');
        var filterDef = this.context.get('filterDef');
        var dashConfig = this.context.get('dashConfig');
        var recordCount = collection.length || null;
        var key;
        var title;

        function isFiscalTimeperiod(group) {
            return !_.isUndefined(group.qualifier) && group.qualifier.indexOf('fiscal') !== -1;
        }
        function titleCase(str) {
            return str
                .replace('_', ' ')
                .split(' ')
                .map(function(d, i) {
                    return d.charAt(0).toUpperCase() + d.slice(1);
                })
                .join(' ');
        }

        title = isFiscalTimeperiod(groupDefs[0]) ? (app.lang.get('LBL_FISCAL', 'Reports') + ' ') : '';
        title += titleCase(dashConfig.groupLabel) + ' ' + app.lang.getModuleName(chartModule, {plural: recordCount > 1});

        if (filterDef.length > 1) {
            key = Object.getOwnPropertyNames(filterDef[1])[0];
            title += ' with ' + titleCase(dashConfig.seriesLabel) + ' ' + titleCase(key);
        }

        title += recordCount ? ' (' + recordCount + ' records)' : '';

        return title;
    }
})
