({
    plugins: ['Dashlet'],
    className:'cases-summary-wrapper',

    initialize: function(o) {
        app.view.View.prototype.initialize.call(this, o);
        this.model.parentModel.on("change", this.loadData, this);
        this.render();
    },

    _render: function() {
        var self = this;
        if (!self.chartData) return;
        app.view.View.prototype._render.call(this);

        var chart = nv.models.pieChart()
                .x(function(d) { return d.key })
                .y(function(d) { return d.value })
                .margin({top:0,right:10,bottom:10,left:10})
                .showLabels(true)
                .showTitle(false)
                .showLegend(false)
                .donutLabelsOutside(true)
                .hole(self.totalCases)
                .colorData( 'class' )
                .colorFill( 'default' )
                .tooltip( function(key, x, y, e, graph) {
                    return '<p><b>' + key +' '+  parseInt(y) +'</b></p>'
                }).donut(true)
            ;
        d3.select('#casesSummaryPie svg')
            .datum(self.chartData)
            .transition().duration(500)
            .call(chart);

        nv.utils.windowResize(function(){chart.update();});
    },

    loadData: function (options) {
        var self = this;
        var oppID = this.model.get('account_id');
        if (oppID) {
            var accountBean = app.data.createBean('Accounts', {id: oppID});
        }
        var relatedCollection = app.data.createRelatedCollection(accountBean || this.model.parentModel,'cases');
        relatedCollection.fetch({
            relate:true,
            success: function(resultCollection) {
                self.chartCollection = resultCollection;
                self.closedCases = self.chartCollection.where({status:'Closed'});
                self.closedCases = self.closedCases.concat(self.chartCollection.where({status:'Rejected'}));
                self.closedCases = self.closedCases.concat(self.chartCollection.where({status:'Duplicate'}));
                self.openCases = self.chartCollection.models.length - self.closedCases.length;
                self.chartData = {
                    'data': [
                    ]
                };
                self.chartData.data.push({
                    key: 'Closed Cases',
                    class: 'nv-fill-green',
                    value: self.closedCases.length
                });
                self.chartData.data.push({
                    key: 'Open Cases',
                    class: 'nv-fill-red',
                    value: self.openCases
                });
                self.totalCases = self.chartCollection.models.length;
                self.processCases();
                self.render();
                self.addFavs();
            },
            complete: options ? options.complete : null
        });
    },

    addFavs: function() {
        var self = this;
        this.favFields = [];
        //loop over chartCollection
        _.each(self.tabData, function(tabGroup) {
            if(tabGroup.models && tabGroup.models.length >0) {
                _.each(tabGroup.models, function(model){
                    var field = app.view.createField({
                            def: {
                                type: "favorite"
                            },
                            model: model,
                            meta: {
                                view: "detail"
                            },
                            viewName: "detail",
                            view: self
                        }
                    );
                    field.setElement(self.$('.favTarget.[data-model-id="'+model.id+'"]'));
                    field.render();
                    self.favFields.push(field);
                });
            }
        });
    },

    processCases: function () {
        var status2css = {
            'Rejected':'label-success',
            'Closed':'label-success',
            'Duplicate':'label-success'
        };
        if (!this.chartCollection || this.chartCollection.models.length == 0) return;
        this.tabData = [];

        var stati = _.uniq(this.chartCollection.pluck('status'));

        _.each(stati, function(status, index){
            if (!status2css[status]) {
                this.tabData.push({
                    index: index,
                    status: status,
                    models: this.chartCollection.where({'status':status}),
                    cssClass: status2css[status] ? status2css[status] : 'label-important'
                });
            }
        }, this);

        this.tabClass = ['one','two','three','four','five'][this.tabData.length] || 'four';
    },

    _dispose: function() {
        _.each(this.favFields, function(field) {
            field._dispose();
        });
        this.model.parentModel.off("change", this.loadData, this);
        app.view.View.prototype._dispose.call(this);
    }

})
