/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/**
 * View that displays header for current app
 *
 * @class View.Views.WorksheetView
 * @alias SUGAR.App.layout.WorksheetView
 * @extends View.View
 *
 *
 * Events Triggered
 *
 * forecasts:commitButtons:triggerCommit
 *      on: context
 *      by: safeFetch()
 *      when: user clicks ok on confirm dialog that they want to commit data
 *
 * forecasts:worksheet:rendered
 *      on: context
 *      by: _render
 *      when: the worksheet is done rendering
 *
 * forecasts:worksheet:filtered
 *      on: context
 *      by: updateWorksheetBySelectedRanges()
 *      when: dataTable is finished filtering itself
 *
 * forecasts:worksheet:filtered
 *      on: context
 *      by: updateWorksheetBySelectedRanges()
 *      when: dataTable is finished filtering itself and has destroyed and redrawn itself
 *
 * forecasts:worksheet:saved
 *      on: context
 *      by: saveWorksheet()
 *      when: saving the worksheet.
 *
 * forecasts:worksheet:dirty
 *      on: context
 *      by: change:worksheet
 *      when: the worksheet is changed.
 *
 * forecasts:change:worksheetRows
 *      on: context
 *      after: this.updateWorksheetBySelectedRanges() is ran in the change:selectedRanges event handler
 *
 * forecasts:worksheet:reloadCommitButton
 *      on: context
 *      by: safeFetch()
 */
