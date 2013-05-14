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
({
    extendsFrom: 'RecordlistView',

    worksheetType: 'manager',

    selectedUser: {},

    selectedTimeperiod: {},

    totals: {},

    /**
     * A Collection to keep track of draft models
     */
    draftModels: new Backbone.Collection(),

    /**
     * If the timeperiod is changed and we have draftModels, keep the previous one to use if they save the models
     */
    draftTimeperiod: undefined,

    /**
     * If the timeperiod is changed and we have draftModels, keep the previous one to use if they save the models
     */
    draftUser: undefined,

    defaultValues: {
        quota: 0,
        best_case: 0,
        best_case_adjusted: 0,
        likely_case: 0,
        likely_case_adjusted: 0,
        worst_case: 0,
        worst_case_adjusted: 0,
        show_history_log: 0
    },

    initialize: function(options) {
        this.plugins.push('cte-tabbing');
        this.plugins.push('dirty-collection');
        app.view.views.RecordlistView.prototype.initialize.call(this, options);
        this.selectedUser = this.context.get('selectedUser') || this.context.parent.get('selectedUser') || app.user.toJSON();
        this.context.set('skipFetch', (this.selectedUser.isManager && this.selectedUser.showOpps));    // skip the initial fetch, this will be handled by the changing of the selectedUser
        this.collection.sync = _.bind(this.sync, this);
    },

    bindDataChange: function() {
        // these are handlers that we only want to run when the parent module is forecasts
        if (!_.isUndefined(this.context.parent) && !_.isUndefined(this.context.parent.get('model'))) {
            if (this.context.parent.get('model').module == 'Forecasts') {
                this.before('render', function() {
                    // if manager is not set or manager == false
                    var ret = true;
                    if (_.isUndefined(this.selectedUser.isManager) || this.selectedUser.isManager == false) {
                        console.log('manager before render return false');
                        ret = false;
                    }

                    // only render if this.selectedUser.showOpps == false which means
                    // we want to display the manager worksheet view
                    if (ret) {
                        ret = !(this.selectedUser.showOpps);
                    }

                    if (ret === false && this.layout.isVisible()) {
                        // hide the layout
                        console.log('manager beforeRender hide');
                        this.layout.hide();
                    }

                    console.log('manager showOpps before render return: ', ret);
                    return ret;
                }, true);
                this.on('render', function() {
                    var user = this.context.parent.get('selectedUser') || app.user.toJSON();
                    console.log('Manager Render:', user.isManager, user.showOpps);
                    if (user.isManager && user.showOpps == false) {
                        if (!this.layout.isVisible()) {
                            console.log('Manager Show', user);
                            this.layout.show();
                        }

                        // insert the footer
                        if (!_.isEmpty(this.totals) && this.layout.isVisible()) {
                            console.log('insert manager footer');
                            var tpl = app.template.getView('recordlist.totals', this.module);
                            this.$el.find('tbody').after(tpl(this));
                        }

                        // set the commit button states to match the models
                        this.setCommitLogButtonStates();
                    } else {
                        if (this.layout.isVisible()) {
                            console.log('Manager Hide', user);
                            this.layout.hide();
                        }
                    }
                }, this);

                this.context.parent.on('button:save_draft_button:click', function() {
                    if (this.layout.isVisible()) {
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

                this.context.parent.on('change:selectedTimePeriod', function(model, changed) {
                    this.selectedTimeperiod = changed;
                    if (this.layout.isVisible()) {
                        this.collection.fetch();
                    }
                }, this);

                this.context.parent.on('forecasts:worksheet:totals', function(totals, type) {
                    if (type == this.worksheetType) {
                        console.log('update mgr footer');
                        var tpl = app.template.getView('recordlist.totals', this.module);
                        this.$el.find('tfoot').remove();
                        this.$el.find('tbody').after(tpl(this));
                    }
                }, this);

                this.context.parent.on('change:selectedUser', function(model, changed) {
                    // selected user changed
                    var doFetch = false;
                    if (this.selectedUser.id != changed.id) {
                        doFetch = true;
                    }
                    if (!doFetch && this.selectedUser.isManager != changed.isManager) {
                        doFetch = true;
                    }
                    if (!doFetch && this.selectedUser.showOpps != changed.showOpps) {
                        doFetch = !(changed.showOpps);
                    }
                    this.selectedUser = changed;

                    if (doFetch) {
                        this.collection.fetch();
                    } else {
                        if (this.selectedUser.isManager && this.selectedUser.showOpps == true && this.layout.isVisible()) {
                            // viewing managers opp worksheet so hide the manager worksheet
                            this.layout.hide();
                        }
                    }
                }, this);

                this.context.parent.on('change:currentForecastCommitDate', function(context, changed) {
                    this.checkForDraftRows(changed);
                }, this);

                this.collection.on('reset', function() {
                    this.checkForDraftRows(this.context.parent.get('currentForecastCommitDate'));
                }, this);
            }
        }

        if (!_.isUndefined(this.dirtyModels)) {
            this.dirtyModels.on('add', function() {
                var ctx = this.context.parent || this.context
                ctx.trigger('forecast:worksheet:dirty', this.worksheetType);
            }, this);
        }

        this.context.on('list:history_log:fire', function(model) {
            // parent row

            var row_name = model.module + '_' + model.id;

            // check if the row is open, if it is, just destroy it
            var log_row = this.$el.find('tr[name="' + row_name + '_commit_history"]');

            // if we have a row, just close it and destroy the field
            if (log_row.length == 1) {
                // remove it and dispose the field
                log_row.remove();
                // find the field
                var field = _.find(this.fields, function(field, idx) {
                    return (field.name == row_name + '_commit_history');
                }, this);
                field.dispose();
            } else {
                var rowTpl = app.template.getView('recordlist.commithistory', this.module);
                var field = app.view.createField({
                    def: {
                        'type': 'commithistory',
                        'name': row_name + '_commit_history'
                    },
                    view: this,
                    model: model
                });
                this.$el.find('tr[name="' + row_name + '"]').after(rowTpl({
                    module: this.module,
                    id: model.id,
                    placeholder: field.getPlaceholder(),
                    colspan: this._fields.visible.length
                }));
                field.render();
            }
        }, this);

        this.collection.on('reset change', function() {
            this.calculateTotals();
        }, this);

        app.view.views.RecordlistView.prototype.bindDataChange.call(this);
    },

    checkForDraftRows: function(lastCommitDate) {
        if (this.layout.isVisible()) {
            // check to see if anything in the collection is a draft, if it is, then send an event
            // to notify the commit button to enable
            this.collection.find(function(item) {
                if (item.get('date_modified') > lastCommitDate) {
                    this.context.parent.trigger('forecast:worksheet:needs_commit', this.worksheetType);
                    return true;
                }
                return false;
            }, this);

            /*if (items.length > 0) {
                // we have draft items. enable the commit button
                this.context.parent.trigger('forecast:worksheet:needs_commit', this.worksheetType);
            }*/
        }
    },

    setCommitLogButtonStates: function() {
        _.each(this.fields, function(field) {
            if (field.def.event === 'list:history_log:fire' && (field.model.get('show_history_log') == "0")) {
                // we have a field that needs to be disabled, so disable it!
                field.setDisabled(true);
            }
        });
    },

    sync: function(method, model, options) {

        if (!_.isUndefined(this.context.parent) && !_.isUndefined(this.context.parent.get('selectedUser'))) {
            var sl = this.context.parent.get('selectedUser');

            if (sl.isManager == false) {
                // they are not a manager, we should always hide this if it's not already hidden
                if (this.layout.isVisible()) {
                    this.layout.hide();
                }
                return;
            }
        }

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

        // custom success handler
        options.success = _.bind(function(model, data, options) {
            this.collectionSuccess(data);
        }, this);

        callbacks = app.data.getSyncCallbacks(method, model, options);
        this.trigger("data:sync:start", method, model, options);

        url = app.api.buildURL("ForecastManagerWorksheets", null, null, options.params);
        app.api.call("read", url, null, callbacks);
    },

    /**
     * Method to handle the success of a collection call to make sure that all reportee's show up in the table
     * even if they don't have data for the user that is asking for it.
     * @param data
     */
    collectionSuccess: function(data) {
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
            var row = _.find(data, function(rec) {
                return (rec.user_id == this.id)
            }, user);
            if (!_.isUndefined(row)) {
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

    calculateTotals: function() {
        // add up all the currency fields
        var fields = _.filter(this._fields.visible, function(field) {
                return field.type === 'currency';
            }),
            fieldNames = [];

        _.each(fields, function(field) {
            fieldNames.push(field.name);
            this.totals[field.name] = 0;
            this.totals[field.name + "_display"] = true;
        }, this);

        if (this.collection.length == 0) {
            // no items, just bail
            return;
        }

        this.collection.each(function(model) {
            _.each(fieldNames, function(field) {
                // convert the value to base
                var val = model.get(field);
                if (_.isUndefined(val) || _.isNaN(val)) {
                    return;
                }
                val = app.currency.convertWithRate(val, model.get('base_rate'));
                this.totals[field] = app.math.add(this.totals[field], val);
            }, this)
        }, this);

        var ctx = this.context.parent || this.context;
        // fire an event on the parent context
        if (this.isVisible()) {
            ctx.trigger('forecasts:worksheet:totals', this.totals, this.worksheetType);
        }
    },

    getCommitTotals: function() {
        var quota = 0,
            best_case = 0,
            best_case_adjusted = 0,
            likely_case = 0,
            likely_case_adjusted = 0,
            worst_case_adjusted = 0,
            worst_case = 0,
            included_opp_count = 0,
            pipeline_opp_count = 0,
            pipeline_amount = 0,
            closed_amount = 0;


        this.collection.forEach(function(model) {
            var base_rate = parseFloat(model.get('base_rate')),
                mPipeline_opp_count = model.get("pipeline_opp_count"),
                mPipeline_amount = model.get("pipeline_amount"),
                mClosed_amount = model.get("closed_amount"),
                mOpp_count = model.get("opp_count");

            quota += app.currency.convertWithRate(model.get('quota'), base_rate);
            best_case += app.currency.convertWithRate(model.get('best_case'), base_rate);
            best_case_adjusted += app.currency.convertWithRate(model.get('best_case_adjusted'), base_rate);
            likely_case += app.currency.convertWithRate(model.get('likely_case'), base_rate);
            likely_case_adjusted += app.currency.convertWithRate(model.get('likely_case_adjusted'), base_rate);
            worst_case += app.currency.convertWithRate(model.get('worst_case'), base_rate);
            worst_case_adjusted += app.currency.convertWithRate(model.get('worst_case_adjusted'), base_rate);
            included_opp_count += (_.isUndefined(mOpp_count)) ? 0 : parseInt(mOpp_count);
            pipeline_opp_count += (_.isUndefined(mPipeline_opp_count)) ? 0 : parseInt(mPipeline_opp_count);
            if (!_.isUndefined(mPipeline_amount)) {
                pipeline_amount = app.math.add(pipeline_amount, mPipeline_amount);
            }
            if (!_.isUndefined(mClosed_amount)) {
                closed_amount = app.math.add(closed_amount, mClosed_amount);
            }

        });

        return {
            'quota': quota,
            'best_case': best_case,
            'best_adjusted': best_case_adjusted,
            'likely_case': likely_case,
            'likely_adjusted': likely_case_adjusted,
            'worst_case': worst_case,
            'worst_adjusted': worst_case_adjusted,
            'included_opp_count': included_opp_count,
            'pipeline_opp_count': pipeline_opp_count,
            'pipeline_amount': pipeline_amount,
            'closed_amount': closed_amount,
            'closed_count' : (included_opp_count-pipeline_opp_count)
        };
    },

    /**
     * We have to overwrite this method completely, since there is currently no way to completely disable
     * a field from being displayed
     *
     * @returns {{default: Array, available: Array, visible: Array, options: Array}}
     */
    parseFields: function() {
        var catalog = {
            'default': [], //Fields visible by default
            'available': [], //Fields hidden by default
            'visible': [], //Fields user wants to see,
            'options': []
        };
        // TODO: load field prefs and store names in this._fields.available.visible
        // no prefs so use viewMeta as default and assign hidden fields
        _.each(this.meta.panels, function(panel) {
            _.each(panel.fields, function(fieldMeta, i) {
                if (app.utils.getColumnVisFromKeyMap(fieldMeta.name, 'forecastsWorksheetManager')) {
                    if (fieldMeta['default'] === false) {
                        catalog.available.push(fieldMeta);
                    } else {
                        catalog['default'].push(fieldMeta);
                        catalog.visible.push(fieldMeta);
                    }
                    catalog.options.push(_.extend({
                        selected: (fieldMeta['default'] !== false)
                    }, fieldMeta));
                }
            }, this);
        }, this);
        return catalog;
    },

    /**
     * Clean Up the Draft Modules Collection and dirtyVariables
     */
    cleanUpDraftModels: function() {
        // clean up the draft records and variables
        this.draftModels.reset();
        this.draftTimeperiod = undefined;
        this.draftUser = undefined;
    },

    saveWorksheet: function(isDraft) {
        // only run the save when the worksheet is visible and it has dirty records
        var saveObj = {
                totalToSave: 0,
                saveCount: 0,
                model: undefined,
                isDraft: isDraft,
                timeperiod: this.dirtyTimeperiod,
                userId: this.dirtyUser
            },
            ctx = this.context.parent || this.context;

        if (this.layout.isVisible()) {

            if(_.isUndefined(saveObj.userId)) {
                saveObj.userId = this.selectedUser;
            }
            saveObj.userId = saveObj.userId.id;
            /**
             * If the sheet is dirty, save the dirty rows. Else, if the save is for a commit, and we have
             * draft models (things saved as draft), we need to resave those as committed (version 1). If neither
             * of these conditions are true, then we need to fall through and signal that the save is complete so other
             * actions listening for this can continue.
             */
            if (this.isDirty()) {
                saveObj.totalToSave = this.dirtyModels.length;

                this.dirtyModels.each(function(model) {
                    saveObj.model = model;
                    this._worksheetSaveHelper(saveObj, ctx);

                    //add to draft structure so committing knows what to save as non-draft
                    /*if (isDraft == true) {
                        this.draftModels.add(model, {merge: true});
                    }*/
                }, this);

                this.cleanUpDirtyModels();
            /*} else if (!isDraft && this.draftModels.length > 0) {
                saveObj.totalToSave = this.draftModels.length;

                this.draftModels.each(function(model) {
                    saveObj.model = model;
                    this._worksheetSaveHelper(saveObj, ctx);
                }, this);

                //Need to clean up dirty models too as the save event above triggers a change event on the worksheet.
                this.cleanUpDirtyModels();
                this.cleanUpDraftModels();*/
            } else {
                if (isDraft) {
                    app.alert.show('success', {
                        level: 'success',
                        autoClose: true,
                        title: app.lang.get("LBL_FORECASTS_WIZARD_SUCCESS_TITLE", "Forecasts") + ":",
                        messages: [app.lang.get("LBL_FORECASTS_WORKSHEET_SAVE_DRAFT_SUCCESS", "Forecasts")]
                    });
                }
                ctx.trigger('forecasts:worksheet:saved', saveObj.totalToSave, 'mgr_worksheet', isDraft);
            }
        }

        return saveObj.totalToSave
    },

    /**
     * Helper function for worksheet save
     */
    _worksheetSaveHelper: function(saveObj, ctx) {
        saveObj.model.set({
            current_user: saveObj.userId || this.selectedUser.id
        }, {silent: true});

        saveObj.model.save({}, {success: _.bind(function() {
            saveObj.saveCount++;
            //if this is the last save, go ahead and trigger the callback;
            if (saveObj.totalToSave === saveObj.saveCount) {
                if (saveObj.isDraft) {
                    app.alert.show('success', {
                        level: 'success',
                        autoClose: true,
                        title: app.lang.get("LBL_FORECASTS_WIZARD_SUCCESS_TITLE", "Forecasts") + ":",
                        messages: [app.lang.get("LBL_FORECASTS_WORKSHEET_SAVE_DRAFT_SUCCESS", "Forecasts")]
                    });
                }
                ctx.trigger('forecasts:worksheet:saved', saveObj.totalToSave, 'mgr_worksheet', saveObj.isDraft);
            }
        }, this), silent: true, alerts: { 'success': false }});
    }
})
