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

    totals: {},

    selectedUser: {},

    selectedTimeperiod: '',

    initialize: function(options) {
        this.plugins.push('cte-tabbing');
        this.plugins.push('dirty-collection');
        app.view.views.RecordlistView.prototype.initialize.call(this, options);
        this.selectedUser = this.context.get('selectedUser') || this.context.parent.get('selectedUser') || app.user.toJSON();
        this.context.set('skipFetch', !(this.selectedUser.showOpps || !this.selectedUser.isManager)); // if user is a manager, skip the initial fetch
        this.collection.sync = _.bind(this.sync, this);
    },

    bindDataChange: function() {
        // these are handlers that we only want to run when the parent module is forecasts
        if (!_.isUndefined(this.context.parent) && !_.isUndefined(this.context.parent.get('model'))) {
            if (this.context.parent.get('model').module == 'Forecasts') {
                this.before('render', function() {
                    // set the defaults to make it act like a manager so it doesn't actually render till the selected
                    // user is updated
                    var showOpps = (_.isUndefined(this.selectedUser.showOpps)) ? false : this.selectedUser.showOpps,
                        isManager = (_.isUndefined(this.selectedUser.isManager)) ? true : this.selectedUser.isManager;

                    if (!(showOpps || !isManager) && this.layout.isVisible()) {
                        console.log('beforeRender Hide');
                        this.layout.hide();
                    } else if ((showOpps || !isManager) && !this.layout.isVisible()) {
                        console.log('beforeRender Show');
                        this.layout.show();
                    }

                    console.log('Rep beforeRender will return ', (showOpps || !isManager));

                    return (showOpps || !isManager);
                });
                this.on('render', function() {
                    var user = this.context.parent.get('selectedUser') || app.user.toJSON()
                    if (user.showOpps || !user.isManager) {
                        if (!this.layout.isVisible()) {
                            console.log('Rep Show', user);
                            this.layout.show();
                        }

                        if (this.collection.length == 0) {
                            console.log('render rep worksheet empty row');
                            var tpl = app.template.getView('recordlist.noresults', this.module);
                            this.$el.find('tbody').html(tpl(this));
                        }

                        // insert the footer
                        if (!_.isEmpty(this.totals) && this.layout.isVisible()) {
                            console.log('render rep footer');
                            var tpl = app.template.getView('recordlist.totals', this.module);
                            this.$el.find('tbody').after(tpl(this));
                        }
                    } else {
                        if (this.layout.isVisible()) {
                            console.log('Rep Hide', user);
                            this.layout.hide();
                        }
                    }
                }, this);

                this.context.parent.on('forecasts:worksheet:totals', function(totals, type) {
                    if (type == "rep" && this.layout.isVisible()) {
                        console.log('update rep footer');
                        var tpl = app.template.getView('recordlist.totals', this.module);
                        this.$el.find('tfoot').remove();
                        this.$el.find('tbody').after(tpl(this));
                    }
                }, this);

                this.context.parent.on('change:selectedTimePeriod', function(model, changed) {
                    this.selectedTimeperiod = changed;
                    if (this.layout.isVisible()) {
                        this.collection.fetch();
                    }
                }, this);

                this.context.parent.on('change:selectedUser', function(model, changed) {
                    var doFetch = false;
                    console.log('Rep SelectedUser Change');
                    if (this.selectedUser.id != changed.id) {
                        // user changed. make sure it's not a manager view before we say fetch or not
                        doFetch = (changed.showOpps || !changed.isManager);
                    }
                    // if we are already not going to fetch, check to see if the new user is showingOpps or is not
                    // a manager, then we want to fetch
                    if (!doFetch && (changed.showOpps || !changed.isManager)) {
                        doFetch = true;
                    }
                    this.selectedUser = changed;

                    if (doFetch) {
                        this.collection.fetch();
                    } else {
                        if ((!this.selectedUser.showOpps && this.selectedUser.isManager) && this.layout.isVisible()) {
                            // we need to hide
                            console.log('rep fetch hide');
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
                                this.context.parent.trigger('forecasts:worksheet:commit', 'mgr', this.getCommitTotals())
                        }, this);
                        this.saveWorksheet(false);
                        this.getCommitTotals();
                    }
                }, this);

                this.context.parent.on('change:currentForecastCommitDate', function(context, changed) {
                    if (this.layout.isVisible()) {
                        // check to see if anything in the collection is a draft, if it is, then send an event
                        // to notify the commit button to enable
                        this.collection.find(function(item) {
                            if (item.get('date_modified') > changed) {
                                this.context.parent.trigger('forecast:worksheet:needs_commit', 'rep');
                                return true;
                            }
                            return false;
                        }, this);
                    }
                }, this);
            }
        }

        this.collection.on('reset change', function() {
            this.calculateTotals();
        }, this);

        if (!_.isUndefined(this.dirtyModels)) {
            this.dirtyModels.on('add', function() {
                var ctx = this.context.parent || this.context
                ctx.trigger('forecast:worksheet:dirty', 'rep');
            }, this);
        }

        app.view.views.RecordlistView.prototype.bindDataChange.call(this);
    },

    /**
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
                        "timeperiod_id": this.dirtyTimeperiod || this.timePeriod,
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
                            ctx.trigger('forecasts:worksheet:saved', totalToSave, 'rep_worksheet', isDraft);
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
                ctx.trigger('forecasts:worksheet:saved', totalToSave, 'rep_worksheet', isDraft);
            }
        }

        return totalToSave
    },

    calculateTotals: function() {
        var fields = _.filter(this._fields.visible, function(field) {
                return field.type === 'currency';
            }),
            fieldNames = [];

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

        //Get the excluded_sales_stage property.  Default to empty array if not set
        //var sales_stage_won_setting = app.metadata.getModule('Forecasts', 'config').sales_stage_won || [];
        //var sales_stage_lost_setting = app.metadata.getModule('Forecasts', 'config').sales_stage_lost || [];

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
            ctx.trigger('forecasts:worksheet:totals', this.totals, 'rep');
        }
    },

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
                'timeperiod_id': this.selectedTimeperiod,
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
    }
})
