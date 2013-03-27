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
 * @class View.Views.WorksheetView
 * @alias SUGAR.App.layout.WorksheetView
 * @extends View.View
 *
 *
 * Events Triggered
 *
 * forecasts:commitButtons:enabled
 *      on: context
 *      by: _render()
 *      when: done rendering if enableCommit is true
 *
 * forecasts:worksheetmanager:rendered
 *      on: context
 *      by: _render()
 *      when: done rendering
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
 * forecasts:worksheetManager:reloadCommitButton
 *      on: context
 *      by: safeFetch()
 */
({
    url: 'rest/v10/ForecastManagerWorksheets',
    selectedUser: {},
    timePeriod: '',
    gTable: '',

    /**
     * boolean to denote that a fetch is currently in progress
     */
    fetchInProgress: false,

    /**
     * Template to use when updating the likelyCase on the committed bar
     */
    commitLogTemplate: _.template('<article><%= text %><br><date><%= text2 %></date></article>'),

    /**
     * Template to use when we are fetching the commit history
     */
    commitLogLoadingTemplate: _.template('<div class="extend results"><article><%= loadingMessage %></article></div>'),

    dirtyModels: new Backbone.Collection(),

    /**
     * If the timeperiod is changed and we have dirtyModels, keep the previous one to use if they save the models
     */
    dirtyTimeperiod: '',

    /**
     * If the timeperiod is changed and we have dirtyModels, keep the previous one to use if they save the models
     */
    dirtyUser: '',

    /**
     * A Collection to keep track of draft models
     */
    draftModels: new Backbone.Collection(),

    /**
     * If the timeperiod is changed and we have draftModels, keep the previous one to use if they save the models
     */
    draftTimeperiod: '',

    /**
     * If the timeperiod is changed and we have draftModels, keep the previous one to use if they save the models
     */
    draftUser: '',

    defaultValues: {
        quota: 0,
        best_case: 0,
        best_case_adjusted: 0,
        likely_case: 0,
        likely_case_adjusted: 0,
        worst_case: 0,
        worst_case_adjusted: 0
    },

    /**
     * Handle Any Events
     */
    events: {
        'click a[rel=historyLog] i.icon-exclamation-sign': 'displayHistoryLog'
    },

    /**
     * Initialize the View
     *
     * @constructor
     * @param {Object} options
     */
    initialize: function(options) {
        // we need a custom model and collection in this view, so just create them on the options
        // before we call the parent method
        options.model = app.data.createBean('Forecasts');
        options.collection = app.data.createBeanCollection('Forecasts');

        app.view.View.prototype.initialize.call(this, options);

        // we have to override sync right now as there is no way to run the filter by default
        this.collection.sync = _.bind(function(method, model, options) {
            options.success = _.bind(function(resp, status, xhr) {
                this.collectionSuccess(resp, status, xhr);
            }, this);
            // we need to force a post, so get the url object and put it in
            var url = this.createURL();
            app.api.call("create", url.url, url.filters, options);
        }, this);

        this.timePeriod = app.defaultSelections.timeperiod_id.id
        this.ranges = app.defaultSelections.ranges.id

        this.totalModel = new (Backbone.Model.extend(this.defaultValues));

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
     * Method to handle the success of a collection call to make sure that all reportee's show up in the table
     * even if they don't have data for the user that is asking for it.
     * @param resp
     * @param status
     * @param xhr
     */
    collectionSuccess: function(resp, status, xhr) {
        var records = [],
            users = $.map(this.selectedUser.reportees, function(obj) {
                return $.extend(true, {}, obj);
            });

        // put the selected user on top
        users.unshift({id: this.selectedUser.id, name: this.selectedUser.full_name});

        // get the base currency
        var currency_id = app.currency.getBaseCurrencyId();
        var currency_base_rate = app.metadata.getCurrency(app.currency.getBaseCurrencyId()).conversion_rate;

        _.each(users, function(user) {
            var row = _.find(resp.records, function(rec) {
                return (rec.user_id == this.id)
            }, user);
            if(!_.isUndefined(row)) {
                // update the name on the row as this will have the correct formatting for the locale
                row.name = user.name;
            } else {
                row = _.clone(this.defaultValues);
                row.currency_id = currency_id;
                row.base_rate = currency_base_rate;
                row.user_id = user.id;
                row.assigned_user_id = this.selectedUser.id;
                row.draft = (this.selectedUser.id == app.user.id) ? 1 : 0;
                row.name = user.name;
            }
            records.push(row);
        }, this);

        this.collection.reset(records);
    },

    /**
     * Overwrite Load Data for now
     */
    loadData: function() {
        // only load the data if the sheets is visible.
        if(this.isVisible() && !_.isUndefined(this.selectedUser.id)) {
            this.safeFetch(true);
        }
    },

    /**
     *
     * @return {object}
     */
    createURL: function(isCommitLog, userId, showOpps, isManager) {
        isCommitLog = isCommitLog || false;

        var args_filter = [],
            beanName = 'ForecastManagerWorksheets',
            url;

        if(this.timePeriod) {
            args_filter.push({"timeperiod_id": this.timePeriod});
        }

        if(isCommitLog && this.selectedUser) {
            args_filter.push({"user_id": userId});
            beanName = 'Forecasts';
            var forecastType = 'Direct';
            /**
             * Three cases exist when a row is showing historyLog icon:
             *
             * Manager  - showOpps=1 - isManager=1 => Manager's Opportunities row - forecast_type = 'Direct'
             * Manager  - showOpps=0 - isManager=1 => Manager has another manager in their ManagerWorksheet - forecast_type = 'Rollup'
             * Rep      - showOpps=0 - isManager=0 => Sales Rep (not a manager) row - forecast_type = 'Direct'
             *
             */
            if(!showOpps && isManager) {
                forecastType = 'Rollup';
            }

            args_filter.push({"forecast_type": forecastType});
        } else if(this.selectedUser) {
            args_filter.push({"assigned_user_id": this.selectedUser.id});
        }

        url = app.api.buildURL(beanName, 'filter');

        return {"url": url, "filters": {"filter": args_filter}};
    },

    /**
     * Event Handler for updating the worksheet by a selected user
     *
     * @param selectedUser          the new selected User
     */
    updateWorksheetBySelectedUser: function(selectedUser) {
        if(this.isDirty()) {
            // since the model is dirty, save it so we can use it later
            this.dirtyUser = this.selectedUser;
            this.draftUser = this.selectedUser;
        }
        var userChanged = (this.selectedUser.id != selectedUser.id),
            showOppsChanged = (this.selectedUser.showOpps != selectedUser.showOpps);
        this.selectedUser = selectedUser;
        if(!this.isVisible()) {
            return false;
        }
        if(userChanged || showOppsChanged) {
            this.loadData();
        }

        return true;
    },

    /**
     * Clean up any left over bound data to our context
     */
    unbindData: function() {
        //if we don't unbind this, then recycle of this view if a change in rendering occurs will result in multiple bound events to possibly out of date functions
        $(window).unbind("beforeunload");
        if(this.context) {
            this.context.off(null, null, this);
        }
        app.view.View.prototype.unbindData.call(this);
    },

    bindDataChange: function() {
        if(this.collection) {
            this.collection.on("reset", function() {
                this.cleanUpDirtyModels();
                this.cleanUpDraftModels();
                if (!this.disposed) {
                    this.render();
                }
            }, this);

            this.collection.on("change", function(model) {
                // The Model has changed via CTE. save it in the isDirty
                this.dirtyModels.add(model);
                this.context.trigger('forecasts:worksheet:dirty', model, model.changed);

                // anytime the collection changes, we need to caculate the totals
                this.calculateTotals();
            }, this);
        }

        if(this.context) {
            // listening for updates to context for selectedUser:change
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
                }, this);
            this.context.on("forecasts:committed:saved forecasts:worksheet:saved", function() {
                if(this.isVisible()) {
                    this.safeFetch(true);
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

            this.context.on('forecasts:worksheet:saveWorksheet', function(isDraft) {
                this.saveWorksheet(isDraft);
            }, this);

            this.context.on('forecasts:tabKeyPressed', function(isShift, field) {
                this.editableFieldNavigate(isShift, field);
            }, this);
        }

        $(window).bind("beforeunload", _.bind(function() {
            if(this.isDirty()) {
                return app.lang.get("LBL_WORKSHEET_SAVE_CONFIRM_UNLOAD", "Forecasts");
            }
        }, this));
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
     * Is this worksheet dirty or not?
     * @return {boolean}
     */
    isDirty: function() {
        return (this.dirtyModels.length > 0);
    },

    /**
     * Handles saving the Worksheet
     * @triggers forecasts:worksheet:saved
     * @return {Number}
     */
    saveWorksheet: function(isDraft) {
        // only run the save when the worksheet is visible and it has dirty records
        var saveObj = {
            totalToSave: 0,
            saveCount: 0,
            model: "",
            isDraft: isDraft,
            timeperiod: this.dirtyTimeperiod,
            userId: this.dirtyUser.id
        };

        if(this.isVisible()) {
            /**
             * If the sheet is dirty, save the dirty rows. Else, if the save is for a commit, and we have
             * draft models (things saved as draft), we need to resave those as committed (version 1). If neither
             * of these conditions are true, then we need to fall through and signal that the save is complete so other
             * actions listening for this can continue.
             */
            if(this.isDirty()) {
                saveObj.totalToSave = this.dirtyModels.length;

                this.dirtyModels.each(function(model) {
                    saveObj.model = model;
                    this._worksheetSaveHelper(saveObj);

                    //add to draft structure so committing knows what to save as non-draft
                    if(isDraft == true) {
                        this.draftModels.add(model, {merge: true});
                    }
                }, this);

                this.cleanUpDirtyModels();
            } else if(!isDraft && this.draftModels.length > 0) {
                saveObj.totalToSave = this.draftModels.length;

                this.draftModels.each(function(model) {
                    saveObj.model = model;
                    this._worksheetSaveHelper(saveObj);
                }, this);

                //Need to clean up dirty models too as the save event above triggers a change event on the worksheet.
                this.cleanUpDirtyModels();
                this.cleanUpDraftModels();
            } else {
                if(isDraft) {
                    app.alert.show('success', {
                        level: 'success',
                        autoClose: true,
                        title: app.lang.get("LBL_FORECASTS_WIZARD_SUCCESS_TITLE", "Forecasts") + ":",
                        messages: [app.lang.get("LBL_FORECASTS_WORKSHEET_SAVE_DRAFT_SUCCESS", "Forecasts")]
                    });
                }
                this.context.trigger('forecasts:worksheet:saved', saveObj.totalToSave, 'mgr_worksheet', isDraft);
            }
        }

        return saveObj.totalToSave
    },

    /**
     * Helper function for worksheet save
     */
    _worksheetSaveHelper: function(saveObj) {
        saveObj.model.set({
            timeperiod_id: saveObj.timeperiod || this.timePeriod,
            current_user: saveObj.userId || this.selectedUser.id
        }, {silent: true});

        saveObj.model.module = "ForecastManagerWorksheets";
        saveObj.model.save({}, {success: _.bind(function() {
            saveObj.saveCount++;
            //if this is the last save, go ahead and trigger the callback;
            if(saveObj.totalToSave === saveObj.saveCount) {
                if(saveObj.isDraft) {
                    app.alert.show('success', {
                        level: 'success',
                        autoClose: true,
                        title: app.lang.get("LBL_FORECASTS_WIZARD_SUCCESS_TITLE", "Forecasts") + ":",
                        messages: [app.lang.get("LBL_FORECASTS_WORKSHEET_SAVE_DRAFT_SUCCESS", "Forecasts")]
                    });
                }
                this.context.trigger('forecasts:worksheet:saved', saveObj.totalToSave, 'mgr_worksheet', saveObj.isDraft);
            }
        }, this), silent: true, alerts: { 'success': false }});
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
     * Clean Up the Draft Modules Collection and dirtyVariables
     */
    cleanUpDraftModels: function() {
        // clean up the draft records and variables
        this.draftModels.reset();
        this.draftTimeperiod = '';
        this.draftUser = '';
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
     * @param fetch {boolean}(Optional) Tells the function to go ahead and fetch if true, or runs dirty checks (saving) w/o fetching if false
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
        if(this.isDirty()) {
            //unsaved changes, ask if you want to save.
            if(confirm(app.lang.get("LBL_WORKSHEET_SAVE_CONFIRM", "Forecasts"))) {
                this.context.trigger('forecasts:worksheetManager:reloadCommitButton');
                this.context.once('forecast:worksheet:saved', _.bind(function() {
                    this.collection.fetch()
                }, this));

                this.saveWorksheet()
            } else {
                //ignore, fetch still
                this.context.trigger('forecasts:worksheetManager:reloadCommitButton');
                if(fetch) {
                    this.collection.fetch();
                }

            }
        } else if(fetch) {
            //no changes, fetch like normal.
            this.collection.fetch();
        }
        this.fetchInProgress = false;
    },

    /**
     * Renders view
     */
    _render: function() {
        if(!this.isVisible()) {
            return false;
        }
        $("#view-sales-rep").addClass('hide').removeClass('show');
        $("#view-manager").addClass('show').removeClass('hide');
        this.context.set({currentWorksheet: "worksheetmanager"});

        app.view.View.prototype._render.call(this);

        // parse metadata into columnDefs
        // so you can sort on the column's "name" prop from metadata
        var columnDefs = [];
        var fields = this.meta.panels[0].fields;

        for(var i = 0; i < fields.length; i++) {
            if(fields[i].enabled) {
                // in case we add column rearranging
                var fieldDef = {
                    "sName": fields[i].name,
                    "bVisible": this.checkConfigForColumnVisibility(fields[i].name)
                };

                //Apply sorting for the worksheet
                if(!_.isUndefined(fields[i].type)) {
                    switch(fields[i].type) {
                        case "int":
                        case "currency":
                        case "editableCurrency":
                            fieldDef["sSortDataType"] = "dom-number";
                            fieldDef["sType"] = "numeric";
                            fieldDef["sClass"] = "number";
                            break;
                    }
                    switch(fields[i].name) {
                        case "name":
                            fieldDef["sWidth"] = "30%";
                            break;
                    }
                }

                columnDefs.push(fieldDef);
            }
        }

        this.gTable = this.$el.find(".worksheetManagerTable").dataTable(
            {
                "bAutoWidth": false,
                "aaSorting": [],
                "aoColumns": columnDefs,
                "bInfo": false,
                "bPaginate": false
            }
        );

        if (this.getDraftModels().length > 0) {
            this.context.trigger("forecasts:commitButtons:enabled");
        }

        this.calculateTotals();
        this.context.trigger('forecasts:worksheetmanager:rendered');

        return this;
    },
    
    getDraftModels: function(){
      //see if anything in the model is a draft version
        return this.collection.filter(function(model) {
            if (model.get("version") == "0") {
                this.draftModels.add(model, {merge: true});
                return true;
            }            
            return false;            
        }, this);
    },

    /**
     * Handle the click event from a history log icon click.
     *
     * @param event
     */
    displayHistoryLog: function(event) {
        var nTr = _.first($(event.target).parents('tr'));
        // test to see if it's open
        if(this.gTable.fnIsOpen(nTr)) {
            // if it's open, close it
            this.gTable.fnClose(nTr);
        } else {
            //Open this row

            var colspan = $(nTr).children('td').length;

            this.gTable.fnOpen(nTr, this.commitLogLoadingTemplate({'loadingMessage': App.lang.get("LBL_LOADING_COMMIT_HISTORY", 'Forecasts')}), 'details');
            $(nTr).next().children("td").attr("colspan", colspan);

            this.fetchUserCommitHistory(event, nTr);
        }
    },

    /**
     * Event handler when popoverIcon is clicked,
     * @param event
     * @param nTr
     * @return {*}
     */
    fetchUserCommitHistory: function(event, nTr) {
        var jTarget = $(event.target),
            forecastCommitDate = new Date(this.context.get('currentForecastCommitDate')),
            url = this.createURL(true, jTarget.data('uid'), jTarget.data('showopps'), jTarget.data('ismanager'));

        return app.api.call('create',
            url.url,
            url.filters,
            {
                success: function(data) {
                    data = data.records;
                    var commitDate = new Date(forecastCommitDate),
                        newestModel = new Backbone.Model(_.first(data)),
                    // get everything that is left but the first item.
                        otherModels = _.last(data, data.length - 1),
                        oldestModel = {};

                    // using for because you can't break out of _.each
                    for(var i = 0; i < otherModels.length; i++) {
                        // check for the first model equal to or past the forecast commit date
                        // we want the last commit just before the whole forecast was committed
                        if(new Date(otherModels[i].date_modified) <= commitDate) {
                            oldestModel = new Backbone.Model(otherModels[i]);
                            break;
                        }
                    }

                    // create the history log
                    outputLog = app.utils.createHistoryLog(oldestModel, newestModel);
                    // update the div that was created earlier and set the html to what was the commit log
                    $(nTr).next().children("td").children("div").html(this.commitLogTemplate(outputLog));
                }
            },
            { context: this }
        );
    },


    calculateTotals: function() {
        var quota = 0,
            best_case = 0,
            best_case_adjusted = 0,
            likely_case = 0,
            likely_case_adjusted = 0,
            worst_case_adjusted = 0,
            worst_case = 0,
            included_opp_count = 0,
            pipeline_opp_count = 0,
            pipeline_amount = 0;


        this.collection.forEach(function(model) {
            var base_rate = parseFloat(model.get('base_rate')),
                mPipeline_opp_count = model.get("pipeline_opp_count"),
                mPipeline_amount = model.get("pipeline_amount"),
                mOpp_count = model.get("opp_count");

            quota += app.currency.convertWithRate(model.get('quota'), base_rate);
            best_case += app.currency.convertWithRate(model.get('best_case'), base_rate);
            best_case_adjusted += app.currency.convertWithRate(model.get('best_case_adjusted'), base_rate);
            likely_case += app.currency.convertWithRate(model.get('likely_case'), base_rate);
            likely_case_adjusted += app.currency.convertWithRate(model.get('likely_case_adjusted'), base_rate);
            worst_case += app.currency.convertWithRate(model.get('worst_case'), base_rate);
            worst_case_adjusted += app.currency.convertWithRate(model.get('worst_case_adjusted'), base_rate);
            included_opp_count += (_.isUndefined(mOpp_count))? 0 : parseInt(mOpp_count);
            pipeline_opp_count += (_.isUndefined(mPipeline_opp_count))? 0 : parseInt(mPipeline_opp_count);
            if(!_.isUndefined(mPipeline_amount)) {
                pipeline_amount = app.math.add(pipeline_amount, mPipeline_amount);
            }

        });

        //in case this is needed later..
        this.totalModel.set({
            'quota': quota,
            'best_case': best_case,
            'best_adjusted': best_case_adjusted,
            'likely_case': likely_case,
            'likely_adjusted': likely_case_adjusted,
            'worst_case': worst_case,
            'worst_adjusted': worst_case_adjusted,
            'included_opp_count': included_opp_count,
            'pipeline_opp_count': pipeline_opp_count,
            'pipeline_amount': pipeline_amount

        });

        this.context.trigger("forecasts:worksheetManager:updateTotals", this.totalModel.toJSON());
    },

    /**
     * Determines if this Worksheet should be rendered
     */
    isVisible: function() {
        var selectedUser = (this.isDirty() && this.dirtyUser) ? this.dirtyUser : this.selectedUser;

        return (!selectedUser.showOpps && selectedUser.isManager)
    },

    /**
     * Event Handler for updating the worksheet by a selected ranges
     *
     * @param params is always a context
     */
    updateWorksheetBySelectedRanges: function(params) {
        if(app.metadata.getModule('Forecasts', 'config').forecast_ranges != 'show_binary') {
            // TODO: this.
        } else {
            this.ranges = _.first(params);
        }

        if(!this.isVisible()) {
            return false;
        }
        this.safeFetch(true);
    },

    /**
     * Event Handler for updating the worksheet by a timeperiod id
     *
     * @param params is always a context
     */
    updateWorksheetBySelectedTimePeriod: function(params) {
        if(this.isDirty()) {
            // since the model is dirty, save it so we can use it later
            this.dirtyTimeperiod = this.timePeriod;
            this.draftTimeperiod = this.timePeriod;
        }
        this.timePeriod = params.id;
        if(!this.isVisible()) {
            return false;
        }
        this.safeFetch(true);
    }
})