({

    url: 'rest/v10/ForecastWorksheets',
    show: false,
    selectedUser: {},
    timePeriod: '',
    gTable: '',
    gTableDefs: {},
    aaSorting: [],
    isEditableWorksheet: false,
    columnDefs: [],
    mgrNeedsCommitted: false,
    commitButtonEnabled: false,
    commitFromSafeFetch: false,
    // boolean to denote that a fetch is currently in progress
    fetchInProgress: false,

    /**
     * A Collection to keep track of all the dirty models
     */
    dirtyModels: new Backbone.Collection(),

    /**
     * If the timeperiod is changed and we have dirtyModels, keep the previous one to use if they save the models
     */
    dirtyTimeperiod: '',

    /**
     * If the timeperiod is changed and we have dirtyModels, keep the previous one to use if they save the models
     */
    dirtyUser: '',

    events: {
        'click a["rel=inspector"]>i': 'inspector'
    },

    inspector: function(evt) {
        var nTr = $(evt.target).parents('tr'),
            uid = $(evt.target).data('uid'),
            moduleType = $(evt.target).data('type'),
            totalRows = $(evt.target).parents('table').find('tr.odd, tr.even'),
            selIndex = -1;
        _.each(totalRows, function(element, index) {
            if(nTr[0] == element) {
                selIndex = index;
            }
        });

        // begin building params to pass to modal
        var params = {
            selectedIndex: selIndex,
            dataset: totalRows,
            title: 'Preview',
            context: {
                module: moduleType,
                model: app.data.createBean(moduleType, {id: uid}),
                meta: app.metadata.getModule(moduleType).views.forecastInspector.meta
            },
            components: [
                { view: 'forecastInspector' }
            ]
        };

        this.layout.getComponent('inspector').showInspector(params);
    },

    /**
     * Initialize the View
     *
     * @constructor
     * @param {Object} options
     */
    initialize: function(options) {

        this.gTableDefs = {
            "bAutoWidth": false,
            "aoColumnDefs": this.columnDefs,
            "aaSorting": this.aaSorting,
            "bInfo": false,
            "bPaginate": false
        };

        // we need a custom model and collection in this view, so just create them on the options
        // before we call the parent method
        options.model = app.data.createBean('Forecasts');
        options.collection = app.data.createBeanCollection('Forecasts');

        app.view.View.prototype.initialize.call(this, options);

        //set up base selected user
        this.selectedUser = {id: app.user.get('id'), "isManager": app.user.get('isManager'), "showOpps": false};

        this.collection.sync = _.bind(function(method, model, options) {
            options.success = _.bind(function(resp, status, xhr) {
                this.collection.reset(resp.records);
            }, this);
            // we need to force a post, so get the url object and put it in
            var url = this.createURL();
            app.api.call("create", url.url, url.filters, options);
        }, this);

        // INIT tree with logged-in user       
        this.timePeriod = app.defaultSelections.timeperiod_id.id;
        this.updateWorksheetBySelectedRanges(app.defaultSelections.ranges);
        this.loadData();
    },

    /**
     * override dispose function to remove custom listener off the window
     * @private
     */
    _dispose: function() {
        $(window).off("beforeUnload");
        app.view.Component.prototype._dispose.call(this);
    },

    /**
     * Overwrite Load Data for now
     */
    loadData: function() {
        // only load the data if the sheets is visible.
        if(this.isVisible()) {
            this.safeFetch(true);
        }
    },

    /**
     *
     * @return {Object}
     */
    createURL: function() {
        // we need to default the type to products
        var args_filter = [
            {
                "type": app.metadata.getModule('Forecasts', 'config').forecast_by
            }
        ];
        if(this.timePeriod) {
            args_filter.push({"timeperiod_id": this.timePeriod});
        }

        if(this.selectedUser) {
            args_filter.push({"assigned_user_id": this.selectedUser.id});
        }

        var url = app.api.buildURL('ForecastWorksheets', 'filter');

        return {"url": url, "filters": {"filter": args_filter}};
    },

    /**
     * Renders a field.
     *
     * This method sets field's view element and invokes render on the given field.  If clickToEdit is set to true
     * in metadata, it will also render it as clickToEditable.
     * @param {View.Field} field The field to render
     * @protected
     */
    _renderField: function(field) {
        this._createFieldColumnDef(field.def);
        app.view.View.prototype._renderField.call(this, field);
    },

    /**
     * Adding the field to the ColumnDef for the DataTables Plugin.  If the field is already in the array it will not be
     * added again.
     *
     * @param field {Object}        Field Def Information
     * @private
     */
    _createFieldColumnDef: function(field) {
        // make sure we don't already have the field in the list.
        if(!_.isEmpty(this.columnDefs) && _.find(this.columnDefs, _.bind(function(obj) {
            return obj.sName == this.name
        }, field))) {
            // we have the field in the columnDefs, just ignore it now.
            return;
        }
        if(field.enabled) {
            var fieldDef = {
                "sName": field.name,
                "aTargets": [ this.columnDefs.length ],
                "bVisible": this.checkConfigForColumnVisibility(field.name)
            };

            if(!_.isUndefined(field.type)) {
                //Apply sorting for the worksheet
                switch(field.type) {
                    case "commitStage":
                    case "enum":
                    case "bool":
                        // disable sorting for non-numerical fields
                        fieldDef["bSortable"] = false;
                        break;
                    case "int":
                    case "editableInt":
                    case "currency":
                    case "editableCurrency":
                        fieldDef["sSortDataType"] = "dom-number";
                        fieldDef["sType"] = "numeric";
                        break;
                }
                // apply class and width
                switch(field.name) {
                    case "likely_case":
                        fieldDef["sClass"] = "number likely";
                        fieldDef["sWidth"] = "22%";
                        break;
                    case "best_case":
                        fieldDef["sClass"] = "number best";
                        fieldDef["sWidth"] = "22%";
                        break;
                    case "worst_case":
                        fieldDef["sClass"] = "number worst";
                        fieldDef["sWidth"] = "22%";
                        break;
                    case "probability":
                        fieldDef["sClass"] = "number";
                        break;
                }
            }

            this.columnDefs.push(fieldDef);
        }
    },

    /**
     * Clean up any left over bound data to our context
     */
    unbindData: function() {
        if(this.context) {
            this.context.off(null, null, this)
        }
        //if we don't unbind this, then recycle of this view if a change in rendering occurs will result in multiple bound events to possibly out of date functions
        $(window).unbind("beforeunload");
        app.view.View.prototype.unbindData.call(this);
    },

    /**
     * Is this worksheet dirty or not?
     * @return {boolean}
     */
    isDirty: function() {
        return (this.dirtyModels.length > 0);
    },

    /**
     *
     * @triggers forecasts:worksheet:saved
     * @return {Number}
     */
    saveWorksheet: function(isDraft) {
        // only run the save when the worksheet is visible and it has dirty records
        var totalToSave = 0;
        if(this.isVisible()) {
            var saveCount = 0;

            totalToSave = this.dirtyModels.length;

            if(this.isDirty()) {
                this.dirtyModels.each(function(model) {
                    //set properties on model to aid in save
                    model.set({
                        "draft": (isDraft && isDraft == true) ? 1 : 0,
                        "timeperiod_id": this.dirtyTimeperiod || this.timePeriod,
                        "current_user": this.dirtyUser.id || this.selectedUser.id
                    }, {silent: true});

                    // set the correct module on the model since sidecar doesn't support sub-beans yet
                    model.module = "ForecastWorksheets";
                    model.save({}, {success: _.bind(function() {
                        saveCount++;
                        //if this is the last save, go ahead and trigger the callback;
                        if(totalToSave === saveCount) {
                            // we only want to show this when the draft is being saved
                            if(isDraft) {
                                app.alert.show('success', {
                                    level: 'success',
                                    autoClose: true,
                                    title: app.lang.get("LBL_FORECASTS_WIZARD_SUCCESS_TITLE", "Forecasts") + ":",
                                    messages: [app.lang.get("LBL_FORECASTS_WORKSHEET_SAVE_DRAFT_SUCCESS", "Forecasts")]
                                });
                            }
                            this.context.trigger('forecasts:worksheet:saved', totalToSave, 'rep_worksheet', isDraft);
                        }
                    }, this), silent: true, alerts: { 'success': false }});
                }, this);

                this.cleanUpDirtyModels();
            } else {
                // we only want to show this when the draft is being saved
                if(isDraft) {
                    app.alert.show('success', {
                        level: 'success',
                        autoClose: true,
                        title: app.lang.get("LBL_FORECASTS_WIZARD_SUCCESS_TITLE", "Forecasts") + ":",
                        messages: [app.lang.get("LBL_FORECASTS_WORKSHEET_SAVE_DRAFT_SUCCESS", "Forecasts")]
                    });
                }
                this.context.trigger('forecasts:worksheet:saved', totalToSave, 'rep_worksheet', isDraft);
            }
        }

        return totalToSave
    },

    /**
     * Clean Up the Dirty Modules Collection and dirtyVariables
     */
    cleanUpDirtyModels: function() {
        // clean up the dirty records and variables
        this.dirtyModels.reset();
        this.dirtyTimeperiod = '';
        this.dirtyUser = '';
    },

    /**
     *
     * @param {Object} params
     */
    bindDataChange: function(params) {
        if(this.collection) {
            this.collection.on("reset", function() {
                this.cleanUpDirtyModels();
                if (!this.disposed) {
                    this.render();
                }
            }, this);

            this.collection.on("change", function(model) {
                if(_.include(_.keys(model.changed), 'commit_stage')) {
                    this.gTable.fnDestroy();
                    this.gTable = this.$('.worksheetTable').dataTable(this.gTableDefs);
                }
                // The Model has changed via CTE. save it in the isDirty
                this.dirtyModels.add(model);
                this.context.trigger('forecasts:worksheet:dirty', model, model.changed);

                this.calculateTotals();
            }, this);
        }

        // listening for updates to context for selectedUser:change
        if(this.context) {
            this.context.on("change:selectedUser",
                function(context, selectedUser) {
                    this.updateWorksheetBySelectedUser(selectedUser);
                }, this);
            this.context.on("change:selectedTimePeriod",
                function(context, timePeriod) {
                    this.updateWorksheetBySelectedTimePeriod(timePeriod);
                }, this);
            this.context.on("change:selectedRanges",
                function(context, ranges) {
                    this.updateWorksheetBySelectedRanges(ranges);
                    this.context.trigger('forecasts:change:worksheetRows', this.$el.find('tr.odd, tr.even'));
                }, this);
            this.context.on("forecasts:committed:saved", function() {
                if(this.isVisible()) {
                    this.safeFetch();
                    if(!this.commitFromSafeFetch) {
                        this.mgrNeedsCommitted = true;
                    } else {
                        this.commitFromSafeFetch = false;
                    }

                }
            }, this);

            this.context.on('forecasts:committed:saved', function() {
                if(this.isVisible()) {
                    // display a success message
                    app.alert.show('success', {
                        level: 'success',
                        autoClose: true,
                        title: app.lang.get("LBL_FORECASTS_WIZARD_SUCCESS_TITLE", "Forecasts") + ":",
                        messages: [app.lang.get("LBL_FORECASTS_WORKSHEET_COMMIT_SUCCESS", "Forecasts")]
                    });
                }
            }, this);

            this.context.on("forecasts:commitButtons:enabled", function() {
                if(_.isEqual(app.user.get('id'), this.selectedUser.id)) {
                    this.commitButtonEnabled = true;
                }
            }, this);

            this.context.on("forecasts:commitButtons:disabled", function() {
                this.commitButtonEnabled = false;
            }, this);

            this.context.on('forecasts:worksheet:saveWorksheet', function(isDraft) {
                this.saveWorksheet(isDraft);
            }, this);
            
            this.context.on("forecasts:worksheet:saved", function(totalToSave, type, isDraft){
                if (isDraft) {
                    //normal fetch instead of safefetch.  We've already saved, we just want to reload the worksheet.
                    this.collection.fetch();
                }
            }, this);

            this.context.on('forecasts:tabKeyPressed', function(isShift, field) {
                this.editableFieldNavigate(isShift, field);
            }, this);

            $(window).bind("beforeunload", _.bind(function() {
                //if the record is dirty, warn the user.
                if(this.isDirty()) {
                    return app.lang.get("LBL_WORKSHEET_SAVE_CONFIRM_UNLOAD", "Forecasts");
                } else if(!_.isUndefined(this.context) && (this.context.get("currentWorksheet") == "worksheet") && this.selectedUser.isManager && app.metadata.getModule('Forecasts', 'config').show_forecasts_commit_warnings) {
                    //special manager cases for messages
                    /*
                     * If the manager has a draft version saved, but hasn't committed that yet, they need to be shown a dialog that
                     * lets them know, and gives them the option of committing before the page reloads. This happens if the commit button
                     * is enabled and they are on the rep worksheet.
                     */
                    if(this.commitButtonEnabled) {
                        var msg = app.lang.get("LBL_WORKSHEET_COMMIT_CONFIRM", "Forecasts").split("<br>");
                        //show dialog
                        return msg[0];
                    }
                    else if(this.mgrNeedsCommitted) {
                        return app.lang.get("LBL_WORKSHEET_COMMIT_ALERT", "Forecasts");
                    }
                }
            }, this));
        }
    },

    /**
     * Navigation to another editable field
     *
     * @param isShift
     * @param field
     */
    editableFieldNavigate: function(isShift, field) {
        if(!this.isVisible()) {
            return -1;
        }
        // tab key was pressed, we cycle to the next/prev field
        // get list of editable fields
        var editableFields = this.$el.find('span.editable,span.edit'),
            currentFieldIdx = editableFields.index(field.$el.find('span.editable,span.edit')),
            targetFieldIdx = 0;
        if(!isShift) {
            if(currentFieldIdx != (editableFields.length - 1)) {
                // go to next field
                targetFieldIdx = currentFieldIdx + 1;
            } // otherwise go to first index which is default
        } else {
            if(currentFieldIdx == 0) {
                // first field, go to last
                targetFieldIdx = editableFields.length - 1;
            } else {
                // go to prev field
                targetFieldIdx = currentFieldIdx - 1;
            }
        }
        editableFields[targetFieldIdx].click();
        return targetFieldIdx;
    },

    /**
     * Checks if colKey is in the table config keymaps on the context
     *
     * @param colKey {String} the column sName to check in the keymap
     * @return {*} returns null if not found in the keymap, returns true/false if it did find it
     */
    checkConfigForColumnVisibility: function(colKey) {
        return app.utils.getColumnVisFromKeyMap(colKey, this.name);
    },

    /**
     * This function checks to see if the worksheet is dirty, and gives the user the option
     * of saving their work before the sheet is fetched.
     *
     * @param fetch {boolean} Tells the function to go ahead and fetch if true, or runs dirty checks (saving) w/o fetching if false
     */
    safeFetch: function(fetch) {
        //fetch currently already in progress, no need to duplicate
        if(this.fetchInProgress) {
            return;
        }
        //mark that a fetch is in process so no duplicate fetches begin
        this.fetchInProgress = true;
        if(_.isUndefined(fetch)) {
            fetch = true;
        }

        /*
         * First we need to see if the collection is dirty. This is marked if any of the models
         * is marked as dirty. This will show the "unsaved changes" dialog
         */
        if(this.isDirty()) {
            //unsaved changes, ask if you want to save.
            if(confirm(app.lang.get("LBL_WORKSHEET_SAVE_CONFIRM", "Forecasts"))) {
                this.context.set({reloadCommitButton: true});

                this.context.once('forecasts:worksheet:saved', function() {
                    this.collection.fetch();
                }, this);
                this.saveWorksheet()
            } else {
                //user clicked cancel, ignore and fetch if fetch is enabled
                this.context.set({reloadCommitButton: true});
                if(fetch) {
                    this.collection.fetch();
                }
            }
        }
        /*
         * Next, we need to check to see if the user is a manager.  They have their own requirements and dialogs (those described below)
         */
        else if(this.selectedUser.isManager && (this.context.get("currentWorksheet") == "worksheet") && app.metadata.getModule('Forecasts', 'config').show_forecasts_commit_warnings) {
            /*
             * If the manager has a draft version saved, but hasn't committed that yet, they need to be shown a dialog that
             * lets them know, and gives them the option of committing before the page reloads. This happens if the commit button
             * is enabled and they are on the rep worksheet.
             */
            if(this.commitButtonEnabled) {
                var msg = app.lang.get("LBL_WORKSHEET_COMMIT_CONFIRM", "Forecasts").split("<br>");
                //show dialog
                if(confirm(msg[0] + "\n\n" + msg[1])) {
                    this.context.trigger("forecasts:commitButtons:triggerCommit");
                    this.commitFromSafeFetch = true;
                } else if(fetch) {
                    //canceled, continue fetching
                    this.collection.fetch();
                }

            } else if(this.mgrNeedsCommitted) {
                alert(app.lang.get("LBL_WORKSHEET_COMMIT_ALERT", "Forecasts"));
                this.mgrNeedsCommitted = false;
                if(fetch) {
                    this.collection.fetch();
                }

            } else if(fetch) {
                //No popups needed, fetch like normal
                this.collection.fetch();
            }
        } else if(fetch) {
            //default case, fetch like normal
            this.collection.fetch();
        }
        //mark that the fetch is over
        this.fetchInProgress = false;
    },

    /**
     * renders the view
     *
     * @return {Object} this
     * @private
     */
    _render: function() {

        if(!this.isVisible()) {
            return this;
        }
        $("#view-sales-rep").addClass('show').removeClass('hide');
        $("#view-manager").addClass('hide').removeClass('show');

        this.context.set({currentWorksheet: "worksheet"});
        this.isEditableWorksheet = this.isMyWorksheet();

        // empty out the columnDefs if it's be re-rendred again
        this.columnDefs = [];

        app.view.View.prototype._render.call(this);

        // if there is no data for the worksheet, this.columnDefs will be empty
        // but we still need the column visibility definitions
        if(_.isEmpty(this.columnDefs)) {
            _.each(this.options.meta.panels[0].fields, function(field) {
                // creates column def and adds it to this.columnDefs
                this._createFieldColumnDef(field);
            }, this);
        }

        // set the columnDefs back into the tableDefs
        this.gTableDefs['aoColumnDefs'] = this.columnDefs;

        // render the table
        this.gTable = this.$('.worksheetTable').dataTable(this.gTableDefs);

        this.adjustCurrencyColumnWidths();
        this.calculateTotals();

        // fix the style on the rows that contain a checkbox
        this.$el.find('td:has(span>input[type=checkbox])').addClass('center');

        // Trigger event letting other components know worksheet finished rendering
        this.context.trigger("forecasts:worksheet:rendered");

        //Check to see if any worksheet entries are older than the source data.  If so, that means that the
        //last commit is older, and that we need to enable the commit buttons
        var enableCommit = this.collection.find(function(model) {
            return !_.isEmpty(model.get("w_date_modified")) && (new Date(model.get("w_date_modified")) < new Date(model.get("date_modified")))
        }, this);
        if(_.isObject(enableCommit)) {
            this.context.trigger("forecasts:commitButtons:enabled");
        }

        return this;
    },

    /**
     * set dynamic widths on currency columns showing original currency
     */
    adjustCurrencyColumnWidths: function() {
        var likelyConverted = this.$el.find('.likely .converted'),
            likelyOriginal = this.$el.find('.likely label.original'),
            bestConverted = this.$el.find('.best .converted'),
            bestOriginal = this.$el.find('.best label.original'),
            worstConverted = this.$el.find('.worst .converted'),
            worstOriginal = this.$el.find('.worst label.original');

        var likelyWidths = likelyConverted.map(function() {
            return $(this).width();
        }).get();

        var likelyLabelWidths = likelyOriginal.map(function() {
            return $(this).width();
        }).get();

        var bestWidths = bestConverted.map(function() {
            return $(this).width();
        }).get();

        var bestLabelWidths = bestOriginal.map(function() {
            return $(this).width();
        }).get();

        var worstWidths = worstConverted.map(function() {
            return $(this).width();
        }).get();

        var worstLabelWidths = worstOriginal.map(function() {
            return $(this).width();
        }).get();

        likelyConverted.width(_.max(likelyWidths));
        likelyOriginal.width(_.max(likelyLabelWidths));
        bestConverted.width(_.max(bestWidths));
        bestOriginal.width(_.max(bestLabelWidths));
        worstConverted.width(_.max(worstWidths));
        worstOriginal.width(_.max(worstLabelWidths));

        // now set table column width from this value
        this.$el.find('.number .likely').width(likelyConverted.width() + likelyOriginal.width());
        this.$el.find('.number .best').width(bestConverted.width() + bestOriginal.width());
        this.$el.find('.number .worst').width(worstConverted.width() + worstOriginal.width());
    },

    /**
     * Determines if this Worksheet belongs to the current user, applicable for determining if this view should show,
     * or whether to render the clickToEdit field
     *
     * @return {Boolean} true if it is the worksheet of the logged in user, false if not.
     */
    isMyWorksheet: function() {
        return _.isEqual(app.user.get('id'), this.selectedUser.id);
    },

    /**
     * Determines if this Worksheet should be rendered
     *
     * @return {Boolean} this.show
     */
    isVisible: function() {
        var selectedUser = (this.isDirty() && this.dirtyUser) ? this.dirtyUser : this.selectedUser;

        return selectedUser.showOpps || !selectedUser.isManager;
    },

    /**
     * Calculate the totals for the worksheet
     */
    calculateTotals: function() {
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
            totalCount = 0;
            includedClosedCount = 0;
            includedClosedAmount = 0;

        if(!this.isVisible()) {
            // if we don't show this worksheet set it all to zero
            this.context.set({
                updatedTotals: {
                    'amount': includedAmount,
                    'best_case': includedBest,
                    'worst_case': includedWorst,
                    'overall_amount': overallAmount,
                    'overall_best': overallBest,
                    'overall_worst': overallWorst,
                    'timeperiod_id': this.timePeriod,
                    'lost_count': lostCount,
                    'lost_amount': lostAmount,
                    'won_count': wonCount,
                    'won_amount': wonAmount,
                    'included_opp_count': includedCount,
                    'total_opp_count': totalCount,
                    'includedClosedCount' : 0,
                    'includedClosedAmount' : 0

                }
            }, {silent: true});
            return false;
        }

        //Get the excluded_sales_stage property.  Default to empty array if not set
        var sales_stage_won_setting = app.metadata.getModule('Forecasts', 'config').sales_stage_won || [];
        var sales_stage_lost_setting = app.metadata.getModule('Forecasts', 'config').sales_stage_lost || [];

        // set up commit_stages that should be processed in included total
        var forecast_ranges = app.metadata.getModule('Forecasts', 'config').forecast_ranges,
            commit_stages_in_included_total = [],
            ranges;

        if ( forecast_ranges == 'show_custom_buckets' ) {
            ranges = app.metadata.getModule('Forecasts', 'config')[forecast_ranges + '_ranges'];
            _.each(ranges, function(value, key){
                if ( !_.isUndefined(value.in_included_total) && value.in_included_total ) {
                    commit_stages_in_included_total.push(key);
                }
            })
        } else {
            commit_stages_in_included_total.push('include');
        }

        _.each(this.collection.models, function(model) {
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

            if(won) {
                wonAmount = app.math.add(wonAmount, amount_base);
                wonCount++;
            } else if(lost) {
                lostAmount = app.math.add(lostAmount, amount_base);
                lostCount++;
            }
            if ( _.include(commit_stages_in_included_total, commit_stage) ) {
                includedAmount += amount_base;
                includedBest += best_base;
                includedWorst += worst_base;
                includedCount++;
                if(won || lost) {
                    includedClosedCount++;
                    includedClosedAmount = app.math.add(amount_base, includedClosedAmount);
                }
            }

            overallAmount += amount_base;
            overallBest += best_base;
            overallWorst += worst_base;
        }, this);

        var totals = {
            'amount': includedAmount,
            'best_case': includedBest,
            'worst_case': includedWorst,
            'overall_amount': overallAmount,
            'overall_best': overallBest,
            'overall_worst': overallWorst,
            'timeperiod_id': this.timePeriod,
            'lost_count': lostCount,
            'lost_amount': lostAmount,
            'won_count': wonCount,
            'won_amount': wonAmount,
            'included_opp_count': includedCount,
            'total_opp_count': this.collection.models.length,
            'includedClosedCount': includedClosedCount,
            'includedClosedAmount': includedClosedAmount

        };

        this.context.unset("updatedTotals", {silent: true});
        this.context.set("updatedTotals", totals);

        return true;
    },

    /**
     * Event Handler for updating the worksheet by a selected user
     *
     * @param params is always a context
     */
    updateWorksheetBySelectedUser: function(selectedUser) {
        //do a dirty check before fetching. Safe fetch uses selected user for some of its checks, so we need to check
        //things before this.selectedUser is replaced.
        if(this.isDirty()) {
            // since the model is dirty, save it so we can use it later
            this.dirtyUser = this.selectedUser;
        }
        this.safeFetch(false);
        this.selectedUser = selectedUser;
        if(!this.isVisible()) {
            return false;
        }
        this.collection.fetch();
    },

    /**
     * Event Handler for updating the worksheet by a selected range
     *
     * @param params array of selected filters
     */
    updateWorksheetBySelectedRanges: function(params) {
        // Set the filters for the datatable then re-render
        var forecast_ranges_setting = app.metadata.getModule('Forecasts', 'config').forecast_ranges || 'show_binary';

        // start with no filters, i. e. show everything.
        if(!_.isUndefined($.fn.dataTableExt)) {
            $.fn.dataTableExt.afnFiltering.splice(0, $.fn.dataTableExt.afnFiltering.length);
            if(!_.isEmpty(params)) {
                $.fn.dataTableExt.afnFiltering.push(
                    _.bind(function(oSettings, aData, iDataIndex) {
                        // This is required to prevent manager view from filtering incorrectly, since datatables does filtering globally
                        if(oSettings.nTable == _.first($('.worksheetManagerTable'))) {
                            return true;
                        }

                        var editable = this.isMyWorksheet(),
                            selectVal,
                            rowCategory = $(_.first(aData)),
                            checkState;

                        //If we are in an editable worksheet get the selected dropdown/checkbox value; otherwise, get the detail/default text
                        if(forecast_ranges_setting == 'show_binary') {
                            checkState = rowCategory.find('input').attr('checked');
                            selectVal = ((checkState == "checked") || (checkState == "on") || (checkState == "1")) ? 'include' : 'exclude';
                        } else {
                            //we need to check to see if the select exists, because this gets fired before the commitStage field re-renders this back
                            //to a text field.
                            if(rowCategory.find("select").length == 0) {
                                selectVal = rowCategory.text().trim().toLowerCase();
                            } else {
                                selectVal = rowCategory.find("select")[0].value.toLowerCase();
                            }
                        }

                        this.context.trigger('forecasts:worksheet:filtered');
                        return (_.contains(params, selectVal));
                    }, this)
                );
            }
        }

        if(!_.isUndefined(this.gTable.fnDestroy)) {
            this.gTable.fnDestroy();
            this.gTable = this.$('.worksheetTable').dataTable(this.gTableDefs);
            // fix the style on the rows that contain a checkbox
            this.$el.find('td:has(span>input[type=checkbox])').addClass('center');
            this.context.trigger('forecasts:worksheet:filtered');
        }
    },

    /**
     * Event Handler for updating the worksheet by a timeperiod id
     *
     * @param {Object} params is always a context
     */
    updateWorksheetBySelectedTimePeriod: function(params) {
        if(this.isDirty()) {
            // since the model is dirty, save it so we can use it later
            this.dirtyTimeperiod = this.timePeriod;
        }
        this.timePeriod = params.id;

        this.loadData();
    }
})
