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
/**
 * Forecast Sales Rep Worksheet Record List
 *
 * Events
 *
 * forecasts:worksheet:is_dirty
 *  on: this.context.parent || this.context
 *  by: this.dirtyModels 'add' Event
 *  when: a model is added to the dirtModels collection
 *
 * forecasts:worksheet:needs_commit
 *  on: this.context.parent || this.context
 *  by: checkForDraftRows
 *  when: this.collection has a row newer than the last commit date
 *
 * forecasts:worksheet:totals
 *  on: this.context.parent || this.context
 *  by: calculateTotals
 *  when: after it's done calculating totals from a collection change or reset event
 *
 * forecasts:worksheet:saved
 *  on: this.context.parent || this.context
 *  by: saveWorksheet
 *  when: after it's done saving the worksheets to the db for a save draft
 *
 * forecasts:worksheet:commit
 *  on: this.context.parent || this.context
 *  by: forecasts:worksheet:saved event
 *  when: only when the commit button is pressed
 *
 */
({
    /**
     * Who is my parent
     */
    extendsFrom: 'RecordlistView',

    /**
     * Type of worksheet
     */
    worksheetType: 'sales_rep',

    /**
     * Totals Storage
     */
    totals: {},

    /**
     * Totals Colspan
     */
    totals_colspan: 0,

    /**
     * Selected User Storage
     */
    selectedUser: {},

    /**
     * Can we edit this worksheet?
     *
     * defaults to true as it's always the current user that loads first
     */
    canEdit: true,

    /**
     * Active Filters
     */
    filters: [],

    /**
     * Filtered Collection
     */
    filteredCollection: new Backbone.Collection(),

    /**
     * Selected Timeperiod Storage
     */
    selectedTimeperiod: '',

    /**
     * Navigation Message To Display
     */
    navigationMessage: '',

    /**
     * Special Navigation for the Window Refresh
     */
    routeNavigationMessage: '',

    /**
     * Do we actually need to display a navigation message
     */
    displayNavigationMessage: false,

    /**
     * Only check for draft records once
     */
    hasCheckedForDraftRecords: false,

    initialize: function(options) {
        // we need to make a clone of the plugins and then push to the new object. this prevents double plugin
        // registration across ExtendedComponents
        this.plugins = _.clone(this.plugins);
        this.plugins.push('cte-tabbing');
        this.plugins.push('dirty-collection');
        app.view.invokeParent(this, {type: 'view', name: 'recordlist', method: 'initialize', args: [options]});
        // we need to get the flex-list template from the ForecastWorksheets module so it can use the filteredCollection
        // for display
        this.template = app.template.getView('flex-list', this.module);
        this.selectedUser = this.context.get('selectedUser') || this.context.parent.get('selectedUser') || app.user.toJSON();
        this.selectedTimeperiod = this.context.get('selectedTimePeriod') || this.context.parent.get('selectedTimePeriod') || '';
        this.context.set('skipFetch', !(this.selectedUser.showOpps || !this.selectedUser.isManager)); // if user is a manager, skip the initial fetch
        this.filters = this.context.get('selectedRanges') || this.context.parent.get('selectedRanges');
        this.collection.sync = _.bind(this.sync, this);
    },

    _dispose: function() {
        if (!_.isUndefined(this.context.parent) && !_.isNull(this.context.parent)) {
            this.context.parent.off(null, null, this);
        }
        app.routing.offBefore(null, null, this);
        $(window).off("beforeunload." + this.worksheetType);
        app.view.invokeParent(this, {type: 'view', name: 'recordlist', method: '_dispose'});
    },

    bindDataChange: function() {
        // these are handlers that we only want to run when the parent module is forecasts
        if (!_.isUndefined(this.context.parent) && !_.isUndefined(this.context.parent.get('model'))) {
            if (this.context.parent.get('model').module == 'Forecasts') {
                this.context.parent.on('button:export_button:click', function() {
                    if (this.layout.isVisible()) {
                        this.exportCallback();
                    }
                }, this);
                this.before('render', function() {
                    return this.beforeRenderCallback()
                }, this);
                this.on('render', function() {
                    this.renderCallback();
                }, this);

                this.context.parent.on('forecasts:worksheet:totals', function(totals, type) {
                    if (type == this.worksheetType && this.layout.isVisible()) {
                        var tpl = app.template.getView('recordlist.totals', this.module);
                        this.$el.find('tfoot').remove();
                        this.$el.find('tbody').after(tpl(this));
                    }
                }, this);

                this.context.parent.on('change:selectedTimePeriod', function(model, changed) {
                    this.updateSelectedTimeperiod(changed);
                }, this);

                this.context.parent.on('change:selectedUser', function(model, changed) {
                    this.updateSelectedUser(changed)
                }, this);

                this.context.parent.on('button:save_draft_button:click', function() {
                    if (this.layout.isVisible()) {
                        // after we save, trigger the needs_commit event
                        this.context.parent.once('forecasts:worksheet:saved', function() {
                            // clear out the current navigation message
                            this.setNavigationMessage(false, '', '');
                            this.context.parent.trigger('forecasts:worksheet:needs_commit', this.worksheetType);
                        }, this);
                        this.saveWorksheet(true);
                    }
                }, this);

                this.context.parent.on('button:commit_button:click', function() {
                    if (this.layout.isVisible()) {
                        this.context.parent.once('forecasts:worksheet:saved', function() {
                            this.context.parent.trigger('forecasts:worksheet:commit', this.selectedUser, this.worksheetType, this.getCommitTotals())
                        }, this);
                        this.saveWorksheet(false);
                    }
                }, this);

                this.context.parent.on('change:currentForecastCommitDate', function(context, changed) {
                    if (this.layout.isVisible()) {
                        this.checkForDraftRows(changed);
                    }
                }, this);

                this.collection.on('reset', function() {
                    this.setNavigationMessage(false, '', '');
                    this.cleanUpDirtyModels();
                    var ctx = this.context.parent || this.context
                    ctx.trigger('forecasts:worksheet:is_dirty', this.worksheetType, false);
                    this.checkForDraftRows(ctx.get('currentForecastCommitDate'));
                    this.filterCollection();
                }, this);

                this.collection.on('change:commit_stage', function(model) {
                    if (!_.isEmpty(this.filters)  // we have filters
                        && _.indexOf(this.filters, model.get('commit_stage')) === -1 // and the commit_stage is not shown
                        ) {
                        this.filterCollection();
                        if (!this.disposed) {
                            this.render();
                        }
                    } else {
                        // we need to update the data-forecast attribute on the row and the new commit stage is visible
                        this.$el.find('tr[name=' + model.module + '_' + model.id + ']')
                            .attr('data-forecast', model.get('commit_stage'));
                    }
                }, this);

                this.context.parent.on('change:selectedRanges', function(model, changed) {
                    this.filters = changed;
                    this.filterCollection();
                    if (!this.disposed) this.render();
                }, this);

                this.context.parent.on('forecasts:worksheet:committed', function() {
                    if (this.layout.isVisible()) {
                        this.setNavigationMessage(false, '', '');
                        this.cleanUpDirtyModels();
                        var ctx = this.context.parent || this.context;
                        ctx.trigger('forecasts:worksheet:is_dirty', this.worksheetType, false);

                        if (this.selectedUser.isManager && app.metadata.getModule('Forecasts', 'config').show_forecasts_commit_warnings == 1) {
                            this.collection.once('reset', function() {
                                this.setNavigationMessage(true, 'LBL_WORKSHEET_COMMIT_ALERT', 'LBL_WORKSHEET_COMMIT_ALERT');
                            }, this)
                        }
                        this.refreshData();
                    }
                }, this);

                this.context.parent.on('forecasts:worksheet:is_dirty', function(worksheetType, is_dirty) {
                    if (this.worksheetType == worksheetType) {
                        if (is_dirty) {
                            this.setNavigationMessage(true, 'LBL_WORKSHEET_SAVE_CONFIRM', 'LBL_WORKSHEET_SAVE_CONFIRM_UNLOAD');
                        } else {
                            this.setNavigationMessage(false, '', '');
                        }
                    }
                }, this);

                this.context.parent.on('forecasts:worksheet:needs_commit', function(worksheetType) {
                    if (this.worksheetType == worksheetType && app.metadata.getModule('Forecasts', 'config').show_forecasts_commit_warnings == 1) {
                        // only set this if they are a manager
                        this.setNavigationMessage(true, 'LBL_WORKSHEET_COMMIT_CONFIRM', '');
                    }
                }, this);

                app.routing.before('route', function() {
                    var ret = this.showNavigationMessage('router');
                    this.processNavigationMessageReturn(ret);
                }, {}, this);

                $(window).bind("beforeunload." + this.worksheetType, _.bind(function() {
                    var ret = this.showNavigationMessage('window');
                    if (_.isString(ret)) {
                        return ret;
                    }
                }, this));
            }
        }

        this.collection.on('reset change', function() {
            this.calculateTotals();
        }, this);

        if (!_.isUndefined(this.dirtyModels)) {
            this.dirtyModels.on('add', function() {
                if (this.canEdit) {
                    var ctx = this.context.parent || this.context;
                    ctx.trigger('forecasts:worksheet:is_dirty', this.worksheetType, true);
                }
            }, this);
        }

        app.view.invokeParent(this, {type: 'view', name: 'recordlist', method: 'bindDataChange'});
    },

    /**
     * Handle Showing of the Navigation messages if any are applicable
     *
     * @param type
     * @returns {*}
     */
    showNavigationMessage: function(type) {
        if (this.layout.isVisible()) {
            var canEdit = this.dirtyCanEdit || this.canEdit;
            if (canEdit && this.displayNavigationMessage) {
                if (type == 'window') {
                    if (!_.isEmpty(this.routeNavigationMessage)) {
                        return app.lang.get(this.routeNavigationMessage, 'Forecasts');
                    }
                    return false;
                }
                if (this.navigationMessage == 'LBL_WORKSHEET_COMMIT_ALERT') {
                    alert(app.lang.get(this.navigationMessage, 'Forecasts'));
                    return true;
                } else {
                    var ret = confirm(app.lang.get(this.navigationMessage, 'Forecasts').split("<br>"));
                    return {'message': this.navigationMessage, 'run_action': ret};
                }
            }
            return true;
        }

        return true;
    },

    /**
     * Utility to set the Navigation Message and Flag
     *
     * @param display
     * @param reload_label
     * @param route_label
     */
    setNavigationMessage: function(display, reload_label, route_label) {
        this.displayNavigationMessage = display;
        this.navigationMessage = reload_label;
        this.routeNavigationMessage = route_label;
    },

    /**
     * Handle the export callback
     */
    exportCallback: function() {
        var url = 'index.php?module=Forecasts&action=ExportWorksheet';
        url += '&user_id=' + this.selectedUser.id;
        url += '&timeperiod_id=' + this.selectedTimeperiod;

        if (this.canEdit && this.isDirty()) {
            if (confirm(app.lang.get("LBL_WORKSHEET_EXPORT_CONFIRM", "Forecasts"))) {
                this.runExport(url);
            }
        } else {
            this.runExport(url);
        }
    },

    /**
     * runExport
     * triggers the browser to download the exported file
     * @param url URL to the file to download
     */
    runExport: function(url) {
        var dlFrame = $("#forecastsDlFrame");
        //check to see if we got something back
        if (dlFrame.length == 0) {
            //if not, create an element
            dlFrame = $("<iframe>");
            dlFrame.attr("id", "forecastsDlFrame");
            dlFrame.css("display", "none");
            $("body").append(dlFrame);
        }
        dlFrame.attr("src", url);
    },

    /**
     * Callback for the before('render') event
     * @returns {boolean}
     */
    beforeRenderCallback: function() {
        // set the defaults to make it act like a manager so it doesn't actually render till the selected
        // user is updated
        var showOpps = (_.isUndefined(this.selectedUser.showOpps)) ? false : this.selectedUser.showOpps,
            isManager = (_.isUndefined(this.selectedUser.isManager)) ? true : this.selectedUser.isManager;

        if (!(showOpps || !isManager) && this.layout.isVisible()) {
            this.layout.hide();
        } else if ((showOpps || !isManager) && !this.layout.isVisible()) {
            this.layout.show();
        }

        // empty out the left columns
        this.leftColumns = [];

        return (showOpps || !isManager);
    },

    /**
     * Callback for the on('render') event
     */
    renderCallback: function() {
        var user = this.selectedUser || this.context.parent.get('selectedUser') || app.user.toJSON()
        if (user.showOpps || !user.isManager) {
            if (!this.layout.isVisible()) {
                this.layout.show();
            }

            if (this.filteredCollection.length == 0) {
                var tpl = app.template.getView('recordlist.noresults', this.module);
                this.$el.find('tbody').html(tpl(this));
            }

            // insert the footer
            if (!_.isEmpty(this.totals) && this.layout.isVisible()) {
                // recalculate totals colspan
                this.calculateTotalsColspan();
                var tpl = app.template.getView('recordlist.totals', this.module);
                this.$el.find('tbody').after(tpl(this));
            }

            // figure out if any of the row actions need to be disabled
            this.setRowActionButtonStates();
            this.adjustCurrencyColumnWidths();
        } else {
            if (this.layout.isVisible()) {
                this.layout.hide();
            }
        }
    },

    /**
     * Code to handle if the selected user changes
     *
     * @param changed
     */
    updateSelectedUser: function(changed) {
        var doFetch = false;
        if (this.selectedUser.id != changed.id) {
            // user changed. make sure it's not a manager view before we say fetch or not
            doFetch = (changed.showOpps || !changed.isManager);
        }
        // if we are already not going to fetch, check to see if the new user is showingOpps or is not
        // a manager, then we want to fetch
        if (!doFetch && (changed.showOpps || !changed.isManager)) {
            doFetch = true;
        }

        if (this.displayNavigationMessage) {
            // save the user just in case
            this.dirtyUser = this.selectedUser;
            this.dirtyCanEdit = this.canEdit;
        }

        this.selectedUser = changed;

        // Set the flag for use in other places around this controller to suppress stuff if we can't edit
        this.canEdit = (this.selectedUser.id == app.user.get('id'));
        this.hasCheckedForDraftRecords = false;

        if (doFetch) {
            this.refreshData();
        } else {
            if ((!this.selectedUser.showOpps && this.selectedUser.isManager) && this.layout.isVisible()) {
                if (this.displayNavigationMessage && this.dirtyUser.id == this.selectedUser.id) {
                    this.showNavigationMessage('rep_to_manager');
                } else if (this.displayNavigationMessage) {
                    this.showNavigationMessage('user_switch');
                }
                this.cleanUpDirtyModels();
                // we need to hide
                this.layout.hide();
            }
        }
    },

    updateSelectedTimeperiod: function(changed) {
        if (this.displayNavigationMessage) {
            // save the time period just in case
            this.dirtyTimeperiod = this.selectedTimeperiod;
        }
        this.selectedTimeperiod = changed;
        this.hasCheckedForDraftRecords = false;
        if (this.layout.isVisible()) {
            this.refreshData();
        }
    },

    /**
     * Check to make sure that if there are dirty rows, then trigger the needs_commit event to enable
     * the buttons
     *
     * @triggers forecasts:worksheet:needs_commit
     * @param lastCommitDate
     */
    checkForDraftRows: function(lastCommitDate) {
        if (this.layout.isVisible() && this.canEdit && this.hasCheckedForDraftRecords === false
            && !_.isEmpty(this.collection.models)) {

            this.hasCheckedForDraftRecords = true;
            if(_.isUndefined(lastCommitDate)) {
                // we have rows but no commit, enable the commit button
                this.context.parent.trigger('forecasts:worksheet:needs_commit', this.worksheetType);
            } else {
                // check to see if anything in the collection is a draft, if it is, then send an event
                // to notify the commit button to enable
                this.collection.find(function(item) {
                    if (item.get('date_modified') > lastCommitDate) {
                        this.context.parent.trigger('forecasts:worksheet:needs_commit', this.worksheetType);
                        return true;
                    }
                    return false;
                }, this);
            }
        }
    },

    /**
     * Handles setting the proper state for the Preview in the row-actions
     */
    setRowActionButtonStates: function() {
        _.each(this.fields, function(field) {
            if (field.def.event === 'list:preview:fire') {
                // we have a field that needs to be disabled, so disable it!
                field.setDisabled((field.model.get('parent_deleted') == "1"));
                field.render();
            }
        });
    },

    /**
     * Filter the Collection so we only show what the filter says we should show
     */
    filterCollection: function() {
        this.filteredCollection.reset();
        if (_.isEmpty(this.filters)) {
            this.filteredCollection.add(this.collection.models);
        } else {
            this.collection.each(function(model) {
                if (_.indexOf(this.filters, model.get('commit_stage')) !== -1) {
                    this.filteredCollection.add(model);
                }
            }, this);
        }
    },

    /**
     * Save the worksheet to the database
     *
     * @triggers forecasts:worksheet:saved
     * @return {Number}
     */
    saveWorksheet: function(isDraft) {
        // only run the save when the worksheet is visible and it has dirty records
        var totalToSave = 0;
        if (this.layout.isVisible()) {
            var saveCount = 0,
                ctx = this.context.parent || this.context;

            if (this.isDirty()) {
                totalToSave = this.dirtyModels.length;
                this.dirtyModels.each(function(model) {
                    //set properties on model to aid in save
                    model.set({
                        "draft": (isDraft && isDraft == true) ? 1 : 0,
                        "timeperiod_id": this.dirtyTimeperiod || this.selectedTimeperiod,
                        "current_user": this.dirtyUser.id || this.selectedUser.id
                    }, {silent: true});

                    // set the correct module on the model since sidecar doesn't support sub-beans yet
                    model.save({}, {success: _.bind(function() {
                        saveCount++;
                        //if this is the last save, go ahead and trigger the callback;
                        if (totalToSave === saveCount) {
                            // we only want to show this when the draft is being saved
                            if (isDraft) {
                                app.alert.show('success', {
                                    level: 'success',
                                    autoClose: true,
                                    title: app.lang.get("LBL_FORECASTS_WIZARD_SUCCESS_TITLE", "Forecasts") + ":",
                                    messages: [app.lang.get("LBL_FORECASTS_WORKSHEET_SAVE_DRAFT_SUCCESS", "Forecasts")]
                                });
                            }
                            ctx.trigger('forecasts:worksheet:saved', totalToSave, this.worksheetType, isDraft);
                        }
                    }, this), silent: true, alerts: { 'success': false }});
                }, this);

                this.cleanUpDirtyModels();
            } else {
                // we only want to show this when the draft is being saved
                if (isDraft) {
                    app.alert.show('success', {
                        level: 'success',
                        autoClose: true,
                        title: app.lang.get("LBL_FORECASTS_WIZARD_SUCCESS_TITLE", "Forecasts") + ":",
                        messages: [app.lang.get("LBL_FORECASTS_WORKSHEET_SAVE_DRAFT_SUCCESS", "Forecasts")]
                    });
                }
                ctx.trigger('forecasts:worksheet:saved', totalToSave, this.worksheetType, isDraft);
            }
        }

        return totalToSave
    },

    /**
     * Calculate the totals colspan for the visible fields
     */
    calculateTotalsColspan: function() {
        this.totals_colspan = this._fields.visible.length;
        _.each(this._fields.visible, function(field) {
            if(field.type === 'currency') {
                this.totals_colspan--;
            }
        }, this);
    },

    /**
     * Calculate the totals for the visible fields
     */
    calculateTotals: function() {
        var fields = _.filter(this._fields.visible, function(field) {
                return field.type === 'currency';
            }),
            fieldNames = [];

        this.calculateTotalsColspan();
        _.each(fields, function(field) {
            fieldNames.push(field.name);
            this.totals[field.name] = 0;
            this.totals["overall_" + field.name] = 0;
            this.totals[field.name + "_display"] = true;
        }, this);

        // add up all the currency fields
        if (this.collection.length == 0) {
            // no items, just bail and set back the 0 totals
            return;
        }

        // set up commit_stages that should be processed in included total
        var forecast_ranges = app.metadata.getModule('Forecasts', 'config').forecast_ranges,
            commit_stages_in_included_total = [],
            ranges;

        if (forecast_ranges == 'show_custom_buckets') {
            ranges = app.metadata.getModule('Forecasts', 'config')[forecast_ranges + '_ranges'];
            _.each(ranges, function(value, key) {
                if (!_.isUndefined(value.in_included_total) && value.in_included_total) {
                    commit_stages_in_included_total.push(key);
                }
            })
        } else {
            commit_stages_in_included_total.push('include');
        }

        this.collection.each(function(model) {
            _.each(fieldNames, function(field) {
                // convert the value to base
                var val = model.get(field);
                if (_.isUndefined(val) || _.isNaN(val)) {
                    return;
                }
                val = app.currency.convertWithRate(val, model.get('base_rate'));
                this.totals["overall_" + field] = app.math.add(this.totals["overall_" + field], val);
                if (_.include(commit_stages_in_included_total, model.get('commit_stage'))) {
                    this.totals[field] = app.math.add(this.totals[field], val);
                }
            }, this)
        }, this);

        // fire an event on the parent context
        if (this.isVisible()) {
            var ctx = this.context.parent || this.context;
            ctx.trigger('forecasts:worksheet:totals', this.totals, this.worksheetType);
        }
    },

    /**
     * Custom Method to handle the refreshing of the worksheet Data
     */
    refreshData: function() {
        var ret = this.showNavigationMessage('forecast');

        if (this.processNavigationMessageReturn(ret)) {
            this.collection.fetch();
        }
    },

    /**
     * Utility to process the return from the Navigation Message
     *
     * @param message_result
     * @returns {boolean}
     */
    processNavigationMessageReturn: function(message_result) {
        if (_.isObject(message_result) && message_result.run_action === true) {
            if (message_result.message == 'LBL_WORKSHEET_SAVE_CONFIRM') {
                this.context.parent.once('forecasts:worksheet:saved', function() {
                    this.collection.fetch();
                }, this);
                this.saveWorksheet(true);
            } else if (message_result.message == 'LBL_WORKSHEET_COMMIT_CONFIRM') {
                // once the commit happens, it will trigger a new fetch.
                this.context.parent.trigger('forecasts:worksheet:commit', this.dirtyUser || this.selectedUser, this.worksheetType, this.getCommitTotals());
            }

            return false
        }

        return true;
    },

    /**
     * Custom Sync Method
     *
     * @param method
     * @param model
     * @param options
     */
    sync: function(method, model, options) {
        var callbacks,
            url;

        options = options || {};
        options.params = options.params || {};

        if (!_.isUndefined(this.selectedUser.id)) {
            options.params.user_id = this.selectedUser.id;
        }
        if (!_.isEmpty(this.selectedTimeperiod)) {
            options.params.timeperiod_id = this.selectedTimeperiod;
        }

        options.limit = 1000;
        options = app.data.parseOptionsForSync(method, model, options);

        // Since parent_name breaks the XHR call in the order by, just use the name field instead
        // they are the same anyways.
        if (!_.isUndefined(options.params.order_by) && options.params.order_by.indexOf('parent_name') === 0) {
            options.params.order_by = options.params.order_by.replace('parent_', '');
        }

        // custom success handler
        options.success = _.bind(function(model, data, options) {
            this.collection.reset(data);
        }, this);

        callbacks = app.data.getSyncCallbacks(method, model, options);
        this.trigger("data:sync:start", method, model, options);

        url = app.api.buildURL("ForecastWorksheets", null, null, options.params);
        app.api.call("read", url, null, callbacks);
    },

    /**
     * We have to overwrite this method completely, since there is currently no way to completely disable
     * a field from being displayed
     *
     * @returns {{default: Array, available: Array, visible: Array, options: Array}}
     */
    parseFields: function() {
        var catalog = app.view.invokeParent(this, {
            type: 'view',
            name: 'recordlist',
            method: 'parseFields'
        });
        _.each(catalog, function (group, i) {
            catalog[i] = _.filter(group, function (fieldMeta) {
                return app.utils.getColumnVisFromKeyMap(fieldMeta.name, 'forecastsWorksheetManager');
            });
        });
        return catalog;
    },

    /**
     * Get the totals that need to be committed
     *
     * @returns {{amount: number, best_case: number, worst_case: number, overall_amount: number, overall_best: number, overall_worst: number, timeperiod_id: (*|bindDataChange.selectedTimeperiod), lost_count: number, lost_amount: number, won_count: number, won_amount: number, included_opp_count: number, total_opp_count: Number, closed_count: number, closed_amount: number}}
     */
    getCommitTotals: function() {
        if (this.layout.isVisible()) {
            var includedAmount = 0,
                includedBest = 0,
                includedWorst = 0,
                overallAmount = 0,
                overallBest = 0,
                overallWorst = 0,
                includedCount = 0,
                lostCount = 0,
                lostAmount = 0,
                wonCount = 0,
                wonAmount = 0,
                includedClosedCount = 0,
                includedClosedAmount = 0;

            //Get the excluded_sales_stage property.  Default to empty array if not set
            var sales_stage_won_setting = app.metadata.getModule('Forecasts', 'config').sales_stage_won || [];
            var sales_stage_lost_setting = app.metadata.getModule('Forecasts', 'config').sales_stage_lost || [];

            // set up commit_stages that should be processed in included total
            var forecast_ranges = app.metadata.getModule('Forecasts', 'config').forecast_ranges,
                commit_stages_in_included_total = [],
                ranges;

            if (forecast_ranges == 'show_custom_buckets') {
                ranges = app.metadata.getModule('Forecasts', 'config')[forecast_ranges + '_ranges'];
                _.each(ranges, function(value, key) {
                    if (!_.isUndefined(value.in_included_total) && value.in_included_total) {
                        commit_stages_in_included_total.push(key);
                    }
                })
            } else {
                commit_stages_in_included_total.push('include');
            }

            this.collection.each(function(model) {
                var won = _.include(sales_stage_won_setting, model.get('sales_stage')),
                    lost = _.include(sales_stage_lost_setting, model.get('sales_stage')),
                    amount = parseFloat(model.get('likely_case')),
                    commit_stage = model.get('commit_stage'),
                    best = parseFloat(model.get('best_case')),
                    base_rate = parseFloat(model.get('base_rate')),
                    worst = parseFloat(model.get('worst_case')),
                    worst_base = app.currency.convertWithRate(worst, base_rate),
                    amount_base = app.currency.convertWithRate(amount, base_rate),
                    best_base = app.currency.convertWithRate(best, base_rate);

                if (won) {
                    wonAmount = app.math.add(wonAmount, amount_base);
                    wonCount++;
                } else if (lost) {
                    lostAmount = app.math.add(lostAmount, amount_base);
                    lostCount++;
                }
                if (_.include(commit_stages_in_included_total, commit_stage)) {
                    includedAmount += amount_base;
                    includedBest += best_base;
                    includedWorst += worst_base;
                    includedCount++;
                    if (won || lost) {
                        includedClosedCount++;
                        includedClosedAmount = app.math.add(amount_base, includedClosedAmount);
                    }
                }

                overallAmount += amount_base;
                overallBest += best_base;
                overallWorst += worst_base;
            }, this);

            return {
                'amount': includedAmount,
                'best_case': includedBest,
                'worst_case': includedWorst,
                'overall_amount': overallAmount,
                'overall_best': overallBest,
                'overall_worst': overallWorst,
                'timeperiod_id': this.dirtyTimeperiod || this.selectedTimeperiod,
                'lost_count': lostCount,
                'lost_amount': lostAmount,
                'won_count': wonCount,
                'won_amount': wonAmount,
                'included_opp_count': includedCount,
                'total_opp_count': this.collection.length,
                'closed_count': includedClosedCount,
                'closed_amount': includedClosedAmount

            };
        }
    },

    /**
     * We need to overwrite so we pass in the filterd list
     */
    addPreviewEvents: function() {
        //When clicking on eye icon, we need to trigger preview:render with model&collection
        this.context.on("list:preview:fire", function(model) {
            var previewCollection = new Backbone.Collection();
            this.filteredCollection.each(function(model) {
                if (model.get('parent_deleted') !== "1") {
                    previewCollection.add(model);
                }
            }, this);

            app.events.trigger("preview:render", model, previewCollection, true);
        }, this);


        //When switching to next/previous record from the preview panel, we need to update the highlighted row
        app.events.on("list:preview:decorate", this.decorateRow, this);
        if (this.layout) {
            this.layout.on("list:sort:fire", function() {
                //When sorting the list view, we need to close the preview panel
                app.events.trigger("preview:close");
            }, this);
        }
    },

    /**
     * set dynamic widths on currency columns showing original currency
     */
    adjustCurrencyColumnWidths: function() {
        // empty collection, don't worry about this
        if (this.collection.length == 0) {
            return;
        }

        _.each(this._fields.visible, function(field) {
            // only adjust the currency fields
            if (field.type === 'currency') {
                var converted = this.$el.find('span[data-name^="' + field.name + '"] .converted'),
                    original = this.$el.find('span[data-name^="' + field.name + '"] label.original'),
                    widths = converted.map(function() {
                        return $(this).width();
                    }).get(),
                    labelWidths = original.map(function() {
                        return $(this).width();
                    }).get();

                converted.width(_.max(widths));
                original.width(_.max(labelWidths));

                var parentTds = this.$el.find('span[data-name^="' + field.name + '"]'),
                    parentWidth = _.max(parentTds.map(function() {
                        return $(this).outerWidth();
                    }).get()),
                    finalTDWidth = parentWidth+20;
                this.$el.find('th[data-fieldname^="' + field.name + '"]')
                    .width(finalTDWidth)
                    .css('maxWidth', finalTDWidth + 'px')
                    .css('minWidth', finalTDWidth + 'px');
            }
        }, this);
    }
})
