({
    plugins: ['Dashlet'],

    events: {
        'click .interactions-chart': 'switchChart',
        'click .filter-list .dropdown-menu a': 'changeFilter'
    },

    className: 'thumbnail widget',

    ui: {
        colors: {
            default: '#085f94',
            calls: '#cce8f6',
            emailsSent: '#0092d1',
            emailsRecv: '#085f94',
            meetings: '#0d3d66'
        },
        order: {
            calls: app.lang.getAppString('LBL_CALLS'),
            emailsSent: app.lang.getAppString('LBL_EMAILS') + ' (' + app.lang.getAppString('LBL_EMAIL_SENT') + ')',
            emailsRecv: app.lang.getAppString('LBL_EMAILS') + ' (' + app.lang.getAppString('LBL_EMAIL_RECV') + ')',
            meetings: app.lang.getAppString('LBL_MEETINGS')
        }
    },

    filter: [
        {val: '7', l: app.lang.getAppString('LBL_LAST_7_DAYS')},
        {val: '30', l: app.lang.getAppString('LBL_LAST_30_DAYS')},
        {val: '90', l: app.lang.getAppString('LBL_LAST_QUARTER')},
        {val: 'favorites', l: app.lang.getAppString('LBL_FAVORITES')},
        {val: 'custom', l: app.lang.getAppString('LBL_MY_CUSTOM_FILTER')}
    ],

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this,options);

        this.dataset = {};
        this.params  = {
            list: 'all',
            filter: '30',
            limit: 0
        };

        this.on("data-changed", function () {
            this.updateChart();
        });
    },

    updateChart: function () {
        var self = this;

        nv.addGraph(function() {
            var canvas = self.$el.find("svg"),
                chart = nv.models.multiBarChart()
                    .tooltips(false)
                    .showControls(false)
                    .reduceXTicks(false)
                    .noData(app.lang.getAppString('LBL_CHART_NO_DATA'))
                    .showLegend(self.params.list == "all")
                    .stacked(true);

            canvas.children().remove();

            chart.xAxis
                .tickFormat(d3.format(',r'));
            chart.yAxis
                .tickFormat(d3.format(',i'));

            d3.select(canvas[0])
                .datum(self.dataset)
                .transition()
                .duration(500)
                .call(chart);

            return chart;
        });
    },

    evaluateGroupResult: function (data) {
        var self = this,
            // data is a hash map of collections {calls: {count: 0, data: []}, meetings: {}, etc}
            users = _.chain(data)
                // extract raw collections
                .pluck('data')
                // convert hashmap to array
                .toArray()
                // and join all collection in a single array
                .flatten()
                // get user data for every item
                .map(function (record) { return _.pick(record, 'assigned_user_id', 'assigned_user_name') })
                // leave only unique users
                .uniq(function(o) { return o.assigned_user_id })
                // and bring to known order - this will be chart labels
                .sortBy(function (o) { return o.assigned_user_id; })
                .value(),
            // give every user default number of interactions (zero)
            countById = _.object(_.pluck(users, 'assigned_user_id'), _.map(users, function(){return 0;})),
            // generate chart dataset:
            // every collection is grouped by user id
            preparedData = _.chain(data).map(function(c, k) {
                    return {
                        key: self.ui.order[k],
                        type: 'bar',
                        color: self.ui.colors[k],
                        values: _.chain(c.data)
                            // count item for each user: {<user_id>: <number of items>}
                            .countBy(function (record) { return record.assigned_user_id; })
                            // some users can have no items in some collections, they are assigned by default value
                            .defaults(countById)
                            // convert users' hash map to chart format
                            .map(function(count, uid){ return {x: uid, y: count} })
                            // sort by id to bring to same order as labels
                            .sortBy(function (o) { return o.x; })
                            .value()
                    };
                }).sortBy(function (o) {
                    return _.toArray(self.ui.order).indexOf(o.key);
                }).value(),
            userNames = _.map(users, function (u) { return {l:u.assigned_user_name}; });

        this.dataset = {data: preparedData, properties: {labels: userNames}};
    },

    evaluatePersonalResult: function (data) {
        var total = _.reduce(data, function (total, collection) {
                return total + collection.count;
            }, 0),
            preparedData = [{type: 'bar', color: this.ui.colors.default, values: []}],
            labels = _.toArray(this.ui.order);

        if (total)
        {
            _.each(this.ui.order, function (l, k) {
                preparedData[0].values.push({y: parseInt(data[k].count), x: labels.indexOf(l)});
            });
        }

        this.dataset = {data: preparedData, properties: {labels:_.map(labels, function (label) { return {l: label}; })}};
    },

    loadData: function(options) {
        var self = this,
            params = _.extend({"id": app.controller.context.get("model").id}, this.params),
            url = app.api.buildURL(this.model.parentModel.module,
                                   "interactions",
                                   params);

        app.api.call("read", url, null, 
                     {
                         success: function(data) {
                             if (self.params.list == "all") {
                                 self.evaluateGroupResult(data);
                             } else {
                                 self.evaluatePersonalResult(data);
                             }
                             self.trigger("data-changed");
                         },
                         complete: (options) ? options.complete : null
                     });
    },

    changeFilter: function (e) {
        if (this.params.filter == $(e.currentTarget).data('value')) return;

        this.params.filter = $(e.currentTarget).data('value');

        $('.filter-list .dropdown-toggle span').text($(e.currentTarget).text()  );

        this.loadData();
    },

    switchChart: function (e) {
        if (this.params.list == e.currentTarget.value) return;

        this.params.list = e.currentTarget.value;

        this.render();
        this.loadData();
    }
})
