({
    plugins: ['Dashlet', 'GridBuilder'],
    events: {
        'change select[name=filter_duration]': 'filterChanged'
    },
    initDashlet: function(viewName) {
        this.viewName =viewName;
        if(viewName !== "config") {
            this.collection.on("reset", this.render, this);
        } else {
            // TODO: Calling "across controllers" considered harmful .. please consider using a plugin instead.
            app.view.invokeParent(this, {type: 'view', name: 'record', method: '_buildGridsFromPanelsMetadata', args: [this.meta.panels]});
        }
    },
    _mapping: {
        meetings: {
            icon: 'icon-comments',
            label: 'LBL_MOST_MEETING_HELD'
        },
        inbound_emails: {
            icon: 'icon-envelope',
            label: 'LBL_MOST_EMAILS_RECEIVED'
        },
        outbound_emails: {
            icon: 'icon-envelope-alt',
            label: 'LBL_MOST_EMAILS_SENT'
        },
        calls: {
            icon: 'icon-phone',
            label: 'LBL_MOST_CALLS_MADE'
        }
    },
    loadData: function(params) {
        if(this.viewName === "config") {
            return;
        }
        var url = app.api.buildURL('mostactiveusers', null, null, {days: this.model.get("filter_duration")}),
            self = this;
        app.api.call("read", url, null, {
            success: function(data) {
                if(self.disposed) {
                    return;
                }
                var models = [];
                _.each(data, function(attributes, module){
                    if(_.isEmpty(attributes)) {
                        return;
                    }
                    var model = new app.Bean(_.extend({
                        id: _.uniqueId('aui')
                    }, attributes));
                    model.module = module;
                    model.set("name", model.get("first_name") + ' ' + model.get("last_name"));
                    model.set("icon", self._mapping[module]['icon']);
                    var template = Handlebars.compile(app.lang.get(self._mapping[module]['label'], self.module));
                    model.set("label", template({
                        count: model.get("count")
                    }));
                    model.set("pictureUrl", app.api.buildFileURL({
                        module: "Users",
                        id: model.get("user_id"),
                        field: "picture"
                    }));
                    models.push(model);
                }, this);
                self.collection.reset(models);
            },
            complete: params ? params.complete : null
        });
    },
    filterChanged: function(evt) {
        this.loadData();
    },

    _dispose: function() {
        if(this.collection) {
            this.collection.off("reset", null, this);
        }
        app.view.View.prototype._dispose.call(this);
    }
})
