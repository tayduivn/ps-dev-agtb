// FILE SUGARCRM flav=ent ONLY
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
 * @class View.Views.Base.PipelineRecordlistContentView
 * @alias SUGAR.App.view.views.BasePipelineRecordlistContentView
 * @extends View.Views.Base.PipelineRecordlistContentView
 */
({
    className: 'my-pipeline-content',
    monthsToDisplay: 6,

    events: {
        'click a[name=arrow-left]': 'navigateLeft',
        'click a[name=arrow-right]': 'navigateRight'
    },

    resultsPerPageColumn: 7,

    tileVisualIndicator: {
        'outOfDate': '#CC1E13', // We can use any CSS accepted value for color, e.g: #CC1E13
        'nearFuture': 'orange',
        'inFuture': 'green',
        'default': '#0F374B'
    },

    //used to force api to return these fields also for a proper coloring.
    tileVisualIndicatorFields: {
        'Opportunities': 'date_closed',
        'Tasks': 'date_due',
        'Leads': 'status',
        'Cases': 'status'
    },

    hasAccessToView: true,

    /**
     * Initialize various pipelineConfig variables and set action listeners
     *
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        this.startDate = app.date().format('YYYY-MM-DD');
        this.pipelineConfig = app.metadata.getModule('VisualPipeline', 'config');

        this.pipelineFilters = [];
        this.hiddenHeaderValues = [];
        this.action = 'list';
    },

    /**
     * @inheritdoc
     */
    bindDataChange: function() {
        this._super('bindDataChange');

        this.context.on('newModelCreated', this.addModelToCollection, this);
        this.context.on('filterChanged', this.buildFilters, this);
        this.context.on('button:delete_button:click', this.deleteRecord, this);
    },

    /**
     * Builds metadata for each tile in the recordlist view
     */
    buildTileMeta: function() {
        var tileDef = this.meta.tileDef || [];
        var tileBodyArr = [];
        var fieldMetadata = app.metadata.getModule(this.module, 'fields');

        _.each(tileDef.panels, function(panel) {
            if (panel.is_header) {
                panel.fields = [fieldMetadata[this.pipelineConfig.tile_header[this.module]]];
            } else {
                var tileBodyField = this.pipelineConfig.tile_body_fields[this.module];
                _.each(tileBodyField, function(tileBody) {
                    tileBodyArr.push(fieldMetadata[tileBody]);
                }, this);
                panel.fields = tileBodyArr;
            }
        }, this);

        this.meta.tileDef = tileDef;
    },

    /**
     * Sets number of results to be displayed for a column in the page
     * @param {integer} resultsNum
     */
    setResultsPerPageColumn: function(resultsNum) {
        resultsNum = resultsNum || this.pipelineConfig.records_per_column;
        var results = parseInt(resultsNum);
        if (!isNaN(results)) {
            this.resultsPerPageColumn = results;
        }
    },

    /**
     * Sets values to be hidden in the tile
     * @param {Array} hiddenValues an array of values to be hidden
     */
    setHiddenHeaderValues: function(hiddenValues) {
        hiddenValues =
            hiddenValues || this.pipelineConfig.hidden_values || [];
        if (_.isEmpty(hiddenValues)) {
            return;
        }

        this.hiddenHeaderValues = hiddenValues;
    },

    /**
     * Builds filter definition for the tiles to be recordlist to be displayed and reloads the data
     * @param {Array} filterDef
     */
    buildFilters: function(filterDef) {
        this.offset = 0;
        this.pipelineFilters = filterDef || [];
        this.loadData();
    },

    /**
     * Checks if the user has access to view and loads data to be displayed on the recordlist
     */
    loadData: function() {
        this.recordsToDisplay = [];
        this.buildTileMeta();
        this.setResultsPerPageColumn();
        this.setHiddenHeaderValues();

        this.getTableHeader();
        if (this.hasAccessToView) {
            this.buildRecordsList();
        }
    },

    /**
     * Gets the table headers for all the columns being displayed on the page
     */
    getTableHeader: function() {
        var headerColors = this.getColumnColors();

        if ((this.context.get('model').get('pipeline_type') !== 'date_closed')) {
            var headerField = this.context.get('model').get('pipeline_type');

            if (!app.acl.hasAccessToModel('read', this.model, headerField)) {
                this.context.trigger('open:config:fired');
                return;
            }

            if (headerField) {
                var moduleFields = app.metadata.getModule(this.module, 'fields');
                var optionsList = moduleFields[headerField].options;

                if (optionsList) {
                    var options = app.lang.getAppListStrings(optionsList) || [];
                }

                if (!_.isEmpty(options)) {
                    var items = _.difference(options, this.hiddenHeaderValues);
                    _.each(options, function(option, key) {
                        var index = _.indexOf(items, option);
                        if (!_.isEmpty(key) && (_.indexOf(this.hiddenHeaderValues, key) === -1)) {
                            this.recordsToDisplay.push({
                                'headerName': option,
                                'headerKey': key,
                                'records': [],
                                'color': !_.isUndefined(headerColors[index]) ? headerColors[index] : ''
                            });
                        }
                    }, this);
                }
            }

            this.headerField = headerField;
        } else {
            var self = this;
            var currDate = app.date(this.startDate);

            this.recordsToDisplay.push({
                'headerName': currDate.format('MMMM YYYY'),
                'headerKey': currDate.format('MMMM YYYY'),
                'records': [],
                'color': headerColors[0]
            });

            for (var i = 1; i < this.monthsToDisplay; i++) {
                currDate.add(1, 'months');
                self.recordsToDisplay.push({
                    'headerName': currDate.format('MMMM YYYY'),
                    'headerKey': currDate.format('MMMM YYYY'),
                    'records': [],
                    'color': headerColors[i]
                });
            }
            this.headerField = 'date_closed';
        }

        this.hasAccessToView = app.acl.hasAccessToModel('read', this.model, this.headerField) ? true : false;
        this._super('render');
    },

    /**
     * Gets the colors for each of the column headers
     * @return {string[]|null|Array} an array of hexcode for the colors
     */
    getColumnColors: function() {
        var columnColor = this.pipelineConfig.header_colors;
        if (_.isEmpty(columnColor) || columnColor == 'null') {
            columnColor = {};
        }

        return columnColor;
    },

    /**
     * Sets offset to 0 before render
     */
    preRender: function() {
        this.offset = 0;
    },

    /**
     * Call the render method from the super class to render the view between the calls to preRender and postRender
     * @inheritdoc
     */
    render: function() {
        this.preRender();
        this._super('render');
        this.postRender();
    },

    /**
     * Calls methods to add draggable action to the tile and bind scroll to the view
     */
    postRender: function() {
        this.buildDraggable();
        this.bindScroll();
    },

    /**
     * Adds a newly created model to the view
     * @param {Object} model for the newly created opportunity
     */
    addModelToCollection: function(model) {
        var collection = this.getColumnCollection(model);

        var literal = this.addTileVisualIndicator([model.toJSON()]);
        model.set('tileVisualIndicator', literal[0].tileVisualIndicator);

        collection.records.add(model, {at: 0});
        this._super('render');
        this.postRender();
    },

    /**
     * Returns the collection of the column to which a new opportunity is being added
     * @param {Object} model for the newly created opportunity
     * @return {*} a collection object
     */
    getColumnCollection: function(model) {
        var contextModel = this.context.get('model');
        if (contextModel && contextModel.get('pipeline_type') === 'date_closed') {
            return _.findWhere(this.recordsToDisplay, {
                headerName: app.date(model.get(this.headerField)).format('MMMM YYYY')
            });
        }

        return _.findWhere(this.recordsToDisplay, {headerName: model.get(this.headerField)});
    },

    /**
     * Shows the loading cell and calls method to fetch all the records to be displayed on the page
     */
    buildRecordsList: function() {
        this.$('#loadingCell').show();
        this.getRecords();
    },

    /**
     * Returns an array of all the filters to be applied on the records
     * @param {Object} column contains details like headerName, headerKey etc. about a column of records
     * @return {Array}
     */
    getFilters: function(column) {
        var filter = [];
        var filterObj = {};

        if (this.context.get('model').get('pipeline_type') !== 'date_closed') {
            filterObj[this.headerField] = {'$equals': column.headerKey};
            filter.push(filterObj);
            _.each(this.pipelineFilters, function(filterDef) {
                filter.push(filterDef);
            }, this);
        } else {
            var startMonth = app.date(column.headerName, 'MMMM YYYY').startOf('month').format('YYYY-MM-DD');
            var endMonth = app.date(column.headerName, 'MMMM YYYY').endOf('month').format('YYYY-MM-DD');
            filterObj[this.headerField] = {'$dateBetween': [startMonth, endMonth]};
            filter.push(filterObj);

            _.each(this.pipelineFilters, function(filterDef) {
                filter.push(filterDef);
            }, this);
        }

        return filter;
    },

    /**
     * Return an array of fields to be fetched and displayed on each tile
     * @return {Array} an array of fields
     */
    getFieldsForFetch: function() {
        var fields =
            _.flatten(
                _.map(_.flatten(_.pluck(this.meta.tileDef.panels, 'fields')), function(field) {
                    if (field === undefined) {
                        return;
                    }
                    return _.union(_.pluck(field.fields, 'name'), _.flatten(field.related_fields), [field.name]);
                })
            );

        fields.push(this.tileVisualIndicatorFields[this.module]);

        var fieldMetadata = app.metadata.getModule(this.module, 'fields');
        if (fieldMetadata) {
            // Filter out all fields that are not actual bean fields
            fields = _.reject(fields, function(name) {
                return _.isUndefined(fieldMetadata[name]);
            });
        }

        return _.uniq(fields);
    },

    /**
     * Uses fields to get the requests for the data to be fetched
     */
    getRecords: function() {
        var fields = this.getFieldsForFetch();
        var requests = this.buildRequests(fields);
        this.fetchData(requests);
    },

    /**
     * Uses fields, filters and other properties to build requests for the data to be fetched
     * @param {Array} fields to be displayed on each tile
     * @return {Array} an array of request objects with dataType, method and url
     */
    buildRequests: function(fields) {
        var requests = {};
        requests.requests = [];

        _.each(this.recordsToDisplay, function(column) {
            var filter = this.getFilters(column);

            var getArgs = {
                filter: filter,
                fields: fields,
                'max_num': this.resultsPerPageColumn,
                'offset': this.offset,
                'order_by': {date_modified: 'DESC'}
            };

            var req = {
                'url': app.api.buildURL(this.module, null, null, getArgs).replace('rest/', ''),
                'method': 'GET',
                'dataType': 'json'
            };

            requests.requests.push(req);
        }, this);

        return requests;
    },

    /**
     * Makes the api call to get the data for the tiles
     * @param {Array} requests an array of request objects
     */
    fetchData: function(requests) {
        var self = this;
        this.moreData = false;
        app.api.call('create', app.api.buildURL(null, 'bulk'), requests, {
            success: function(dataColumns) {
                self.$('#loadingCell').hide();
                _.each(self.recordsToDisplay, function(column, index) {
                    var records = app.data.createBeanCollection(self.module);
                    if (!_.isEmpty(column.records.models)) {
                        records = column.records;
                    }
                    var contents = dataColumns[index].contents;
                    var augmentedContents = self.addTileVisualIndicator(contents.records);
                    records.add(augmentedContents);
                    column.records = records;

                    if (contents.next_offset > -1 && !self.moreData) {
                        self.moreData = true;
                    }
                }, self);

                self._super('render');
                self.postRender();

                if (self.moreData) {
                    self.offset += self.resultsPerPageColumn;
                }
            }
        });
    },

    /**
     * Gives the ability for a tile to be dragged and moved to other columns on the page
     */
    buildDraggable: function() {
        var self = this;

        if (!app.acl.hasAccessToModel('edit', this.model) ||
            !app.acl.hasAccessToModel('edit', this.model, this.headerField)) {
            return;
        }

        this.$('.column').sortable({
            connectWith: '.column',
            handle: '.span12',
            cancel: '.portlet-toggle',
            placeholder: 'portlet-placeholder ui-corner-all',
            receive: _.bind(function(event, ui) {
                var modelId = this.$(ui.item).data('modelid');
                var oldCollection = _.findWhere(this.recordsToDisplay, {
                    headerKey: this.$(ui.sender).data('column-name')
                });
                var newCollection = _.findWhere(this.recordsToDisplay, {
                    headerKey: this.$(ui.item).parent('ul').data('column-name')
                });
                var model = oldCollection.records.get(modelId);

                if (!app.acl.hasAccessToModel('edit', model)) {
                    app.alert.show('not_authorized', {
                        level: 'error',
                        messages: 'Not allowed to perform action "save" on this record',
                        autoClose: true,
                    });

                    this.$(ui.sender).sortable('cancel');
                    return;
                } else {
                    app.alert.show('pipeline-loading', {
                        level: 'process',
                        autoClose: true
                    });

                    this.switchCollection(oldCollection, model, newCollection);
                    this.saveModel(model, ui);
                }
            }, this)
        });

        this.$('.portlet')
            .addClass('ui-widget ui-widget-content ui-helper-clearfix ui-corner-all')
            .find('.span12')
            .addClass('ui-widget-header ui-corner-all');
    },

    /**
     * Gets called when a tile is dragged to another column
     * Removes the tile from the former column collection and adds it to the later one
     * @param {Object} oldCollection Collection object for the column to which the tile previously belonged
     * @param {Object} model model of the tile being moved
     * @param {Object} newCollection Collection object for the column to which the tile is moved
     */
    switchCollection: function(oldCollection, model, newCollection) {
        oldCollection.records.remove(model);
        newCollection.records.add(model, {at: 0});
    },

    /**
     * Gets called to save the model once it switches columns
     * @param {Object} model for the tile to be saved
     * @param {Object} ui an object with the ui details of the tiles like originalPosition, offset, etc.
     */
    saveModel: function(model, ui) {
        var self = this;
        model.set(this.headerField, this.$(ui.item).parent('ul').data('column-name'));
        model.save({}, {
            success: function(model) {
                self._super('render');
                self.postRender();
            },
            error: function(data) {
                self._super('render');
                self.postRender();
            }
        });
    },

    /**
     * Action listener when the delete button is clicked on the tile
     * Asks for user confirmation and delete the tile record from the view
     * @param {Object} model model object of the tile to be deleted
     */
    deleteRecord: function(model) {
        var collection = model.collection;
        var self = this;

        app.alert.show('delete_confirmation', {
            level: 'confirmation',
            messages: self.getDeleteMessages(model).confirmation,
            onConfirm: function() {
                model.destroy({
                    showAlerts: {'process': true, 'success': {messages: self.getDeleteMessages(model).success}},
                    success: function(data) {
                        self._super('render');
                        self.postRender();
                    }
                });
            },
            onCancel: function() {
                return;
            }
        });
    },

    /**
     * Gets called when a tile record is successfully deleted
     * Displays the delete confirmation and success message
     * @param {Object} model model object of the deleted tile
     */
    getDeleteMessages: function(model) {
        var messages = {};
        var name = Handlebars.Utils.escapeExpression(app.utils.getRecordName(model)).trim();
        var context = app.lang.getModuleName(model.module).toLowerCase() + ' ' + name;
        messages.confirmation = app.utils.formatString(app.lang.get('NTC_DELETE_CONFIRMATION_FORMATTED'), [context]);
        messages.success = app.utils.formatString(app.lang.get('NTC_DELETE_SUCCESS'), [context]);
        return messages;
    },

    /**
     * Binds scroll to the recordlist pane
     */
    bindScroll: function() {
        this.$('.my-pipeline-content').bind('scroll', _.bind(this.listScrolled, this));
    },

    /**
     * Listens to the scroll event on the list
     * Checks and displays if more data is present on the page
     * @param event
     */
    listScrolled: function(event) {
        var elem = this.$(event.currentTarget);
        var isAtBottom = (elem[0].scrollHeight - elem.scrollTop()) <= elem.outerHeight();

        if (isAtBottom && this.moreData) {
            this.buildRecordsList();
        }
    },

    /**
     * Adds the visual indicator to all the tiles based on the status or date depending on the modules
     * @param {Array} modelsList a list of all the tile models
     * @return {Array} updated model list with all the indicator values
     */
    addTileVisualIndicator: function(modelsList) {
        var self = this;
        var updatedModel = {};
        var dueDate = app.date();
        var expectedCloseDate = app.date();

        return _.map(modelsList, function(model) {
            switch (model._module) {
                case 'Cases':
                    updatedModel = self.addIndicatorBasedOnStatus(model);
                    break;
                case 'Leads':
                    updatedModel = self.addIndicatorBasedOnStatus(model);
                    break;
                case 'Opportunities':
                    expectedCloseDate = app.date(model.date_closed, 'YYYY-MM-DD');
                    updatedModel = self.addIndicatorBasedOnDate(model, expectedCloseDate);
                    break;
                case 'Tasks':
                    dueDate = app.date.parseZone(model.date_due);
                    updatedModel = self.addIndicatorBasedOnDate(model, dueDate);
                    break;
                default:
                    model.tileVisualIndicator = self.tileVisualIndicator.default;
                    updatedModel = model;
            }

            return updatedModel;
        });
    },

    /**
     * Adds indicator based on the date_closed or date_due
     * @param {Object} model model object for the tile to which the indicator is being added
     * @param {string} date date string related to the model
     * @return {Object} updated model with visual indicator
     */
    addIndicatorBasedOnDate: function(model, date) {
        var now = app.date();
        var aMonthFromNow = app.date().add(1, 'month');

        if (date.isBefore(now)) {
            model.tileVisualIndicator = this.tileVisualIndicator.outOfDate;
        }
        if (date.isAfter(aMonthFromNow)) {
            model.tileVisualIndicator = this.tileVisualIndicator.inFuture;
        }
        if (date.isBetween(now, aMonthFromNow)) {
            model.tileVisualIndicator = this.tileVisualIndicator.nearFuture;
        }

        return model;
    },

    /**
     * Adds indicator based on the Opportunity status
     * @param {Object} model model object for the tile to which the indicator is being added
     * @return {Object} updated model with visual indicator
     */
    addIndicatorBasedOnStatus: function(model) {
        // Group statuses in 3 categories:
        var inFuture = ['New', 'Converted'];
        var outOfDate = ['Dead', 'Closed', 'Rejected', 'Duplicate','Recycled'];
        var nearFuture = ['Assigned', 'In Process', , 'Pending Input', ''];

        if (_.indexOf(outOfDate, model.status) !== -1) {
            model.tileVisualIndicator = this.tileVisualIndicator.outOfDate;
        }
        if (_.indexOf(inFuture, model.status) !== -1) {
            model.tileVisualIndicator = this.tileVisualIndicator.inFuture;
        }
        if (_.indexOf(nearFuture, model.status) !== -1) {
            model.tileVisualIndicator = this.tileVisualIndicator.nearFuture;
        }

        return model;
    },

    /**
     * Listens to the arrow-left button click
     * Updates the start date to 5 months prior
     * Sets offset to 0
     * Reloads the data in the recordlist view
     */
    navigateLeft: function() {
        this.startDate = app.date(this.startDate).subtract(5, 'month').format('YYYY-MM-DD');
        this.offset = 0;
        this.loadData();
    },

    /**
     * Listens to the arrow-right button click
     * Updates the start date to next 5 months
     * Sets offset to 0
     * Reloads the data in the recordlist view
     */
    navigateRight: function() {
        this.startDate = app.date(this.startDate).add(5, 'month').format('YYYY-MM-DD');
        this.offset = 0;
        this.loadData();
    },
})
