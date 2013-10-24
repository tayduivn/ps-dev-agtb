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
//FILE SUGARCRM flav=ent ONLY
({
    extendsFrom: 'ForecastdetailsView',

    /**
     * Holds the logged-in user's ID
     */
    selectedUserId: '',

    /**
     * Holds a reference to the RevenueLineItems subpanel collection
     */
    rliCollection: undefined,

    /**
     * An array of the RLI ids that go into Closed Won
     */
    closedWonIds: [],

    /**
     * An array of the RLI ids that are included in likely/best/worst values
     */
    includedIds: [],

    /**
     * Holds Sales Stage values that get added to Closed Won amounts
     */
    salesStageWon: [],

    /**
     * Array of commit_stages that are included in totals
     */
    commitStagesInIncludedTotal: [],

    /**
     * {@inheritdoc}
     */
    initialize: function(options) {
        this.selectedUserId = app.user.get('id');
        app.view.invokeParent(this, {type: 'view', name: 'forecastdetails', method: 'initialize', args:[options]});

        this.salesStageWon = app.metadata.getModule("Forecasts", "config").sales_stage_won;
        var forecastRanges = app.metadata.getModule('Forecasts', 'config').forecast_ranges;

        if (forecastRanges == 'show_custom_buckets') {
            var ranges = app.metadata.getModule('Forecasts', 'config')[forecastRanges + '_ranges'];
            _.each(ranges, function(value, key) {
                if (!_.isUndefined(value.in_included_total) && value.in_included_total) {
                    this.commitStagesInIncludedTotal.push(key);
                }
            })
        } else {
            this.commitStagesInIncludedTotal.push('include');
        }
    },

    /**
     * Builds widget url
     * @override
     * @return {String} url to call
     */
    getProjectedURL: function() {
        var method = this.shouldRollup ? "progressManager" : "progressRep",
            url = 'Forecasts/' + this.model.get('selectedTimePeriod') + '/' + method + '/' + this.selectedUserId;

        return app.api.buildURL(url, 'create');
    },

    /**
     * {@inheritDoc}
     */
    renderSubDetails: function() {
        // clear the footer class
        var subEl = this.$el.find('.forecast-details');
        if(subEl && subEl.hasClass('block-footer')) {
            subEl.removeClass('block-footer');
        }

        if(this.currentModule != 'Opportunities'
            || (this.currentModule == 'Opportunities' && this.model.get('selectedTimePeriod'))) {
            app.view.invokeParent(this, {type: 'view', name: 'forecastdetails', method: 'renderSubDetails'});
        } else {
            subEl.addClass('block-footer');
            subEl.html(app.lang.get('LBL_NO_DATA_AVAILABLE'));
        }
    },

    /**
     * {@inheritdoc}
     */
    bindDataChange: function() {
        if(this.meta.config) {
            return;
        }

        // reset closedWonIds
        this.closedWonIds = [];
        this.includedIds = [];

        var ctx = this.context.parent || this.context,
            ctxMdl = ctx.get('model');

        ctxMdl.on('sync', function(model) {
            this.fetchNewTPByDate(model.get('date_closed'));
        }, this);

        if(this.currentModule == 'Opportunities') {

            this.rliCollection = app.utils.getSubpanelCollection(ctx, 'RevenueLineItems');

            if(this.rliCollection) {
                this.rliCollection.on('reset', this.processRLICollection, this);

                this.rliCollection.on('change:likely_case change:best_case change:worst_case change:amount', this.processCases, this);

                this.rliCollection.on('change:sales_stage', this.processSalesStage, this);

                this.rliCollection.on('change:commit_stage', this.processCommitStage, this);

                this.rliCollection.on('change:date_closed', function(model, date) {
                    if(this.checkDateAgainstCurrentTP(date)) {
                        this.fetchNewTPByDate(date)
                    }
                }, this);

                // Process RLICollection
                this.processRLICollection();
            }
        } else if(this.currentModule == 'RevenueLineItems') {
            // RLI only listeners

            // set up closedWonIds when we change dashboards
            this.initClosedWonIds(ctxMdl);

            ctxMdl.on('change:likely_case change:best_case change:worst_case change:amount', this.processCases, this);

            ctxMdl.on('change:sales_stage', this.processSalesStage, this);

            ctxMdl.on('change:commit_stage', this.processCommitStage, this);

            ctxMdl.on('change:date_closed', function(model, date) {
                if(this.checkDateAgainstCurrentTP(date)) {
                    this.fetchNewTPByDate(date)
                }
            }, this);

            ctxMdl.on('sync', function(model) {
                // updates our lhsData when the user saves the model
                if(_.has(ctxMdl.attributes, 'lhsData')) {
                    ctxMdl.set({
                        lhsData: {
                            best: model.get('best_case'),
                            likely: model.get('likely_case'),
                            worst: model.get('worst_case')
                        }
                    });
                }
            }, this);

            // Using LHS Model to store the initial values of the LHS model so we don't have
            // to ping the server every dashlet load for the true original DB values of the LHS model
            if(!_.has(ctxMdl.attributes, 'lhsData')) {
                ctxMdl.set({
                    lhsData: {
                        best: ctxMdl.get('best_case'),
                        likely: ctxMdl.get('likely_case'),
                        worst: ctxMdl.get('worst_case')
                    }
                });
            }

            if(_.contains(this.closedWonIds, ctxMdl.get('id'))) {
                this.processSalesStage(ctxMdl);
            }
        }
    },

    /**
     * {@inheritdoc}
     *
     * @override just calls calculateData on it's own instead of going back to the parent
     */
    handleNewDataFromServer: function(data) {
        // since the user might add this dashlet after they have changed the RLI model, but before they saved it
        // we have to check and make sure that we're accounting for any changes in the dashlet totals that come
        // from the server
        if(this.currentModule == 'RevenueLineItems' && this.context) {
            var mdl = this.context.parent.get('model') || this.context.get('model'),
                lhsData = mdl.get('lhsData');
            if(lhsData.likely != mdl.get('likely_case')) {
                data.amount = data.amount - (lhsData.likely - mdl.get('likely_case'));
            }
            if(lhsData.best != mdl.get('best_case')) {
                data.best_case = data.best_case - (lhsData.best - mdl.get('best_case'));
            }
            if(lhsData.worst != mdl.get('worst_case')) {
                data.worst_case = data.worst_case - (lhsData.worst - mdl.get('worst_case'));
            }
        }
        this.calculateData(this.mapAllTheThings(data, false));
    },

    /**
     * Processes this.rliCollection.models to determine which models IDs should be
     * saved into the closedWonIds array
     */
    processRLICollection: function(collection) {
        this.oldTotals.models = new Backbone.Model();
        _.each(this.rliCollection.models, function(model) {
            // save all the initial likely values
            this.oldTotals.models.set(model.get('id'), {
                likely: model.get('likely_case'),
                best: model.get('best_case'),
                worst: model.get('worst_case')
            });
            this.initClosedWonIds(model);
        }, this);

        if(collection) {
            // if this is coming from the rliCollection reset, fetch server data
            this.loadData();
        }
    },

    /**
     * {@inheritdoc}
     */
    unbindData: function() {
        if(this.context.parent) {
            this.context.parent.off(null, null, this);
            if(this.context.parent.get('model')) {
                this.context.parent.get('model').off(null, null, this);
            }
        }
        if(this.context) {
            this.context.off(null, null, this);
            if(this.context.get('model')) {
                this.context.get('model').off(null, null, this);
            }
        }

        if(this.currentModule == 'Opportunities' && this.rliCollection) {
            this.rliCollection.off(null, null, this);
            this.rliCollection = undefined;
        }

        app.view.View.prototype.unbindData.call(this);
    },

    /**
     * Handles when likely/best/worst case changes, processes numbers and does math before sending
     * to calculateTotals
     *
     * @param {Backbone.Model} model the RLI/Opp model
     */
    processCases: function(model) {
        // model is undefined when users change currency symbols,
        // it throws a change:best_case but there's no model
        if(!_.isUndefined(model) && (app.user.get('id') == model.get('assigned_user_id'))
            && _.contains(this.includedIds, model.get('id'))) {
            var data = _.clone(model.toJSON()),
                diff = 0,
                old = 0;

            var totals = {};
            if(this.currentModule == 'Opportunities') {
                // if amount is not undefined, push amount into likely_case
                data.likely_case = (!_.isUndefined(data.amount)) ? data.amount : data.likely_case;
                totals = this.getOldTotalFromCollectionById(model.get('id'));
            } else {
                totals = this.oldTotals;
            }

            // process numbers before parent calculateData
            if(_.has(model.changed, 'likely_case') || _.has(model.changed, 'amount')) {
                old = data.likely_case;
                diff = app.math.sub(data.likely_case, totals.likely);
                data.likely_case = app.math.add(this.likelyTotal, diff);
                totals.likely = old;
            } else {
                data.likely_case = this.likelyTotal;
            }

            if(_.has(model.changed, 'best_case')) {
                old = data.best_case;
                diff = app.math.sub(data.best_case, totals.best);
                data.best_case = app.math.add(this.bestTotal, diff);
                totals.best = old;
            } else {
                data.best_case = this.bestTotal;
            }

            if(_.has(model.changed, 'worst_case')) {
                old = data.worst_case;
                diff = app.math.sub(data.worst_case, totals.worst);
                data.worst_case = app.math.add(this.worstTotal, diff);
                totals.worst = old;
            } else {
                data.worst_case = this.worstTotal;
            }

            // set oldTotals back
            if(this.currentModule == 'Opportunities') {
                this.setOldTotalFromCollectionById(model.get('id'), totals);
            } else {
                this.oldTotals = totals;
            }

            return this.calculateData(this.mapAllTheThings(data, true));
        }
    },

    /**
     * Checks a model to see if it should be added to closedWonIds
     *
     * @param {Backbone.Model} model
     */
    initClosedWonIds: function(model) {
        var ss = model.get('sales_stage'),
            cs = model.get('commit_stage'),
            id = model.get('id');

        // If this model's sales_stage and commit_stage both are included in Closed Won totals
        // and the id isnt already in closedWonIds
        if(_.contains(this.salesStageWon, ss)
            && _.contains(this.commitStagesInIncludedTotal, cs)
            && !_.contains(this.closedWonIds, id)) {
            this.closedWonIds.push(id);
        }

        // If this model's commit_stage is included in included totals
        // and the id isnt already in includedIds
        if(_.contains(this.commitStagesInIncludedTotal, cs) && !_.contains(this.includedIds, id)) {
            this.includedIds.push(id);
        }
    },

    /**
     * Process model changes when sales_stage is changed
     * @param model
     */
    processSalesStage: function(model) {
        var shouldBeIncluded = false,
            updatedData = false,
            ss = model.get('sales_stage'),
            cs = model.get('commit_stage'),
            id = model.get('id');

        if(_.contains(this.salesStageWon, ss) && _.contains(this.commitStagesInIncludedTotal, cs)) {
            shouldBeIncluded = true;
        }

        // If the ID was already included in the totals, and now should not be
        if(_.contains(this.closedWonIds, id) && !shouldBeIncluded) {
            // remove the model's ID from the array
            this.closedWonIds = _.without(this.closedWonIds, id);

            // remove this model's likely from the closed won amount
            this.serverData.set({closed_amount: app.math.sub(this.serverData.get('closed_amount'), model.get('likely_case'))});

            updatedData = true;
        } else if(!_.contains(this.closedWonIds, id) && shouldBeIncluded) {
            // model needs to be included in closed_amount
            this.closedWonIds.push(id);

            // add likely amount to closed won
            this.serverData.set({closed_amount: app.math.add(this.serverData.get('closed_amount'), model.get('likely_case'))});

            updatedData = true;
        }

        if(updatedData) {
            // update the calculations
            this.calculateData(this.serverData.toJSON());
        }
    },

    /**
     * Processes a model to see if it should be add/subtracted from likely/best/worst totals
     * based on it's commit_stage
     *
     * @param model
     */
    processCommitStage: function(model) {
        var shouldBeIncluded = false,
            updatedData = false,
            cs = model.get('commit_stage'),
            id = model.get('id');

        if(_.contains(this.commitStagesInIncludedTotal, cs)) {
            shouldBeIncluded = true;
        }

        // If the ID was already included in the totals, and now should not be
        if(_.contains(this.includedIds, id) && !shouldBeIncluded) {
            // remove the model's ID from the array
            this.includedIds = _.without(this.includedIds, id);

            // remove amounts from best/likely/worst
            this.serverData.set({
                likely: app.math.sub(this.serverData.get('likely'), model.get('likely_case')),
                best: app.math.sub(this.serverData.get('best'), model.get('best_case')),
                worst: app.math.sub(this.serverData.get('worst'), model.get('worst_case')),
            });

            updatedData = true;
        } else if(!_.contains(this.includedIds, id) && shouldBeIncluded) {
            // model needs to be included in closed_amount
            this.includedIds.push(id);

            // add amounts to best/likely/worst
            this.serverData.set({
                likely: app.math.add(this.serverData.get('likely'), model.get('likely_case')),
                best: app.math.add(this.serverData.get('best'), model.get('best_case')),
                worst: app.math.add(this.serverData.get('worst'), model.get('worst_case')),
            });
            updatedData = true;
        }

        if(updatedData) {
            // update the calculations
            this.calculateData(this.serverData.toJSON());
        }
    },

    /**
     * Given a date, this function makes a call to TimePeriods/<date> to get the whole timeperiod bean
     *
     * @param {String} date
     */
    fetchNewTPByDate: function(date) {
        app.api.call('GET', app.api.buildURL('TimePeriods/' + date), null, {
            success: _.bind(function(o) {
                // Make sure the model is here when we get back and this isn't mid-pageload or anything
                if(this.model) {
                    this.model.set({selectedTimePeriod: o.id}, {silent: true});
                    this.loadData();
                }
            }, this)
        });
    },

    /**
     * Called during initialize to fetch any relevant data
     *
     * @override
     * @param options
     */
    getInitData: function(options) {
        var ctx = this.context.parent || this.context,
            ctxModel = ctx.get('model'),
            date = ctxModel.get('date_closed');

        // set selectedUser id for progress endpoint param
        this.selectedUser.id = ctxModel.get('assigned_user_id');

        // set old totals in case they change
        this.oldTotals = _.extend(this.oldTotals, {
            best: ctxModel.get('best_case'),
            likely: ctxModel.get('likely_case') || ctxModel.get('amount'),
            worst: ctxModel.get('worst_case')
        });

        if(!_.isEmpty(date)) {
            // get the current timeperiod
            app.api.call('GET', app.api.buildURL('TimePeriods/' + date), null, {
                success: _.bind(function(o) {
                    if(this.model) {
                        // Make sure the model is here when we get back and this isn't mid-pageload or anything
                        this.initDataLoaded = true;
                        this.model.set({selectedTimePeriod: o.id}, {silent: true});
                        this.loadData();
                    }
                }, this),
                complete: options ? options.complete : null
            });
        } else {
            // this model doesnt have a selectedTimePeriod yet, so use the current date
            var d = new Date(),
                month = (d.getUTCMonth().toString().length == 1) ? '0' + d.getUTCMonth() : d.getUTCMonth(),
                day = (d.getUTCDate().toString().length == 1) ? '0' + d.getUTCDate() : d.getUTCDate()
            date = d.getFullYear() + '-' + month + '-' + day;
            // set initDataLoaded to true so we don't infiniteloop
            this.initDataLoaded = true;
            this.fetchNewTPByDate(date);
        }
    },

    /**
     * Checks a given date from the datepicker against the start/end timestamps of the current
     * timeperiod to see if the user selected a date that needs new data
     *
     * @param date
     * @returns {boolean} true if a new timeperiod should be fetched from server
     */
    checkDateAgainstCurrentTP: function(date) {
        var fetchNewTP = false;
        date = new Date(date).getTime();

        // if there isnt a currentTimeperiod yet, or date is AFTER the end of the current timeperiod or BEFORE the start
        if(_.isUndefined(this.currentTimeperiod)
            || date >= this.currentTimeperiod.end_date_timestamp
            || date <= this.currentTimeperiod.start_date_timestamp) {
            fetchNewTP = true;
        }

        return fetchNewTP;
    }
})
