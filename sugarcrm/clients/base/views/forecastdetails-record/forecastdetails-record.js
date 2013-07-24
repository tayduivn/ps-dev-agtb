({
    extendsFrom: 'ForecastdetailsView',

    /**
     * Holds previous totals for math
     */
    oldTotals: {},

    /**
     * Holds the logged-in user's ID
     */
    selectedUserId: '',

    /**
     * {@inheritdoc}
     */
    initialize: function(options) {
        this.selectedUserId = app.user.get('id');
        app.view.invokeParent(this, {type: 'view', name: 'forecastdetails', method: 'initialize', args:[options]});
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
     * {@inheritdoc}
     */
    bindDataChange: function() {
        if(this.meta.config) {
            return;
        }

        var ctx = this.context.parent || this.context;
        ctx = ctx.get('model');

        ctx.on('change:likely_case change:best_case change:worst_case change:amount' , this.processCases, this);

        ctx.on('sync', function(model) {
            this.fetchNewTPByDate(model.get('date_closed'));
        }, this);

        ctx.on('change:date_closed', function(model, date) {
            if(this.checkDateAgainstCurrentTP(date)) {
                this.fetchNewTPByDate(date)
            }
        }, this);
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
        if(!_.isUndefined(model) && (app.user.get('id') == model.get('assigned_user_id'))) {
            var data = _.clone(model.toJSON()),
                diff = 0,
                old = 0;

            if(this.currentModule == 'Opportunities') {
                // if amount is not undefined, push amount into likely_case
                data.likely_case = (!_.isUndefined(data.amount)) ? data.amount : data.likely_case;
            }

            // process numbers before parent calculateData
            if(_.has(model.changed, 'likely_case') || _.has(model.changed, 'amount')) {
                old = data.likely_case;
                diff = app.math.sub(data.likely_case, this.oldTotals.likely);
                data.likely_case = app.math.add(this.likelyTotal, diff);
                this.oldTotals.likely = old;
            } else {
                data.likely_case = this.likelyTotal;
            }

            if(_.has(model.changed, 'best_case')) {
                old = data.best_case;
                diff = app.math.sub(data.best_case, this.oldTotals.best);
                data.best_case = app.math.add(this.bestTotal, diff);
                this.oldTotals.best = old;
            } else {
                data.best_case = this.bestTotal;
            }

            if(_.has(model.changed, 'worst_case')) {
                old = data.worst_case;
                diff = app.math.sub(data.worst_case, this.oldTotals.worst);
                data.worst_case = app.math.add(this.worstTotal, diff);
                this.oldTotals.worst = old;
            } else {
                data.worst_case = this.worstTotal;
            }

            return this.calculateData(this.mapAllTheThings(data, true));
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
        this.oldTotals = {
            best: ctxModel.get('best_case'),
            likely: ctxModel.get('likely_case') || ctxModel.get('amount'),
            worst: ctxModel.get('worst_case')
        };

        if(!_.isUndefined(date)) {
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
