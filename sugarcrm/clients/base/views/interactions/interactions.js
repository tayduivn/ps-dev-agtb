({
    plugins: ['Dashlet', 'timeago'],
    events: {
        'click ul.nav-tabs > li > a' : 'contentSwitcher',
        'click button.interactions-list' : 'listSwitcher',
        'click a.more' : 'showMore'
    },

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this,options);

        this.collections = {
            'calls' : app.data.createBeanCollection('Calls'),
            'meetings' : app.data.createBeanCollection('Meetings'),
            'emailsSent' : app.data.createBeanCollection('Emails'),
            'emailsRecv' : app.data.createBeanCollection('Emails')
        };

        this.limit = 5;
        this.params = {
            'list' : 'all',
            'filter' : '7',
            'limit' : this.limit,
            'view' : 'calls'
        };
        this.model.on("change:filter_duration", this.filterSwitcher, this);
        if(this.model.parentModel) {
            this.model.parentModel.on("change", this.loadData, this);
        }
    },

    initDashlet: function(view) {
        var dashlet = JSON.parse(JSON.stringify(this.context.get("dashlet")));

        if(view === 'config') {
            app.view.views.RecordView.prototype._renderPanels.call(this, this.meta.panels);
        }
    },
    loadData: function(params) {
        if(this.disposed || !this.model.parentModel || !this.model.parentModel.get("id")) {
            return;
        }

        var self = this,
            url = app.api.buildURL(this.model.parentModel.module, "interactions", {"id": this.model.parentModel.get("id")}, null);

        var querystring = $.param(this.params);
        if (querystring.length > 0) {
            url += "?" + querystring;
        }

        app.api.call("read", url, null, {
            success: function(data) {
                if(self.disposed){
                    return;
                }
                _.each(data, function(el, name) {
                    self.collections[name].count = el.count;
                    self.collections[name].update(el.data);
                });
                self.collection = self.collections[self.params.view];
                self.render();
            },
            complete: params ? params.complete : null
        });
    },

    listSwitcher: function(e) {
        var $sender = this.$(e.currentTarget);
        this.params.list = $sender.val();
        this.params.limit = this.limit;
        this.layout.loadData();
    },

    filterSwitcher: function() {
        this.params.filter = this.model.get("filter_duration");
        this.params.limit = this.limit;
        this.layout.loadData();
    },

    showMore: function(e) {
        var $sender = this.$(e.currentTarget);
        e.preventDefault();
        this.params.limit += this.limit;
        this.layout.loadData();
    },

    contentSwitcher: function(e) {
        var $sender = this.$(e.currentTarget), self = this;
        _.each(this.collections, function(item, key) {
            if (key == $sender.attr('class')) {
                self.collection = item;
                self.params.view = key;
                self.params.limit = item.length;
            }
        });
        this.render();
    },
    _render: function() {
        app.view.View.prototype._render.call(this);
        this.$(".select2").select2({
            width: '100%'
        });
    },
    _dispose: function() {
        if(this.model.parentModel) {
            this.model.parentModel.off("change", null, this);
        }
        this.model.off("change", null, this);
        app.view.View.prototype._dispose.call(this);
    }
})
